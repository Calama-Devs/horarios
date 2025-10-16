<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Exceptions\ReferenciaException;

class TurmasModel extends Model
{
    protected $table            = 'turmas';
    protected $primaryKey       = 'id';

    public function getTurmasByCursos($curso_id)
    {
        return $this->where('curso_id', $curso_id)
                    ->orderBy('sigla', 'ASC')
                    ->findAll();
    }

}
