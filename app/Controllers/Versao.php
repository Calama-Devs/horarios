<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\ReferenciaException;

use App\Models\VersoesModel;
use App\Models\AulasModel;
use App\Models\AulaHorarioModel;

class Versao extends BaseController
{
    public function index()
    {
        $versao = new VersoesModel();
        $data['versoes'] = $versao->orderBy('nome', 'asc')->findAll();

        $data['versao_nome'] = $this->content_data['versao_nome'];

        $this->content_data['content'] = view('sys/versoes', $data);
        return view('dashboard', $this->content_data);
    }

    public function salvar()
    {
        $versao = new VersoesModel();

        //coloca todos os dados do formulario no vetor dadosPost
        $dadosPost = $this->request->getPost();
        $dadosLimpos['nome'] = strip_tags($dadosPost['nome']);
        $dadosLimpos['semestre'] = strip_tags($dadosPost['semestre']);

        if ($versao->insert($dadosLimpos))
        {
            session()->setFlashdata('sucesso', 'Versão cadastrado com sucesso.');
            return redirect()->to(base_url('/sys/versao'));
        }
        else
        {
            $data['erros'] = $versao->errors(); //o(s) erro(s)
            return redirect()->to(base_url('/sys/versao'))->with('erros', $data['erros'])->withInput(); //retora com os erros e os inputs
        }
    }

    public function atualizar()
    {
        $dadosPost = $this->request->getPost();

        $dadosLimpos['id'] = strip_tags($dadosPost['id']);
        $dadosLimpos['nome'] = strip_tags($dadosPost['nome']);
        $dadosLimpos['semestre'] = strip_tags($dadosPost['semestre']);

        $versao = new VersoesModel();
        if ($versao->save($dadosLimpos))
        {
            session()->setFlashdata('sucesso', 'Versão atualizado com sucesso.');
            return redirect()->to(base_url('/sys/versao')); // Redireciona para a página de listagem
        }
        else
        {
            $data['erros'] = $versao->errors(); //o(s) erro(s)
            return redirect()->to(base_url('/sys/versao'))->with('erros', $data['erros']); //retora com os erros
        }
    }

    public function deletar()
    {
        $dadosPost = $this->request->getPost();
        $id = (int)strip_tags($dadosPost['id']);

        $versaoModel = new VersoesModel();

        try
        {
            $restricoes = $versaoModel->getRestricoes(['id' => $id]);

            if (!$restricoes['horarios'] && !$restricoes['aulas'])
            {
                if ($versaoModel->delete($id))
                {
                    session()->setFlashdata('sucesso', 'Versão excluída com sucesso!');

                    //Retira versão excluída de usuários que estejam usando ela
                    $versaoModel->retirarVersaoExcluidaDeUsuarios($id);

                    $versao = $versaoModel->getVersaoByUser(auth()->id());

                    if ($versao == $id)
                    {
                        $versao = $versaoModel->getLastVersion();
                        $versaoModel->setVersaoByUser(auth()->id(), $versao);
                    }

                    return redirect()->to(base_url('/sys/versao'));
                }
                else
                {
                    return redirect()->to(base_url('/sys/versao'))->with('erro', 'Erro inesperado ao excluir Versão!');
                }
            }
            else
            {
                $mensagem = "A versão não pode ser excluída.<br>Esta versão possui ";

                if ($restricoes['aulas'] && $restricoes['horarios'])
                {
                    $mensagem = $mensagem . "aula(s) e horário(s) de aula relacionados a ela!";
                }
                else if ($restricoes['aulas'])
                {
                    $mensagem = $mensagem . "aula(s) relacionada(s) a ela!";
                }
                else
                {
                    $mensagem = $mensagem . "horário(s) de aula relacionado(s) a ela!";
                }

                throw new ReferenciaException($mensagem);
            }
        }
        catch (ReferenciaException $e)
        {
            session()->setFlashdata('erro', $e->getMessage());
            return redirect()->to(base_url('/sys/versao'));
        }
    }

    public function ativar()
    {
        $dadosPost = $this->request->getPost();

        $versao = strip_tags($dadosPost['id']);

        $versaoModel = new VersoesModel();

        if ($versaoModel->setVersaoByUser(auth()->id(), $versao))
        {
            session()->setFlashdata('sucesso', 'Versão ativada com sucesso.');
            return redirect()->to(base_url('/sys/versao'));
        }
        else
        {
            $data['erros'] = $versaoModel->errors();
            return redirect()->to(base_url('/sys/versao'))->with('erros', $data['erros']);
        }
    }

    public function duplicar()
    {
        $versaoModel = new VersoesModel();

        //coloca todos os dados do formulario no vetor dadosPost
        $dadosPost = $this->request->getPost();
        $versaoOld = strip_tags($dadosPost['id']);
        $dadosLimpos['nome'] = strip_tags($dadosPost['nome']);
        $dadosLimpos['semestre'] = strip_tags($dadosPost['semestre']);

        if ($versaoModel->insert($dadosLimpos))
        {
            session()->setFlashdata('sucesso', 'Cópia da versão criada com sucesso.');

            //TODO: rotina pra clonar no BD todos os dados que tem relação com versão
            $versaoModel->copyAllData($versaoOld);

            return redirect()->to(base_url('/sys/versao'));
        }
        else
        {
            $data['erros'] = $versaoModel->errors(); //o(s) erro(s)
            return redirect()->to(base_url('/sys/versao'))->with('erros', $data['erros'])->withInput(); //retora com os erros e os inputs
        }
    }
}
