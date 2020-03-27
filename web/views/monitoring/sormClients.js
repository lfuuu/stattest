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

      if (data.value.length == 0) {
        $cell.attr('contenteditable', false).css('background-color', '');
        document.editCell = null;
        return;
      }

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

      // click на уже редактируемую ячейку
      if (document.editCell && document.editCell == event.currentTarget) {
        event.stopPropagation();
        return;
      }

      endEditable();

      $(event.currentTarget)
        .attr('contenteditable', true)
        .css('background-color', 'yellow')
        .focus();

      document.editCell = event.currentTarget;
      event.stopPropagation();
    });

    $('.sorm-save-equ').click(function(event) {

      var $tg = $(event.currentTarget);

      var data = {
        value: $tg.text(),
        field: 'equ',
        accountId: $tg.data('account_id')
      };

      console.log($tg);
      console.log(data);

      $.ajax({
        url: '/monitoring/sorm-clients-save',
        method: 'get',
        data: data,
        success: function (content) {
          console.log(content);
          console.log($tg);

          if (content == 'ok') {
            $tg.parent().text(data.value).css('background-color', '');
          } else {
            alert(content);
          }
        },
        error: function (content) {
          alert(content.responseText);
        }
      });
    });

    $('html').click(function () {
      endEditable();
    });

  })

}(jQuery);