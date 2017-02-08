<?php
use yii\helpers\Url;

kartik\typeahead\TypeaheadAsset::register(Yii::$app->getView());
$request = Yii::$app->request->get();
?>

<div>
    <form action="<?= Url::toRoute(['search/index', 'search' => isset($request['search']) ? $request['search'] : '']) ?>"
          id="search-form">
        <input type="hidden" name="searchType" value="<?= (isset($request['searchType'])) ? $request['searchType'] : '' ?>"
               id="search-type">

        <div class="col-sm-4">
            <div class="input-group">
                <input id="search" type="text" class="form-control input-sm" placeholder="Search ..." name="search"
                       value="<?= (isset($request['search'])) ? $request['search'] : '' ?>">
                <span class="input-group-btn" title="Submit">
                <button type="submit" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-search"></i></button>
            </span>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="btn-group" id="btn-options">
                <button type="submit" class="btn btn-link btn-xs">Искать по</button>
                <button type="submit" class="btn btn-primary btn-xs" data-search="clients"
                        data-placeholder="№ ЛС или Названию">Клиентам
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="contractNo"
                        data-placeholder="Номеру договора">Договору
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="inn"
                        data-placeholder="ИНН">ИНН
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="voip"
                        data-placeholder="номеру">Voip
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="email"
                        data-placeholder="email">Email
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="troubles"
                        data-placeholder="№ заявки">Заявкам
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="bills"
                        data-placeholder="№ счёта">Счетам
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="ip"
                        data-placeholder="IP адресу">IP
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="address"
                        data-placeholder="адресу">Адресу
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="domain"
                        data-placeholder="домену">Домену
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="adsl"
                        data-placeholder="ADSL">ADSL
                </button>
            </div>
        </div>
    </form>
    <script>
        var setInput = function () {
            var el = $('#btn-options .btn-primary');
            $('#search').attr('placeholder', 'Поиск по ' + el.data('placeholder'));
            $('#search-type').val(el.data('search'));
        };

        $(function () {
            if ($('#search-type').val()) {
                $('#btn-options .btn:not(.btn-link)').addClass('btn-default').removeClass('btn-primary');
                $('.btn[data-search="' + $('#search-type').val() + '"]').removeClass('btn-default').addClass('btn-primary');
                //$('#btn-options .btn-link').click();
            }
            setInput();

            var substringMatcher = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '/search/index?search=%QUERY&searchType=' + $("#search-type").val(),
                    wildcard: '%QUERY'
                }
            });

            $('#search').typeahead({
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
        });

        $('#btn-options .btn:not(.btn-link)').on('click', function (e) {
            e.preventDefault();
            $('#btn-options .btn:not(.btn-link)').addClass('btn-default').removeClass('btn-primary');
            $(this).addClass('btn-primary');
            setInput();
            $(this).parents('form').trigger('submit');
        });

        $('#search-form').on('submit', function (e) {
            if ($('#search').val() == '') {
                e.preventDefault();
                return false;
            }
        });

    </script>
</div>
