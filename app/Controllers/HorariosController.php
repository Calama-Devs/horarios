<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TurmasModel;

class HorariosController extends BaseController
{
    public function getTurmasByCurso()
    {
        if (!$this->request->isAJAX() || !$this->request->getPost('cursos')) {
            return $this->response->setStatusCode(400, 'Requisição inválida');
        }

        $turmasModel = new TurmasModel();
        $cursoId = $this->request->getPost('cursos')[0];

        $turmas = $turmasModel->where('curso_id', $cursoId)
                              ->orderBy('sigla', 'ASC')
                              ->findAll();

        $data = [];
        foreach ($turmas as $turma) {
            $data[] = [
                'id'   => $turma['id'],
                'text' => esc($turma['sigla']),
            ];
        }
        return $this->response->setJSON($data);
    }


    public function filtrar()
    {
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400, 'Requisição inválida');
    }

    $horariosModel = new \App\Models\HorariosModel();

    $filtros = [
        'cursos'      => $this->request->getPost('cursos'),
        'turmas'      => $this->request->getPost('turmas'),
        'professores' => $this->request->getPost('professores'),
        'ambientes'   => $this->request->getPost('ambientes'),
    ];

    $dadosHorarios = $horariosModel->findHorariosFiltrados($filtros);

return $this->response
    ->setHeader('X-CSRF-TOKEN', csrf_hash()) // Define o novo token no cabeçalho
    ->setJSON([
        'success' => true,
        'data'    => $dadosHorarios
    ]);

}
}