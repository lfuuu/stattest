var statLeadNotifier = {

  initLead: function (messageId) {
    var $table = $("#client_info_" + messageId);
    var clientAccountId = null;

    $table.find('tr').hover(
      function () {
        $(this).addClass('bg-info');
      },
      function () {
        $(this).removeClass('bg-info');
      }
    );

    var $lastSelected = null;
    $table.find('tr').click(function () {
      clientAccountId = $(this).data('client_account_id');
      if ($lastSelected) {
        $lastSelected.removeClass('bg-success');
      }
      $(this).addClass('bg-success');
      $lastSelected = $(this);
    });

    $table.find('tr:first').trigger('click');

    $('#message_id_' + messageId + ' button').on('click', function () {

      var $el = $(this);
      var messageId = $el.parents('.message-buttons').data('message-id');
      var name = $el.attr('name');

      var data = {
        messageId: messageId,
        clientAccountId: $('#message_id_' + messageId + ' tr.bg-success').data('client_account_id')
      };

      switch (name) {
        case 'to_lead':
          $.get('/lead/to-lead', data);
          break;

        case 'make_client':
          $.get('/lead/make-client', data);
          break;

        case 'set_state':
          data.stateId = $el.attr('value');
          $.get('/lead/set-state', data,
            function (data) {
              $el.parents('div.alert').find('button.close').click();
            });
          break;

        case 'to_trash':
          $.get('/lead/to-trash', {
            messageId: messageId,
            trash: 1
          }, function (data) {
            $el.parents('div.alert').find('button.close').click();
          });
      }
    });
  }
};