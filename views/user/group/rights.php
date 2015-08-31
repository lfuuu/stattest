<?php

use yii\helpers\ArrayHelper;
use app\forms\user\GroupForm;
use app\models\UserRight;

/** @var GroupForm $model */

$groupRights = ArrayHelper::map($model->initModel->rights, 'resource', 'access');
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
                        <?php foreach ($item['values'] as $num => $value): ?>
                            <?php
                            $applied_rights = explode(',', $groupRights[$groupItemKey]);
                            ?>
                            <div>
                                <input
                                    type="checkbox"
                                    id="<?= $groupKey . '_' . $value; ?>"
                                    value="<?= $value; ?>"
                                    name="<?= $model->formName(); ?>[rights][<?= $groupKey; ?>][]"
                                    class="checkbox_<?= $groupKey; ?>"
                                    <?= (in_array($value, $applied_rights) ? ' checked="checked"' : ''); ?> />
                                <label for="<?= $groupKey . '_' . $value; ?>">
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

<script type="text/javascript">
jQuery(document).ready(function() {
    $('.active-element')
        .on('click', function() {
            var next = $(this).next('div').length ? $(this).next('div') : $(this).next('table');
            next.toggle();
        });
});
</script>