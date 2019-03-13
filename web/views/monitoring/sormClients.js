+function ($) {
  'use strict';

  $(function () {

    function endEditable() {
      if (!document.editCell) {
        return;
      }

      var $cell = $(document.editCell);
      var data = {
        value: $cell.text(),
        field: $cell.data('field'),
        accountId: $cell.parent('tr').data('key')
      };

      $.ajax({
        url: '/monitoring/sorm-clients-save',
        method: 'get',
        data: data,
        success: function (content) {
          if (content == 'ok') {
            $cell.attr('contenteditable', false).css('background-color', '');
          } else {
            alert(content);
          }
        },
        error: function (content) {
          alert(content.responseText);
        }
      });
      document.editCell = null;
    }


    $('[class="sorm-client-cell"]').click(function (event) {
      endEditable();

      $(event.currentTarget)
        .attr('contenteditable', true)
        .css('background-color', 'yellow')
        .focus();

      document.editCell = event.currentTarget;
      event.stopPropagation();
    });

    $('html').click(function () {
      endEditable();
    });

  })

}(jQuery);