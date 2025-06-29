<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\CursosModel;
use App\Models\VersoesModel;
use App\Models\AmbientesModel;
use App\Models\AulaHorarioModel;
use App\Models\AulaHorarioAmbienteModel;

class TabelaHorarios extends BaseController
{
    public function index()
    {
        $ambientesModel = new AmbientesModel();
        $data['ambientes'] = $ambientesModel->orderBy('nome')->findAll();

        $cursosModel = new CursosModel();
        $data['cursos'] = $cursosModel->orderBy('nome')->findAll();

        $versaoModel = new VersoesModel();
        $versao = $versaoModel->getVersaoByUser(auth()->id());
        if (empty($versao))
        {
            $versao = $versaoModel->getLastVersion();
            $versaoModel->setVersaoByUser(auth()->id(), $versao);
        }

        if ($versao > 0)
        {
            $versao = $versaoModel->find($versao);
            $data['semestre'] = $versao['semestre'];
        }

        $this->content_data['content'] = view('sys/tabela-horarios.php', $data);
        
        return view('dashboard', $this->content_data);
    }

    public function atribuirAula()
    {
        $dadosPost = $this->request->getPost();
        $dado['aula_id'] = strip_tags($dadosPost['aula_id']);
        $dado['tempo_de_aula_id'] = strip_tags($dadosPost['tempo_de_aula_id']);
        
        $versaoModel = new VersoesModel();
        $dado['versao_id'] = $versaoModel->getVersaoByUser(auth()->id());

        $aulaHorarioModel = new AulaHorarioModel();

        //verificar se já existe para fazer a substituição
        $aulaHorarioModel->deleteAulaNoHorario($dado['aula_id'], $dado['tempo_de_aula_id'], $dado['versao_id']);

        if ($aulaHorarioModel->insert($dado))
        {
            $aulaHorarioId = $aulaHorarioModel->getInsertID();

            $aulaHorarioAmbienteModel = new AulaHorarioAmbienteModel();

            if(is_array($dadosPost['ambiente_id'])) //se tiver mais de um ambiente
            {
                foreach ($dadosPost['ambiente_id'] as $k => $v)
                {
                    $insert = ["aula_horario_id" => $aulaHorarioId, "ambiente_id" => $v];
                    $aulaHorarioAmbienteModel->insert($insert);
                }
            }
            else  //apenas um ambiente
            {
                $insert = ["aula_horario_id" => $aulaHorarioId, "ambiente_id" => $dadosPost['ambiente_id']];
                $aulaHorarioAmbienteModel->insert($insert);
            }

            $tresturnos = $aulaHorarioModel->verificarTresTurnos($aulaHorarioId);

            if ($tresturnos > 0)
            {
                echo "$aulaHorarioId-TRES-TURNOS";
                return;
            }

            $restricao = $aulaHorarioModel->restricaoDocente($aulaHorarioId);

            if ($restricao > 0)
            {
                echo "$aulaHorarioId-RESTRICAO-PROFESSOR-$restricao"; // restrição de professor
                return;
            }

            $choque = $aulaHorarioModel->choqueAmbiente($aulaHorarioId);

            if ($choque > 0)
            {
                echo "$aulaHorarioId-CONFLITO-AMBIENTE-$choque"; // choque de ambiente
                return;
            }

            $choque = $aulaHorarioModel->choqueDocente($aulaHorarioId);

            if ($choque > 0)
            {
                echo "$aulaHorarioId-CONFLITO-PROFESSOR-$choque"; // choque de professor
                return;
            }

            $intervalo = $aulaHorarioModel->verificarTempoEntreTurnos($aulaHorarioId);

            if ($intervalo > 0)
            {
                echo "$intervalo-INTERVALO";
                return;
            }

            echo "$aulaHorarioId-OK"; // tudo certo e sem choques
        }
        else
        {
            echo "0"; //erro de inserção .. sera?
        }
    }

    public function removerAula()
    {
        $dadosPost = $this->request->getPost();
        $dado['aula_id'] = strip_tags($dadosPost['aula_id']);
        $dado['tempo_de_aula_id'] = strip_tags($dadosPost['tempo_de_aula_id']);

        //echo $dado['aula_id'] . " - " . $dado['tempo_de_aula_id'];

        $versaoModel = new VersoesModel();
        $dado['versao_id'] = $versaoModel->getVersaoByUser(auth()->id());

        $aulaHorarioModel = new AulaHorarioModel();

        //verificar se já existe para fazer a substituição
        $aulaHorarioModel->deleteAulaNoHorario($dado['aula_id'], $dado['tempo_de_aula_id'], $dado['versao_id']);
        echo "1";
    }

    public function fixarAula()
    {
        $dadosPost = $this->request->getPost();
        $dado['aula_horario_id'] = strip_tags($dadosPost['aula_horario_id']);

        $aulaHorarioModel = new AulaHorarioModel();

        if($dadosPost['tipo'] == 1)
        {
            $aulaHorarioModel->fixarAulaHorario($dado['aula_horario_id']);
        }
        else
        {
            $aulaHorarioModel->desfixarAulaHorario($dado['aula_horario_id']);
        }
        echo "1";
    }

    public function bypassAula()
    {
        $dadosPost = $this->request->getPost();
        $dado['aula_horario_id'] = strip_tags($dadosPost['aula_horario_id']);

        $aulaHorarioModel = new AulaHorarioModel();

        if($dadosPost['tipo'] == 1)
        {
            $aulaHorarioModel->bypassarAulaHorario($dado['aula_horario_id']);
        }
        else
        {
            $aulaHorarioModel->desBypassarAulaHorario($dado['aula_horario_id']);
        }
        echo "1";
    }

    public function dadosDaAula($aulaHorarioId)
    {
        $aulaHorarioModel = new AulaHorarioModel();
        $data = $aulaHorarioModel->getAulaHorario($aulaHorarioId);
        echo json_encode($data);
    }

    public function teste($aulaHorarioId)
    {
        $aulaHorarioModel = new AulaHorarioModel();
        $data = $aulaHorarioModel->choqueAmbiente($aulaHorarioId);
        echo json_encode($data);
    }


}
