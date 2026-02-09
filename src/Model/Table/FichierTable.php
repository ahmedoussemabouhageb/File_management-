<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class FichierTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fichiers'); 
        $this->setPrimaryKey('id');

        $this->belongsTo('Dossier', [
            'foreignKey' => 'dossier_id',
        ]);
    }
}
