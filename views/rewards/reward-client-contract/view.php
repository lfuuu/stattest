<?php

/**
 * @var ContractEditForm $model
 * @var \app\classes\BaseView $this
 */

use app\models\rewards\RewardClientContractService;
use app\modules\uu\models\ServiceType;
use yii\helpers\Url;


$model = $formModel;

$reward = RewardClientContractService::findOne(['client_contract_id' => $model->id]);
if ($reward) {
    $url = Url::toRoute(['/rewards/reward-client-contract/edit', 'id' => $model->id]);
} else {
    $url = Url::toRoute(['/rewards/reward-client-contract/new', 'contract_id' => $model->id]);
}

?>

<div class="text-right" style="vertical-align: middle; margin-top:5px">
    <?= $this->render('//layouts/_link', [
        'url' => $url,
        'text' => $reward ? 'Редактировать' : 'Создать',
        'params' => [
            'class' => 'btn btn-primary',
        ],
    ])
    ?>
</div>

<?php
$serviceTypes = ServiceType::find()->asArray()->all();
foreach ($serviceTypes as $i => $service) :
?>
    <?php
    $thisPeriod = (new DateTime('first day of this month'))->format("Y-m-d");
    if ($reward = RewardClientContractService::find()->where(['client_contract_id' => $model->id, 'service_type_id' => $i, 'actual_from' => $thisPeriod])->orderBy(['id' => SORT_DESC])->one()) :
        $resources = $reward->resources;
    ?>
        <h3 style="font-size:15px;font-weight:bold;"><?php echo $reward->serviceType->name ?></h3>
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
                        <p>% от абонентской платы</p>
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
                    <?php if ($resources) : ?>
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
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $actualFrom = substr($reward->actual_from, 0, 7);
                ?>
                <tr>
                    <td data-field="actual_from"><?= $actualFrom ?></td>
                    <td data-field="once_only"><?= $reward->once_only ?></td>
                    <td data-field="percentage_once_only"><?= $reward->percentage_once_only ?></td>
                    <td data-field="percentage_of_fee"><?= $reward->percentage_of_fee ?></td>
                    <td data-field="percentage_of_minimal"><?= $reward->percentage_of_minimal ?></td>
                    <td data-field="period_type" data-value="<?= $reward->period_type ?>">
                        <?= RewardClientContractService::$periods[$reward->period_type] ?></td>
                    <td data-field="period_month"><?= $reward->period_month ?> </td>
                    <td>
                        <table>
                            <colgroup>
                                <col width="200" />
                                <col width="160" />
                                <col width="160" />
                            </colgroup>
                            <tbody>
                                <?php
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
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
<?php endforeach; ?>