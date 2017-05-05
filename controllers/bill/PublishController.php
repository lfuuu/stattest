<?php
namespace app\controllers\bill;

use app\classes\Utils;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Organization;
use app\models\Param;
use app\models\Region;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;

class PublishController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['newaccounts_mass.access'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $organizationId
     * @param int $regionId
     * @return string
     */
    public function actionIndex($organizationId = Organization::MCN_TELEKOM, $regionId = Region::HUNGARY)
    {
        $isNotificationsOff = false;
        $switchOffParam = Param::findOne(Param::NOTIFICATIONS_SWITCH_OFF_DATE);
        if ($switchOffParam) {
            $isNotificationsOff = DateTimeZoneHelper::getDateTime($switchOffParam->value);
        }

        $isNotificationsOn = false;
        $switchOnParam = Param::findOne(Param::NOTIFICATIONS_SWITCH_ON_DATE);
        if ($switchOnParam) {
            $isNotificationsOn = DateTimeZoneHelper::getDateTime($switchOnParam->value);
        }

        $isEnabledRecalcWhenEditBill = !((bool)Param::findOne(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL));

        return $this->render('index', [
            'organizationId' => $organizationId,
            'regionId' => $regionId,
            'isNotificationsOff' => $isNotificationsOff,
            'isNotificationsOn' => $isNotificationsOn,
            'isNotificationsRunning' => Utils::isFileLocked(Param::NOTIFICATIONS_LOCK_FILEPATH),
            'isEnabledRecalcWhenEditBill' => $isEnabledRecalcWhenEditBill,
        ]);
    }

    /**
     * Публикация счетов в регионе
     *
     * @param int $regionId
     * @return \yii\web\Response
     */
    public function actionRegion($regionId)
    {
        $query = Bill::find()
            ->from(['b' => Bill::tableName()])
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = b.client_id')
            ->where([
                'c.region' => $regionId,
                'b.is_show_in_lk' => 0
            ])
            ->andWhere(['like', 'b.bill_no', date('Ym') . '-%', false]);

        $count = 0;

        /** @var \app\models\Bill $bill */
        foreach ($query->each() as $bill) {
            $bill->is_show_in_lk = 1;
            $bill->save();
            $count++;
        }

        Yii::$app->session->addFlash('success', 'Опубликовано ' . $count . ' счетов');

        return $this->redirect(['/bill/publish/index', 'regionId' => $regionId]);
    }

    /**
     * Публикация счетов по организации
     *
     * @param int $organizationId
     * @return \yii\web\Response
     */
    public function actionOrganization($organizationId)
    {
        $query = Bill::find()
            ->from(['b' => Bill::tableName()])
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = b.client_id')
            ->innerJoin(['cc' => ClientContract::tableName()], 'cc.id = c.contract_id')
            ->where([
                'cc.organization_id' => $organizationId,
                'b.is_show_in_lk' => 0
            ])
            ->andWhere(['like', 'b.bill_no',  date('Ym') . '-%', false]);

        $count = 0;

        /** @var Bill $bill */
        foreach ($query->each() as $bill) {
            $bill->is_show_in_lk = 1;
            $bill->save();
            $count++;
        }

        Yii::$app->session->addFlash('success', Yii::t('common', 'Published {n, plural, one{# bill} other{# bills}}', ['n' => $count]));

        return $this->redirect(['/bill/publish/index', 'organizationId' => $organizationId]);
    }

}