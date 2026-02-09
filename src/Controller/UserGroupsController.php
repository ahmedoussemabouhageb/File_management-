<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\ORM\TableRegistry;


class UserGroupsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('FormProtection');
    }

    private function isAdmin(): bool
    {
        $username = $this->request->getSession()->read("Auth.User.username");
        return $username === 'farah';
    }

    private function getCurrentUser(): string
    {
        $username = $this->request->getSession()->read("Auth.User.username");
        return $username ?? 'Guest';
    }

    // Get user permissions from Pr table
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
            $prTable = $this->fetchTable('Pr');
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

    
    private function isUserInGroup($groupId, $username): array
    {
        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $group = $groupTable->get($groupId);
            
            for ($i = 1; $i <= 100; $i++) {
                $userColumn = "user_{$i}";
                $permissionColumn = "user_{$i}_permissions";
                
                if ($group->$userColumn === $username) {
                    $permissions = json_decode($group->$permissionColumn, true) ?? [
                        'can_read' => 0,
                        'can_delete' => 0,
                        'can_download' => 0
                    ];
                    return [
                        'is_member' => true,
                        'permissions' => $permissions
                    ];
                }
            }
            
            return ['is_member' => false, 'permissions' => []];
        } catch (\Exception $e) {
            return ['is_member' => false, 'permissions' => []];
        }
    }

    private function getGroupUsers($groupId): array
    {
        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $group = $groupTable->get($groupId);
            $users = [];
            
            for ($i = 1; $i <= 100; $i++) {
                $userColumn = "user_{$i}";
                $permissionColumn = "user_{$i}_permissions";
                
                if (!empty($group->$userColumn)) {
                    $permissions = json_decode($group->$permissionColumn, true) ?? [
                        'can_read' => 0,
                        'can_delete' => 0,
                        'can_download' => 0
                    ];
                    $users[] = [
                        'username' => $group->$userColumn,
                        'permissions' => $permissions
                    ];
                }
            }
            
            return $users;
        } catch (\Exception $e) {
            return [];
        }
    }


    public function index($groupId = null)
    {
        $username = $this->getCurrentUser();

        if ($username === 'Guest') {
            $this->Flash->error('Please log in to access this page.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        
        if ($groupId === null) {
            return $this->listAvailableGroups();
        }

        return $this->showGroupContent($groupId);
    }


    private function listAvailableGroups()
    {
        $username = $this->getCurrentUser();
        $isAdmin = $this->isAdmin();
        $availableGroups = [];

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $groups = $groupTable->find('all')->toArray();
            
            foreach ($groups as $group) {
                if ($isAdmin) {
                    $availableGroups[] = $group;
                } else {
                    for ($i = 1; $i <= 100; $i++) {
                        $userColumn = "user_{$i}";
                        if (!empty($group->$userColumn) && $group->$userColumn === $username) {
                            $availableGroups[] = $group;
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->Flash->error('Error loading groups: ' . $e->getMessage());
        }

        $permissions = $this->getUserPermissions();
        
        $this->set(compact('availableGroups', 'username', 'isAdmin', 'permissions'));
        $this->render('list_groups');
    }


    private function showGroupContent($groupId)
    {
        $username = $this->getCurrentUser();
        $isAdmin = $this->isAdmin();
        $userPermissions = $this->getUserPermissions();
        
        $groupMembership = $this->isUserInGroup($groupId, $username);
        
        if (!$isAdmin && !$groupMembership['is_member']) {
            $this->Flash->error('You are not a member of this group.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $group = $groupTable->get($groupId);
            $groupUsers = $this->getGroupUsers($groupId);
        } catch (\Exception $e) {
            $this->Flash->error('Group not found.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        $dossierTable = $this->fetchTable('Dossier');
        $fichierTable = $this->fetchTable('Fichier');
        $search = $this->request->getQuery('search');
        $results = [];

        $groupUsernames = array_column($groupUsers, 'username');

        if (!empty($groupUsernames)) {
            $dossiers = $dossierTable->find('all')
                ->where([
                    'parent_id IS' => null,
                    'username IN' => $groupUsernames
                ])
                ->toArray();
            
            $rootFiles = $fichierTable->find()
                ->where([
                    'dossier_id IS' => null,
                    'username IN' => $groupUsernames
                ])
                ->toArray();

            if ($search) {
                $matchingFolders = $dossierTable->find()
                    ->where([
                        'name LIKE' => '%' . $search . '%',
                        'username IN' => $groupUsernames
                    ])
                    ->all();

                $matchingFiles = $fichierTable->find()
                    ->where([
                        'name LIKE' => '%' . $search . '%',
                        'username IN' => $groupUsernames
                    ])
                    ->all();

                $results = [
                    'folders' => $matchingFolders,
                    'files' => $matchingFiles
                ];
            }
        } else {
            $dossiers = [];
            $rootFiles = [];
        }

        $permissions = $isAdmin ? $userPermissions : ($groupMembership['is_member'] ? $groupMembership['permissions'] : $userPermissions);
        $canReadAll = $isAdmin || $permissions['can_read'] == 1;

        $this->set(compact(
            'dossiers', 
            'rootFiles', 
            'search', 
            'results', 
            'username', 
            'isAdmin', 
            'permissions', 
            'canReadAll',
            'group',
            'groupUsers',
            'groupId'
        ));
        
        $this->render('index'); 
    }

    public function view($groupId = null, $dossierId = null)
    {
        $username = $this->getCurrentUser();
        
        if ($username === 'Guest') {
            $this->Flash->error('Please log in to view folders.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if (!$groupId || !$dossierId) {
            $this->Flash->error('Group ID or folder ID missing.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        $isAdmin = $this->isAdmin();
        $userPermissions = $this->getUserPermissions();
        
        $groupMembership = $this->isUserInGroup($groupId, $username);
        
        if (!$isAdmin && !$groupMembership['is_member']) {
            $this->Flash->error('You are not a member of this group.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        try {
            $groupTable = TableRegistry::getTableLocator()->get('UserGroups');
            $group = $groupTable->get($groupId);
            $groupUsers = $this->getGroupUsers($groupId);
        } catch (\Exception $e) {
            $this->Flash->error('Group not found.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        $dossierTable = $this->fetchTable('Dossier');
        $fichierTable = $this->fetchTable('Fichier');

        $dossier = $dossierTable->get($dossierId);
        $groupUsernames = array_column($groupUsers, 'username');

        
        if (!$isAdmin && !in_array($dossier->username, $groupUsernames)) {
            $this->Flash->error('This folder does not belong to a group member.');
            return $this->redirect(['action' => 'index', $groupId]);
        }

        
        $files = $fichierTable->find()
            ->where([
                'dossier_id' => $dossierId,
                'username IN' => $groupUsernames
            ])
            ->all();

        
        $subfolders = $dossierTable->find()
            ->where([
                'parent_id' => $dossierId,
                'username IN' => $groupUsernames
            ])
            ->all();

        
        $breadcrumbs = $this->buildBreadcrumbs($dossier);
        
        $currentPath = $this->buildCurrentPath($dossier);

        $permissions = $isAdmin ? $userPermissions : ($groupMembership['is_member'] ? $groupMembership['permissions'] : $userPermissions);
        $canReadAll = $isAdmin || $permissions['can_read'] == 1;

        $this->set(compact(
            'dossier', 
            'files', 
            'subfolders', 
            'breadcrumbs', 
            'currentPath', 
            'username', 
            'isAdmin', 
            'permissions', 
            'canReadAll',
            'group',
            'groupUsers',
            'groupId'
        ));
    }

    private function buildCurrentPath($dossier): string
    {
        $path = [];
        $current = $dossier;
        
        while ($current) {
            array_unshift($path, $current->name);
            
            if ($current->parent_id) {
                $current = $this->fetchTable('Dossier')->get($current->parent_id);
            } else {
                break;
            }
        }
        
        return implode('/', $path);
    }

    private function buildBreadcrumbs($dossier): array
    {
        $breadcrumbs = [];
        $current = $dossier;
        
        while ($current) {
            array_unshift($breadcrumbs, [
                'name' => $current->name,
                'id' => $current->id
            ]);
            
            if ($current->parent_id) {
                $current = $this->fetchTable('Dossier')->get($current->parent_id);
            } else {
                break;
            }
        }
        
        return $breadcrumbs;
    }
}