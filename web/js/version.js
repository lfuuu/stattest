$(document).ready(function () {
    function generateTable(versions) {
        var body = $('.versionForm tbody');
        body.empty();
        var genDiff = function (k) {
            var res = '';
            var s = versions[k];
            for (var i = k - 1; i > 0; i--) {
                var c = versions[i];
                if (c[0] === s[0] && c[1] === s[1]) {
                    $(s[3]).each(function (k, v) {
                        console.log(v);
                        if (typeof c[3][k] === 'undefined')
                            res += k + '( ' + ' => ' + v + ' )<br/>';
                        else if (c[3][k] !== v)
                            res += k + '( ' + c[3][k] + ' => ' + v + ' )<br/>';
                        else
                            res += k + '( ' + c[3][k] + ' => ' + v + ' )<br/>';
                    });
                    $(c[3]).each(function (k, v) {
                        if (typeof s[3][k] === 'undefined')
                            res += k + '( ' + v + ' => ' + ' )<br/>';
                    });
                    break;
                }
            }
            console.log('----');
            return res;
        };
        $.each(versions, function (k, v) {
            var tr = '<tr data-key="' + k + '">'
                    + '<td>' + v[0] + '</td>'
                    + '<td>' + v[1] + '</td>'
                    + '<td>' + v[2] + '</td>'
                    + '<td><a href="#" class="moreDetail">details</a><br/>'
                    + ($('.modelName').val().length > 0 && $('.modelId').val().length > 0
                            ? '<a href="#" class="differents">diff</a><br/>'
                            : '<a href="#" class="getAll">>>></a>')
                    + '</td>'
                    + ($('.modelName').val().length > 0 && $('.modelId').val().length > 0 ? genDiff(k) : '')
                    + '</tr>';
            body.append(tr);
        });
    }
    function search()
    {
        $.ajax({
            url: '/version/index',
            method: 'GET',
            dataType: "json",
            data: {
                modelName: $('.modelName').val(),
                modelId: $('.modelId').val(),
                date: $('.date').val(),
                dateType: $('.dateType').val()
            },
            success: function (data) {
                versions = data;
                generateTable(versions);
            }
        });
    }

    function getDataKey(obj)
    {
        return $(obj).closest('tr').data('key');
    }

    $('body').on('submit', '.versionForm', function (e) {
        e.preventDefault();
        search();
    });

    $('body').on('click', '.moreDetail', function (e) {
        e.preventDefault();
        var d = $("#dialog");
        d.empty();
        var dataKey = getDataKey(this);
        var tmp = '';
        $.each(versions[dataKey][3], function (k, v) {
            tmp += '<tr><td>' + k + '</td><td>' + v + '</td><td><a href="#" class="editField">edit</a></td></tr>';
        });
        d.append($('<table data-key="' + dataKey + '"><tr><td>Key</td><td>Value</td><td></td></tr>' + tmp + '<table>'));
        $("#dialog").dialog({close: function () {
                generateTable(versions);
            }});
    });

    $('body').on('click', '.getAll', function (e) {
        e.preventDefault();
        var data = versions[getDataKey(this)];
        $('.modelName').val(data[0]);
        $('.modelId').val(data[1]);
        search();
    });

    $('body').on('click', '.editField', function (e) {
        e.preventDefault();
        var el = $(this);
        var fieldKey = el.parent().prev().prev().text();
        var valEl = el.parent().prev();
        var versionKey = el.closest('table').data('key');
        var editForm = $('#editForm');
        var form = editForm.find('form').first();
        editForm.dialog({
            width: 400,
            open: function () {
                editForm.find('textarea').val(valEl.text());
            },
            buttons: {
                'Save this': function () {
                    var val = form.find('textarea').val();
                    valEl.text(val);
                    versions[versionKey][3][fieldKey] = val;
                },
                'Save <= date': function () {
                    var valOld = valEl.text();
                    var valNew = form.find('textarea').val();

                    for (var i = versionKey; i > 0; i--) {
                        var c = versions[i];
                        if (c[0] === versions[versionKey][0] && c[1] === versions[versionKey][1]) {
                            if (c[3][fieldKey] == valOld)
                                versions[i][3][fieldKey] = valNew;
                            else
                                break;
                        }
                    }
                    valEl.text(valNew);
                },
                'Save >= date': function () {
                    var valOld = valEl.text();
                    var valNew = form.find('textarea').val();
                    var count = versions.length;

                    for (var i = versionKey; i < count; i++) {
                        var c = versions[i];
                        if (c[0] === versions[versionKey][0] && c[1] === versions[versionKey][1]) {
                            if (c[3][fieldKey] == valOld)
                                versions[i][3][fieldKey] = valNew;
                            else
                                break;
                        }
                    }
                    valEl.text(valNew);
                },
                'Save <=> date': function () {
                    var valOld = valEl.text();
                    var valNew = form.find('textarea').val();
                    var count = versions.length;

                    for (var i = versionKey; i > 0; i--) {
                        var c = versions[i];
                        if (c[0] === versions[versionKey][0] && c[1] === versions[versionKey][1]) {
                            if (c[3][fieldKey] == valOld)
                                versions[i][3][fieldKey] = valNew;
                            else
                                break;
                        }
                    }

                    for (var i = versionKey; i < count; i++) {
                        var c = versions[i];
                        if (c[0] === versions[versionKey][0] && c[1] === versions[versionKey][1]) {
                            if (c[3][fieldKey] == valOld)
                                versions[i][3][fieldKey] = valNew;
                            else
                                break;
                        }
                    }
                    valEl.text(valNew);
                }
            },
            close: function () {
                form.reset();
                editForm.dialog('close');
            }
        });
    });

    generateTable(versions);
});


$.each($.makeArray(versions[2][3])[0], function (k, v) {
    console.log(k + ' == ' + v);
});

$.each($(versions[2][3])[0], function (k, v) {
    console.log(k + ' == ' + v);
});