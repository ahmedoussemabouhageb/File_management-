<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\ORM\TableRegistry;

class DossiersController extends AppController
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
            // Log error if needed
        }

        return [
            'can_read' => 0,
            'can_delete' => 0,
            'can_download' => 0
        ];
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
            // Log error
        }

        return array_values($groupMembers);
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

    private function canDeleteContent($contentUsername): bool
    {
        $currentUsername = $this->getCurrentUser();
        $permissions = $this->getUserPermissions();
        
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($contentUsername === $currentUsername) {
            return true;
        }
        
        if ($permissions['can_delete'] == 1 && $this->canAccessUserContent($contentUsername)) {
            return true;
        }
        
        return false;
    }

    public function index()
    {
        $dossierTable = $this->fetchTable('Dossier');
        $fichierTable = $this->fetchTable('Fichier');
        $username = $this->getCurrentUser();

        if ($username === 'Guest') {
            $this->Flash->error('Please log in to access this page.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $permissions = $this->getUserPermissions();
        $search = $this->request->getQuery('search');
        $results = [];

        $hasGlobalRead = $this->isAdmin() || $permissions['can_read'] == 1;
        
        $groupMembers = $this->getGroupMembers();
        $accessibleUsers = array_merge([$username], $groupMembers);

        if ($hasGlobalRead) {
            $dossiers = $dossierTable->find('all')
                ->where(['parent_id IS' => null])
                ->toArray();
            
            $rootFiles = $fichierTable->find()
                ->where(['dossier_id IS' => null])
                ->toArray();

            if ($search) {
                $matchingFolders = $dossierTable->find()
                    ->where(['name LIKE' => '%' . $search . '%'])
                    ->all();

                $matchingFiles = $fichierTable->find()
                    ->where(['name LIKE' => '%' . $search . '%'])
                    ->all();

                $results = [
                    'folders' => $matchingFolders,
                    'files' => $matchingFiles
                ];
            }
        } else {
            $dossiers = $dossierTable->find()
                ->where([
                    'username IN' => $accessibleUsers,
                    'parent_id IS' => null
                ])
                ->toArray();
                
            $rootFiles = $fichierTable->find()
                ->where([
                    'dossier_id IS' => null,
                    'username IN' => $accessibleUsers
                ])
                ->toArray();

            if ($search) {
                $matchingFolders = $dossierTable->find()
                    ->where([
                        'name LIKE' => '%' . $search . '%',
                        'username IN' => $accessibleUsers
                    ])
                    ->all();

                $matchingFiles = $fichierTable->find()
                    ->where([
                        'name LIKE' => '%' . $search . '%',
                        'username IN' => $accessibleUsers
                    ])
                    ->all();

                $results = [
                    'folders' => $matchingFolders,
                    'files' => $matchingFiles
                ];
            }
        }

        $isAdmin = $this->isAdmin();
        $canReadAll = $hasGlobalRead;
        
        $this->set(compact('dossiers', 'rootFiles', 'search', 'results', 'username', 'isAdmin', 'permissions', 'canReadAll', 'groupMembers'));
    }

    public function view($id = null)
    {
        $username = $this->getCurrentUser();
        
        if ($username === 'Guest') {
            $this->Flash->error('Please log in to view folders.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $permissions = $this->getUserPermissions();
        $dossierTable = $this->fetchTable('Dossier');
        $fichierTable = $this->fetchTable('Fichier');

        $dossier = $dossierTable->get($id);
        $hasGlobalRead = $this->isAdmin() || $permissions['can_read'] == 1;

        if (!$this->canAccessUserContent($dossier->username)) {
            $this->Flash->error('You do not have permission to view this folder.');
            return $this->redirect(['action' => 'index']);
        }

        $groupMembers = $this->getGroupMembers();
        $accessibleUsers = array_merge([$username], $groupMembers);

        if ($hasGlobalRead) {
            $files = $fichierTable->find()
                ->where(['dossier_id' => $id])
                ->all();
        } else {
            $files = $fichierTable->find()
                ->where([
                    'dossier_id' => $id,
                    'username IN' => $accessibleUsers
                ])
                ->all();
        }

        if ($hasGlobalRead) {
            $subfolders = $dossierTable->find()
                ->where(['parent_id' => $id])
                ->all();
        } else {
            $subfolders = $dossierTable->find()
                ->where([
                    'parent_id' => $id,
                    'username IN' => $accessibleUsers
                ])
                ->all();
        }

        $breadcrumbs = $this->buildBreadcrumbs($dossier);
        
        $currentPath = $this->buildCurrentPath($dossier);

        $isAdmin = $this->isAdmin();
        $canReadAll = $hasGlobalRead;
        
        $this->set(compact('dossier', 'files', 'subfolders', 'breadcrumbs', 'currentPath', 'username', 'isAdmin', 'permissions', 'canReadAll', 'groupMembers'));
    }

   public function add($parentId = null): ?Response
{
    $parentFolder = null;
    $parentId = $parentId ?? null;
    $permissions = [];
    
    $username = $this->getCurrentUser();
    
    if ($username === 'Guest') {
        $this->Flash->error('Please log in to upload files.');
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    $permissions = $this->getUserPermissions();
    $dossierTable = $this->fetchTable('Dossier');
    $fichierTable = $this->fetchTable('Fichier');

    $parentPath = '';
    $hasGlobalRead = $this->isAdmin() || $permissions['can_read'] == 1;
    
    if (!$parentId && $this->request->is('post')) {
        $parentId = $this->request->getData('parent_id');
        if (empty($parentId) || $parentId === '0' || $parentId === '') {
            $parentId = null;
        }
    }
    
    if ($parentId) {
        try {
            $parentFolder = $dossierTable->get($parentId);
            
            if (!$this->canAccessUserContent($parentFolder->username)) {
                $this->Flash->error('You can only add files to folders you have access to.');
                return $this->redirect(['action' => 'index']);
            }
            
            $parentPath = $this->buildCurrentPath($parentFolder);
        } catch (\Exception $e) {
            $this->Flash->error('Parent folder not found: ' . $e->getMessage());
            return $this->redirect(['action' => 'index']);
        }
    }

    if ($this->request->is('post')) {
        $type = $this->request->getData('type');

        if ($type === 'fichier') {
            $file = $this->request->getData('fichier');

            if ($file && $file->getError() === UPLOAD_ERR_OK) {
                $filename = $file->getClientFilename();
                $size = $file->getSize();

                $uploadDir = WWW_ROOT . 'uploads';
                
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir)) {
                        $this->Flash->error('Failed to create base uploads directory. Please create manually: ' . $uploadDir);
                        $this->set(compact('parentFolder', 'parentId', 'permissions'));
                        return null;
                    }
                }
                
                $uploadDir .= DS . $username;
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir)) {
                        $this->Flash->error('Failed to create user directory. Please create manually: ' . $uploadDir);
                        $this->set(compact('parentFolder', 'parentId', 'permissions'));
                        return null;
                    }
                }
                
                if ($parentPath) {
                    $pathParts = explode('/', $parentPath);
                    foreach ($pathParts as $part) {
                        if (!empty($part)) {
                            $uploadDir .= DS . $part;
                            if (!is_dir($uploadDir)) {
                                if (!mkdir($uploadDir)) {
                                    $this->Flash->error('Failed to create directory: ' . $uploadDir);
                                    $this->set(compact('parentFolder', 'parentId', 'permissions'));
                                    return null;
                                }
                            }
                        }
                    }
                }
                
                $uploadDir .= DS;
                
                if (!is_writable($uploadDir)) {
                    @chmod($uploadDir, 0777);
                    if (!is_writable($uploadDir)) {
                        $this->Flash->error('Upload directory is not writable: ' . $uploadDir . '. Please check Windows folder permissions.');
                        $this->set(compact('parentFolder', 'parentId', 'permissions'));
                        return null;
                    }
                }

                $fullPath = $uploadDir . $filename;
                $originalFilename = $filename;
                $counter = 1;
                
                while (file_exists($fullPath)) {
                    $pathInfo = pathinfo($originalFilename);
                    $filename = $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
                    $fullPath = $uploadDir . $filename;
                    $counter++;
                }

                try {
                    if (move_uploaded_file($file->getStream()->getMetadata('uri'), $fullPath)) {
                        $moveSuccess = true;
                    } else {
                        $moveSuccess = $file->moveTo($fullPath);
                    }
                    
                    if (!$moveSuccess) {
                        $this->Flash->error('Failed to move uploaded file to: ' . $fullPath . '. Check Windows permissions on the uploads folder.');
                        $this->set(compact('parentFolder', 'parentId', 'permissions'));
                        return null;
                    }
                } catch (\Exception $e) {
                    $this->Flash->error('Error moving file: ' . $e->getMessage());
                    $this->set(compact('parentFolder', 'parentId', 'permissions'));
                    return null;
                }

                $webPath = '/uploads/' . $username . '/';
                if ($parentPath) {
                    $webPath .= $parentPath . '/';
                }
                $webPath .= $filename;

                $fichierData = [
                    'name' => $filename,
                    'upload_date' => date('Y-m-d H:i:s'),
                    'size' => $size,
                    'path' => $webPath,
                    'username' => $username,
                    'dossier_id' => $parentId
                ];

                $newFichier = $fichierTable->newEmptyEntity();
                $newFichier = $fichierTable->patchEntity($newFichier, $fichierData);

                if ($fichierTable->save($newFichier)) {
                    $this->Flash->success('File "' . $filename . '" uploaded successfully!');
                    if ($parentId) {
                        return $this->redirect(['action' => 'view', $parentId]);
                    } else {
                        return $this->redirect(['action' => 'index']);
                    }
                } else {
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    
                    $this->Flash->error('Failed to save file information to database.');
                    $errors = $newFichier->getErrors();
                    if (!empty($errors)) {
                        foreach ($errors as $field => $error) {
                            $this->Flash->error("Field $field: " . implode(', ', $error));
                        }
                    }
                }
            } else {
                $errorMsg = 'No file selected or upload failed.';
                if ($file) {
                    $errorCode = $file->getError();
                    switch ($errorCode) {
                        case UPLOAD_ERR_OK:
                            $uploadError = 'No error';
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                            $uploadError = 'File exceeds upload_max_filesize';
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $uploadError = 'File exceeds MAX_FILE_SIZE';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $uploadError = 'File was only partially uploaded';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $uploadError = 'No file was uploaded';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $uploadError = 'Missing temporary folder';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $uploadError = 'Failed to write file to disk';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $uploadError = 'File upload stopped by extension';
                            break;
                        default:
                            $uploadError = 'Unknown upload error';
                    }
                    $errorMsg .= ' Error: ' . $uploadError;
                }
                $this->Flash->error($errorMsg);
            }

        } elseif ($type === 'dossier') {
            $folderName = trim($this->request->getData('dossier_name'));
            
            if (empty($folderName)) {
                $this->Flash->error('Folder name cannot be empty.');
            } else {
                $whereConditions = [
                    'name' => $folderName,
                    'username' => $username
                ];
                
                if ($parentId === null) {
                    $whereConditions['parent_id IS'] = null;
                } else {
                    $whereConditions['parent_id'] = $parentId;
                }
                
                $existingFolder = $dossierTable->find()
                    ->where($whereConditions)
                    ->first();
                    
                if ($existingFolder) {
                    $this->Flash->error('A folder with this name already exists in this location.');
                } else {
                    $newFolderPath = $parentPath ? $parentPath . '/' . $folderName : $folderName;
                    
                    $dossierData = [
                        'name' => $folderName,
                        'upload_date' => date('Y-m-d H:i:s'),
                        'size' => null,
                        'path' => $newFolderPath,
                        'username' => $username,
                        'parent_id' => $parentId
                    ];
                    
                    $physicalPath = WWW_ROOT . 'uploads' . DS . $username . DS;
                    if ($parentPath) {
                        $physicalPath .= str_replace('/', DS, $parentPath) . DS;
                    }
                    $physicalPath .= $folderName;

                    if (!is_dir($physicalPath)) {
                        if (!mkdir($physicalPath, 0777, true)) {
                            $this->Flash->error('Failed to create physical directory: ' . $physicalPath);
                            $this->set(compact('parentFolder', 'parentId', 'permissions'));
                            return null;
                        }
                    }

                    $newDossier = $dossierTable->newEmptyEntity();
                    $newDossier = $dossierTable->patchEntity($newDossier, $dossierData);

                    if ($dossierTable->save($newDossier)) {
                        $this->Flash->success('Folder "' . $folderName . '" created successfully!');
                        if ($parentId) {
                            return $this->redirect(['action' => 'view', $parentId]);
                        } else {
                            return $this->redirect(['action' => 'index']);
                        }
                    } else {
                        $this->Flash->error('Failed to create folder. Please try again.');
                    }
                }
            }
        } else {
            $this->Flash->error('Invalid type selected: ' . $type);
        }
    }

    $this->set(compact('parentFolder', 'parentId', 'permissions'));
    return null;
}
private function getUploadErrorMessage($errorCode): string
{
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return 'No error';
        case UPLOAD_ERR_INI_SIZE:
            return 'File exceeds upload_max_filesize';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds MAX_FILE_SIZE';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}
    public function delete($id = null, $type = 'dossier'): Response
    {
        if (!$this->request->is(['post', 'delete'])) {
            return $this->redirect($this->referer());
        }

        $username = $this->getCurrentUser();
        
        if ($username === 'Guest') {
            $this->Flash->error('Please log in to delete files.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        try {
            if ($type === 'fichier') {
                $fichierTable = $this->fetchTable('Fichier');
                $file = $fichierTable->get($id);
                
                if (!$this->canDeleteContent($file->username)) {
                    $this->Flash->error('You do not have permission to delete this file.');
                    return $this->redirect($this->referer());
                }
                
                $physicalPath = WWW_ROOT . ltrim($file->path, '/');
                if (file_exists($physicalPath)) {
                    if (!unlink($physicalPath)) {
                        $this->Flash->error('Failed to delete physical file.');
                        return $this->redirect($this->referer());
                    }
                }
                
                if ($fichierTable->delete($file)) {
                    $this->Flash->success('File "' . $file->name . '" deleted successfully.');
                } else {
                    $this->Flash->error('Failed to delete file from database.');
                }
                
            } else {
                $dossierTable = $this->fetchTable('Dossier');
                $folder = $dossierTable->get($id);
                
                if (!$this->canDeleteContent($folder->username)) {
                    $this->Flash->error('Permission denied for user: ' . $username . ' trying to delete folder owned by: ' . $folder->username);
            $this->Flash->error('You do not have permission to delete this folder.');
                    return $this->redirect($this->referer());
                }
                
                $this->deleteFolderRecursively($folder);
                $this->Flash->success('Folder "' . $folder->name . '" deleted successfully.');
            }
            
        } catch (\Exception $e) {
            $this->Flash->error('Error deleting item: ' . $e->getMessage());
        }

        return $this->redirect($this->referer());
    }

    private function deleteFolderRecursively($folder): void
    {
        $dossierTable = $this->fetchTable('Dossier');
        $fichierTable = $this->fetchTable('Fichier');
        
        $groupMembers = $this->getGroupMembers();
        $accessibleUsers = array_merge([$this->getCurrentUser()], $groupMembers);
        $hasGlobalRead = $this->isAdmin() || $this->getUserPermissions()['can_read'] == 1;
        
        if ($hasGlobalRead) {
            $files = $fichierTable->find()
                ->where(['dossier_id' => $folder->id])
                ->all();
        } else {
            $files = $fichierTable->find()
                ->where([
                    'dossier_id' => $folder->id,
                    'username IN' => $accessibleUsers
                ])
                ->all();
        }
        
        foreach ($files as $file) {
            if ($this->canDeleteContent($file->username)) {
                $physicalPath = WWW_ROOT . ltrim($file->path, '/');
                if (file_exists($physicalPath)) {
                    unlink($physicalPath);
                }
                $fichierTable->delete($file);
            }
        }
        
        if ($hasGlobalRead) {
            $subfolders = $dossierTable->find()
                ->where(['parent_id' => $folder->id])
                ->all();
        } else {
            $subfolders = $dossierTable->find()
                ->where([
                    'parent_id' => $folder->id,
                    'username IN' => $accessibleUsers
                ])
                ->all();
        }
        
        foreach ($subfolders as $subfolder) {
            if ($this->canDeleteContent($subfolder->username)) {
                $this->deleteFolderRecursively($subfolder);
            }
        }
        
        if ($this->canDeleteContent($folder->username)) {
            $physicalPath = WWW_ROOT . 'uploads' . DS . $folder->username . DS . str_replace('/', DS, $folder->path);
            if (is_dir($physicalPath)) {
                rmdir($physicalPath);
            }
            
            $dossierTable->delete($folder);
        }
    }

    public function download($id = null, $type = 'fichier'): Response
    {
        $username = $this->getCurrentUser();
        
        if ($username === 'Guest') {
            $this->Flash->error('Please log in to download files.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $permissions = $this->getUserPermissions();
        
        if (!$this->isAdmin() && $permissions['can_download'] != 1) {
            $this->Flash->error('You do not have permission to download files.');
            return $this->redirect($this->referer());
        }

        try {
            if ($type === 'fichier') {
                $fichierTable = $this->fetchTable('Fichier');
                $file = $fichierTable->get($id);
                
                if (!$this->canAccessUserContent($file->username)) {
                    $this->Flash->error('You can only download files you have access to.');
                    return $this->redirect($this->referer());
                }
                
                $filePath = WWW_ROOT . ltrim($file->path, '/');
                
                if (file_exists($filePath)) {
                    $response = $this->response->withFile($filePath, [
                        'download' => true,
                        'name' => $file->name
                    ]);
                    return $response;
                } else {
                    $this->Flash->error('File not found on server.');
                }
            }
            
        } catch (\Exception $e) {
            $this->Flash->error('Error downloading file: ' . $e->getMessage());
        }

        return $this->redirect($this->referer());
    }

    private function buildCurrentPath($dossier): string
    {
        $path = [];
        $current = $dossier;
        
        while ($current) {
            array_unshift($path, $current->name);
            
            if ($current->parent_id) {
                try {
                    $current = $this->fetchTable('Dossier')->get($current->parent_id);
                } catch (\Exception $e) {
                    
                    break;
                }
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
                try {
                    $current = $this->fetchTable('Dossier')->get($current->parent_id);
                } catch (\Exception $e) {
                    
                    break;
                }
            } else {
                break;
            }
        }
        
        return $breadcrumbs;
    }

    public function getUsers()
    {
        $username = $this->getCurrentUser();
        
        if ($username === 'Guest') {
            $this->response = $this->response->withStatus(401);
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['error' => 'Authentication required']));
        }

        try {
            $prTable = $this->fetchTable('Pr');
            $permissions = $this->getUserPermissions();
            $hasGlobalRead = $this->isAdmin() || $permissions['can_read'] == 1;
            $groupMembers = $this->getGroupMembers();
            $accessibleUsers = array_merge([$username], $groupMembers);

            if ($hasGlobalRead) {
                $users = $prTable->find('all')
                    ->select(['id', 'username', 'password', 'can_read', 'can_delete', 'can_download'])
                    ->toArray();
            } else {
                
                $users = $prTable->find('all')
                    ->where(['username IN' => $accessibleUsers])
                    ->select(['id', 'username', 'password', 'can_read', 'can_delete', 'can_download'])
                    ->toArray();
            }

            return $this->response->withType('application/json')
                ->withStringBody(json_encode($users));
        } catch (\Exception $e) {
            $this->response = $this->response->withStatus(500);
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['error' => 'Failed to fetch users: ' . $e->getMessage()]));
        }
    }

    public function updateUserPermission()
    {
        
        if (!$this->isAdmin()) {
            $this->response = $this->response->withStatus(403);
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['error' => 'Access denied']));
        }

        if (!$this->request->is('post')) {
            $this->response = $this->response->withStatus(405);
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['error' => 'Method not allowed']));
        }

        try {
            $data = $this->request->getData();
            $userId = $data['user_id'] ?? null;
            $permission = $data['permission'] ?? null;
            $value = $data['value'] ?? null;

            if (!$userId || !$permission || $value === null) {
                $this->response = $this->response->withStatus(400);
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'Missing required parameters']));
            }

            $prTable = $this->fetchTable('Pr');
            $user = $prTable->get($userId);
            
            if (!$user) {
                $this->response = $this->response->withStatus(404);
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'User not found']));
            }

            if (!$this->canAccessUserContent($user->username)) {
                $this->response = $this->response->withStatus(403);
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'Cannot modify permissions for this user']));
            }

            $allowedPermissions = ['can_read', 'can_delete', 'can_download'];
            if (!in_array($permission, $allowedPermissions)) {
                $this->response = $this->response->withStatus(400);
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'Invalid permission field']));
            }

            $user->$permission = $value ? 1 : 0;
            
            if ($prTable->save($user)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['success' => true]));
            } else {
                $this->response = $this->response->withStatus(500);
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'Failed to update permission']));
            }

        } catch (\Exception $e) {
            $this->response = $this->response->withStatus(500);
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['error' => 'Server error: ' . $e->getMessage()]));
        }
    }
}