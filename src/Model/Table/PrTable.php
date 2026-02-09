<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PrTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('pr');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->boolean('can_read')
            ->notEmptyString('can_read');

        $validator
            ->boolean('can_delete')
            ->notEmptyString('can_delete');

        $validator
            ->boolean('can_download')
            ->notEmptyString('can_download');

        return $validator;
    }
}