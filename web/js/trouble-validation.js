$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $troubleComment = $('#trouble-comment');
    $submit = $('#form-trouble-submit');
    $commentRequired = $('#comment-required');
    $length = $.trim($troubleComment.val()).length;
    $commentRequired.css('display', ($length ? 'none' : ''));
    $submit.prop('disabled', !$length);
    $troubleComment.on('input propertychange', function (e) {
        if ($.trim(this.value).length) {
            $submit.prop('disabled', false);
            $commentRequired.slideUp();
        } else {
            $submit.prop('disabled', true);
            $commentRequired.slideDown();
        }
    });
});
