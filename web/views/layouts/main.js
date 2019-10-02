var LOADED = 1,
    datepicker_ru = {
        closeText: 'Закрыть',
        prevText: '&#x3c;Пред',
        nextText: 'След&#x3e;',
        currentText: 'Сегодня',
        monthNames: [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ],
        monthNamesShort: [
            'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
            'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'
        ],
        dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
        dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        weekHeader: 'Не',
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        showMonthAfterYear: false,
        yearSuffix: ''
    };

// перерендерить пришпиленную шапка таблицы
window.reflowTableHeader = function () {
    var $table = $('.kv-grid-table');
    try {
        $table.floatThead && $table.floatThead('reflow');
    } catch (err) {
    }
};

+function ($) {
    'use strict';

    $(function () {
        $('.panel-toggle-button')
            .on('click', function() {
                var $this = $(this),
                    $layoutLeft = $('.layout_left'),
                    $layoutMain = $('.layout_main');

                if ($this.hasClass('active')) {
                    $layoutLeft.animate({left: '-350px'});
                    $layoutMain.animate({left: 0}, function() {
                        $layoutMain.removeClass('col-sm-10 col-md-push-2').addClass('col-sm-12');
                        window.reflowTableHeader();
                    });
                    $this.text('›');
                    $this.removeClass('active');
                    $.get('/utils/layout/hide/'); // запомнить
                } else {
                    $layoutLeft.animate({left: 0});
                    $layoutMain.animate({left: '16.667%'}, function() {
                        $layoutMain.removeClass('col-sm-12').addClass('col-sm-10 col-md-push-2');
                        window.reflowTableHeader();
                    });
                    $this.text('‹');
                    $this.addClass('active');
                    $.get('/utils/layout/show/'); // запомнить
                }
            });

        $('.select2').select2({'width': '100%'});
        $.datepicker.setDefaults(datepicker_ru);
        $('.datepicker').datepicker();

        $('.layout_main , .layout_left, .panel-toggle-button').css('top', $('#top_search').closest('.row').height() + 25);
    })

}(jQuery);

var getLocation = function (href) {
  var l = document.createElement("a");
  l.href = href;
  return l.pathname;
};

var setPossition = function (obj, event, isEdit) {
  if (event.pageX == null && event.clientX != null) {
    var doc = document.documentElement, body = document.body;
    event.pageX = event.clientX + (doc && doc.scrollLeft || body && body.scrollLeft || 0) - (doc && doc.clientLeft || body && body.clientLeft || 0);
    event.pageY = event.clientY + (doc && doc.scrollTop || body && body.scrollTop || 0) - (doc && doc.clientTop || body && body.clientTop || 0);
  }

  if (isEdit) {
    obj.css({'width': '440px'});
    obj.find('.alf_delete').show();
    obj.find('.alf_save').text('Сохранить');
    obj.find('label').text('Редактировать ссылку на описание:');
  } else {
    obj.css({'width': '360px'});
    obj.find('.alf_delete').hide();
    obj.find('.alf_save').text('Создать');
    obj.find('label').text('Создать ссылку на описание:');
  }

  obj.css({
    'left': event.pageX + 20,
    'top': event.pageY - 75,
    'display': 'inline',
    "position": "absolute"
  }).show();
};

$(document).ready(function () {
  // надо ли вообще обработку делать
  var $formW0 = $('form#w0');
  if (!$formW0.length) {
    return;
  }

  document.alf_form_url = getLocation($formW0.attr('action'));

  $.get('/dictionary/data/', {
    form_url: document.alf_form_url
  }).done(function (data) {

    $formW0.find('label').each(function (i, a) {
      var $a = $(a);
      if ($a.attr('for') == undefined || $a.find('a').length) {
        return
      }

      var key = $a.attr('for');
      if (key in data) {
        a.innerHTML += '&nbsp;<a href="' + data[key].url + '" target=_blank><span class="glyphicon glyphicon-question-sign description-info" data-key="' + key + '"></span></a>';
      } else {
        a.innerHTML += '&nbsp;<span class="glyphicon glyphicon-question-sign text-info description-info-add" style="opacity: 0.3;" title="*Создать ссылку на описание*" data-key="' + key + '"></span>';
      }
    });

    // редактирование
    $('span.description-info').each(function (i, elem) {
      var $elem = $(elem);

      $elem.bind('contextmenu', function (event) {

        var key = $(event.target).data('key');
        var linkForm = $('#alf_form');

        linkForm.hide().find('input').val('');
        linkForm.data('key', key);

        $('#alf_url').val(data[key].url);

        setPossition(linkForm, event, true);

        return false;
      });
    })

    // создание
    $('span.description-info-add').each(function (i, elem) {
      var $elem = $(elem);

      $elem.bind("contextmenu", function () {
        return false;
      })

      $elem.click(function (event) {
        var linkForm = $('#alf_form');
        linkForm.hide().find('input').val('');
        linkForm.data('key', $(event.target).data('key'));
        $('#alf_url').val('http://');
        setPossition(linkForm, event, false);
      });
    });

    $('.alf_cancel').click(function () {
      $('#alf_form').hide();
    });

    $('.alf_save').click(function () {
      $.get('/dictionary/data/save', {
        form_url: document.alf_form_url,
        key: $('#alf_form').data('key'),
        url: $('#alf_url').val()
      }).done(function (data) {
        if ('status' in data && data.status == 'ok') {
          document.location.replace(document.location.href);
        } else {
          alert(data);
        }
      });
    });
    $('.alf_delete').click(function () {
      $.get('/dictionary/data/save', {
        form_url: document.alf_form_url,
        key: $('#alf_form').data('key'),
        url: $('#alf_url').val(),
        is_delete: 1
      }).done(function (data) {
        if ('status' in data && data.status == 'ok') {
          document.location.replace(document.location.href);
        } else {
          alert(data);
        }
      });
    });

  });
});