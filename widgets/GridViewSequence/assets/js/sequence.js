+function ($) {
    "use strict";

    $.fn.SequenceGridView = function(action) {
        var $this = this,
            $grid = $('tbody', $this),
            initialIndex = [];

        $('tr', $grid).each(function() {
            initialIndex.push($(this).data('key'));
        });

        $grid.sortable({
            items: 'tr',
            axis: 'y',
            update: function(event, ui) {
                var movedElementId = $(ui.item).data('key'),
                    nextElementId = $('tr[data-key="' + movedElementId + '"]', $grid).next().data('key');

                $.ajax({
                    url: action !== '' ? action : window.location.href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'grid-sort[moved_element_id]': movedElementId,
                        'grid-sort[next_element_id]': nextElementId
                    },
                    success: function(data) {
                        if (data.result !== 'success') {
                            $.notify('Порядок не был изменен (' + data.result + ')', 'error');
                        } else {
                            $.notify('Порядок был изменен', 'success');
                        }
                        $this.trigger('sortableSuccess');
                    },
                    error: function(request, status, error) {
                        $.notify('Порядок не был изменен (' + error + ')', 'error');
                    }
                });
            },
            helper: function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            }
        }).disableSelection();
    };

}(jQuery);