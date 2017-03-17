+function ($) {
    'use strict';

    $(function () {
        var
            $searchForm = $('#search-form'),
            $searchField = $('#search'),
            $searchTypes = $('#btn-options .btn:not(.btn-link)'),
            $searchType = $('#search-type'),
            setInput = function () {
                var $searchTypeBtn = $('#btn-options .btn-primary');
                $searchField.attr('placeholder', 'Поиск по ' + $searchTypeBtn.data('placeholder'));
                $searchType.val($searchTypeBtn.data('search'));
            };

        if ($searchType.val()) {
            $searchTypes
                .addClass('btn-default')
                .removeClass('btn-primary');
            $('.btn[data-search="' + $searchType.val() + '"]')
                .removeClass('btn-default')
                .addClass('btn-primary');
        }

        setInput();

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