<?php

use yii\helpers\ArrayHelper;
use app\forms\user\UserForm;
use app\models\UserRight;

/** @var UserForm $model */

$groupRights = ArrayHelper::map($model->initModel->groupRights, 'resource', 'access');
$userRights = ArrayHelper::map($model->initModel->userRights, 'resource', 'access');
$realRights = ArrayHelper::merge($groupRights, $userRights);
?>

<legend>
    <span>Права доступа</span>
</legend>

<div>
    <?php foreach (UserRight::dao()->getList() as $groupKey => $group): ?>
        <table width="98%" align="center">
            <colgroup>
                <col width="40%" />
                <col width="60%" />
            </colgroup>
            <?php foreach ($group as $groupItemKey => $item): ?>
                <tr><td colspan="2" style="padding-top: 20px;"></td></tr>
                <tr style="border-bottom: 1px solid #E5E5E5;">
                    <td valign="top">
                        <span style="font-weight: bold; font-size: 14px; padding-left: 15px;"><?= $item['comment'] . ' (' . $groupItemKey . ')'; ?></span>
                    </td>
                    <td valign="top">
                        <div style="margin-bottom: 10px;">
                            <input
                                name="rights_radio[<?= $groupItemKey; ?>]"
                                type="radio"
                                class="rights_mode"
                                value="default"
                                data-group="<?= $groupItemKey; ?>"
                                <?= (!isset($userRights[$groupItemKey]) ? ' checked="checked"' : ''); ?> />
                            <label style="font-size: 9px;vertical-align: top; line-height: 18px;">Стандартный</label>
                            <input
                                name="rights_radio[<?= $groupItemKey; ?>]"
                                type="radio"
                                value="custom"
                                class="rights_mode"
                                data-group="<?= $groupItemKey; ?>"
                                <?= (isset($userRights[$groupItemKey]) ? ' checked="checked"' : ''); ?> />
                            <label style="font-size: 9px;vertical-align: top; line-height: 18px;">Особый</label>
                        </div>
                        <?php foreach ($item['values'] as $num => $value): ?>
                            <?php
                            $applied_rights = explode(',', $realRights[$groupItemKey]);
                            ?>
                            <div>
                                <input
                                    type="checkbox"
                                    id="<?= $groupItemKey . '_' . $value; ?>"
                                    value="<?= $value; ?>"
                                    name="<?= $model->formName(); ?>[rights][<?= $groupKey; ?>][]"
                                    class="checkbox_<?= $groupKey; ?>"
                                    <?= (in_array($value, $applied_rights) ? ' checked="checked"' : ''); ?>
                                    <?= (!isset($userRights[$groupItemKey]) ? ' disabled="disabled"' : '');?> />
                                <label
                                    for="<?= $groupItemKey . '_' . $value; ?>"
                                    <?= (!isset($userRights[$groupItemKey]) ? ' class="disabled-label"' : ''); ?>>
                                    <?= $item['values_desc'][$num]; ?> (<b><?= $value; ?></b>)
                                </label>
                            </div>
                        <?php endforeach ;?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
</div>

<style type="text/css">
.disabled-label {
    color: #CCCCCC;
}
</style>
<script type="text/javascript">
jQuery(document).ready(function() {
    var $groupRights = $.parseJSON('<?= json_encode($groupRights); ?>');

    $('.rights_mode')
        .on('click', function() {
            var mode = $(this).val(),
                group = $(this).data('group'),
                inputs = $('input[id^="' + group + '"]'),
                labels = $('label[for^="' + group + '"]');

            if (mode == 'custom') {
                inputs.prop('disabled', false);
                labels.toggleClass('disabled-label');
            }
            else {
                inputs
                    .prop('checked', false)
                    .prop('disabled', true);
                labels.toggleClass('disabled-label');

                if ($groupRights[group]) {
                    var values = $groupRights[group].split(',');
                    for (var i=0,s=values.length; i<s; i++) {
                        inputs.filter('[value="' + values[i] + '"]').prop('checked', true);
                    }
                }
            }
        });
});
</script>