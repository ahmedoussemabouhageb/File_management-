<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Authentication\PasswordHasher\DefaultPasswordHasher;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('FormProtection');

        // Utilisation correcte de fetchTable dans CakePHP 5
        $this->Pr = $this->fetchTable('Pr');
        $this->Personnel = $this->fetchTable('Personnel');
    }

    public function login(): ?Response
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $user = $this->Pr->find()
                ->where(['username' => $data['username']])
                ->first();

            if ($user) {
                $hasher = new DefaultPasswordHasher();

                if ($hasher->check($data['password'], $user->password)) {
                    $this->Flash->success('Login successful!');
                    $this->request->getSession()->write('Auth.User', $user);

                    return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
                } else {
                    $this->Flash->error('Invalid password.');
                }
            } else {
                $this->Flash->error('User not found. Please check your username or sign up.');
            }
        }

        return null;
    }

    public function register(): ?Response
    {
        $user = $this->Pr->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['password'])) {
                $hasher = new DefaultPasswordHasher();
                $data['password'] = $hasher->hash($data['password']);
            }

            $userData = [
                'username' => $data['username'] ?? '',
                'password' => $data['password'] ?? '',
                'can_read' => 0,
                'can_delete' => 1,
                'can_download' => 1
            ];

            $user = $this->Pr->patchEntity($user, $userData);

            if ($this->Pr->save($user)) {
                $personnel = $this->Personnel->newEmptyEntity();

                $personnelData = [
                    'pr_id'   => $user->id,
                    'email'   => $data['email'] ?? '',
                    'nom'     => $data['nom'] ?? '',
                    'prenom'  => $data['prenom'] ?? '',
                    'num'     => $data['num'] ?? '',
                    'adresse' => $data['adresse'] ?? '',
                ];

                $personnel = $this->Personnel->patchEntity($personnel, $personnelData);

                if ($this->Personnel->save($personnel)) {
                    $this->Flash->success('Registration successful. You can now login.');
                    return $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error('User saved but failed to save personnel data.');
                }
            } else {
                $this->Flash->error('Unable to register. Please check the form for errors.');
            }
        }

        $this->set(compact('user'));
        return null;
    }

    public function interface(): ?Response
    {
        $user = $this->request->getSession()->read('Auth.User');

        if (!$user) {
            $this->Flash->error('Please login first.');
            return $this->redirect(['action' => 'login']);
        }

        return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
    }

    public function logout(): ?Response
    {
        $this->request->getSession()->destroy();
        $this->Flash->success('You have been logged out.');
        return $this->redirect(['action' => 'login']);
    }

    public function savePermissions(): ?Response
    {
        if (!$this->request->is('post')) {
            $this->Flash->error('Invalid request method.');
            return $this->redirect(['action' => 'index']);
        }

        $currentUser = $this->request->getSession()->read('Auth.User');
        if (!$currentUser || $currentUser->username !== 'farah') {
            $this->Flash->error('Access denied. Admin privileges required.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        $data = $this->request->getData('permissions');

        if (empty($data)) {
            $this->Flash->error('No permissions data received.');
            return $this->redirect(['action' => 'permissions']);
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($data as $userId => $perms) {
            try {
                $user = $this->Pr->get($userId);

                $user->can_download = !empty($perms['download']) ? 1 : 0;
                $user->can_delete = !empty($perms['delete']) ? 1 : 0;
                $user->can_read = !empty($perms['read']) ? 1 : 0;

                if ($this->Pr->save($user)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors = $user->getErrors();
                    if (!empty($errors)) {
                        $this->log('Permission save errors for user ' . $userId . ': ' . json_encode($errors), 'error');
                    }
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->log('Exception while saving permissions for user ' . $userId . ': ' . $e->getMessage(), 'error');
            }
        }

        if ($successCount > 0) {
            $this->Flash->success("Permissions updated successfully for {$successCount} user(s).");
        }

        if ($errorCount > 0) {
            $this->Flash->error("Failed to update permissions for {$errorCount} user(s). Check logs for details.");
        }

        return $this->redirect(['action' => 'permissions']);
    }

    public function permissions()
    {
        $currentUser = $this->request->getSession()->read('Auth.User');
        if (!$currentUser || $currentUser->username !== 'farah') {
            $this->Flash->error('Access denied. Admin privileges required.');
            return $this->redirect(['controller' => 'Dossiers', 'action' => 'index']);
        }

        $users = $this->Pr->find('all')->toArray();
        $this->set(compact('users'));
    }
}
