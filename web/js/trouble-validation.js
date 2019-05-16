$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $troubleComment = $('#trouble-comment');
    $commentRequired = $('#comment-required');
    $length = $.trim($troubleComment.val()).length;
    $troubleComment.on('input propertychange', function (e) {
        if ($.trim(this.value).length) {
            $commentRequired.slideUp();
        } else {
            $commentRequired.slideDown();
        }
    });

    $('#form-trouble-submit').click(function() {
        if ($.trim($troubleComment.val()).length) {
            $commentRequired.slideUp();
            $('#form').submit();
        } else {
            $commentRequired.slideDown();
        }
    });
});
