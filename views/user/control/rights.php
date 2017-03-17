<?php

/** @var UserForm $model */
/** @var \app\classes\BaseView $this */

use app\forms\user\UserForm;
use app\models\UserRight;
use yii\helpers\ArrayHelper;

$groupRights = ArrayHelper::map($model->initModel->groupRights, 'resource', 'access');
$userRights = ArrayHelper::map($model->initModel->userRights, 'resource', 'access');
$realRights = ArrayHelper::merge($groupRights, $userRights);

$this->registerJsVariable('groupRights', $groupRights);
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
                <tr><td colspan="2" class="group-indent"></td></tr>
                <tr class="group">
                    <td valign="top">
                        <span class="title"><?= $item['comment'] . ' (' . $groupItemKey . ')'; ?></span>
                    </td>
                    <td valign="top">
                        <div class="rights-indent">
                            <input
                                name="rights_radio[<?= $groupItemKey; ?>]"
                                type="radio"
                                class="rights_mode"
                                value="default"
                                data-group="<?= $groupItemKey; ?>"
                                <?= (!isset($userRights[$groupItemKey]) ? ' checked="checked"' : ''); ?> />
                            <label>Стандартный</label>
                            <input
                                name="rights_radio[<?= $groupItemKey; ?>]"
                                type="radio"
                                value="custom"
                                class="rights_mode"
                                data-group="<?= $groupItemKey; ?>"
                                <?= (isset($userRights[$groupItemKey]) ? ' checked="checked"' : ''); ?> />
                            <label>Особый</label>
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
                                    name="<?= $model->formName(); ?>[rights][<?= $groupItemKey; ?>][]"
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