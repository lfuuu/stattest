<?php
use app\classes\Html;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;

$modes = [];
foreach ($operator->requestModes as $mode => $params) {
    $modes[$mode] = $params['title'];
}

echo Html::formLabel('Статистика - Отчет по OnLime');
?>

<div class="well">
    <form method="GET">
        <div class="col-xs-8">
            <legend style="font-size: 16px;">Фильтр</legend>
            <table border="0" width="100%">
                <colgroup>
                    <col width="30%" />
                    <col width="20%" />
                    <col width="20%" />
                    <col width="*" />
                </colgroup>
                <thead>
                    <tr>
                        <th style="font-size: 12px;">Период</th>
                        <th style="font-size: 12px;"><span style="padding-left: 14px">Состояние</span></th>
                        <th style="font-size: 12px;"><span style="padding-left: 14px">Дополнительно</span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php
                            echo DateRangePicker::widget([
                                'name' => 'filter[range]',
                                'presetDropdown' => true,
                                'hideInput' => true,
                                'value' => $filter['dateFrom'] . ' : ' . $filter['dateTo'],
                                'pluginOptions' => [
                                    'format' => 'YYYY-MM-DD',
                                    'separator'=>' : ',
                                ],
                            ]);
                            ?>
                        </td>
                        <td>
                            <div class="col-xs-12">
                                <?php
                                echo Select2::widget([
                                    'name' => 'filter[mode]',
                                    'data' => $modes,
                                    'value' => $filter['mode'],
                                    'options' => [
                                        'placeholder' => '-- Выбрать --'
                                    ]
                                ]);
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="col-xs-12">
                                <?php
                                echo Select2::widget([
                                    'name' => 'filter[promo]',
                                    'value' => $filter['promo'],
                                    'data' => [
                                        '--' => 'Все заявки',
                                        'promo' => 'По акции',
                                        'no_promo' => 'Не по акции',
                                    ],
                                ]);
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            echo Html::submitButton('Применить', ['class' => 'btn btn-primary',]);
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-xs-4">
            <legend style="font-size: 16px;">Проверка заказа</legend>
            <table border="0" width="100%" style="margin-top: 32px;">
                <tbody>
                    <tr>
                        <td>
                            <?php
                            echo Html::input('text', 'bill_no', '', [
                                'class' => 'form-control',
                                'placeholder' => 'Введите номер заказа',
                            ]);
                            ?>
                        </td>
                        <td>
                            <div class="col-xs-10">
                                <?php
                                echo Html::button('Ок', [
                                    'class' => 'btn btn-primary check-order',
                                ]);
                                ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

    <legend style="padding-top: 50px; font-size: 16px;">
        Отчет

        <?php if ($filter['mode']): ?>
            <?php if ($filter['promo']): ?>
                <?php
                switch ($filter['promo']) {
                    case 'promo':
                        echo 'по акционным заявкам';
                        break;
                    case 'no_promo':
                        echo 'по не акционным заявкам';
                        break;
                    default:
                        echo ' по всем заявкам ';
                        break;
                }
                ?>
            <?php endif; ?>

            <?php if ($filter['dateFrom'] && $filter['dateTo']): ?>
                за период с <?= $filter['dateFrom']; ?> по <?= $filter['dateTo']; ?>
            <?php endif; ?>

            <?php if (isset($operator->requestModes[ $filter['mode'] ])): ?>
                в состоянии "<?= $operator->requestModes[ $filter['mode'] ]['title']; ?>"
            <?php endif; ?>
        <?php endif; ?>
    </legend>

    <?php if ($filter['mode']): ?>

        <?php
        echo $this->render('table.php', [
            'operator' => $operator,
            'filter' => $filter,
            'report' => $report,
        ]);
        ?>

    <?php endif; ?>

</div>
<div id="order_details" title="Подробная информация"></div>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('a.ui-dialog-titlebar-close').trigger('click');
    $('#order_details').dialog({
        width: 850,
        height: 400,
        autoOpen: false,
        open: function() {
            $(this).load('./index_lite.php?module=stats&action=onlime_details&order_id=' + $('input[name="bill_no"]').val());
        }
    });


    $('button.check-order')
        .on('click', function() {
            var $details = $('#order_details');
            $details.html('');
            $details.dialog('open');
        });
});
</script>