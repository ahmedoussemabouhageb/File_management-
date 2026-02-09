<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class DossierTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dossier');  
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Fichier', [
            'foreignKey' => 'dossier_id',
        ]);

        $this->belongsTo('ParentDossier', [
            'className' => 'Dossier',
            'foreignKey' => 'parent_id',
        ]);

        $this->hasMany('ChildDossiers', [
            'className' => 'Dossier',
            'foreignKey' => 'parent_id',
        ]);
    }
}
