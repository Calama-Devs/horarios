<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\VersoesModel;

class HorariosModel extends Model
{
    public function findHorariosFiltrados(array $filtros): array
    {

        $versaoModel = new VersoesModel();
        $versaoVigenteId = $versaoModel->getVigenteId();

        if (!$versaoVigenteId) {
            return []; 
        }

        $builder = $this->db->table('aula_horario ah');

        $builder->select([
            'ta.dia_semana',
            "CONCAT(LPAD(ta.hora_inicio,2,'0'), ':', LPAD(ta.minuto_inicio,2,'0')) AS hora_inicio",
            'd.nome AS disciplina',
            't.sigla AS turma',
            'c.nome AS curso',
            "GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') AS professor",
            "GROUP_CONCAT(DISTINCT amb.nome SEPARATOR ', ') AS ambiente",
        ]);

        $builder->join('aulas a', 'a.id = ah.aula_id');
        $builder->join('tempos_de_aula ta', 'ta.id = ah.tempo_de_aula_id');
        $builder->join('turmas t', 't.id = a.turma_id');
        $builder->join('cursos c', 'c.id = t.curso_id');
        $builder->join('disciplinas d', 'd.id = a.disciplina_id');
        $builder->join('aula_professor ap', 'ap.aula_id = a.id', 'left');
        $builder->join('professores p', 'p.id = ap.professor_id', 'left');
        $builder->join('aula_horario_ambiente aha', 'aha.aula_horario_id = ah.id', 'left');
        $builder->join('ambientes amb', 'amb.id = aha.ambiente_id', 'left');

        // Filtro pela versão vigente
        $builder->where('a.versao_id', $versaoVigenteId);

        // Filtros opcionais vindos do formulário
        if (!empty($filtros['cursos'])) {
            $builder->whereIn('t.curso_id', $filtros['cursos']);
        }
        if (!empty($filtros['turmas'])) {
            $builder->whereIn('a.turma_id', $filtros['turmas']);
        }
        if (!empty($filtros['professores'])) {
            $builder->whereIn('ap.professor_id', $filtros['professores']);
        }
        if (!empty($filtros['ambientes'])) {
            $builder->whereIn('aha.ambiente_id', $filtros['ambientes']);
        }

        // Agrupa e ordena os resultados
        $builder->groupBy('ah.id');
        $builder->orderBy('c.nome, t.sigla, ta.dia_semana, ta.hora_inicio');

        // Retorna os dados formatados
        $resultados = $builder->get()->getResultArray();

        return $resultados;
    }
}
