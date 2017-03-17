+function ($) {
    'use strict';

    $(function () {
        $('button[data-important-event-id]').on('click', function(e) {
            e.preventDefault();

            var eventId = $(this).data('important-event-id');
            $.ajax({
                url: '/important_events/report/set-comment/',
                data: {
                    'id': eventId,
                    'comment': $('input[data-important-event-id="' + eventId + '"]').val()
                },
                method: 'POST',
                success: function() {
                    $.notify('Комментарий добавлен', 'success');
                },
                error: function() {
                    $.notify('Комментарий не может быть добавлен', 'error');
                }
            });
        });
    })

}(jQuery);