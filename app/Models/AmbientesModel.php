<?php

namespace App\Models;

use CodeIgniter\Model;

class AmbientesModel extends Model
{
    protected $table = 'ambientes';
    protected $primaryKey = 'id';

    public function getAllOrdered()
    {
        return $this->orderBy('nome', 'ASC')
                    ->findAll();
    }
}
