<?php

namespace app\controllers\bill;

use app\classes\Utils;
use app\dao\BillDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Invoice;
use app\models\Organization;
use app\models\Param;
use app\models\Region;
use Yii;
use yii\db\Query;
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
                        'actions' => ['make-invoice', 'invoice-reversal'],
                        'roles' => ['newaccounts_bills.edit'],
                    ],
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
    public function actionIndex($organizationId = Organization::MCN_TELECOM, $regionId = Region::HUNGARY)
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
            ->andWhere(['like', 'b.bill_no', date('Ym') . '-%', false]);

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

    /**
     * Генерация счет-фактур по организации
     *
     * @param int $organizationId
     * @return \yii\web\Response
     */
    public function actionInvoices($organizationId)
    {
        $query = Bill::find()
            ->from(['b' => Bill::tableName()])
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = b.client_id')
            ->innerJoin(['cc' => ClientContract::tableName()], 'cc.id = c.contract_id')
            ->where([
                'cc.organization_id' => $organizationId,
                'b.is_show_in_lk' => 0
            ]);

        $this->_genetateInvocesForBill($query);

        return $this->redirect(['/bill/publish/index', 'organizationId' => $organizationId]);

    }

    /**
     * Генерация с/ф для всех в этом месяце
     */
    public function actionInvoicesForAll()
    {
        $query = Bill::find()
            ->alias('b')
        ;

        $this->_genetateInvocesForBill($query);

        return $this->redirect(['/bill/publish/index']);
    }

    public function actionMakeInvoice($bill_no)
    {
        $billQuery = Bill::find()
            ->alias('b')
            ->where(['bill_no' => $bill_no]);

        /** @var Bill $bill */
        $billQuery2 = (clone $billQuery);

        $bill = $billQuery2->one();

        if (!$bill) {
            Yii::$app->session->addFlash('error', 'Счет не найден');
        } else {
            $this->_genetateInvocesForBill($billQuery);
        }

        return $this->redirect($bill->getUrl());
    }

    /**
     * @param Query $query
     * @return \yii\web\Response
     */
    private function _genetateInvocesForBill(Query $query)
    {
        $from = (new \DateTimeImmutable())->setTime(0, 0, 0)->modify('first day of this month');
        $to = $from->modify('last day of this month');

        $query->andWhere([
            'between',
            'b.bill_date',
            $from->format(DateTimeZoneHelper::DATE_FORMAT),
            $to->format(DateTimeZoneHelper::DATE_FORMAT)
        ]);

        /** @var Bill $bill */
        foreach ($query->each() as $bill) {
            $bill->generateInvoices();
        }

        return $this->redirect(['/bill/publish/index']);
    }

    public function actionInvoiceReversal($bill_no)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            Yii::$app->session->addFlash('error', 'Счет не найден');
        }

        Bill::dao()->invoiceReversal($bill);

        return $this->redirect($bill->getUrl());
    }



}