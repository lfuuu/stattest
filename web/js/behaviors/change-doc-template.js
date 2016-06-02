if (typeof documentFolders == 'undefined') {
    var documentFolders = [];
}

if (typeof documentTemplates == 'undefined') {
    var documentTemplates = [];
}

$(function () {

    var
        setDocuments = function($target, folderId) {
            $target.find('option').remove();
            $.each(documentTemplates, function() {
                if (this.folder_id == folderId && $target.data('documents-type') == this.type) {
                    $target.append($('<option />').val(this.id).text(this.name));
                }
            });
        },
        setFolders = function($target, folderId) {
            $target.find('option').remove();
            if (typeof documentFolders[folderId] !== 'undefined') {
                $.each(documentFolders[folderId], function () {
                    $target.append($('<option />').val(this.id).text(this.name));
                });
            }
            $target.trigger('change');
        };

    $('.document-template').on('change', function () {
        var selectedValue = $(this).find('option:selected').val();

        if ($(this).has('data-documents')) {
            setDocuments($('.tmpl-documents[data-documents-type="' + $(this).data('documents') + '"]'), selectedValue);
        }

        if ($(this).has('data-folders')) {
            setFolders($('.document-template[data-folder-type="' + $(this).data('folders') + '"]'), selectedValue);
        }

    }).trigger('change');

});