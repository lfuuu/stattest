<?php

use app\forms\transfer\ServiceTransferForm;
use kartik\widgets\DatePicker;

/** @var $model ServiceTransferForm */

$servicesTitle = [
    'usage_extra' => 'Доп. услуги',
    'usage_sms' => 'SMS',
    'usage_welltime' => 'Welltime',
    'usage_voip' => 'Телефония',
    'emails' => 'E-mail',
    'usage_ip_ports' => 'Интернет'
];

$possibleServices = $model->getPossibleServices($client);
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
                <th>Перенести</th>
                <th>на лицевой счет</th>
                <th>дата переноса</th>
            </tr>
        </thead>
    </table>

    <div style="overflow: auto; max-height: 400px;">
        <table border="0" width="95%" align="center">
            <col width="40%" />
            <col width="40%" />
            <col width="15%" />

            <thead>
                <tr>
                    <th valign="top">
                        <?php
                        if ($model->getFirstError('source_service_ids') || $model->getFirstError('services_got_errors')):
                            ?>
                            <br />
                            <div class="label label-danger">
                                <?php echo $model->getFirstError('source_service_ids'); ?>
                                <?php echo $model->getFirstError('services_got_errors'); ?>
                            </div>
                        <?php
                        endif;
                        ?>
                    </th>
                    <th valign="top">
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

                        if ($model->getFirstError('target_account_not_found')):
                            ?>
                            <br />
                            <div class="label label-danger">
                                <?php echo $model->getFirstError('target_account_not_found'); ?>
                            </div>
                        <?php
                        endif;
                        ?>
                    </th>
                    <th valign="top">
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
                                Все (<?php echo $possibleServices['total'];?> шт.)
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
                            foreach ($possibleServices['items'] as $service_type => $services):
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
                                    № <?php echo $account->id; ?> - <?php echo $account->contragent->name; ?>
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

                        <input type="text" name="target_account_search" class="form-control" style="margin-left: 20px; width: 150px; visibility: hidden;" />
                        <input type="hidden" name="transfer[target_account_id_custom]" value="0" />
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

                        <?php
                        echo DatePicker::widget([
                            'type' => DatePicker::TYPE_INPUT,
                            'value' => $model->actual_custom,
                            'name' => 'transfer[actual_custom]',
                            'language' => 'ru',
                            'options' => [
                                'style' => 'margin-left: 20px; width: 100px'
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'dd.mm.yyyy',
                                'orientation' => 'top right',
                                'startDate' =>  'today'
                            ]
                        ]);
                        ?>

                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="position: fixed; bottom: 0; right: 15px;">
        <button type="button" id="dialog-close" style="width: 100px; margin-right: 15px;" class="btn btn-link">Отмена</button>
        <button type="submit" style="width: 100px;" class="btn btn-primary"<?php echo (!$possibleServices['total'] ? 'disabled="disabled"' : '');?>>OK</button>
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
                var extend_block = $('input[name="target_account_search"]');
                element.val() == 'custom' ? extend_block.css('visibility', 'visible') : extend_block.css('visibility', 'hidden');
            }
        };

    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });

    $('input[name="target_account_search"]')
        .bind('keydown', function(e) {
            if (e.keyCode === $.ui.keyCode.TAB && $(this).autocomplete('instance').menu.active)
                e.preventDefault();
            if (e.keyCode === $.ui.keyCode.ENTER)
                $(this).blur();
        })
        .bind('blur', function() {
            $('input[name="transfer[target_account_id_custom]"]').val($(this).val());
        })
        .autocomplete({
            source: '/transfer/account-search?client_id=<?php echo $client->id;?>',
            minLength: 2,
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                $('input[name="transfer[target_account_id_custom]"]').val(ui.item.value);
                $(this).val(ui.item.label);
                return false;
            }
        })
        .data('autocomplete')._renderItem = function(ul, item) {
            return $('<li />')
                .data('item.autocomplete', item)
                .append('<a title="' + item.full + '">' + item.label + '</a>')
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
.ui-autocomplete {
    max-height: 145px;
    overflow-y: auto;
    overflow-x: hidden;
}
.ui-menu-item {
    white-space: nowrap;
}
.form-control {
    padding: 0;
    height: auto;
    font-size: 12px;
}
</style>