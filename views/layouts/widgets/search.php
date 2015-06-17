<?php
use yii\helpers\Url;

?>
<form action="<?= Url::toRoute(['search/index', 'search' => Yii::$app->request->get()['search']]) ?>" id="search-form">
    <input type="hidden" name="searchType" value="<?= Yii::$app->request->get()['searchType'] ?>" id="search-type">

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
            <button type="submit" class="btn btn-default" style="display: none;" data-search="inn"
                    data-placeholder="ИНН">ИНН
            </button>
            <button type="submit" class="btn btn-default" style="display: none;" data-search="voip"
                    data-placeholder="номеру">Номеру
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