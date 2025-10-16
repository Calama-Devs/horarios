//Função para retornar o objeto de uma aula pelo id
function getAulaById(id) 
{
    let theIdObj = null;

    $.each(aulas, function(idx, obj) 
    {
        if (obj.id == id) 
        {
            theIdObj = obj;
            return false; //simula o BREAK no .each do JQuery
        }
    });

    return theIdObj;
}

function getAmbienteNome(id) 
{
    let ambienteNome = "";

    $("#selectAmbiente option").each(function() 
    {
        if ($(this).val() == id) 
        {
            ambienteNome = $(this).text();
        }
    });

    return ambienteNome;
}

function getAmbienteId(nome) 
{
    let ambienteId = -1;

    $("#selectAmbiente option").each(function() 
    {
        if ($(this).text().startsWith(nome) || $(this).text().indexOf(nome) >= 0)
        {
            ambienteId = $(this).val();
            return false; //simula o BREAK no .each do JQuery
        }
    });

    return ambienteId;
}

//Função para pesquisar o id de um horário pelo dia e horários
function getIdByDiaHoraMinuto(vetor, dia, hora_inicio, minuto_inicio, hora_fim, minuto_fim) 
{
    let id = 0;

    $.each(vetor, function(idx, obj) 
    {
        if (obj.dia_semana == dia && obj.hora_inicio == hora_inicio && obj.minuto_inicio == minuto_inicio && obj.hora_fim == hora_fim && obj.minuto_fim == minuto_fim) 
        {
            id = obj.id;
            return false; //simula o BREAK no .each do JQuery
        }
    });

    return id;
}

//Função para retornar os dados de um horário pelo id
function getHorarioById(id) 
{
    let theIdObj = null;

    $.each(horarios, function(idx, obj) 
    {
        if (obj.id == id) 
        {
            theIdObj = obj;
            return false; //simula o BREAK no .each do JQuery
        }
    });

    return theIdObj;
}