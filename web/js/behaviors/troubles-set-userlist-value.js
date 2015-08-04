jQuery(document).ready(function() {

    var $usersList = $('select.tt_users_list'),
        $userLnk = $('a.trouble-set-user');

    $userLnk
        .on('click', function () {
            console.log($(this).data('user'));

            $usersList.find('option[value="' + $(this).data('user') + '"]')
                .prop('selected', true)
                .trigger('change');
        });
});