<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Fichier extends Entity
{
    // Explicitly type the property as array
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];
}