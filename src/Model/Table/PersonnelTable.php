<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class PersonnelTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('personnel');
        $this->setPrimaryKey('id');

        $this->belongsTo('Pr', [
            'foreignKey' => 'pr_id',
        ]);
    }
}
