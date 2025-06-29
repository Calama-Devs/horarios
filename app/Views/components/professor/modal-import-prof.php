<div class="modal fade" id="modal-import-prof" tabindex="-1" aria-labelledby="ModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel">Importar Professores</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-primary text-dark" role="alert">
                        <i class="fa fa-info-circle"></i><strong>Caminho para exportação destes dados no SUAP:</strong><br>
                        Ensino -> Alunos e Professores -> Professores<br>
                        Prencher os filtros (Campus e Ativo) e clicar em Filtrar<br>
                        Nos resultados, clicar em Docentes<br>
                        Clicar no botão [Exportar para XLS], no canto superior direito.<br>
                        Salvar o arquivo e então enviar através do campo abaixo.
                    </div>
                </div>
            </div>

            <form id="importarProfessor" class="forms-sample" method="post" action='<?php echo base_url('sys/professor/importar'); ?>' enctype="multipart/form-data">
                <div class="modal-body">
                    <?php echo csrf_field() ?>
                    <div class="form-group">
                        <label>Enviar arquivo</label>
                        <input id="arquivo" type="file" name="arquivo" class="file-upload-default">
                        <div id="drop-area" class="drop-area input-group col-xs-12 d-flex align-items-center">
                            <input id="drop-text" type="text" class="form-control file-upload-info" disabled placeholder="Selecione o arquivo clicando ao lado ->">
                            <span class="input-group-append ms-2">
                                <button class="file-upload-browse btn btn-primary" type="button">Buscar arquivo</button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const dropArea = document.getElementById('drop-area');
    const inputArquivo = document.getElementById('arquivo');
    const dropText = document.getElementById('drop-text');

    //looping para prever falhas no DOM em cada um desses eventos dentro da "drop-area"
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => e.preventDefault());
        dropArea.addEventListener(eventName, e => e.stopPropagation());
    });

    dropArea.addEventListener('dragover', () => dropArea.classList.add('dragover'));
    dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));

    dropArea.addEventListener('drop', (e) => { 
        dropArea.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            inputArquivo.files = e.dataTransfer.files;
            dropText.placeholder = `${e.dataTransfer.files[0].name}`;
        }
    });
</script>