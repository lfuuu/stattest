$pencil = '';

$('html').click(function(e) {
    if ($pencil === '') {
        return;
    }
    $inputField = $($pencil).siblings('textarea.comment-input')[0];
    if (!$inputField) {
        return;
    }
    $text = $inputField.value;

    if (!$(e.target).hasClass('comment-input') && !$(e.target).hasClass('edit')) {
        $account_id = $($pencil).data().id;

        $inputField.remove();
        $('<span>' + $text + '</span>').appendTo($($pencil).parent());
        sendAjax($account_id, $text);
    }
});

$('td[data-col-seq=\'comment\'] img.edit').click(function () {

    $commentInput = $('td[data-col-seq=\'comment\'] .comment-input')[0];
    $currentCommentInput = $(this).siblings('.comment-input')[0];
    if ($commentInput && !$currentCommentInput) {
        $text = $commentInput.value;
        $account_id = $($pencil).data().id;

        $('<span>' + $text + '</span>').appendTo($($commentInput).parent());
        $commentInput.remove();
        sendAjax($account_id, $text);

        return;
    }

    $pencil = this;
    toggleElements($pencil);
});

function sendAjax($account_id, $text) {
    $.ajax({
        type: 'POST',
        url: 'client/save-comment',
        data: {
            account_id: $account_id,
            comment: $text
        },
        complete: function (data) {
            if (data.status !== 200) {
                alert('Произошла ошибка. Код ошибки: ' + data.status);
            } else {
                toggleCheckMark($pencil);
            }
        }
    });
}

function toggleElements($pencil) {
    if ($($pencil).siblings('span').length) {
        $span = $($pencil).siblings('span')[0];
        $text = $span.innerText;
        $span.remove();
        $('<textarea style="width: 80%" class="comment-input form-control">' + $text + '</textarea>').appendTo($($pencil).parent());
    }
}

function toggleCheckMark($pencil) {
    $($pencil).hide();
    $('<img src="/images/icons/enable.gif" class="apply pull-right" />').appendTo($($pencil).parent());
    setTimeout(function () {
        $($pencil).siblings('img.apply')[0].remove();
        $($pencil).show();
    }, 2000);
}