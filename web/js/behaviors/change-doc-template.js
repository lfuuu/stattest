if(typeof templates == 'undefined')
    var templates = [];
var folders = [];

function generateTmplList(type, selected) {
    if (!selected)
        selected = $('.tmpl-group[data-type="' + type + '"]').first().val();
    var tmpl = $('.tmpl[data-type="' + type + '"]');
    if (folders[selected] !== 'undefined') {
        tmpl.empty();
        $.each(templates, function (k, v) {
            if(type == v['type'] && v['folder_id'] == selected)
                tmpl.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
        });
    }
}

$(function () {
    $('.tmpl-group').each(function () {
        var type = $(this).data('type');
        var t = $(this);
        var first = true;
        $.each(templates, function (k, v) {
            if(type == v['type'] && typeof folders[v['folder_id']] == 'undefined') {
                first = false;
                t.append('<option value="' + v['folder_id'] + '" ' + (first ? 'selected=selected' : '') + ' >' + v['folder'] + '</option>');
                folders[v['folder_id']] = v['folder'];
            }
        });
        generateTmplList(type, first);
    });

    $('.tmpl-group').on('change', function () {
        generateTmplList($(this).data('type'), $(this).val());
    });
});