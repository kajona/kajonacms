<portalupload_uploadform>
<div class="portalUploadWrapper">


    <div id="%%elementId%%">
        <div class="fileupload-buttonbar">

            <button type="submit" class="btn fileinput-button">
                <span>[lang,mediamanager_upload,mediamanager]</span>
                <input type="file" name="%%name%%" multiple>
            </button>

            <button type="submit" class="btn start" style="display: none;">
                <span>[lang,upload_multiple_uploadFiles,mediamanager]</span>
            </button>

            <button type="reset" class="btn  cancel" style="display: none;">
                <span>[lang,upload_multiple_cancel,mediamanager]</span>
            </button>

            <span class="fileupload-process"></span>
            <div class="alert alert-info">
                [lang,upload_dropArea,mediamanager]<br />
                %%allowedExtensions%%
            </div>
        </div>

        <div class=" fileupload-progress " style="display: none;">

            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width:0%;"></div>
            </div>

            <div class="progress-extended">&nbsp;</div>
        </div>

        <table class="table admintable table-striped-tbody files"></table>
    </div>

    <script type="text/javascript">

        KAJONA.portal.loader.loadFile([
            "/templates/default/css/element_portalupload.css",
            "/templates/default/js/jquery-fileupload/css/jquery.fileupload.css",
            "/templates/default/js/jquery-fileupload/css/jquery.fileupload.css",
            "/templates/default/js/jquery-fileupload/css/jquery.fileupload-ui.css",
            "/templates/default/js/jquery-fileupload/js/jquery.ui.widget.js",
            "/templates/default/js/jquery-fileupload/js/load-image.min.js",
            "/templates/default/js/jquery-fileupload/js/canvas-to-blob.min.js",
            "/templates/default/js/jquery-fileupload/js/jquery.iframe-transport.js",
            "/templates/default/js/jquery-fileupload/js/jquery.fileupload.js"
        ], function() {
            KAJONA.portal.loader.loadFile([
                "/templates/default/js/jquery-fileupload/js/jquery.fileupload-process.js"
            ], function() {
                KAJONA.portal.loader.loadFile([
                    "/templates/default/js/jquery-fileupload/js/jquery.fileupload-image.js",
                    "/templates/default/js/jquery-fileupload/js/jquery.fileupload-audio.js",
                    "/templates/default/js/jquery-fileupload/js/jquery.fileupload-video.js",
                    "/templates/default/js/jquery-fileupload/js/jquery.fileupload-validate.js"
                ], function() {
                    KAJONA.portal.loader.loadFile([
                        "/templates/default/js/jquery-fileupload/js/jquery.fileupload-ui.js"
                    ], function() {

                        var filesToUpload = 0;
                        $('#%%elementId%%').fileupload({
                            url: '%%formAction%%',
                            dataType: 'json',
                            autoUpload: false,
                            paramName : 'portaluploadFile',
                            filesContainer: $('table.files'),
                            formData: [
                                {name: 'portaluploadDlfolder', value: '%%portaluploadDlfolder%%'},
                                {name: 'inputElement', value : 'portaluploadFile'},
                                {name: 'submitAjaxUpload', value : '1'}
                            ],
                            messages: {
                                maxNumberOfFiles: 'Maximum number of files exceeded',
                                acceptFileTypes: "[lang,upload_fehler_filter,mediamanager]",
                                maxFileSize: "[lang,upload_multiple_errorFilesize,mediamanager]",
                                minFileSize: 'File is too small'
                            },
                            maxFileSize: %%maxFileSize%%,
                            acceptFileTypes: %%acceptFileTypes%%,
                            uploadTemplateId: null,
                            downloadTemplateId: null,
                            uploadTemplate: function (o) {
                            var rows = $();
                            $.each(o.files, function (index, file) {
                                var row = $('<tbody class="template-upload fade"><tr>' +
                                        '<td><span class="preview"></span></td>' +
                                        '<td><p class="name"></p>' +
                                        '<div class="error"></div>' +
                                        '</td>' +
                                        '<td><p class="size"></p>' +
                                        '<div class="progress progress-striped active"><div class="progress-bar"></div></div>' +
                                        '</td>' +
                                        '<td>' +
                                        (!index && !o.options.autoUpload ?
                                                '<button class="btn start " disabled style="display: none;">Start</button>' : '') +
                                        (!index ? '<button class="btn cancel ">[lang,upload_multiple_cancel,mediamanager]</button>' : '') +
                                        '</td>' +
                                        '</tr></tbody>');
                                row.find('.name').text(file.name);
                                row.find('.size').text(o.formatFileSize(file.size));
                                if (file.error) {
                                    row.find('.error').text(file.error);
                                }
                                rows = rows.add(row);
                            });
                            return rows;
                        }
                    })
                    .bind('fileuploadadded', function (e, data) {
                        $(this).find('.fileupload-buttonbar button.start').css('display', '');
                        $(this).find('.fileupload-buttonbar button.cancel').css('display', '');
                        $(this).find('.fileupload-progress').css('display', '');
                        filesToUpload++;
                    })
                    .bind('fileuploadfail', function (e, data) {
                        filesToUpload--;
                        $(this).trigger('kajonahideelements');
                    })
                    .bind('fileuploaddone', function (e, data) {
                        filesToUpload--;
                        $(this).trigger('kajonahideelements');
                    })
                    .bind('fileuploadstop', function (e) {
                        $(this).trigger('kajonahideelements');
                        document.location.reload();
                    })
                    .bind('kajonahideelements', function() {
                        if(filesToUpload == 0) {
                            $(this).find('.fileupload-buttonbar button.start').css('display', 'none');
                            $(this).find('.fileupload-buttonbar button.cancel').css('display', 'none');
                            $(this).find('.fileupload-progress').css('display', 'none');
                        }
                    });
                });
            });
        });
        });

    </script>

</div>
</portalupload_uploadform>