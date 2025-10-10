<?php

namespace App\Models;

use CodeIgniter\Model;

class CursosModel extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['nome', 'codigo'];

    public function getAllOrdered()
    {
        return $this->orderBy('nome', 'ASC')->findAll();
    }
}
