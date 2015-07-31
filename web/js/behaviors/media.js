jQuery(document).ready(function() {

    $('div.media-list').find('div').each(function() {
        $(this).css({
            'display':'inline-block',
            'float':'left',
            'width': '155',
            'height':'155',
            'border':'1px solid black',
            'text-align':'center',
            'vertical-align':'bottom',
            'padding':'5px',
            'margin':'5px auto',
            'overflow':'hidden',
            'position':'relative'
        });

        var $is_image = /^image/.test($(this).data('mime-type'));

        if (!$is_image) {
            $(this).html(
                $('<div />')
                    .css({
                        'position':'absolute',
                        'display':'inline-block',
                        'margin':'auto',
                        'top':'-150px',
                        'bottom':'-150px',
                        'left':'-150px',
                        'right':'-150px'
                    })
                    .text($(this).html())
            );
        }
        else {
            $(this).html(
                'test'
                //$('<img />').src($(this).html())
            );
            /*
            $(this).nailthumb({
                width: 150,
                height: 150,
                fitDirection: 'top left'
            });
            */
        }
    });

});