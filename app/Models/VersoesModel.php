<?php

namespace App\Models;

use CodeIgniter\Model;

class VersoesModel extends Model
{

    protected $table = 'versoes';

    protected $primaryKey = 'id';

    protected $allowedFields = ['descricao', 'vigente'];

    public function getVigente()
    {
        return $this->where('vigente', 1)->first();
    }

    public function getVigenteId()
    {
        return $this->where('vigente', 1)->first()['id'] ?? 0;
    }
}
