<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\ORM\TableRegistry;

class AdminController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('FormProtection', [
            'unlockedActions' => ['updateAllPermissions', 'createGroup', 'updateGroupPermissions', 'deleteGroup', 'deleteUser', 'updateUsername','editUser','getUserData']
        ]);
    }

    private function isAdmin(): bool
    {
        $username = $this->request->getSession()->read('Auth.User.username');
        return $username === 'farah';
    }

    private function getCurrentUser(): string
    {
        return $this->request->getSession()->read('Auth.User.username') ?? 'Guest';
    }

    private function getGroupMembers(): array
    {
        $username = $this->getCurrentUser();

        if ($username === 'Guest') {
            return [];
        }

        $groupMembers = [];

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $groups = $groupTable->find('all')->toArray();

            foreach ($groups as $group) {
                $isUserInGroup = false;
                $currentGroupMembers = [];

                for ($i = 1; $i <= 100; $i++) {
                    $userColumn = "user_{$i}";
                    if (!empty($group->$userColumn)) {
                        $currentGroupMembers[] = $group->$userColumn;
                        if ($group->$userColumn === $username) {
                            $isUserInGroup = true;
                        }
                    }
                }

                if ($isUserInGroup) {
                    $groupMembers = array_merge($groupMembers, $currentGroupMembers);
                }
            }

            $groupMembers = array_unique($groupMembers);
            $groupMembers = array_filter($groupMembers, function($member) use ($username) {
                return $member !== $username;
            });

        } catch (\Exception $e) {
        }

        return array_values($groupMembers);
    }

    private function getUserPermissions(): array
    {
        $username = $this->getCurrentUser();

        if ($username === 'Guest') {
            return [
                'can_read' => 0,
                'can_delete' => 0,
                'can_download' => 0
            ];
        }

        if ($this->isAdmin()) {
            return [
                'can_read' => 1,
                'can_delete' => 1,
                'can_download' => 1
            ];
        }

        try {
            $prTable = TableRegistry::getTableLocator()->get('Pr');
            $user = $prTable->find()
                ->where(['username' => $username])
                ->first();

            if ($user) {
                return [
                    'can_read' => (int)$user->can_read,
                    'can_delete' => (int)$user->can_delete,
                    'can_download' => (int)$user->can_download
                ];
            }
        } catch (\Exception $e) {
        }

        return [
            'can_read' => 0,
            'can_delete' => 0,
            'can_download' => 0
        ];
    }

    private function canAccessUserContent($contentUsername): bool
    {
        $currentUsername = $this->getCurrentUser();

        if ($contentUsername === $currentUsername) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        $groupMembers = $this->getGroupMembers();
        return in_array($contentUsername, $groupMembers);
    }

    public function index(): ?Response
    {
        $username = $this->getCurrentUser();

        if ($username === 'Guest') {
            $this->Flash->error('Veuillez vous connecter pour accéder à cette page.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé. Seuls les administrateurs peuvent accéder à cette page.');
            return $this->redirect(['controller' => 'Dossier', 'action' => 'index']);
        }

        $prTable = TableRegistry::getTableLocator()->get('Pr');
        $users = $prTable->find('all')->toArray();

        $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
        $groups = $groupTable->find('all')->toArray();

        $dossierTable = TableRegistry::getTableLocator()->get('Dossier');
        $fichierTable = TableRegistry::getTableLocator()->get('Fichier');

        $permissions = $this->getUserPermissions();
        $hasGlobalRead = $this->isAdmin() || $permissions['can_read'] == 1;
        $groupMembers = $this->getGroupMembers();
        $accessibleUsers = array_merge([$username], $groupMembers);

        if ($hasGlobalRead) {
            $totalFolders = $dossierTable->find()->count();
            $totalFiles = $fichierTable->find()->count();
        } else {
            $totalFolders = $dossierTable->find()
                ->where(['username IN' => $accessibleUsers])
                ->count();
            $totalFiles = $fichierTable->find()
                ->where(['username IN' => $accessibleUsers])
                ->count();
        }

        $activeUsers = count($users);
        $totalGroups = count($groups);

        $this->set(compact('username', 'users', 'groups', 'totalFolders', 'totalFiles', 'activeUsers', 'totalGroups', 'groupMembers'));

        $usersJson = json_encode(array_map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->username,
                'email' => $user->email ?? $user->username . '@example.com',
                'role' => ($user->username === 'farah') ? 'Administrateur' : 'Utilisateur',
                'permissions' => [
                    'can_read' => (bool)$user->can_read,
                    'can_delete' => (bool)$user->can_delete,
                    'can_download' => (bool)$user->can_download
                ],
                'accessible' => $this->canAccessUserContent($user->username)
            ];
        }, $users));

        $groupsJson = json_encode(array_map(function($group) {
            $members = [];
            for ($i = 1; $i <= 100; $i++) {
                $userColumn = "user_{$i}";
                $permissionColumn = "user_{$i}_permissions";
                if (!empty($group->$userColumn)) {
                    $permissions = json_decode($group->$permissionColumn, true) ?? [
                        'can_read' => false,
                        'can_delete' => false,
                        'can_download' => false
                    ];
                    $members[] = [
                        'name' => $group->$userColumn,
                        'permissions' => $permissions,
                        'accessible' => $this->canAccessUserContent($group->$userColumn)
                    ];
                }
            }
            return [
                'id' => $group->id,
                'name' => $group->name,
                'members' => $members
            ];
        }, $groups));

        $this->set(compact('usersJson', 'groupsJson'));

        return null;
    }

    public function deleteUser(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = $this->request->getData('user_id');

        try {
            $prTable = TableRegistry::getTableLocator()->get('Pr');
            $user = $prTable->get($userId);

            // Prevent admin from deleting themselves
            if ($user->username === 'farah') {
                $this->Flash->error('Vous ne pouvez pas supprimer le compte administrateur.');
                return $this->redirect(['action' => 'index']);
            }

            $username = $user->username;

            // Delete user from all groups
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $groups = $groupTable->find('all')->toArray();

            foreach ($groups as $group) {
                for ($i = 1; $i <= 100; $i++) {
                    $userColumn = "user_{$i}";
                    if (!empty($group->$userColumn) && $group->$userColumn === $username) {
                        $group->$userColumn = null;
                        $permissionColumn = "user_{$i}_permissions";
                        $group->$permissionColumn = null;
                    }
                }
                $groupTable->save($group);
            }

            // Delete user's files and folders
            $dossierTable = TableRegistry::getTableLocator()->get('Dossier');
            $fichierTable = TableRegistry::getTableLocator()->get('Fichier');

            $dossierTable->deleteAll(['username' => $username]);
            $fichierTable->deleteAll(['username' => $username]);

            // Delete user account
            if ($prTable->delete($user)) {
                $this->Flash->success("L'utilisateur {$username} a été supprimé avec succès.");
            } else {
                $this->Flash->error("Erreur lors de la suppression de l'utilisateur.");
            }

        } catch (\Exception $e) {
            $this->Flash->error('Utilisateur non trouvé: ' . $e->getMessage());
        }

        return $this->redirect(['action' => 'index']);
    }

    public function updateUsername(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = $this->request->getData('user_id');
        $newUsername = trim($this->request->getData('new_username'));

        if (empty($newUsername)) {
            $this->Flash->error('Le nouveau nom d\'utilisateur ne peut pas être vide.');
            return $this->redirect(['action' => 'index']);
        }

        try {
            $prTable = TableRegistry::getTableLocator()->get('Pr');
            $user = $prTable->get($userId);
            $oldUsername = $user->username;

            // Prevent changing admin username
            if ($oldUsername === 'farah') {
                $this->Flash->error('Vous ne pouvez pas modifier le nom de l\'administrateur.');
                return $this->redirect(['action' => 'index']);
            }

            // Check if new username already exists
            $existingUser = $prTable->find()
                ->where(['username' => $newUsername])
                ->first();

            if ($existingUser) {
                $this->Flash->error('Ce nom d\'utilisateur existe déjà.');
                return $this->redirect(['action' => 'index']);
            }

            // Update username in user table
            $user->username = $newUsername;

            if ($prTable->save($user)) {
                // Update username in all groups
                $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
                $groups = $groupTable->find('all')->toArray();

                foreach ($groups as $group) {
                    for ($i = 1; $i <= 100; $i++) {
                        $userColumn = "user_{$i}";
                        if (!empty($group->$userColumn) && $group->$userColumn === $oldUsername) {
                            $group->$userColumn = $newUsername;
                        }
                    }
                    $groupTable->save($group);
                }

                // Update username in dossiers and fichiers
                $dossierTable = TableRegistry::getTableLocator()->get('Dossier');
                $fichierTable = TableRegistry::getTableLocator()->get('Fichier');

                $dossierTable->updateAll(
                    ['username' => $newUsername],
                    ['username' => $oldUsername]
                );

                $fichierTable->updateAll(
                    ['username' => $newUsername],
                    ['username' => $oldUsername]
                );

                $this->Flash->success("Le nom d'utilisateur a été changé de '{$oldUsername}' à '{$newUsername}' avec succès.");
            } else {
                $this->Flash->error('Erreur lors de la mise à jour du nom d\'utilisateur.');
            }

        } catch (\Exception $e) {
            $this->Flash->error('Erreur: ' . $e->getMessage());
        }

        return $this->redirect(['action' => 'index']);
    }

    public function updatePermission(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = $this->request->getData('user_id');
        $permission = $this->request->getData('permission');
        $value = $this->request->getData('value') ? 1 : 0;

        $validPermissions = [
            'can_read' => 'can_read',
            'can_delete' => 'can_delete',
            'can_download' => 'can_download'
        ];

        if (!isset($validPermissions[$permission])) {
            $this->Flash->error('Permission invalide.');
            return $this->redirect(['action' => 'index']);
        }

        try {
            $prTable = TableRegistry::getTableLocator()->get('Pr');
            $user = $prTable->get($userId);

            if (!$this->canAccessUserContent($user->username)) {
                $this->Flash->error('Vous ne pouvez pas modifier les permissions de cet utilisateur.');
                return $this->redirect(['action' => 'index']);
            }

            $column = $validPermissions[$permission];
            $user->$column = $value;

            if ($prTable->save($user)) {
                $this->Flash->success('Permission mise à jour avec succès.');
            } else {
                $this->Flash->error('Erreur lors de la mise à jour de la permission.');
            }
        } catch (\Exception $e) {
            $this->Flash->error('Utilisateur non trouvé.');
        }

        return $this->redirect(['action' => 'index']);
    }

    public function updateAllPermissions(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        try {
            $prTable = TableRegistry::getTableLocator()->get('Pr');
            $permissions = $this->request->getData('permissions', []);
            $updatedCount = 0;
            $errors = [];

            foreach ($permissions as $userId => $userPermissions) {
                try {
                    $user = $prTable->get($userId);

                    if (!$this->canAccessUserContent($user->username)) {
                        $errors[] = "Vous ne pouvez pas modifier les permissions de {$user->username}";
                        continue;
                    }

                    $user->can_read = $this->normalizePermissionValue($userPermissions['can_read'] ?? 0);
                    $user->can_delete = $this->normalizePermissionValue($userPermissions['can_delete'] ?? 0);
                    $user->can_download = $this->normalizePermissionValue($userPermissions['can_download'] ?? 0);

                    if ($prTable->save($user)) {
                        $updatedCount++;
                    } else {
                        $errors[] = "Erreur lors de la mise à jour de l'utilisateur {$user->username}";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Utilisateur avec ID {$userId} non trouvé";
                }
            }

            if ($updatedCount > 0) {
                $this->Flash->success("Permissions mises à jour pour {$updatedCount} utilisateur(s).");
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->Flash->error($error);
                }
            }

        } catch (\Exception $e) {
            $this->Flash->error('Erreur lors de la mise à jour des permissions: ' . $e->getMessage());
        }

        return $this->redirect(['action' => 'index']);
    }

    private function normalizePermissionValue($value): int
    {
        if (is_array($value)) {
            $value = end($value);
        }

        return $value ? 1 : 0;
    }

    public function createGroup(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        $groupName = trim($this->request->getData('group_name'));
        $selectedUsers = $this->request->getData('selected_users', []);
        $groupPermissions = $this->request->getData('group_permissions', []);

        if (empty($groupName)) {
            $this->Flash->error('Le nom du groupe est requis.');
            return $this->redirect(['action' => 'index']);
        }

        if (empty($selectedUsers)) {
            $this->Flash->error('Veuillez sélectionner au moins un utilisateur pour le groupe.');
            return $this->redirect(['action' => 'index']);
        }

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');

            $existingGroup = $groupTable->find()->where(['name' => $groupName])->first();
            if ($existingGroup) {
                $this->Flash->error('Un groupe avec ce nom existe déjà.');
                return $this->redirect(['action' => 'index']);
            }

            $group = $groupTable->newEmptyEntity();
            $group->name = $groupName;
            $prTable = TableRegistry::getTableLocator()->get('Pr');
            $userCount = 0;

            foreach ($selectedUsers as $index => $userId) {
                try {
                    $user = $prTable->get($userId);
                    $username = $user->username;

                    if (!$this->canAccessUserContent($username)) {
                        $this->Flash->error("Vous ne pouvez pas ajouter {$username} au groupe.");
                        continue;
                    }

                    $columnFound = false;
                    for ($i = 1; $i <= 100; $i++) {
                        $columnName = "user_{$i}";
                        $permissionColumn = "user_{$i}_permissions";

                        if (empty($group->$columnName)) {
                            $group->$columnName = $username;

                            $userPermissions = [
                                'can_read' => isset($groupPermissions[$userId]['can_read']) ? 1 : 0,
                                'can_delete' => isset($groupPermissions[$userId]['can_delete']) ? 1 : 0,
                                'can_download' => isset($groupPermissions[$userId]['can_download']) ? 1 : 0
                            ];
                            $group->$permissionColumn = json_encode($userPermissions);

                            $columnFound = true;
                            $userCount++;
                            break;
                        }
                    }

                    if (!$columnFound) {
                        $this->Flash->error("Impossible d'ajouter {$username} - groupe plein.");
                    }

                } catch (\Exception $e) {
                    $this->Flash->error("Erreur lors de l'ajout de l'utilisateur ID {$userId}: " . $e->getMessage());
                    continue;
                }
            }

            if ($groupTable->save($group)) {
                $this->Flash->success("Groupe '{$groupName}' créé avec succès avec {$userCount} utilisateur(s).");
            } else {
                $this->Flash->error('Erreur lors de la création du groupe.');
            }

        } catch (\Exception $e) {
            $this->Flash->error('Erreur lors de la création du groupe: ' . $e->getMessage());
        }

        return $this->redirect(['action' => 'index']);
    }

    public function updateGroupPermissions(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        $groupId = $this->request->getData('group_id');
        $groupPermissions = $this->request->getData('group_permissions', []);

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $group = $groupTable->get($groupId);

            for ($i = 1; $i <= 100; $i++) {
                $userColumn = "user_{$i}";
                $permissionColumn = "user_{$i}_permissions";

                if (!empty($group->$userColumn)) {
                    $username = $group->$userColumn;

                    if (!$this->canAccessUserContent($username)) {
                        continue;
                    }

                    if (isset($groupPermissions[$username])) {
                        $userPermissions = [
                            'can_read' => isset($groupPermissions[$username]['can_read']) ? 1 : 0,
                            'can_delete' => isset($groupPermissions[$username]['can_delete']) ? 1 : 0,
                            'can_download' => isset($groupPermissions[$username]['can_download']) ? 1 : 0
                        ];
                        $group->$permissionColumn = json_encode($userPermissions);
                    }
                }
            }

            if ($groupTable->save($group)) {
                $this->Flash->success('Permissions du groupe mises à jour avec succès.');
            } else {
                $this->Flash->error('Erreur lors de la mise à jour des permissions du groupe.');
            }

        } catch (\Exception $e) {
            $this->Flash->error('Erreur lors de la mise à jour: ' . $e->getMessage());
        }

        return $this->redirect(['action' => 'index']);
    }

    public function deleteGroup(): Response
    {
        if (!$this->request->is('post')) {
            return $this->redirect($this->referer());
        }

        if (!$this->isAdmin()) {
            $this->Flash->error('Accès refusé.');
            return $this->redirect(['action' => 'index']);
        }

        $groupId = $this->request->getData('group_id');

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $group = $groupTable->get($groupId);

            $canDeleteGroup = false;
            for ($i = 1; $i <= 100; $i++) {
                $userColumn = "user_{$i}";
                if (!empty($group->$userColumn)) {
                    if ($this->canAccessUserContent($group->$userColumn)) {
                        $canDeleteGroup = true;
                        break;
                    }
                }
            }

            if (!$canDeleteGroup) {
                $this->Flash->error('Vous ne pouvez pas supprimer ce groupe.');
                return $this->redirect(['action' => 'index']);
            }

            if ($groupTable->delete($group)) {
                $this->Flash->success('Groupe supprimé avec succès.');
            } else {
                $this->Flash->error('Erreur lors de la suppression du groupe.');
            }

        } catch (\Exception $e) {
            $this->Flash->error('Groupe non trouvé.');
        }

        return $this->redirect(['action' => 'index']);
    }
    public function editUser(): Response
{
    $this->autoRender = false;

    if (!$this->request->is('post')) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
    }

    if (!$this->isAdmin()) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Accès refusé']));
    }

    $data = $this->request->getData();
    $userId = $data['user_id'] ?? null;

    if (empty($userId)) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Données manquantes']));
    }

    try {
        $prTable = TableRegistry::getTableLocator()->get('Pr');
        $personnelTable = TableRegistry::getTableLocator()->get('Personnel');

        $user = $prTable->get($userId);
        $oldUsername = $user->username;

        if ($oldUsername === 'farah') {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'message' => 'Impossible de modifier le compte administrateur']));
        }

        // Vérifier si le nouveau nom existe déjà
        $newUsername = trim($data['username'] ?? '');
        if (!empty($newUsername) && $newUsername !== $oldUsername) {
            $existingUser = $prTable->find()
                ->where(['username' => $newUsername])
                ->first();

            if ($existingUser) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur existe déjà']));
            }
        }

        // Mettre à jour le nom d'utilisateur si fourni
        if (!empty($newUsername)) {
            $user->username = $newUsername;
        }

        // Mettre à jour le mot de passe si fourni
        if (!empty($data['password'])) {
            $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
            $user->password = $hasher->hash($data['password']);
        }

        if ($prTable->save($user)) {
            // Mettre à jour les données personnelles
            $personnel = $personnelTable->find()
                ->where(['pr_id' => $userId])
                ->first();

            if ($personnel) {
                // Mettre à jour les informations existantes
                if (isset($data['email'])) $personnel->email = trim($data['email']);
                if (isset($data['nom'])) $personnel->nom = trim($data['nom']);
                if (isset($data['prenom'])) $personnel->prenom = trim($data['prenom']);
                if (isset($data['num'])) $personnel->num = trim($data['num']);
                if (isset($data['adresse'])) $personnel->adresse = trim($data['adresse']);

                $personnelTable->save($personnel);
            } else {
                // Créer un nouvel enregistrement personnel
                $personnel = $personnelTable->newEmptyEntity();
                $personnel->pr_id = $userId;
                $personnel->email = trim($data['email'] ?? '');
                $personnel->nom = trim($data['nom'] ?? '');
                $personnel->prenom = trim($data['prenom'] ?? '');
                $personnel->num = trim($data['num'] ?? '');
                $personnel->adresse = trim($data['adresse'] ?? '');

                $personnelTable->save($personnel);
            }

            // Si le nom d'utilisateur a changé, mettre à jour dans les groupes, dossiers et fichiers
            if (!empty($newUsername) && $newUsername !== $oldUsername) {
                $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
                $groups = $groupTable->find('all')->toArray();

                foreach ($groups as $group) {
                    for ($i = 1; $i <= 100; $i++) {
                        $userColumn = "user_{$i}";
                        if (!empty($group->$userColumn) && $group->$userColumn === $oldUsername) {
                            $group->$userColumn = $newUsername;
                        }
                    }
                    $groupTable->save($group);
                }

                $dossierTable = TableRegistry::getTableLocator()->get('Dossier');
                $fichierTable = TableRegistry::getTableLocator()->get('Fichier');

                $dossierTable->updateAll(['username' => $newUsername], ['username' => $oldUsername]);
                $fichierTable->updateAll(['username' => $newUsername], ['username' => $oldUsername]);
            }

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Utilisateur mis à jour avec succès',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username
                    ]
                ]));
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']));

    } catch (\Exception $e) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]));
    }
}
public function getUserData(): Response
{
    $this->autoRender = false;

    if (!$this->request->is('post')) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
    }

    if (!$this->isAdmin()) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Accès refusé']));
    }

    $data = $this->request->getData();
    $userId = $data['user_id'] ?? null;

    if (empty($userId)) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'ID utilisateur manquant']));
    }

    try {
        $prTable = TableRegistry::getTableLocator()->get('Pr');
        $personnelTable = TableRegistry::getTableLocator()->get('Personnel');

        $user = $prTable->get($userId);
        $personnel = $personnelTable->find()
            ->where(['pr_id' => $userId])
            ->first();

        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $personnel->email ?? '',
            'nom' => $personnel->nom ?? '',
            'prenom' => $personnel->prenom ?? '',
            'num' => $personnel->num ?? '',
            'adresse' => $personnel->adresse ?? ''
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'user' => $userData
            ]));

    } catch (\Exception $e) {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]));
    }
}
}
