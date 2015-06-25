<?php
use yii\helpers\Url;


kartik\typeahead\TypeaheadAsset::register(Yii::$app->getView())
?>
<div>
    <form action="<?= Url::toRoute(['search/index', 'search' => Yii::$app->request->get()['search']]) ?>"
          id="search-form">
        <input type="hidden" name="searchType" value="<?= Yii::$app->request->get()['searchType'] ?>"
               id="search-type">

        <div class="col-sm-4">
            <div class="input-group">
                <input id="search" type="text" class="form-control" placeholder="Search ..." name="search"
                       value="<?= Yii::$app->request->get()['search'] ?>">
            <span class="input-group-btn" title="Submit">
                <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-search"></i></button>
            </span>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="btn-group" id="btn-options">
                <button type="submit" class="btn btn-link">Искать по</button>
                <button type="submit" class="btn btn-primary" style="display: none;" data-search="clients"
                        data-placeholder="№ ЛС или Названию">Клиентам
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="contractNo"
                        data-placeholder="Номеру договора">Договору
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="inn"
                        data-placeholder="ИНН">ИНН
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="voip"
                        data-placeholder="номеру">Voip
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="email"
                        data-placeholder="email">Email
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="troubles"
                        data-placeholder="№ заявки">Заявкам
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="bills"
                        data-placeholder="№ счёта">Счетам
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="ip"
                        data-placeholder="IP адресу">IP
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="address"
                        data-placeholder="адресу">Адресу
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="domain"
                        data-placeholder="домену">Домену
                </button>
                <button type="submit" class="btn btn-default" style="display: none;" data-search="adsl"
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

            var serchs = true;

            var substringMatcher = function () {
                return function findMatches(q, cb) {
                    var matches, substringRegex;
                    matches = [];
                    substrRegex = new RegExp(q, 'i');
                    searchs = false;
                    if (serchs)
                    {
                        $.getJSON('search/index', {
                            search: $("#search").val(),
                            searchType: $("#search-type").val()
                        }, function (matches) {
                            searchs = true;
                            cb(matches);
                        });
                    }
                };
            };

            $('#search').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 3,
                    async: true
                },
                {
                    name: 'states',
                    source: substringMatcher()
                }).bind('typeahead:selected', function(obj, selected, name) {
                    location.href = selected['url'];
                }).off('blur');
        });

        $('#btn-options .btn-link').on('click', function (e) {
            e.preventDefault();
            $(this).parent().children(':not(.btn-link)').toggle();
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
