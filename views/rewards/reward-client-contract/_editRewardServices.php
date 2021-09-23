<?php

/**
 * @var \app\classes\BaseView $this
 * @var \app\forms\rewards\RewardClientContractFormEdit $formModel
 *
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\Html;
use app\models\rewards\RewardClientContractService;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\widgets\MonthPicker;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

echo Html::formLabel('Параметры вознаграждения v3');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Аккаунт', 'url' => Url::toRoute(['/client/view', 'id' => $formModel->id])],
        ['label' => 'Редактирование договора', 'url' => Url::toRoute(['/contract/edit', 'id' => $formModel->id])],
       
    ],
]);

$serviceRewards = $formModel->serviceRewards;
$resourceRewards = $formModel->resourceRewards;

?>
<?php
$this->registerJsVariable('formId', $form->getId());

$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
];
?>
<div class="row" style="margin-left:2%; margin-right:2%;">
    <?php
    $activeServices = ServiceType::getList();
    foreach ($activeServices as $index => $activeService) : ?>
        <?
        foreach ($serviceRewards as $id => $serviceReward) : ?>
            <? if ($serviceReward['service_type_id'] == $index) : ?>
                <h3 style="font-size:15px">
                    <?php echo $serviceReward->serviceType->name; ?>
                </h3>
                </br>
                <div class="well">
                    <?php
                    $serviceLogs = RewardClientContractService::findAll(['client_contract_id' => $formModel->id, 'service_type_id' => $index]);
                    if ($serviceLogs) :
                    ?>
                        <table class="table table-hover" width="100%">
                            <colgroup>
                                <col width="100" />
                                <col width="100" />
                                <col width="130" />
                                <col width="130" />
                                <col width="100" />
                                <col width="130" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <td>
                                        <p>Дата начала</p>
                                    </td>
                                    <td>
                                        <p>Разовое</p>
                                    </td>
                                    <td>
                                        <p>% от подключения</p>
                                    </td>
                                    <td>
                                        <p>% от абонентки</p>
                                    </td>
                                    <td>
                                        <p>% от минималки</p>
                                    </td>
                                    <td>
                                        <p>Тип периода</p>
                                    </td>
                                    <td>
                                        <p>Количество месяцев</p>
                                    </td>
                                    <td>
                                        <table>
                                            <colgroup>
                                                <col width="200" />
                                                <col width="160" />
                                                <col width="160" />
                                            </colgroup>
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <p>Ресурс</p>
                                                    </td>
                                                    <td>
                                                        <p>% от платы</p>
                                                    </td>
                                                    <td>
                                                        <p>% от маржи</p>
                                                    </td>
                                                </tr>
                                            </thead>
                                        </table>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                foreach ($serviceLogs as $log) :
                                    $actualFrom = substr($log->actual_from, 0, 7);
                                ?>
                                    <tr>
                                        <td data-field="actual_from"><?= $actualFrom ?></td>
                                        <td data-field="once_only"><?= $log->once_only ?></td>
                                        <td data-field="percentage_once_only"><?= $log->percentage_once_only ?></td>
                                        <td data-field="percentage_of_fee"><?= $log->percentage_of_fee ?></td>
                                        <td data-field="percentage_of_minimal"><?= $log->percentage_of_minimal ?></td>
                                        <td data-field="period_type" data-value="<?= $log->period_type ?>">
                                            <?= RewardClientContractService::$periods[$log->period_type] ?></td>
                                        <td data-field="period_month"><?= $log->period_month ?> </td>
                                        <td>
                                            <table>
                                                <colgroup>
                                                    <col width="200" />
                                                    <col width="160" />
                                                    <col width="160" />
                                                </colgroup>
                                                <tbody>
                                                    <?php
                                                    $resources = $log->resources;
                                                    foreach ($resources as $resource) :
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $resource->resourceModel->name ?></td>
                                                            <td data-field="price_percent"><?= $resource->price_percent ?></td>
                                                            <?php if (!$resource->percent_margin_fee) : ?>
                                                                <td>----</td>
                                                            <?php else : ?>
                                                                <td data-field="percent_margin_fee"><?= $resource->percent_margin_fee ?></td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td class="text-right">
                                            <?php
                                                if ($log->user_id) {
                                                    echo
                                                        $log->user->name .
                                                        Html::tag('br') .
                                                        Html::tag('small', '(' . $log->insert_time . ')');
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <? endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif ?>
                    <div class="row">
                        <div class="col-sm-2">
                            <?= $form->field($serviceReward, "[{$id}]actual_from",
                                [   
                                    'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
                                ])->widget(MonthPicker::class,
                                    [
                                        'options' => [

                                            'class' => 'form-control',
                                        ],
                                        'widgetOptions' => [
                                            'ShowIcon' => false,
                                            'MonthFormat' => 'yy-mm',
                                        ],
                                ])->textInput([
                                    'autocomplete' => 'off'
                                ])->label('Дата начала')
                            ?>
                        </div>
                        <div class="col-sm-1">
                            <?= $form->field($serviceReward, "[{$id}]once_only")->input('number')->label('Разовое') ?>
                        </div>
                        <div class="col-sm-2">
                            <?= $form->field($serviceReward, "[{$id}]percentage_once_only")->input('number')->label('% от подключения') ?>
                        </div>
                        <div class="col-sm-2">
                            <?= $form->field($serviceReward, "[{$id}]percentage_of_fee")->input('number')->label('% от абонентской платы') ?>
                        </div>
                        <div class="col-sm-2">
                            <?= $form->field($serviceReward, "[{$id}]percentage_of_minimal")->input('number')->label('% от минималки') ?>
                        </div>
                        <div class="col-sm-2">
                            <?= $form->field($serviceReward, "[{$id}]period_type")->dropDownList(['month' => 'Месяц', 'always' => 'Всегда'])->label('Тип периода')
                            ?>
                        </div>
                        <div class="col-sm-2">
                            <?= $form->field($serviceReward, "[{$id}]period_month")->input('number')->label('Количество месяцев') ?>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($resourceRewards[$serviceReward->service_type_id] as $i => $resource) : ?>
                            <div class="col-sm-2">
                                <h4 style="font-size:13px"><?php echo $resource->resourceModel->name; ?></h4>
                                <?= $form->field($resource, "[{$id}][{$i}]price_percent")->input('number')->label('% от платы') ?>
                            </div>
                            <?php if (in_array($resource->resource_id, array_keys(ResourceModel::$calls))) : ?>
                                <div class="col-sm-2">
                                    <h4 style="font-size:13px"><?php echo $resource->resourceModel->name;  ?></h4>
                                    <?= $form->field($resource, "[{$id}][{$i}]percent_margin_fee")->input('number')->label('% от маржи') ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonCancel', ['url' => Url::toRoute(['/contract/edit', 'id' => $formModel->id])]) ?>
    <?= $this->render('//layouts/_submitButton' . ($rewardClientContractService->isNewRecord ? 'Create' : 'Save'), $viewParams) ?>
</div>
