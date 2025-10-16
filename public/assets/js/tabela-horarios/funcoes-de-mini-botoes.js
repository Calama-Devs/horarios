function fixarAulaHorario(tipo, aula_horario_id, aula_id, url)
{
    elemento = $(`#horario_${aula_id}`);

    $.post(url,
    {
        tipo: tipo, //1 = fixar, 0 = desfixar
        aula_horario_id: aula_horario_id
    },
    function(data) 
    {
        if (data == "1") 
        {
            //encontrar o botão e mudar a cor, além de desativar a remoção de alguma forma
            if (tipo == 1) 
            {
                $("#btnFixar_horario_" + aula_horario_id)
                    .removeClass("text-primary")
                    .addClass("text-warning")
                    .off()
                    .click(function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        fixarAulaHorario(0, aula_horario_id, aula_id, url); //desfixar
                    });

                mostraNotificacao('sucesso', 'A aula foi marcada como fixa no horário.');

                elemento.data('fixa', 1);
            } 
            else 
            {
                $("#btnFixar_horario_" + aula_horario_id)
                    .removeClass("text-warning")
                    .addClass("text-primary")
                    .off()
                    .click(function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        fixarAulaHorario(1, aula_horario_id, aula_id, url); //fixar
                    });

                mostraNotificacao('sucesso', 'A aula foi desmarcada como fixa no horário.');

                elemento.data('fixa', 0);
            }
        } 
        else 
        {
            mostraNotificacao('erro', 'Ocorreu um erro ao tentar fixar/desafixar a aula no horário.');
        }
    });
}

function bypassarAulaHorario(tipo, aula_horario_id, aula_id, url) 
{
    elemento = $(`#horario_${aula_id}`);

    $.post(url, 
    {
        tipo: tipo, //1 = bypass, 0 = desbypass
        aula_horario_id: aula_horario_id
    },
    function(data) 
    {
        if (data == "1") 
        {
            //encontrar o botão pelo nomezim e mudar a cor
            if (tipo == 1) 
            {
                $("#btnBypass_horario_" + aula_horario_id)
                    .removeClass("text-primary")
                    .addClass("text-warning")
                    .off()
                    .click(function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        bypassarAulaHorario(0, aula_horario_id, aula_id, url); //desbypassar
                    });

                mostraNotificacao('sucesso', 'A aula foi marcada como bypass no horário.');
            } 
            else 
            {
                $("#btnBypass_horario_" + aula_horario_id)
                    .removeClass("text-warning")
                    .addClass("text-primary")
                    .off()
                    .click(function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        bypassarAulaHorario(1, aula_horario_id, aula_id, url); //bypassar
                    });

                mostraNotificacao('sucesso', 'A aula foi desmarcada como bypass no horário.');
            }
        } 
        else 
        {
            mostraNotificacao('erro', 'Ocorreu um erro ao tentar adicionar ou remover bypass da aula no horário.');
        }
    });
}

function removerAulaHorario(obj, url) 
{
    // Requisição para remover a disciplina ao horário no backend
    $.post(url,
    {
        aula_id: obj.aula_id,
        tempo_de_aula_id: obj.tempo_de_aula_id
    },
    function(data) 
    {
        if (data == "1") 
        {
            moverDisciplinaParaPendentes($(`#horario_${obj.tempo_de_aula_id}`));

            // Limpa o horário
            $(`#horario_${obj.tempo_de_aula_id}`).html('')
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

            mostraNotificacao('sucesso', 'A disciplina foi removida do horário.');
        } 
        else 
        {
            mostraNotificacao('erro', 'Ocorreu um erro ao remover a aula do horário.');
        }
    });
}

function destacarAula(obj, isDestaque, tipo, url)
{
    $.post(url,
    {
        aula_horario_id: obj.id,
        tipo: tipo
    },
    function(data)
    {
        if (data == "1")
        {
            if (tipo === 1)
            {
                $(`#btnDestacar_horario_${obj.id}`)
                    .removeClass("mdi-star-outline text-primary")
                    .addClass("mdi-star text-warning");
            }
            else 
            {
                $(`#btnDestacar_horario_${obj.id}`)
                    .removeClass("mdi-star text-warning")
                    .addClass("mdi-star-outline text-primary");
            }

            mostraNotificacao('sucesso', 'Operação realizada com sucesso.');
        }
        else if(data === "2")
        {
            mostraNotificacao('erro', 'Não é possível alterar o destaque pois a aula está marcada como destacada no cadastro.');
        }
        else 
        {
            mostraNotificacao('erro', 'Ocorreu um erro ao remover o destaque da aula.');
        }
    });
}

function configurarBotoesDoCardDeAula(obj)
{
    $("#btnRemover_horario_" + obj.id).off().click(function(e) 
    {
        e.preventDefault();
        e.stopPropagation();

        if ($(`#horario_${obj.tempo_de_aula_id}`).data('fixa') == 1) 
        {
            mostraNotificacao('erro', 'Aula fixada, não pode ser removida.');
            return;
        }

        removerAulaHorario(obj, URLbase + 'sys/tabela-horarios/' + 'removerAula');
    });

    $("#btnFixar_horario_" + obj.id).off().click(function(e) 
    {
        e.preventDefault();
        e.stopPropagation();

        if (obj.fixa == 1)
            fixarAulaHorario(0, obj.id, obj.tempo_de_aula_id, URLbase + 'sys/tabela-horarios/' + 'fixarAula'); //desfixar
        else
            fixarAulaHorario(1, obj.id, obj.tempo_de_aula_id, URLbase + 'sys/tabela-horarios/' + 'fixarAula'); //fixar
    });

    $("#btnBypass_horario_" + obj.id).off().click(function(e)
    {
        e.preventDefault();
        e.stopPropagation();

        if (obj.bypass == 1)
            bypassarAulaHorario(0, obj.id, obj.tempo_de_aula_id, URLbase + 'sys/tabela-horarios/' + 'bypassAula'); //desbypass
        else
            bypassarAulaHorario(1, obj.id, obj.tempo_de_aula_id, URLbase + 'sys/tabela-horarios/' +  'bypassAula'); //bypass
    });    

    $("#btnDestacar_horario_" + obj.id).off().click(function(e) 
    {
        e.preventDefault();
        e.stopPropagation();

        const isDestaque = $(this).hasClass("mdi-star");
        const tipo = isDestaque ? 0 : 1;

        destacarAula(obj, isDestaque, tipo, URLbase + 'sys/tabela-horarios/' +  'destacarAula');
    });    
}