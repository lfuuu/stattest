<?php
use yii\helpers\Url;

kartik\typeahead\TypeaheadAsset::register(Yii::$app->getView());
$request = Yii::$app->request->get();
?>
<style>
    .tt-dropdown-menu{
        max-width: 700px;
        width: auto;
    }
</style>
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
                <button type="submit" class="btn btn-primary btn-xs" style="display: none;" data-search="clients"
                        data-placeholder="№ ЛС или Названию">Клиентам
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="contractNo"
                        data-placeholder="Номеру договора">Договору
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="inn"
                        data-placeholder="ИНН">ИНН
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="voip"
                        data-placeholder="номеру">Voip
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="email"
                        data-placeholder="email">Email
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="troubles"
                        data-placeholder="№ заявки">Заявкам
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="bills"
                        data-placeholder="№ счёта">Счетам
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="ip"
                        data-placeholder="IP адресу">IP
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="address"
                        data-placeholder="адресу">Адресу
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="domain"
                        data-placeholder="домену">Домену
                </button>
                <button type="submit" class="btn btn-default btn-xs" style="display: none;" data-search="adsl"
                        data-placeholder="ADSL">ADSL
                </button>
            </div>
        </div>
    </form>
    <script>
        var setInput = function () {
            el = $('#btn-options .btn-primary');
            $('#search').attr('placeholder', 'Поиск по ' + el.data('placeholder'));
            $('#search-type').val(el.data('search'));
        };

        $(function () {
            if ($('#search-type').val()) {
                $('#btn-options .btn:not(.btn-link)').addClass('btn-default').removeClass('btn-primary');
                $('.btn[data-search="' + $('#search-type').val() + '"]').removeClass('btn-default').addClass('btn-primary');
                $('#btn-options .btn-link').click();
            }
            setInput();

            var substringMatcher = function () {
                return function findMatches(q, cb) {
                        $.getJSON('search/index', {
                            search: $("#search").val(),
                            searchType: $("#search-type").val()
                        }, function (matches) {
                            searchs = true;
                            cb(matches);
                            //$('.tt-dropdown-menu').width($(window).width() - $('#search').offset()['left'] - 50);
                        });
                };
            };

            $('#search').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 3,
                    async: true,
                },
                {
                    name: 'search',
                    source: substringMatcher(),
                    templates: {
                        suggestion: function(obj){
                            if(obj['type'] == 'bill'){
                                return '<div>'
                                    + '<a href="' + obj['url'] + '">'
                                    + ' Счет № ' + obj['value']
                                    + '</a></div>';
                            }
                            else {
                                return '<div>'
                                    + '<a href="' + obj['url'] + '">'
                                    + '<div style="background:' + obj['color'] + '; width: 16px;height: 16px;display: inline-block;"></div>'
                                    + ' ЛС № ' + obj['id']
                                    + ' ' + obj['value']
                                    + '</a></div>';
                            }
                        }
                    }
                });
        });

        $('#btn-options .btn-link').on('click', function (e) {
            e.preventDefault();
            $(this).parent().children(':not(.btn-link)').toggle();
            $('.layout_main , .layout_left ').css('top', $('#top_search').closest('.row').height()+25);
        });

        $('#btn-options .btn:not(.btn-link)').on('click', function (e) {
            e.preventDefault();
            $('#btn-options .btn:not(.btn-link)').addClass('btn-default').removeClass('btn-primary');
            $(this).addClass('btn-primary');
            setInput();
        });

        $('#search-form').on('submit', function (e) {
            if ($('#search').val() == '') {
                e.preventDefault();
                return false;
            }
        });

    </script>
</div>
