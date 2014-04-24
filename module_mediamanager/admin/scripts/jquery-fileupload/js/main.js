/*
 * jQuery File Upload Plugin JS Example 8.9.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/* global $, window */

$(function () {


    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        url: 'server/php/',
        filesContainer: $('table.files'),
        uploadTemplateId: null,
        downloadTemplateId: null,
        uploadTemplate: function (o) {
            var rows = $();
            $.each(o.files, function (index, file) {
                var row = $('<tbody><tr class="template-upload ">' +
                    '<td><span class="preview"></span></td>' +
                    '<td><p class="name"></p>' +
                    '<div class="error"></div>' +
                    '</td>' +
                    '<td><p class="size"></p>' +
                    '<div class="progress"></div>' +
                    '</td>' +
                    '<td>' +
                    (!index && !o.options.autoUpload ?
                        '<button class="start" disabled>Start</button>' : '') +
                    (!index ? '<button class="cancel">Cancel</button>' : '') +
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
    });

});
