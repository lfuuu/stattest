+function ($) {
    'use strict';

    $(function () {
        $('#username').keypress(function (event) {
            if (event.which == 13) {
                $("#login_btn").click();
            }
        });
        $('#password').keypress(function (event) {
            if (event.which == 13) {
                $("#login_btn").click();
            }
        });

        $('#code').keypress(function (event) {
            if (event.which == 13) {
                $("#login_with_code_btn").click();
            }
        });

        $("#login_btn").on('click', function (event) {
            let $username = $('#username').val().trim();
            let $password = $('#password').val().trim();

            if ($username.length && $password.length) {
                $('#form-error').hide();
                $('#row-loader').show();

                $('#login_btn').attr('disabled', true);

                $.post('/site/get-code', {'username': $username}, function (answer, status) {
                    $('#row-loader').hide();

                    if (status != 'success') {
                        $('#form-error').text('Network error').show();
                        $('#login_btn').removeAttr('disabled');
                        return;
                    }

                    if (answer.status == 'error') {
                        $('#form-error').text(answer.error).show();
                        $('#login_btn').removeAttr('disabled');
                        return;
                    }

                    if ("code_make" in answer) {
                        if (answer.code_make) {
                            $('#row-code').show();
                            $('#form-error').hide();
                            $('#code').val('');
                            $('#code').focus();
                        } else {
                            $('#form-login').submit();
                        }
                    }
                })
            }
        });

        $("#login_with_code_btn").on('click', function (event) {
            $('#code_verification').val($('#code').val());
            $('#form-login').submit();
        });
    })
}(jQuery);