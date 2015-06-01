<?php

use \yii\helpers\Html;
use app\forms\transfer\ServiceTransferForm;

/** @var $model ServiceTransferForm */

$servicesTitle = [
    'usage_extra' => 'Доп. услуги',
    'usage_sms' => 'SMS',
    'usage_welltime' => 'Welltime',
    'usage_voip' => 'Телефония',
    'emails' => 'E-mail',
    'usage_ip_ports' => 'Интернет'
];
?>

<form method="POST" action="/transfer/index/?client=<?php echo $client->id; ?>">
    <table border="0" width="95%" align="center">
        <col width="40%" />
        <col width="40%" />
        <col width="15%" />
        <thead>
            <tr>
                <th colspan="3">
                    <h2>Лицевой счет № <?php echo $client->id; ?> <?php echo $client->firma; ?></h2>
                    <hr size="1" />
                </th>
            </tr>
            <tr>
                <th>
                    Перенести
                    <?php
                    if ($model->getFirstError('source_service_ids') || $model->getFirstError('services-got-errors')):
                        ?>
                        <br />
                        <div class="label label-danger">
                            <?php echo $model->getFirstError('source_service_ids'); ?>
                            <?php echo $model->getFirstError('services-got-errors'); ?>
                        </div>
                    <?php
                    endif;
                    ?>
                </th>
                <th>
                    на лицевой счет
                    <?php
                    if ($model->getFirstError('target_account_id') || $model->getFirstError('target_account_custom')):
                        ?>
                        <br />
                        <div class="label label-danger">
                            <?php echo $model->getFirstError('target_account_id'); ?>
                            <?php echo $model->getFirstError('target_account_custom'); ?>
                        </div>
                    <?php
                    endif;

                    if ($model->getFirstError('target-account-not-found')):
                        ?>
                        <br />
                        <div class="label label-danger">
                            <?php echo $model->getFirstError('target-account-not-found'); ?>
                        </div>
                    <?php
                    endif;
                    ?>
                </th>
                <th>
                    дата переноса
                    <?php
                    if ($model->getFirstError('actual_from') || $model->getFirstError('actual_custom')):
                        ?>
                        <br />
                        <div class="label label-danger">
                            <?php echo $model->getFirstError('actual_from'); ?>
                            <?php echo $model->getFirstError('actual_custom'); ?>
                        </div>
                    <?php
                    endif;
                    ?>
                </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td valign="top">
                    <div class="radio">
                        <label>
                            <input type="radio" name="services-choose" value="all" data-action="services-choose"<?php echo (!sizeof($model->servicesErrors) ? 'checked="checked"' : ''); ?> />
                            Все
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="services-choose" value="custom" data-action="services-choose"<?php echo (sizeof($model->servicesErrors) ? ' checked="checked"' : '');?> />
                            Выбранные услуги
                        </label>
                    </div>

                    <div id="services-list" style="width: 90%; height: auto; display: none; overflow: auto; margin-left: 20px;">
                        <?php
                        foreach ($model->getPossibleServices($client) as $service_type => $services):
                            ?>
                            <b><?php echo (array_key_exists($service_type, $servicesTitle) ? $servicesTitle[$service_type] : $service_type); ?></b><br />
                            <?php
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
                                    case 'usage_ip_ports':
                                        $text = $fulltext = $service->address;
                                        break;
                                    default:
                                        $tariff = $service->tariff;
                                        if ($tariff)
                                            $text = $fulltext = $tariff->description;
                                        break;
                                }

                                if (mb_strlen($text, 'UTF-8') > 30):
                                    $text = mb_substr($text, 0, 30, 'UTF-8') . '...';
                                endif;
                                ?>

                                <?php
                                if (array_key_exists($service->id, $model->servicesErrors)):
                                    ?>
                                    <img src="/images/icons/error.png" width="16" height="16" border="0" style="vertical-align: top; margin-top: 1px;" title='<?php echo implode($model->servicesErrors[$service->id], "\n"); ?>' />
                                <?php
                                endif;
                                ?>

                                <input type="checkbox" name="transfer[source_service_ids][<?php echo $service_type; ?>][]" value="<?php echo $service->id; ?>" checked="checked" />
                                &nbsp;<?php echo $service->id;?>: <abbr title="<?php echo $service->id . ': ' . $fulltext; ?>"><?php echo $text; ?></abbr><br />
                                <?php
                            endforeach;
                            ?>
                            <br />
                            <?php
                        endforeach;
                        ?>
                    </div>

                </td>
                <td valign="top">
                    <?php
                    if (!is_null($model->targetAccount)):
                        ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="transfer[target_account_id]" value="<?php echo $model->targetAccount->id; ?>" data-action="account-choose" checked="checked" />
                                № <?php echo $model->targetAccount->id; ?> - <?php echo $model->targetAccount->firma; ?>
                            </label>
                        </div>
                    <?php
                    endif;

                    $firstRow = (boolean) !(int) $model->target_account_id;
                    foreach ($model->getClientAccounts($client) as $account):
                        if (!is_null($model->targetAccount) && $account->id == $model->targetAccount->id)
                            continue;
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

                    <div class="radio" data-custom="1">
                        <label>
                            <input type="radio" name="transfer[target_account_id]" value="custom" data-action="account-choose" />
                            Другой клиент
                        </label>
                    </div>

                    <input type="text" name="transfer[target_account_custom]" class="text" style="margin-left: 20px; visibility: hidden;" />
                </td>
                <td valign="top">
                    <?php
                    $firstRow = (boolean) !$model->actual_from;
                    foreach ($model->getActualDateVariants() as $date):
                        ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="transfer[actual_from]" value="<?php echo strtotime($date); ?>" data-action="date-choose"<?php echo ($firstRow || $model->actual_from == strtotime($date) ? 'checked="checked"' : ''); ?> />
                                <?php echo date('d.m.Y', strtotime($date)); ?>
                            </label>
                        </div>
                        <?php
                        $firstRow = false;
                    endforeach;
                    ?>
                    <div class="radio">
                        <label>
                            <input type="radio" name="transfer[actual_from]" value="custom" data-action="date-choose"<?php echo ($model->actual_from == 'custom' ? 'checked="checked"' : ''); ?> />
                            Другая дата
                        </label>
                    </div>
                    <input type="text" name="transfer[actual_custom]" value="<?php echo $model->actual_custom; ?>" class="text" style="margin-left: 20px; visibility: hidden; width: 100px;" />
                </td>
            </tr>
        </tbody>
    </table>

    <div style="position: fixed; bottom: 0; right: 15px;">
        <button type="button" id="dialog-close" style="width: 100px; margin-right: 15px;" class="btn btn-link">Отмена</button>
        <button type="submit" style="width: 100px;" class="btn btn-primary">OK</button>
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
                var extend_block = $('input[name="transfer[target_account_custom]"]');
                element.val() == 'custom' ? extend_block.css('visibility', 'visible') : extend_block.css('visibility', 'hidden');
            }
        };

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

    $('input[name="transfer[target_account_custom]"]')
        .bind('keydown', function(e) {
            if (e.keyCode === $.ui.keyCode.TAB && $(this).autocomplete('instance').menu.active)
                e.preventDefault();
        })
        .autocomplete({
            source: '/transfer/account-search',
            minLength: 2,
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                var input = $('<input />')
                    .attr('type', 'radio')
                    .attr('name', 'transfer[target_account_id]')
                    .attr('data-action', 'account-choose')
                    .attr('checked', 'checked')
                    .prop('checked', true)
                    .val(ui.item.value);

                $('<div />')
                    .insertBefore('div[data-custom]')
                    .addClass('radio')
                    .append(
                        $('<label />')
                            .text(ui.item.label)
                            .prepend(input)
                    );

                input.trigger('click');
                $('input[name="transfer[target_account_custom]"]').val('');
                return false;
            }
        })
        .data('autocomplete')._renderItem = function(ul, item) {
            return $('<li />')
                .data('item.autocomplete', item)
                .append('<a>' + item.label + '</a>')
                .appendTo(ul);
        };

    $(document).on('change', 'input[data-action]', function() {
        if ($(this).has(':checked') && $.isFunction($actions[$(this).data('action')]))
            $actions[$(this).data('action')]($(this));
    });

    $('input[name="services-choose"]:checked').trigger('change');
    $('input[name="transfer[actual_from]"]:checked').trigger('change');
});
</script>

<style type="text/css">
.ui-autocomplete-loading {
    background: white url('images/ajax-loader-small.gif') right center no-repeat;
}
</style>