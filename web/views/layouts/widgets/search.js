+function ($) {
  'use strict';

  $(function () {
    $('#search-options.dropdown-menu a').click(function(event) {
      event.preventDefault();

      let $el = $(event.currentTarget);
      let type = $el.data('search');
      $('#search-type').attr('value',type);
      $('#title_search').text($el.text())
      $('#search-form').submit();
    });

    var
      $searchForm = $('#search-form'),
      $searchField = $('#search'),
      $searchTypes = $('#btn-options .btn:not(.btn-link)'),
      $searchType = $('#search-type'),
      setInput = function () {
        let $searchTypeBtn = $('#search-options a:first');
        $searchField.attr('placeholder', 'Поиск по ' + $searchTypeBtn.attr('title'));
        $searchType.val($searchTypeBtn.data('search'));
      };

    setInput = function ($currentSearchEl) {
      let isTitle = $currentSearchEl.prop('title');
      let title = (isTitle ? $currentSearchEl.attr('title') : $currentSearchEl.text());
      $searchField.attr('placeholder', 'Поиск по ' + title);
      $searchType.val($currentSearchEl.data('search'));
    };

    if ($searchType.val()) {
      let $currentSearchEl = $('#search-options a[data-search='+$searchType.val()+']')

      if ($currentSearchEl.length) {
        $('#title_search').text($currentSearchEl.text())
        setInput($currentSearchEl);
      }
    } else {
      setInput($('#search-options a:first'));
    }

    var substringMatcher = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      remote: {
        url: '/search/index?search=%QUERY&searchType=' + $searchType.val(),
        wildcard: '%QUERY'
      }
    });

    $searchField.typeahead({
        hint: true,
        highlight: true,
        minLength: 3,
        async: true
      },
      {
        name: 'search',
        display: 'value',
        source: substringMatcher,
        templates: {
          suggestion: function (obj) {
            if (obj['type'] == 'bill') {
              return '<div style="overflow: hidden; width: 98%;">'
                + '<a href="' + obj['url'] + '" title="Счет № ' + obj['value'] + '">'
                + ' Счет № ' + obj['value']
                + '</a></div>';
            }
            if (obj['type'] == 'invoice') {
              return '<div style="overflow: hidden; width: 98%;">'
                + '<a href="' + obj['url'] + '" title="С/ф № ' + obj['bill_no'] + '">'
                + ' С/ф'+ '#' + obj['type_id'] + ' Счет № ' + obj['bill_no'] + (obj['is_reversal'] ? ' (сторнирована)' : '')
                + '</a></div>';
            }
            else {
              return '<div style="overflow: hidden; width: 98%;">'
                + '<a href="' + obj['url'] + '" title="' + obj['value'] + '">'
                + '<div style="background:' + obj['color'] + '; width: 16px;height: 16px;display: inline-block;"></div>'
                + ' ' + obj['accountType'] + ' № ' + obj['id']
                + ' ' + obj['value']
                + '</a></div>';
            }
          }
        }
      });

    $searchTypes.on('click', function (e) {
      e.preventDefault();
      $searchTypes.addClass('btn-default').removeClass('btn-primary');
      $(this).addClass('btn-primary');
      setInput();
      $(this).parents('form').trigger('submit');
    });

    $searchForm.on('submit', function (e) {
      if ($searchField.val() == '') {
        e.preventDefault();
        return false;
      }
    });
  });

}(jQuery);