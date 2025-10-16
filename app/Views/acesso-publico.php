<?php

use App\Models\CursosModel;
use App\Models\ProfessorModel;
use App\Models\AmbientesModel;
use App\Models\VersoesModel;

$cursosModel     = new CursosModel();
$professorModel = new ProfessorModel();
$ambientesModel  = new AmbientesModel();
$versoesModel    = new VersoesModel();

$cursos         = $cursosModel->orderBy('nome', 'ASC')->findAll();
$professores    = $professorModel->orderBy('nome', 'ASC')->findAll();
$ambientes      = $ambientesModel->orderBy('nome', 'ASC')->findAll();
$versaoVigente  = $versoesModel->where('vigente', '1')->first();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Horarios - IFRO Calama</title>

    <link rel="stylesheet" href="<?= base_url("assets/vendors/select2/select2.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/modern-vertical/style.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/vendors/jquery-toast-plugin/jquery.toast.min.css"); ?>">
    <link rel="shortcut icon" href="<?= base_url("assets/images/logo-ifro-mini.png"); ?>" />
    <link rel="stylesheet" href="<?= base_url("assets/css/acesso-publico.css"); ?>">

    <script src="<?= base_url("assets/vendors/js/vendor.bundle.base.js"); ?>"></script>
    <script src="<?= base_url("assets/vendors/select2/select2.min.js"); ?>"></script>
    <script src="<?= base_url("assets/vendors/jquery-toast-plugin/jquery.toast.min.js"); ?>"></script>


</head>

<body>          
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="page-header">
                    <h3 class="page-title">HORÁRIOS DE AULA - IFRO CALAMA</h3>
                    <img src="<?= base_url("assets/images/Planifica-s-fundo.png"); ?>" alt="Logo PlanIFica" style="height: 45px;">
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filtros</h4>
                        <form id="formFiltros">
                            <input type="hidden" class="csrf" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroCurso">Curso</label>
                                        <select class="form-control select2-single" id="filtroCurso" name="cursos[]">
                                            <option value="">Selecione um curso</option>
                                            <?php foreach ($cursos as $curso): ?>
                                                <option value="<?= $curso['id'] ?>"><?= esc($curso['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroTurma">Turma</label>
                                        <select class="form-control select2-single" id="filtroTurma" name="turmas[]" disabled>
                                            <option value="">Selecione um curso primeiro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroProfessor">Professor</label>
                                        <select class="form-control select2-single" id="filtroProfessor" name="professores[]">
                                            <option value="">Selecione um professor</option>
                                            <?php foreach ($professores as $professor): ?>
                                                <option value="<?= $professor['id'] ?>"><?= esc($professor['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filtroAmbiente">Ambiente</label>
                                        <select class="form-control select2-single" id="filtroAmbiente" name="ambientes[]">
                                            <option value="">Selecione um ambiente</option>
                                            <?php foreach ($ambientes as $ambiente): ?>
                                                <option value="<?= $ambiente['id'] ?>"><?= esc($ambiente['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group d-flex justify-content-start mt-4">
                                <button type="button" id="btnFiltrar" class="btn btn-primary me-2">
                                    <i class="mdi mdi-eye-outline me-1"></i>Visualizar Horários
                                </button>
                                <button type="button" id="btnLimpar" class="btn btn-secondary me-2">
                                    <i class="mdi mdi-filter-remove me-1"></i>Limpar Filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12" id="resultadosContainer">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <p class="text-center text-muted">
                            Nenhum horário para exibir. Selecione os filtros e clique em "Visualizar Horários".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
            PlanIFica :: <a href="javascript: void()">Calama Devs</a>.
        </span>
    </footer>
</div>

<script>
$(document).ready(function() {
/*     $.ajaxSetup({
         headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' } 
        }); */

    $(".select2-single").select2({
        allowClear: true,
        width: '100%'
    });

    $('#filtroCurso').on('change', function() {
        var cursoId = $(this).val();
        var turmaSelect = $('#filtroTurma');

        if (cursoId) {
            turmaSelect.empty()
                .append('<option value="">Carregando...</option>')
                .trigger('change')
                .prop('disabled', true);

            $.ajax({
                url: '<?= base_url('/horarios/getTurmasByCurso') ?>',
                type: 'POST',
                data: { cursos: [cursoId] },
                dataType: 'json',
                success: function(response) {
                    turmaSelect.empty().append('<option value="">Todas as Turmas</option>');
                    if (response && response.length > 0) {
                        $.each(response, function(index, item) {
                            turmaSelect.append(new Option(item.text, item.id));
                        });
                    }
                    turmaSelect.prop('disabled', false).trigger('change');
                },
                error: function() {
                    turmaSelect.empty()
                        .append('<option value="">Erro ao carregar</option>')
                        .prop('disabled', true)
                        .trigger('change');
                }
            });
        } else {
            turmaSelect.empty()
                .append('<option value="">Selecione um curso primeiro</option>')
                .trigger('change');
            turmaSelect.prop('disabled', true);
        }
    });

    // converte "HH:MM" em minutos para ordenar corretamente
    function timeToMinutes(t) {
        if (!t) return 0;
        var parts = String(t).split(':');
        var h = parseInt(parts[0]) || 0;
        var m = parseInt(parts[1]) || 0;
        return h * 60 + m;
    }

    function renderTimetable(data, tipo) {
        const resultadosContainer = $('#resultadosContainer');
        resultadosContainer.empty();

        if (!data || data.length === 0) {
            const emptyState = `<div class="card bg-dark text-white"><div class="card-body"><p class="text-center text-muted">Nenhum resultado encontrado.</p></div></div>`;
            resultadosContainer.html(emptyState);
            return;
        }

        const tables = {};
        data.forEach(item => {
            let groupKey = `${item.curso || ''} - ${item.turma || ''}`;
            if (!tables[groupKey]) tables[groupKey] = {};
            const dia = Number(item.dia_semana) || 0;
            if (!tables[groupKey][dia]) tables[groupKey][dia] = {};
            tables[groupKey][dia][item.hora_inicio] = {
                disciplina: item.disciplina || '',
                professor: item.professor || '',
                ambiente: item.ambiente || '',
                destaque: Number(item.destaque) || 0
            };
        });

        for (const groupKey in tables) {
            const groupData = tables[groupKey];
            let timeSlots = new Set();
            Object.values(groupData).forEach(day => {
                Object.keys(day).forEach(time => timeSlots.add(time));
            });
            const sortedTimeSlots = Array.from(timeSlots).sort((a, b) => timeToMinutes(a) - timeToMinutes(b));
            const weekDays = { 1: 'Segunda', 2: 'Terça', 3: 'Quarta', 4: 'Quinta', 5: 'Sexta' };

            let tableHtml = `<div class="card mb-4"><div class="card-body"><div class="timetable-caption">${groupKey}</div><div class="table-responsive"><table class="table table-bordered timetable"><thead><tr><th class="time-col">Horário</th><th>${weekDays[1]}</th><th>${weekDays[2]}</th><th>${weekDays[3]}</th><th>${weekDays[4]}</th><th>${weekDays[5]}</th></tr></thead><tbody>`;
            let lastTurno = '';
            sortedTimeSlots.forEach(time => {
                const hora = parseInt(String(time).substring(0, 2)) || 0;
                let turnoAtual = (hora < 12) ? 'MANHÃ' : (hora < 18) ? 'TARDE' : 'NOITE';
                if (turnoAtual !== lastTurno) {
                    tableHtml += `<tr class="period-header"><th colspan="6">${turnoAtual}</th></tr>`;
                    lastTurno = turnoAtual;
                }
                tableHtml += `<tr><td class="time-col">${time}</td>`;
                for (let day = 1; day <= 5; day++) {
                    const cellData = groupData[day] ? groupData[day][time] : null;
                    if (cellData) {

                        let detail1 = `<i class="mdi mdi-account"></i> ${cellData.professor || '—'}`;
                        let detail2 = `<b><i class="mdi mdi-map-marker"></i> ${cellData.ambiente || '—'}</b>`;
                        
                        tableHtml += `<td class="${cellData.destaque == 1 ? 'table-danger' : ''}"><span class="discipline"><i class="mdi mdi-book-open-variant"></i> ${cellData.disciplina}</span><em class="details">${detail1}<br>${detail2}</em></td>`;
                    } else {
                        tableHtml += '<td>—</td>';
                    }
                }
                tableHtml += '</tr>';
            });
            tableHtml += `</tbody></table></div></div></div>`;
            resultadosContainer.append(tableHtml);
        }
    }

    $('#btnFiltrar').on('click', function() {
        var loading = $.toast({
            heading: 'Carregando dados...',
            text: 'Por favor aguarde',
            showHideTransition: 'fade',
            icon: 'info',
            hideAfter: false,
            position: 'top-center',
            bgColor: '#191c24'
        });

        let tipoFiltro = 'curso';
        if ($('#filtroProfessor').val()) {
            tipoFiltro = 'professor';
        } else if ($('#filtroAmbiente').val()) {
            tipoFiltro = 'ambiente';
        }

        if (tipoFiltro == 'curso' && !$('#filtroCurso').val()) {
            $.toast().reset('all');
            $.toast({
                heading: 'Atenção',
                text: 'Selecione um filtro para visualizar os horários.',
                showHideTransition: 'slide',
                icon: 'warning',
                loaderBg: '#f96868',
                position: 'top-center'
            });
            return;
        }

        var dadosFiltro = {
            tipo: tipoFiltro,
            cursos: $('#filtroCurso').val()
                ? ($('#filtroCurso').val() instanceof Array
                    ? $('#filtroCurso').val()
                    : [$('#filtroCurso').val()])
                : [],
            turmas: $('#filtroTurma').val()
                ? ($('#filtroTurma').val() instanceof Array
                    ? $('#filtroTurma').val()
                    : [$('#filtroTurma').val()])
                : [],
            professores: $('#filtroProfessor').val()
                ? ($('#filtroProfessor').val() instanceof Array
                    ? $('#filtroProfessor').val()
                    : [$('#filtroProfessor').val()])
                : [],
            ambientes: $('#filtroAmbiente').val()
                ? ($('#filtroAmbiente').val() instanceof Array
                    ? $('#filtroAmbiente').val()
                    : [$('#filtroAmbiente').val()])
                : []
        };


        $.ajax({
            url: '<?= base_url('/horarios/filtrar') ?>',
            type: 'POST',
            data: dadosFiltro,
            dataType: 'json',
            success: function(response) {
                $.toast().reset('all');
                if (response.success) {
                    renderTimetable(response.data, dadosFiltro.tipo);
                } else {
                    renderTimetable([]);
                }
            },
            error: function() {
                $.toast().reset('all');
                renderTimetable([]);
                $.toast({
                    heading: 'Erro',
                    text: 'Não foi possível carregar os dados.',
                    showHideTransition: 'slide',
                    icon: 'error',
                    loaderBg: '#f96868',
                    position: 'top-center'
                });
            }
        });
    });

    $('#btnLimpar').on('click', function() {
        $('.select2-single').val(null).trigger('change');
        const emptyState = `
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <p class="text-center text-muted">
                        Nenhum horário para exibir. Selecione os filtros e clique em "Visualizar Horários".
                    </p>
                </div>
            </div>`;
        $('#resultadosContainer').html(emptyState);
    });
});
</script>
</body>
</html>