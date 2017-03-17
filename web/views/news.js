+function ($) {
    'use strict';

    function getLastNews() {
        var lastId = getLastNewsId();
        $.get('/news/last', {lastId:lastId}, function(data){
            $('#newsBlock').prepend(data);
            $('.layout_main').animate({scrollTop: 0}, 'slow');
        });
        localStorage.setItem('lastNewsId', lastId);
    }

    function setUnreadMessages() {
        var lastId = localStorage.getItem('lastNewsId'),
            $blocks = $('#newsBlock > .row');

        $blocks.each(function () {
            if($(this).data('id') > lastId) {
                $(this).addClass('unread-msg');
            } else {
                $(this).removeClass('unread-msg');
            }
        });
    }

    function getLastNewsId() {
        return $('#newsBlock>.row').first().data('id');
    }

    $(function () {
        $('.layout_main').animate({scrollTop: 0}, 'slow');

        setUnreadMessages();
        $('#newsBlock > .row.unread-msg').on('click', function () {
            localStorage.setItem('lastNewsId', $(this).data('id'));
            setUnreadMessages();
        });

        $('#sendMessageForm').on('submit', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var params = {
                message: $('#news-form-message').val(),
                to_user_id: $('#news-form-to-user-id').val(),
                priority: $('#news-form-priority').val()
            };

            $.post('/news/create',params, function(data){
                if(data['status'] == 'ok') {
                    getLastNews();
                    $('#news-form-message').val('')
                }
            }, 'json');

            return false;
        });
    })

}(jQuery);