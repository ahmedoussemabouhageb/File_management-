<?php
declare(strict_types=1);

namespace App\Controller;

class FichierController extends AppController
{
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $fichier = $this->Fichier->get($id);
        $currentUser = $this->request->getSession()->read('Auth.User.username');

        if ($currentUser !== 'farah' && $fichier->username !== $currentUser) {
            $this->Flash->error(__('You do not have permission to delete this file.'));
            return $this->redirect($this->referer());
        }

        if ($this->Fichier->delete($fichier)) {
            $this->Flash->success(__('File deleted.'));
        } else {
            $this->Flash->error(__('Unable to delete file.'));
        }

        return $this->redirect($this->referer());
    }

    public function add($dossierId = null)
    {
        $this->loadModel('Fichier');
        $fichier = $this->Fichier->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['dossier_id'] = $dossierId;
            $data['username'] = $this->request->getSession()->read('Auth.User.username');

            if (!empty($data['file']) && $data['file']->getError() === UPLOAD_ERR_OK) {
                $file = $data['file'];
                $filename = $file->getClientFilename();

                $targetDir = WWW_ROOT . 'files' . DS;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $targetPath = $targetDir . $filename;

                if (file_exists($targetPath)) {
                    $filename = time() . '_' . $filename;
                    $targetPath = $targetDir . $filename;
                }

                $file->moveTo($targetPath);

                $data['name'] = $filename;
                $data['path'] = 'files' . DS . $filename;
                $data['size'] = $file->getSize();
            }

            $fichier = $this->Fichier->patchEntity($fichier, $data);

            if ($this->Fichier->save($fichier)) {
                $this->Flash->success(__('Fichier uploaded.'));
                return $this->redirect(['controller' => 'Dossiers', 'action' => 'view', $dossierId]);
            }

            $this->Flash->error(__('Upload failed.'));
        }

        $this->set(compact('fichier', 'dossierId'));
    }
}
