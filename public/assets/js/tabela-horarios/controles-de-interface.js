function removerTagDeConflitoNosSelects()
{
    $('#selectAmbiente, #alteraAmbiente').find('option').each(function()
    {            
        $(this).text($(this).text().replace(textoConflito, ''));
    });
}

// Modal de confirmação de remoção (estilo Bootstrap)
function mostrarModalConfirmacaoRemocao(horarioElement) 
{
    const $horario = $(horarioElement);
    const aulaId = $horario.data('aula-id');

    horarioId = $horario.attr('id').split('_')[1]; // Extrai o ID do horário

    // Adiciona os dados ao horário
    $("#modalAnalisarHorario")
        .data('aula-id', aulaId)
        .data('aula_horario_id', $horario.data('aula_horario_id'))
        .data('horario_id', $horario.attr('id').split('_')[1]);

    // Preenche os dados no modal
    $('#modalRemocaoDisciplina').text($horario.data('disciplina'));
    $('#modalRemocaoProfessor').text('Professor(es): ' + $horario.data('professor'));
    $('#modalRemocaoAmbiente').text('Ambiente(s): ' + $horario.data('ambienteNome').join(", "));

    $('#rowRestricao').hide();
    $('#rowConflito').hide();
    $('#rowTresTurnos').hide();
    $('#rowIntervalo').hide();

    if ($horario.data('fixa') == 1) 
    {
        $("#confirmarRemocao").prop("disabled", true);
    } 
    else 
    {
        $("#confirmarRemocao").prop("disabled", false);
    }

    let selectedAmbientes = [];

    $.each($horario.data('ambienteNome'), function(index, value) 
    {
        selectedAmbientes.push(getAmbienteId(value));
    });

    $('#alteraAmbiente').val(selectedAmbientes);
    $('#alteraAmbiente').trigger('change');

    //Verifica os possívels conflitos dos ambientes para o select
    verificaConflitoDeAmbientes(aulaId, horarioId, $("#alteraAmbiente"));

    //Verificar e preencher dados do conflito
    if ($horario.data('tresturnos') > 0) 
    {
        $('#rowTresTurnos').show();
    } 
    else if ($horario.data('intervalo') != 0)
    {
        descreverProblemaDeIntervalo($horario);
        $('#rowIntervalo').show();
    } 
    else if ($horario.data('restricao') > 0) 
    {
        $('#rowRestricao').show();
    } 
    else if ($horario.data('conflito') > 0) 
    {
        // Requisição para buscar os dados da aula em conflito
        $.get(URLbase + 'sys/tabela-horarios/dadosDaAula/' + $horario.data('conflito'), function(data) 
        {
            $('#modalRemocaoConflitoCurso').text("Curso: " + data[0].curso);
            $('#modalRemocaoConflitoTurma').text("Turma: " + data[0].turma);
            $('#modalRemocaoConflitoDisciplina').text("Disciplina: " + data[0].disciplina);

            let professores = data[0].professor;
            let ambientes = data[0].ambiente;

            data.forEach(function(value) 
            {
                if (professores.indexOf(value.professor) < 0)
                    professores += ", " + value.professor;

                if (ambientes.indexOf(value.ambiente) < 0)
                    ambientes += ", " + value.ambiente;
            });

            if ($horario.data('conflitoProfessor') == 1) 
            {
                $('#modalRemocaoConflitoProfessor')
                    .html('<i class="fa fa-exclamation-circle me-1"></i> ' + 'Professor(es): ' + professores)
                    .addClass('text-danger')
                    .removeClass('text-warning');
            } 
            else 
            {
                $('#modalRemocaoConflitoProfessor')
                    .text("Professor(es): " + professores)
                    .addClass('text-warning')
                    .removeClass('text-danger');
            }

            if ($horario.data('conflitoAmbiente') == 1) 
            {
                $('#modalRemocaoConflitoAmbiente')
                    .html('<i class="fa fa-exclamation-circle me-1"></i> ' + 'Ambiente(s): ' + ambientes)
                    .addClass('text-danger')
                    .removeClass('text-warning');
            } 
            else 
            {
                $('#modalRemocaoConflitoAmbiente')
                    .text("Ambiente(s): " + ambientes)
                    .addClass('text-warning')
                    .removeClass('text-danger');
            }

            /*$.each(data, function(index, value)
            {
                if($('#modalRemocaoConflitoAmbiente').html().indexOf(value.ambiente) < 0)
                {
                    $('#modalRemocaoConflitoAmbiente').append(value.ambiente + " | ");
                }
            });*/

        }, 'json');

        $('#rowConflito').show();
    }

    // Remove qualquer evento anterior do botão de confirmação
    $('#confirmarRemocao').off('click');

    // Configura o evento de confirmação
    $('#confirmarRemocao').on('click', function() 
    {
        horarioId = $horario.attr('id').split('_')[1]; // Extrai o ID do horário
        removerAulaDoHorario(horarioElement, aulaId, horarioId);                
    });

    // Mostra o modal
    modalAnalisarHorario.show();
}

// Carrega as disciplinas pendentes no modal
function carregarDisciplinasPendentes(id)
{
    id = id.split('_')[1]; // Extrai o ID do horário
    let dadosDoHorario = getHorarioById(id);

    $("#dia_da_aula").html(nome_dia[dadosDoHorario.dia_semana]);
    $("#hora_da_aula").html(dadosDoHorario.hora_inicio + ":" + dadosDoHorario.minuto_inicio);
    $("#modal_Turma").html($('#filtroTurma option:selected').text());

    $("#tabelaDisciplinasModal tbody").empty();

    // Verifica se há uma disciplina atribuída no horário selecionado
    if (horarioSelecionado && horarioSelecionado.data('disciplina')) 
    {
        const row = `
            <tr>
                <td>${horarioSelecionado.data('disciplina')}</td>
                <td>${horarioSelecionado.data('professor')}</td>
                <td>1 aula</td>
                <td><button class="btn btn-danger btn-sm btn-remover">Remover</button></td>
            </tr>
        `;

        $("#tabelaDisciplinasModal tbody").append(row);

        // Evento para botão remover
        $("#tabelaDisciplinasModal .btn-remover").click(function() 
        {
            mostrarModalConfirmacaoRemocao(horarioSelecionado[0]);
            modalAtribuirDisciplina.hide();
        });
    }

    $('.card[draggable="true"]').each(function() 
    {
        let theCard = $(this);

        let disciplinaRow = '' +
            '<tr>' +
                '<td>' + $(this).data("disciplina") + '</td>' +
                '<td>' + $(this).data("professor") + '</td>' +
                '<td>' + $(this).data("aulas-pendentes") + ' aula(s)</td>' +
                '<td>' +
                    '<button type="button" class="btn btn-primary btn-sm botao_atribuir" id="botao_atribuir_' + $(this).data("aula-id") + '" >Atribuir</button>' +
                '</td>' +
            '</tr>';

        $("#tabelaDisciplinasModal tbody").append(disciplinaRow);

        // Adiciona evento de clique diretamente
        $("#botao_atribuir_" + $(this).data("aula-id")).on('click', function() 
        {
            atribuirDisciplina($(this).attr('id').split('_')[2], id);
        });
    });
}

function abrirModalAmbiente(aulaId, tempoDeAulaId) 
{
    removerTagDeConflitoNosSelects();

    //Verifica os possívels conflitos dos ambientes para o select
    verificaConflitoDeAmbientes(aulaId, tempoDeAulaId, $("#selectAmbiente"));

    let minhaAula = getAulaById(aulaId);
    $("#modalAmbienteNomeDisciplina").html(minhaAula.disciplina);
    $("#modalAmbienteProfessor").html(minhaAula.professores.join(", "));
    $("#modalAmbienteAulas").html("1 aula"); // Sempre atribui 1 aula por vez

    // Armazena o ID da aula e horario para uso posterior
    $('#modalSelecionarAmbiente').data('aula-id', aulaId).data('horario-id', tempoDeAulaId);

    modalSelecionarAmbiente.show();
}
    
    // Função para atribuir disciplina ao horário selecionado
function atribuirDisciplina(aulaId, horarioId) 
{
    modalAtribuirDisciplina.hide();

    setTimeout(() => {
        abrirModalAmbiente(aulaId, horarioId);
    }, 100);
}

// Configura drag and drop
function configurarDragAndDrop() 
{
    // Drag start para cards de disciplinas
    $('.card[draggable="true"]').off('dragstart');
    $('.card[draggable="true"]').on('dragstart', function(e) 
    {
        e.originalEvent.dataTransfer.setData('text/plain', $(this).data('aula-id'));
        $(this).addClass('dragging');
    });

    // Drag end para cards de disciplinas
    $('.card[draggable="true"]').off('dragend');
    $('.card[draggable="true"]').on('dragend', function() 
    {
        $(this).removeClass('dragging');
    });

    // Drag over para horários
    $('.horario-vazio').off('dragover');
    $('.horario-vazio').on('dragover', function(e) 
    {
        e.preventDefault();
        $(this).addClass('drag-over');
    });

    // Drag leave para horários
    $('.horario-vazio').off('dragleave');
    $('.horario-vazio').on('dragleave', function() 
    {
        $(this).removeClass('drag-over');
    });

    // Drop para horários
    $('.horario-vazio').off('drop');
    $('.horario-vazio').on('drop', function(e) 
    {
        e.preventDefault();
        $(this).removeClass('drag-over');

        horarioId = $(this).attr('id').split('_')[1]; // Extrai o ID do horário

        const aulaId = e.originalEvent.dataTransfer.getData('text/plain');

        if ($(this).html().trim() !== "") 
        {
            return; // Se o horário já contém uma disciplina, não faz nada
        }

        if (aulaId) 
        {
            horarioSelecionado = $(this);
            atribuirDisciplina(aulaId, horarioId);
        }
    });
}

function montarCardDeHorario(obj, aula)
{
    let ambientesSelecionadosNome = [];

    obj.ambiente.forEach(function(item)
    {
        ambientesSelecionadosNome.push(getAmbienteNome(item));
    });

    let conflitoStyle = "text-primary";
    let conflitoIcon = "fa-mortar-board";

    if (obj.tresturnos > 0 || obj.restricao > 0) 
    {
        conflitoStyle = "text-danger";
        conflitoIcon = "fa-warning";
    }
    else if (obj.choque > 0)
    {
        conflitoStyle = "text-warning";
        conflitoIcon = "fa-warning";
    } 
    else if (obj.intervalo != 0) 
    {
        conflitoStyle = "text-info";
        conflitoIcon = "fa-warning";
    }

    let btnFixar = "text-primary";

    if (obj.fixa == 1)
        btnFixar = "text-warning";

    let btnBypass = "text-primary";

    if (obj.bypass == 1)
        btnBypass = "text-warning";

    return `
        <div class="card border-1 shadow-sm bg-gradient min-height-card" style="cursor: pointer; height: 100%;">
            <div class="card-body p-1 d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="text-wrap mb-0 fs-6 ${conflitoStyle}" style="font-size: 0.75rem !important; margin-right: 15px">
                    <i class="fa ${conflitoIcon} me-1"></i>
                    ${aula.disciplina}
                </h6>
                <div class="d-flex align-items-center mb-0 py-0" style="margin-right: 15px">
                    <i class="mdi mdi-account-tie fs-6 text-muted me-1"></i>
                    <small class="text-wrap text-secondary" style="font-size: 0.65rem !important;">${aula.professores.join(", ")}</small>
                </div>
                <div class="d-flex align-items-center" style="margin-right: 15px">
                    <i class="mdi mdi-door fs-6 text-muted me-1"></i>
                    <small class="text-wrap text-secondary" style="font-size: 0.65rem !important;">${ambientesSelecionadosNome.join("<br />")}</small>
                </div>
                <div style="width: 100%; text-align: right; top: 0; position: absolute">
                    <i class="mdi mdi-close-box fs-6 text-danger me-1" id="btnRemover_horario_${obj.id}"></i><br />
                    <i class="mdi mdi-lock fs-6 ${btnFixar} me-1" id="btnFixar_horario_${obj.id}"></i><br />
                    <i class="mdi mdi-account-multiple fs-6 ${btnBypass} me-1" id="btnBypass_horario_${obj.id}"></i><br />
                    <i class="mdi ${(obj.aula_destaque == 1 || obj.destaque == 1) ? 'mdi-star text-warning' : 'mdi-star-outline text-primary'} fs-6 me-1" id="btnDestacar_horario_${obj.id}"></i>
                </div>
            </div>
        </div>
    `;
}

function montarCardDeAula(aulaId, disciplina, professor, aulasTotal, aulasPendentes)
{
    let cardAula = `
        <div id="aula_${aulaId}" draggable="true" data-aula-id="${aulaId}" data-disciplina="${disciplina}" data-professor="${professor}" data-aulas-total="${aulasTotal}" data-aulas-pendentes="${aulasPendentes}" class="card border-1 shadow-sm mx-4 my-1 bg-gradient" style="cursor: pointer;">
            <div class="card-body p-0 d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="text-primary">
                    <i class="mdi mdi-book-outline me-1"></i> ${disciplina}
                </h6>
                <div class="d-flex align-items-center mb-0 py-0" id="professor_aula_${aulaId}">
                    <i class="mdi mdi-account-tie fs-6 text-muted me-1"></i>
                    <small class="text-secondary">${professor}</small>
                </div>
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-door fs-6 text-muted me-1"></i>
                    <small class="text-secondary"><span class="aulas-pendentes">${aulasPendentes}</span> aula(s)</small>
                </div>
            </div>
        </div>
    `;

    return cardAula;
}

function mostraNotificacao(tipo, texto)
{
    let heading = 'Sucesso';
    let icon = 'success';
    let loaderBg = '#1e9c1eff';

    if(tipo == 'erro')
    {
        heading = 'Erro';
        icon = 'error';
        loaderBg = '#f96868';
    }

    $.toast({
        heading: heading,
        text: texto,
        showHideTransition: 'slide',
        icon: icon,
        loaderBg: loaderBg,
        position: 'top-center'
    });
}

// Função para atualizar contador de pendentes
function atualizarContadorPendentes() 
{
    let totalAulasPendentes = 0;
    $('.card[draggable="true"]').each(function() 
    {
        totalAulasPendentes += parseInt($(this).data('aulas-pendentes'));
    });

    $('#aulasCounter').text(totalAulasPendentes);
}

// Função para mover disciplina de volta para pendentes
function moverDisciplinaParaPendentes(horarioElement) 
{
    const $horario = $(horarioElement);
    const disciplina = $horario.data('disciplina');
    const professor = $horario.data('professor');
    const aulaId = $horario.data('aula-id');
    const aulasTotal = $horario.data('aulas-total') || '1';

    // Verifica se já existe na lista de pendentes
    if ($(`#aula_${aulaId}`).length > 0) 
    {
        const cardAula = $(`#aula_${aulaId}`);
        const aulasPendentes = cardAula.data('aulas-pendentes') + 1;
        cardAula.data('aulas-pendentes', aulasPendentes);
        cardAula.find('.aulas-pendentes').text(aulasPendentes);
    } 
    else 
    {
        let cardAula = montarCardDeAula(aulaId, disciplina, professor, aulasTotal, 1);
        $('#aulasContainer').append(cardAula);

        configurarDragAndDrop(); // Reconfigura eventos para o novo card
    }

    atualizarContadorPendentes();
}

function limparHorarios() 
{
    $('.horario-preenchido').each(function() 
    {
        const aulaId = $(this).data('aula-id');
        let tempo_de_aula_id = $(this).attr('id').split('_')[1];

        if ($(`#horario_${tempo_de_aula_id}`).data('fixa') == 1) 
        {
            return;
        }

        // Requisição para remover a disciplina ao horário no backend
        $.post(URLbase + 'sys/tabela-horarios/removerAula',
        {
            aula_id: aulaId,
            tempo_de_aula_id: tempo_de_aula_id
        },
        function(data) 
        {
            if (data == "1") 
            {
                moverDisciplinaParaPendentes($(`#horario_${tempo_de_aula_id}`));

                // Limpa o horário
                $(`#horario_${tempo_de_aula_id}`).html('')
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
            }
        });
    });

    // Atualiza o contador de pendentes
    atualizarContadorPendentes();
}

// Configura o evento de confirmação do ambiente
$("#confirmarAmbiente").click(function(e) 
{
    e.preventDefault();
    e.stopPropagation();

    removerTagDeConflitoNosSelects();

    let ambienteSelecionadoId = $("#selectAmbiente").val();

    let ambientesSelecionadosNome = [];
    let ambientesSelecionados = [];

    let data = $('#selectAmbiente').select2('data');

    data.forEach(function(item)
    {
        item.text = item.text.replace(textoConflito, '');
        ambientesSelecionadosNome.push(item.text);
        ambientesSelecionados.push(item.id);
    });

    let aulaId = $('#modalSelecionarAmbiente').data('aula-id');
    let cardAula = $(`#aula_${aulaId}`);
    let horarioId = $('#modalSelecionarAmbiente').data('horario-id');

    if (horarioSelecionado)
    {
        atribuirAulaAoHorario(cardAula, aulaId, horarioId, ambienteSelecionadoId, ambientesSelecionados, ambientesSelecionadosNome);                
    }

});

//Configura o botao de limpar horários
$("#btn_limpar_horarios").click(function() 
{
    if (confirm("Você tem certeza que deseja limpar todos os horários preenchidos? Esta ação não pode ser desfeita.")) 
    {
        limparHorarios();
    }
});

// Configura o evento de confirmação do ambiente
$("#confirmarAlterarAmbiente").click(function(e) 
{
    e.preventDefault();
    e.stopPropagation();

    removerTagDeConflitoNosSelects();

    const ambienteSelecionadoId = $("#alteraAmbiente").val();

    let ambientesSelecionadosNome = [];
    let ambientesSelecionados = [];
    
    let data = $('#alteraAmbiente').select2('data');

    data.forEach(function(item) 
    {
        item.text = item.text.replace(textoConflito, '');
        ambientesSelecionadosNome.push(item.text);
        ambientesSelecionados.push(item.id);
    });

    const aulaId = $('#modalAnalisarHorario').data('aula-id');
    const cardAula = $(`#aula_${aulaId}`);
    const horarioId = $('#modalAnalisarHorario').data('horario_id');

    atribuirAulaAoHorario(cardAula, aulaId, horarioId, ambienteSelecionadoId, ambientesSelecionados, ambientesSelecionadosNome);

});

//Programação do evento "change" dos select de cursos
$('#filtroCurso').on('change', function() 
{
    aulas = [];

    $(".loader-demo-box").css("visibility", "visible");

    //Limpar a tabela de horários inteira
    $("#tabela-horarios").empty();

    //Limpar card de aulas pendentes
    $('#aulasContainer').empty();

    atualizarContadorPendentes();

    $('#filtroTurma').find('option').remove().end().append('<option value="0">-</option>');
    $('#filtroTurma option[value="0"]').prop('selected', true);

    buscarTurmasDoCurso($('#filtroCurso').val());

});

function montarTabelaTurno(turno, dias, htmlDaTableHead)
{
    let htmlDaTabela = '' +
        '<thead>' +
        '<tr>' +
        '<th colspan="' + (dias.length + 1) + '" class="text-center bg-primary text-white">' + turno.toUpperCase() +'</th>' +
        '</tr>' +
        '</thead>' +
        htmlDaTableHead;

    $('#tabela-horarios').append(htmlDaTabela);

    $('#tabela-horarios').append('<tbody id="tabela-horarios-' + turno +'">');

    //Vetor para guardar os horarios já adicionados na tabela
    let horariosJaAdicionados = [];

    $.each(horarios, function(idx, obj) 
    {
        //Verificar se já tem o horário na lista
        let jaTemHorario = false;

        $.each(horariosJaAdicionados, function(idx2, obj2) 
        {
            if (obj2.hora_inicio == obj.hora_inicio && obj2.minuto_inicio == obj.minuto_inicio && obj2.hora_fim == obj.hora_fim && obj2.minuto_fim == obj.minuto_fim) 
            {
                jaTemHorario = true;
            }
        });

        if (!jaTemHorario) 
        {            
            let linhaDeHorarios = '' +
                '<tr>' +
                '<td class="coluna-fixa">' + obj.hora_inicio + ':' + obj.minuto_inicio + '-' + obj.hora_fim + ':' + obj.minuto_fim + '</td>';
            
            for (let i = 0; i < dias.length; i++) 
            {
                linhaDeHorarios += '<td class="horario-vazio" id="horario_' +
                getIdByDiaHoraMinuto(horarios, dias[i], obj.hora_inicio, obj.minuto_inicio, obj.hora_fim, obj.minuto_fim) +
                '"></td>';
            }

            linhaDeHorarios += '' +
                '</tr>'

            if (turno == 'manhã' && obj.hora_inicio < 13)
            {
                $('#tabela-horarios-' + turno).append(linhaDeHorarios);                
            }
            else if (turno == 'tarde' && obj.hora_inicio >= 13 && obj.hora_inicio < 18)
            {
                $('#tabela-horarios-' + turno).append(linhaDeHorarios);                
            }
            else if (turno == 'noite' && obj.hora_inicio >= 18)
            {
                $('#tabela-horarios-' + turno).append(linhaDeHorarios);                
            }

            let gravaHorario = {
                hora_inicio: obj.hora_inicio,
                minuto_inicio: obj.minuto_inicio,
                hora_fim: obj.hora_fim,
                minuto_fim: obj.minuto_fim
            };

            horariosJaAdicionados.push(gravaHorario);
        }
    });

    $('#tabela-horarios').append('</tbody>');
}

//Programação do evento "change" dos select de turmas
$('#filtroTurma').on('change', function() 
{
    removerTagDeConflitoNosSelects();
    
    aulas = [];

    $(".loader-demo-box").css("visibility", "visible");

    //Limpar a tabela de horários inteira
    $("#tabela-horarios").empty();

    atualizarContadorPendentes();

    if ($('#filtroTurma').val() != 0) 
    {
        let quantasAulas = 0;

        //Buscar aulas da turma selecionada.
        $.get(URLbase + 'sys/aulas/getAulasFromTurma/' + $('#filtroTurma').val(), function(data) 
        {
            //Limpar todas as aulas pendentes.
            $('#aulasContainer').empty();

            //Verifica se a aula atual já está na lista, para a questão de mais de um professor.
            $.each(data, function(idx, obj) 
            {
                let found = false;

                //Vetor dentro do obj para casos de aulas com mais de um professor
                obj.professores = [];

                //Verifica se a aula atual já está na lista, para a questão de mais de um professor.
                $("#aulasContainer").children().each(function() 
                {
                    //Verifica o numero da aula através do id do card.
                    let aula = $(this).attr('id').split('_')[1];

                    if (aula == obj.id) 
                    {
                        found = true; //encontrado

                        //Adiciona o professor na aula já existente (visual do card)
                        $('#professor_aula_' + obj.id).append(' &nbsp; ' +
                            '<i class="mdi mdi-account-tie fs-6 text-muted me-1"></i>' +
                            '<small class="text-secondary">' + obj.professor + '</small>'
                        );

                        //Adiciona o professor na aula já existente (atributo data-professor)
                        $('#aula_' + obj.id).data('professor', $('#aula_' + obj.id).data('professor') + ',' + obj.professor);

                        //Coloca o professor adicional no vetor da aula já existente
                        let objetoAlterar = getAulaById(obj.id);
                        objetoAlterar.professores.push(obj.professor);
                    }
                });

                let regime = $('#filtroCurso option:selected').data('regime');

                //Se não encontrou a aula atual, adiciona na lista.
                if (!found) 
                {
                    let aulasTotal = (obj.ch / ((regime == 2) ? 20 : 40));
                    let cardAula = montarCardDeAula(obj.id, obj.disciplina, obj.professor, aulasTotal, aulasTotal);
                    $('#aulasContainer').append(cardAula);

                    //Coloca o professor no vetor da aula
                    obj.professores.push(obj.professor);

                    //adiciona a aula carregada no vetor de aulas
                    aulas.push(obj);

                    //faz o somatório de aulas da turma
                    quantasAulas += aulasTotal;
                }
            });
        }, 'json')
        .done(function() 
        {
            $("#aulasCounter").html(quantasAulas);
            $("#btn_atribuir_automaticamente").prop('disabled', false);
            configurarDragAndDrop();
            $(".loader-demo-box").css("visibility", "hidden");

            //Buscar horários da turma selecionada para montar a tabela de horários.
            $.get(URLbase + 'sys/tempoAula/getTemposFromTurma/' + $('#filtroTurma').val(), function(data) 
            {
                let dias = [];

                horarios = []; //Limpa o vetor de horários

                let temManha = false;
                let temTarde = false;
                let temNoite = false;

                $.each(data['tempos'], function(idx, obj) 
                {
                    //Montar o array com os dias do horário da turma
                    if (dias.includes(obj.dia_semana) == false) 
                    {
                        dias.push(obj.dia_semana);
                    }

                    //Preencher o vetor de horários com todos os horarios lidos no getTemposFromTurma
                    let horario = {
                        id: obj.id,
                        dia_semana: obj.dia_semana,
                        hora_inicio: obj.hora_inicio,
                        minuto_inicio: obj.minuto_inicio,
                        hora_fim: obj.hora_fim,
                        minuto_fim: obj.minuto_fim
                    };

                    horarios.push(horario);

                    //Verifica se tem horário de manhã, tarde ou noite
                    if (obj.hora_inicio < 12)
                        temManha = true;
                    if (obj.hora_inicio >= 12 && obj.hora_inicio < 18)
                        temTarde = true;
                    if (obj.hora_inicio >= 18)
                        temNoite = true;
                });

                let htmlDaTableHead = '' +
                    '<tr>' +
                    '<th class="col-1">Horário</th>';

                //Iterar pelos dias existentes no horário
                $.each(dias, function(idx, obj) 
                {
                    htmlDaTableHead += '<th class="col-1">' + nome_dia[obj] + '</th>';
                });

                htmlDaTableHead += '' +
                    '</tr>';

                //Insere os horários na tabela se tiver aula pela manhã
                if (temManha) 
                {
                    montarTabelaTurno('manhã', dias, htmlDaTableHead);
                }

                //Insere os horários na tabela se tiver aula pela tarde
                if (temTarde) 
                {
                    montarTabelaTurno('tarde', dias, htmlDaTableHead);
                }

                //Insere os horários na tabela se tiver aula pela tarde
                if (temNoite) 
                {
                    montarTabelaTurno('noite', dias, htmlDaTableHead);
                }

                // Configura eventos após criar a tabela
                configurarDragAndDrop();

                $(".horario-vazio").click(function() 
                {
                    horarioSelecionado = $(this);
                    carregarDisciplinasPendentes($(this).attr('id'));
                    modalAtribuirDisciplina.show();
                });

                let counter = 0;

                $.each(data['aulas'], function(idx, obj) 
                {
                    counter++;

                    setTimeout(function() 
                    {
                        const aulaSelecionadaId = obj.aula_id;
                        const aula = getAulaById(obj.aula_id);

                        aula.destaque = obj.destaque || 0;

                        const ambienteSelecionadoId = obj.ambiente_id;

                        let ambientesSelecionadosNome = [];

                        obj.ambiente.forEach(function(item) 
                        {
                            ambientesSelecionadosNome.push(getAmbienteNome(item));
                        });

                        horarioSelecionado = $(`#horario_${obj.tempo_de_aula_id}`);
                        cardAula = $(`#aula_${obj.aula_id}`);

                        // Preenche o horário selecionado
                        horarioSelecionado.html(montarCardDeHorario(obj, aula));

                        //Configura os botões do card após adicionar
                        configurarBotoesDoCardDeAula(obj);                                

                        // Adiciona os dados ao horário
                        horarioSelecionado
                            .data('disciplina', aula.disciplina)
                            .data('professor', aula.professores.join(", "))
                            .data('ambiente', ambienteSelecionadoId)
                            .data('ambienteNome', ambientesSelecionadosNome)
                            .data('aula-id', obj.aula_id)
                            .data('aulas-total', cardAula.data('aulas-total'))
                            .data('aulas-pendentes', cardAula.data('aulas-pendentes'))
                            .data('conflito', obj.choque)
                            .data('conflitoAmbiente', obj.choqueAmbiente)
                            .data('conflitoProfessor', obj.choqueProfessor)
                            .data('restricao', obj.restricao)
                            .data('tresturnos', obj.tresturnos)
                            .data('intervalo', obj.intervalo)
                            .data('aula_horario_id', obj.id)
                            .data('fixa', obj.fixa)
                            .data('destacada', (obj.destaque == 1 || obj.aula_destaque == 1) ? 1 : 0)
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

                    }, 50 * counter); // Atraso de 50ms para cada iteração
                });

                // Configura eventos após preencher a tabela
                configurarDragAndDrop();

            }, 'json');
        });
    }
    else // nenhuma turma selecionada
    {
        //Limpar a tabela de horários inteira
        $("#tabela-horarios").empty();

        //Limpar card de aulas pendentes
        $('#aulasContainer').empty();

        //Esconder o div do loader
        $(".loader-demo-box").css("visibility", "hidden");
    }
});

//Configurações diversas de componentes visuais
$("#selectAmbiente").select2({dropdownParent: $('#modalSelecionarAmbiente')});
$("#alteraAmbiente").select2({dropdownParent: $('#modalAnalisarHorario')});
$('.js-basic-single').select2({placeholder: "Selecione uma opção:",width: '100%'});
$("body").addClass("sidebar-icon-only");