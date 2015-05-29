<?php

use \yii\helpers\Html;
use app\forms\transfer\ServiceTransferForm;

/** @var $model ServiceTransferForm */
?>

<form method="POST" action="/transfer/index/?client=<?php echo $client->id; ?>">
    <table border="0" width="95%" align="center">
        <col width="35%" />
        <col width="40%" />
        <col width="15%" />
        <thead>
            <tr>
                <th colspan="3">
                    <h2>Лицевой счет № <?php echo $client->id; ?> <?php echo $client->firma; ?></h2>
                    <hr size="1" />
                </th>
            </tr>
            <?php
            if ($model->hasErrors()) :
                ?>
                <tr>
                    <th colspan="3" align="center">
                        <p class="panel-danger alert-danger">
                            <?php
                            foreach($model->getErrors() as $errors):
                                foreach($errors as $error):
                                    ?>
                                    <?php echo $error; ?>
                                <?php
                                endforeach;
                            endforeach;
                            ?>
                        </p>
                    </th>
                </tr>
            <?php
            endif;
            ?>
            <tr>
                <th>Перенести</th>
                <th>на лицевой счет</th>
                <th>дата переноса</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td valign="top">
                    <div class="radio">
                        <label>
                            <input type="radio" name="services-choose" value="all" checked="checked" data-action="services-choose" />
                            Все
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="services-choose" value="custom" data-action="services-choose" />
                            Выбранные услуги
                        </label>
                    </div>

                    <div id="services-list" style="width: 80%; height: auto; display: block; overflow: auto;">
                        <?php
                        foreach ($model->getPossibleServices($client) as $service_type => $services):
                            print $service_type . '<br />';
                            foreach ($services as $service):
                                $text = $fulltext = '';
                                switch ($service_type)
                                {
                                    case 'emails':
                                        $text = $fulltext = $service->local_part . '@' . $service->domain;
                                        break;
                                    case 'usage_voip':
                                        $text = $fulltext = $service->E164 . 'x' . $service->no_of_lines;
                                        break;
                                    default:
                                        $tariff = $service->tariff;
                                        if ($tariff)
                                            $text = $fulltext = $tariff->description;
                                        break;
                                }

                                if (mb_strlen($text, 'UTF-8') > 24)
                                    $text = mb_substr($text, 0, 24, 'UTF-8') . '...';
                                ?>
                                <input type="checkbox" name="transfer[source_service_ids][<?php echo $service_type; ?>][]" value="<?php echo $service->id; ?>" checked="checked" />
                                &nbsp;<?php echo $service->id; ?>: <abbr title="<?php echo $fulltext; ?>"><?php echo $text; ?></abbr>
                                <?php
                                if (array_key_exists($service->id, $model->servicesErrors)):
                                    ?>
                                    <div class="label label-danger">
                                        <?php
                                        echo implode($model->servicesErrors[$service->id], '<br />');
                                        ?>
                                    </div>
                                    <?php
                                endif;
                                ?>
                                <br />
                                <?php
                            endforeach;
                        endforeach;
                        ?>
                    </div>

                </td>
                <td valign="top">
                    <?php
                    $firstRow = (boolean) !$model->target_account_id;
                    foreach ($model->getClientAccounts($client) as $account):
                        ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="transfer[target_account_id]" value="<?php echo $account->id; ?>" data-action="account-choose"<?php echo ($firstRow || $model->target_account_id == $account->id ? 'checked="checked"' : ''); ?> />
                                № <?php echo $account->id; ?> - <?php echo $account->firma; ?>
                            </label>
                        </div>
                        <?php
                        $firstRow = false;
                    endforeach;
                    ?>

                    <div class="radio">
                        <label>
                            <input type="radio" name="transfer[target_account_id]" value="custom" data-action="account-choose" />
                            Другой клиент
                        </label>
                    </div>

                    <div class="account-search" style="display: none;">
                        <input type="text" name="transfer[target_account_custom]" class="text" value="<?php echo $model->actual_custom; ?>" />
                    </div>
                </td>
                <td valign="top">
                    <?php
                    $firstRow = (boolean) !$model->actual_from;
                    foreach ($model->getActualDateVariants() as $date):
                        ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="transfer[actual_from]" value="<?php echo strtotime($date); ?>" data-action="date-choose"<?php echo ($firstRow || $model->actual_from == $date ? 'checked="checked"' : ''); ?> />
                                <?php echo date('d.m.Y', strtotime($date)); ?>
                            </label>
                        </div>
                        <?php
                        $firstRow = false;
                    endforeach;
                    ?>
                    <div class="radio">
                        <label>
                            <input type="radio" name="transfer[actual_from]" value="custom" data-action="date-choose" />
                            Другая дата
                        </label>
                    </div>
                    <input type="text" name="transfer[actual_custom]" class="text" style="visibility: hidden;" />
                </td>
            </tr>
        </tbody>
    </table>

    <div style="position: fixed; bottom: 0; right: 15px;">
        <button type="button" id="dialog-close" style="width: 100px;" class="btn btn-link">Закрыть</button>
        <button type="submit" style="width: 150px;" class="btn btn-primary">OK</button>
    </div>
</form>

<script type="text/javascript">
/**
* Для переноса услуг, создание и обработка popup
*/
jQuery(document).ready(function() {
    var $actions = {
            'services-choose': function(element) {
                var extend_block = $('#services-list');
                if (element.val() == 'custom') {
                    extend_block.find('input[type="checkbox"]').removeAttr('checked').prop('checked', false);
                    extend_block.show();
                }
                else {
                    extend_block.find('input[type="checkbox"]').attr('checked', 'checked').prop('checked', true);
                    extend_block.hide();
                }
            },
            'date-choose': function(element) {
                var extend_block = $('input[name="transfer[actual_custom]"]');
                element.val() == 'custom' ? extend_block.css('visibility', 'visible') : extend_block.css('visibility', 'hidden');
            },
            'account-choose': function(element) {
                var extend_block = $('div.account-search');
                element.val() == 'custom' ? extend_block.show() : extend_block.hide();
            }
        },
        $search_timeout;

    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });

    $('input[name="transfer[actual_custom]"]').datepicker({
        closeText: 'Закрыть',
        prevText: '&#x3c;Пред',
        nextText: 'След&#x3e;',
        currentText: 'Сегодня',
        monthNames: [
            'Январь','Февраль','Март','Апрель','Май','Июнь',
            'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'
        ],
        monthNamesShort: [
            'Янв','Фев','Мар','Апр','Май','Июн',
            'Июл','Авг','Сен','Окт','Ноя','Дек'
        ],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Не',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        showMonthAfterYear: false,
        yearSuffix: '',
        minDate: '+1 day'
    });

    $('input[name="transfer[target_account_custom]"]').autocomplete({
        source: '/transfer/account-search',
        minLength: 2,
        select: function(event, ui) {
            console.log(ui.item);
            /*
             log( ui.item ?
             "Selected: " + ui.item.value + " aka " + ui.item.id :
             "Nothing selected, input was " + this.value );
             */
        }
    });

    $('input[data-action]').click(function() {
        if ($(this).has(':checked') && $.isFunction($actions[$(this).data('action')]))
            $actions[$(this).data('action')]($(this));
    });
});
</script>