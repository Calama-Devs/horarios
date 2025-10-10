<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfessorModel extends Model
{
    protected $table = 'professores';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['nome', 'siape'];

    public function getAllOrdered()
    {
        return $this->orderBy('nome', 'ASC')->findAll();
    }
}
