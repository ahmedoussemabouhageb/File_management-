<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UserGroupsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_groups');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp'); 

    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->notEmptyString('name', 'Group name is required');

        for ($i = 1; $i <= 100; $i++) {
            $validator
                ->scalar("user_$i")
                ->maxLength("user_$i", 255)
                ->allowEmptyString("user_$i");

            $validator
                ->allowEmptyString("user_{$i}_permissions");
        }

        return $validator;
    }
}