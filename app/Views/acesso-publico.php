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
    <link rel="stylesheet" href="<?= base_url("assets/css/horarios-css/horarios.css"); ?>">

    <script src="<?= base_url("assets/vendors/js/vendor.bundle.base.js"); ?>"></script>
    <script src="<?= base_url("assets/vendors/select2/select2.min.js"); ?>"></script>
    <script src="<?= base_url("assets/vendors/jquery-toast-plugin/jquery.toast.min.js"); ?>"></script>


</head>

<body>
    <div id="horarios-wrapper" class="main-panel">
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
                <div class="col-lg-10 col-md-12">
                    <ul class="nav nav-tabs" id="tab_horarios" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-filtros" data-bs-toggle="tab" data-bs-target="#pane-filtros" type="button" role="tab">Filtros</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-resultados" data-bs-toggle="tab" data-bs-target="#resultadosContainer" type="button" role="tab">Resultados</button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row tab-content d-flex justify-content-center" id="tab_horarios_content">
                <div class="tab-pane fade show active row justify-content-center col-lg-10 col-md-12 grid-margin stretch-card" id="pane-filtros" role="tabpanel">

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Filtros</h4>
                            <form id="formFiltros">
                                <input type="hidden" class="csrf" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                                <div class="row">
                                    <div class="col-md-3 form-group">

                                        <label for="filtroCurso">Curso</label>
                                        <select class="form-control" id="filtroCurso" name="cursos[]">
                                            <option value="">Selecione um curso</option>
                                            <?php foreach ($cursos as $curso): ?>
                                                <option value="<?= $curso['id'] ?>"><?= esc($curso['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>

                                    <div class="col-md-3 form-group">

                                        <label for="filtroTurma">Turma</label>
                                        <select class="form-control" id="filtroTurma" name="turmas[]" disabled>
                                            <option value="">Selecione um curso primeiro</option>
                                        </select>

                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                            <label for="filtroProfessor">Professor</label>
                                            <select class="form-control" id="filtroProfessor" name="professores[]">
                                                <option value="">Selecione um professor</option>
                                                <?php foreach ($professores as $professor): ?>
                                                    <option value="<?= $professor['id'] ?>"><?= esc($professor['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                    </div>
                                    <div class="col-md-3 form-group">
                                            <label for="filtroAmbiente">Ambiente</label>
                                            <select class="form-control" id="filtroAmbiente" name="ambientes[]">
                                                <option value="">Selecione um ambiente</option>
                                                <?php foreach ($ambientes as $ambiente): ?>
                                                    <option value="<?= $ambiente['id'] ?>"><?= esc($ambiente['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                    </div>
                                    <div class="form-group d-flex col-md-3 mt-4 flex-wrap gap-2">
                                        <button type="button" id="btnFiltrar" class="btn btn-primary me-2">
                                            <i class="mdi mdi-eye-outline me-1"></i>Visualizar Horários
                                        </button>
                                        <button type="button" id="btnLimpar" class="btn btn-secondary me-2">
                                            <i class="mdi mdi-filter-remove me-1"></i>Limpar Filtros
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>


                </div>
                <div class="tab-pane fade row justify-content-center col-lg-10 col-md-12 rounded" id="resultadosContainer" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="col-auto">
                            <h4 id="tile_resultados" class="card-title">Resultados</h4>
                        </div>
                    </div>
                    <div class="card text-white card-body">
                        <p class="text-center text-muted">
                            Nenhum horário para exibir. Selecione os filtros e clique em "Visualizar Horários".
                        </p>
                    </div>
                    <div id="carousel_horarios" class="carousel slide">
                        <div class="carousel-inner" id="carousel_dias"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel_horarios" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel_horarios" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
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
                        data: {
                            cursos: [cursoId]
                        },
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

            function renderTimetable(data, tipoFiltro, filtro) {
                const filtrosAtivos = tipoFiltro.split('/');
                const resultadosContainer = $('#resultadosContainer');
                resultadosContainer.empty();

                const botaoVoltar = `
                    <div class="d-flex justify-content-end">
                        <button id="btn-voltar-filtro" type="button" class="btn btn-primary me-2 mb-4">
                            <i class="mdi mdi-arrow-left"></i> Voltar para filtros
                        </button>
                    </div>
                `;
                resultadosContainer.append(botaoVoltar);

                if (!data || data.length === 0) {
                    resultadosContainer.html(`
                        <div class="card text-white">
                            <div class="card-body">
                                <p class="text-center text-muted">Nenhum resultado encontrado.</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                // =============== 1. Definir agrupamento ===============
                let agrupamento = "none";

                if (tipoFiltro === "curso" && (!filtro.turmas || filtro.turmas.length === 0)) {
                    agrupamento = "turma"; // curso sem turma → 1 carrossel por turma
                }

                // =============== 2. Agrupar dados ===============
                let grupos = {};

                if (agrupamento === "turma") {
                    data.forEach(item => {
                        let key = item.turma || "SemTurma";
                        if (!grupos[key]) grupos[key] = [];
                        grupos[key].push(item);
                    });
                } else {
                    grupos["unico"] = data;
                }

                // =============== 3. Gerar carrosséis ===============
                Object.keys(grupos).forEach((grupoKey) => {
                    let dadosGrupo = grupos[grupoKey];
                    let carouselId = "carousel_" + grupoKey.replace(/\s+/g, '_');

                    // ----- Título -----
                    let tituloCarrossel = "";
                    if (tipoFiltro === "professor") tituloCarrossel = "Professor: " + dadosGrupo[0].professor;
                    if (tipoFiltro === "ambiente") tituloCarrossel = "Ambiente: " + dadosGrupo[0].ambiente;

                    if (tipoFiltro === "curso") {
                        if (agrupamento === "turma") {
                            tituloCarrossel = `Curso: ${dadosGrupo[0].curso} — Turma: ${grupoKey}`;
                        } else {
                            tituloCarrossel = `Curso: ${dadosGrupo[0].curso}`;
                        }
                    }

                    if (tipoFiltro === "professor" && filtro.cursos?.length > 0)
                        tituloCarrossel += ` — Curso: ${dadosGrupo[0].curso}`;

                    if (tipoFiltro === "ambiente" && filtro.cursos?.length > 0)
                        tituloCarrossel += ` — Curso: ${dadosGrupo[0].curso}`;

                    if (tipoFiltro === "curso/professor") tituloCarrossel = `Curso e Professor`;
                    if (tipoFiltro === "curso/ambiente") tituloCarrossel = `Curso e Ambiente`;
                    if (tipoFiltro === "curso/turma") tituloCarrossel = `Curso e Turma`;

                    // ----- Início do bloco -----
                    let blocoHTML = `
                        <div class="card text-white mb-4">
                            <div class="card-body">
                                <h3 class="text-center mb-3">${tituloCarrossel}</h3>

                                <div id="${carouselId}" class="carousel slide">
                                    <div class="carousel-inner">
                    `;

                    // =============== 4. Gerar slides (Seg → Sex) ===============
                    const diasSemana = {
                        1: "Segunda",
                        2: "Terça",
                        3: "Quarta",
                        4: "Quinta",
                        5: "Sexta"
                    };

                    for (let dia = 1; dia <= 5; dia++) {
                        let aulasDia = dadosGrupo.filter(x => Number(x.dia_semana) === dia);
                        aulasDia.sort((a, b) => timeToMinutes(a.hora_inicio) - timeToMinutes(b.hora_inicio));
                        let activeClass = dia === 1 ? "active" : "";

                        blocoHTML += `
                            <div class="carousel-item ${activeClass}">
                                <h4 class="text-center mb-3">${diasSemana[dia]}</h4>
                                <div class="lista-aulas">
                        `;

                        if (aulasDia.length === 0) {
                            blocoHTML += `<p class="text-center text-muted">Nenhuma aula neste dia.</p>`;
                        } else {
                            aulasDia.forEach(aula => {
                                const destaque = campo => (filtrosAtivos.includes(campo) ? "fw-bold text-info" : "");

                                blocoHTML += `
                                    <div class="d-flex justify-content-center">
                                        <div class="aula-item mb-2 p-2 border rounded d-flex align-items-stretch">
                                            <div class="me-3 pe-2 text-nowrap border-end d-flex align-items-center">
                                                <i class="mdi mdi-clock"></i> ${aula.hora_inicio}
                                            </div>
                                            <div class="d-flex flex-column text-center flex-grow-1 justify-content-center">
                                                <div class="${destaque('disciplina')}"><i class="mdi mdi-book-open-variant"></i> ${aula.disciplina}</div>
                                                <div class="${destaque('professor')}"><i class="mdi mdi-account"></i> ${aula.professor}</div>
                                                <div class="${destaque('turma')}"><i class="mdi mdi-account-group"></i> ${aula.turma}</div>
                                                <div class="${destaque('ambiente')}"><i class="mdi mdi-map-marker"></i> ${aula.ambiente}</div>
                                                <div class="${destaque('curso')}">Curso: ${aula.curso}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        }

                        blocoHTML += `
                                </div>
                            </div>
                        `;
                    }

                    // =============== 5. Adicionar botões condicionalmente ===============
                    blocoHTML += `
                                    </div>
                                    ${dadosGrupo.length > 0 ? `
                                        <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;

                    resultadosContainer.append(blocoHTML);
                });
            }


            $('#resultadosContainer').on('click', '#btn-voltar-filtro', function() {
                const tab = new bootstrap.Tab(document.querySelector('#tab-filtros'));
                tab.show();
            });


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
                    cursos: $('#filtroCurso').val() ?
                        ($('#filtroCurso').val() instanceof Array ?
                            $('#filtroCurso').val() : [$('#filtroCurso').val()]) : [],
                    turmas: $('#filtroTurma').val() ?
                        ($('#filtroTurma').val() instanceof Array ?
                            $('#filtroTurma').val() : [$('#filtroTurma').val()]) : [],
                    professores: $('#filtroProfessor').val() ?
                        ($('#filtroProfessor').val() instanceof Array ?
                            $('#filtroProfessor').val() : [$('#filtroProfessor').val()]) : [],
                    ambientes: $('#filtroAmbiente').val() ?
                        ($('#filtroAmbiente').val() instanceof Array ?
                            $('#filtroAmbiente').val() : [$('#filtroAmbiente').val()]) : []
                };


                $.ajax({
                    url: '<?= base_url('/horarios/filtrar') ?>',
                    type: 'POST',
                    data: dadosFiltro,
                    dataType: 'json',
                    success: function(response) {
                        $.toast().reset('all');
                        if (response.success) {
                            renderTimetable(response.data, dadosFiltro.tipo, dadosFiltro);
                        } else {
                            renderTimetable([], dadosFiltro.tipo, dadosFiltro);
                        }

                        const tab = new bootstrap.Tab(document.querySelector('#tab-resultados'));
                        tab.show();
                    },
                    error: function() {
                        $.toast().reset('all');
                        renderTimetable([], dadosFiltro.tipo, dadosFiltro);
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
                <div class="col-auto d-flex justify-content-end">
                    <button id="btn-voltar-filtro" type="button" class="btn btn-primary me-2 mb-4">Voltar para filtros</button>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted">
                        Nenhum horário para exibir. Selecione os filtros e clique em "Visualizar Horários".
                    </p>
                </div>
            `;
                $('#resultadosContainer').html(emptyState);
            });
            // Os botes de passa os slides do carrossel so desapareciam quando clicava no botao de limpar filtros, entao ao iniciar a pagina limpa filtros :-)
            $('#btnLimpar').trigger('click');
        });
        
    </script>
</body>

</html>