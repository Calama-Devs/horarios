// Requisição para remover a disciplina ao horário no backend
function removerAulaDoHorario(horarioElement, aulaId, horarioId)
{
    $.post(URLbase + 'sys/tabela-horarios/removerAula', 
    {
        aula_id: aulaId,
        tempo_de_aula_id: horarioId
    },
    function(data) 
    {
        if (data == "1") 
        {
            moverDisciplinaParaPendentes(horarioElement);

            // Limpa o horário
            $(horarioElement).html('')
                .removeClass('horario-preenchido')
                .addClass('horario-vazio')
                .removeData(['disciplina', 'professor', 'ambiente', 'aula-id', 'aulas-total', 'aulas-pendentes'])
                .off('click')
                .click(function() {
                    horarioSelecionado = $(this);
                    carregarDisciplinasPendentes($(this).attr('id'));
                    modalAtribuirDisciplina.show();
                });

            configurarDragAndDrop();

            // Fecha o modal
            modalAnalisarHorario.hide();

            // Mostra feedback de sucesso
            mostraNotificacao('sucesso', 'A disciplina foi removida do horário.');
        }
        else 
        {
            // Mostra feedback de erro
            mostraNotificacao('erro', 'Ocorreu um erro ao remover a aula do horário.');
        }
    });
}

// Requisição para atribuir a disciplina ao horário no backend
function atribuirAulaAoHorario(cardAula, aulaId, horarioId, ambienteSelecionadoId, ambientesSelecionados, ambientesSelecionadosNome)
{
    const aula = getAulaById(aulaId);
    
    $.post(URLbase + 'sys/tabela-horarios/atribuirAula',
    {
        aula_id: aulaId,
        tempo_de_aula_id: horarioId,
        ambiente_id: ambienteSelecionadoId
    }, 
    function(data) 
    {
        if (data == "0" || !data) 
        {
            mostraNotificacao('erro', 'Ocorreu um erro ao tentar alterar o ambiente.');                    
            return;
        }

        let tresTurnos = 0;
        let restricao = 0;
        let intervalo = 0;
        let conflitoAmbiente = 0;
        let conflitoProfessor = 0;

        let partes = data.split("-");
        let aulaHorarioId = partes[0];

        let tiposDeConflito = partes[2] || "";
        let aulaConflito = partes[3] || 0;
        
        if (data.indexOf("RESTRICAO") >= 0)
        {
            restricao = partes[3];
        }
        else if (data.indexOf("INTERVALO") >= 0)
        {
            intervalo = data;
        }
        else if (data.indexOf("CONFLITO") >= 0)
        {
            if (data.indexOf("TRES-TURNOS") >= 0) 
            {
                tresTurnos = 1;
            }
            else if (tiposDeConflito.indexOf("AMBIENTE") >= 0) 
            {
                conflitoAmbiente = 1;
            }
            else if (tiposDeConflito.indexOf("PROFESSOR") >= 0) 
            {
                conflitoProfessor = 1;
            }            
        }

        //TODO - verificar todos os atributos aqui que estão sem OK
        let obj = {};
        obj.id = aulaHorarioId;
        obj.aula_id = aulaId;
        obj.tempo_de_aula_id = horarioId;
        obj.ambiente = ambientesSelecionados; //OK
        obj.tresturnos = tresTurnos;
        obj.restricao = restricao;
        obj.intervalo = intervalo;
        obj.choque = aulaConflito;
        obj.fixa = aula.fixa;
        obj.destaque = aula.destaque;
        obj.bypass = aula.bypass;

        // Preenche o horário selecionado
        $(`#horario_${horarioId}`).html(montarCardDeHorario(obj, aula));

        //Configura os botões do card após adicionar
        configurarBotoesDoCardDeAula(obj);

        // Adiciona os dados ao horário
        $(`#horario_${horarioId}`)
            .data('disciplina', aula.disciplina)
            .data('professor', aula.professores.join(", "))
            .data('ambiente', ambienteSelecionadoId)
            .data('ambienteNome', ambientesSelecionadosNome)
            .data('aula-id', aulaId)
            .data('aulas-total', cardAula.data('aulas-total'))
            .data('aulas-pendentes', cardAula.data('aulas-pendentes'))
            .data('conflito', aulaConflito)
            .data('conflitoAmbiente', conflitoAmbiente)
            .data('conflitoProfessor', conflitoProfessor)
            .data('restricao', restricao)
            .data('tresturnos', tresTurnos)
            .data('intervalo', intervalo)
            .data('aula_horario_id', aulaHorarioId)
            .data('fixa', 0)
            .data('destacada', (aula.destaque == 1) ? 1 : 0)
            .removeClass('horario-vazio')
            .addClass('horario-preenchido')
            .off()
            .click(function() {
                mostrarModalConfirmacaoRemocao(this);
            });

        // Atualiza a quantidade de aulas pendentes no card
        const aulasPendentes = cardAula.data('aulas-pendentes') - 1;
        cardAula.data('aulas-pendentes', aulasPendentes);
        cardAula.find('.aulas-pendentes').text(aulasPendentes);

        // Se zerou, remove o card
        if (aulasPendentes <= 0) 
        {
            cardAula.remove();
        }

        atualizarContadorPendentes();

        modalAnalisarHorario.hide();
        modalSelecionarAmbiente.hide();

        // Mostra feedback de sucesso
        mostraNotificacao('sucesso', 'Alteração salva com sucesso.');                
    });
}

// Requisição para buscar os dados da aula causando problema de intervalo
function descreverProblemaDeIntervalo($horario)
{
    $.get(URLbase + 'sys/tabela-horarios/dadosDaAula/' + $horario.data('intervalo').split('-')[2], function(data)
    {
        $('#modalRemocaoIntervaloCurso').text("Curso: " + data[0].curso);
        $('#modalRemocaoIntervaloTurma').text("Turma: " + data[0].turma);
        $('#modalRemocaoIntervaloDisciplina').text("Disciplina: " + data[0].disciplina);

        let motivo = $horario.data('intervalo').split('-')[0];

        let timestamp = $horario.data('intervalo').split('-')[1];
        let horas = parseInt(timestamp / 60);
        let minutos = parseInt(timestamp % 60);

        timestamp = horas + "h e " + minutos + "m";

        $('#modalRemocaoIntervaloTempo').text("Tempo: " + timestamp);

        switch (motivo) 
        {
            case '1':
                $('#modalRemocaoIntervaloTipo').text("Intervalo entre manhã e tarde (mínimo 01 hora).");
                break;
            case '2':
                $('#modalRemocaoIntervaloTipo').text("Intervalo entre tarde e noite (mínimo 01 hora).");
                break;
            case '3':
                $('#modalRemocaoIntervaloTipo').text("Intervalo entre noite e manhã (mínimo 11 horas).");
                break;
            case '4':
                $('#modalRemocaoIntervaloTipo').text("Intervalo entre noite e manhã (mínimo 11 horas).");
                break;
        }

    }, 'json');
}

//Buscar turmas do curso selecionado.
function buscarTurmasDoCurso(curso)
{
    $.get(URLbase + 'sys/turma/getTurmasByCurso/' + curso, function(data) 
    {
        $.each(data, function(idx, obj) 
        {
            $('#filtroTurma').append('<option value="' + obj.id + '">' + obj.sigla + '</option>');
        });
    }, 'json')
    .done(function() 
    {
        $(".loader-demo-box").css("visibility", "hidden");
    });
}

function verificaConflitoDeAmbientes(aulaId, tempoDeAulaId, element)
{
    let ambienteSelecionadoId = element.val();

    $.post(URLbase + 'sys/tabela-horarios/destacar-conflitos-ambiente', 
    {
        aula_id: aulaId, 
        tempo_de_aula_id: tempoDeAulaId
    },
    function(data) 
    {
        let arr = Array.isArray(data) ? data : [];
        let conflitoIds = new Set(arr.map(o => String(o.ambiente_id)));

        //adiciona a tag de conflito à option caso detecte o conflito 
        element.find('option').each(function()
        {
            let id = String($(this).val());
            let textoPadrao = $(this).text().replace(textoConflito, '');
            $(this).remove();
            let newOption = new Option(textoPadrao, id, false, false);

            if (conflitoIds.has(id))
            {
                newOption = new Option(textoPadrao + textoConflito, id, false, false);
            }

            element.append(newOption);
        });

        element.val(ambienteSelecionadoId).trigger("change");

    }, 'json');
}