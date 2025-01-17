<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfessorModel extends Model
{
    protected $table            = 'professores';
    protected $primaryKey       = 'professor_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nome', 'siape'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'professor_id' => 'permit_empty|is_natural_no_zero|max_length[11]',
        'nome' => 'required|is_unique[professores.nome,professor_id,{professor_id}]|max_length[96]',
        'siape' => 'permit_empty|is_unique[professores.siape,professor_id,{professor_id}]|exact_length[7]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    //função pra retornar todos os professores cadastrados no banco
    public function getProfessores($id = null){
        if($id === null){
            $professores = $this->findAll();
        }else{
            return $this->professores->find($id);
    }
}
}
