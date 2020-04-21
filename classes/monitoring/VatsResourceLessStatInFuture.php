<?php

namespace app\classes\monitoring;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\VirtpbxStat;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceClass;
use app\modules\uu\models\ServiceType;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Html;

class VatsResourceLessStatInFuture extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'vats_resource_less_stat_in_future';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'ВАТС: абонентов меньше чем в статистике (подключаемые)';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'ВАТС: абонентов меньше чем в статистике (подключаемые)';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'label' => 'Услуга',
                'format' => 'raw',
                'value' => function ($data) {
                    $usage = AccountTariff::findOne(['id' => $data['account_tariff_id']]);

                    list ($title, $description) = (array)$usage->helper->description;

                    return
                        Html::a(
                            $title . ' ' . $description,
                            $usage->helper->editLink,
                            ['target' => '_blank']
                        );
                },

            ],
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($data) {

                    /** @var ClientAccount $account */
                    $account = ClientAccount::findOne(['id' => $data['id']]);

                    return
                        Html::a(
                            $account->contract->contragent->name .
                            ' / Договор № ' . $account->contract->number .
                            ' / ' . $account->getAccountTypeAndId(),
                            ['/client/view', 'id' => $account->id],
                            ['target' => '_blank']
                        );
                }
            ],
            [
                'label' => 'Ресурсы',
                'format' => 'raw',
                'value' => function ($data) {
                    return (int)$data['amount'];
                }
            ],
            [
                'label' => 'Статситика',
                'format' => 'raw',
                'value' => function ($data) {
                    return (int)$data['numbers'];
                }
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $q = AccountTariff::find()
            ->select(['id', 'prev_usage_id'])
            ->where([
                'service_type_id' => ServiceType::ID_VPBX,
                'tariff_period_id' => null
            ])
            ->andWhere(['>', 'prev_usage_id', 0])
            ->andWhere(['>=', 'insert_time', (new \DateTime())->modify('first day of previous month')->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->orderBy(['insert_time' => SORT_DESC]);

        $result = AccountTariff::find()
            ->alias('u')
            ->innerJoin(['a' => $q])
            ->innerJoin(['s' => VirtpbxStat::tableName()], 's.usage_id = a.prev_usage_id')
            ->joinWith('accountTariffResourceLogs l')
            ->select([
                'id' => 'client_account_id', 'account_tariff_id', 'amount', 'numbers'
            ])
            ->where([
                's.date' => (new \DateTime('now'))->modify('-1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
                'l.id' => (AccountTariffResourceLog::find()->where(['resource_id' => ResourceClass::ID_VPBX_ABONENT])->select(new Expression('max(id)'))->groupBy('account_tariff_id')),
                'service_type_id' => ServiceType::ID_VPBX
            ])
            ->andWhere('u.id = a.id')
            ->having('amount < numbers')
            ->asArray()
            ->all();

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}