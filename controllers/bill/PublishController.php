<?php

namespace app\controllers\bill;

use app\classes\Assert;
use app\classes\helpers\DependecyHelper;
use app\classes\Utils;
use app\dao\BillDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Country;
use app\models\Invoice;
use app\models\InvoiceLine;
use app\models\Organization;
use app\models\Param;
use app\models\Region;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\filters\AccessControl;
use app\classes\BaseController;
use yii\web\NotFoundHttpException;

class PublishController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'make-invoice', 'make-ab-invoice',
                            'invoice-reversal', 'invoice-draft', 'invoice-delete',
                            'invoice-register', 'invoice-storno', 'invoice-edit'
                        ],
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
     * @return \yii\web\Response
     */
    public function actionInvoices()
    {
        $organizationId = \Yii::$app->request->post('organizationId');

        if (!$organizationId) {
            throw new InvalidArgumentException('organizationId нет');
        }

        $query = Bill::find()
            ->from(['b' => Bill::tableName()])
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = b.client_id')
            ->innerJoin(['cc' => ClientContract::tableName()], 'cc.id = c.contract_id')
            ->where([
                'cc.organization_id' => $organizationId,
            ]);

        $this->_filterQueryByThisMonth($query);
        $this->_genetateInvocesForBill($query);

        return $this->redirect(['/bill/publish/index', 'organizationId' => $organizationId]);
    }

    /**
     * Генерация с/ф для всех в этом месяце
     */
    public function actionInvoicesForAll()
    {
        $query = Bill::find()
            ->alias('b');

        $this->_filterQueryByThisMonth($query);
        $this->_genetateInvocesForBill($query);

        return $this->redirect(['/bill/publish/index']);
    }

    private function _filterQueryByThisMonth($query)
    {
        $from = (new \DateTimeImmutable())->setTime(0, 0, 0)->modify('first day of this month');
        $to = $from->modify('last day of this month');

        $query->andWhere([
            'between',
            'b.bill_date',
            $from->format(DateTimeZoneHelper::DATE_FORMAT),
            $to->format(DateTimeZoneHelper::DATE_FORMAT)
        ]);

    }

    /**
     * Создание с/ф у счета
     *
     * @param string $bill_no
     * @return \yii\web\Response
     */
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
            exit();
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

        /** @var Bill $bill */
        foreach ($query->each() as $bill) {
            $bill->generateInvoices();
        }

        return $this->redirect(['/bill/publish/index']);
    }

    /**
     * Сторнирование с/ф
     *
     * @param string $bill_no
     * @return \yii\web\Response
     */
    public function actionInvoiceReversal($bill_no)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            Yii::$app->session->addFlash('error', 'Счет не найден');
            exit();
        }

        Bill::dao()->invoiceReversal($bill);

        return $this->redirect($bill->getUrl());
    }

    /**
     * Создание авансовой с/ф
     *
     * @param string $bill_no
     * @return \yii\web\Response
     */
    public function actionMakeAbInvoice($bill_no)
    {
        /** @var Bill $bill */
        $bill = Bill::find()
            ->alias('b')
            ->where(['bill_no' => $bill_no])
            ->one();

        if (!$bill) {
            Yii::$app->session->addFlash('error', 'Счет не найден');
            exit();
        } else {
            $bill->generateAbInvoice();
        }

        return $this->redirect($bill->getUrl());
    }

    /**
     * Сторнирование авансовой с/ф
     *
     * @param string $bill_no
     * @return \yii\web\Response
     */
    public function actionInvoiceAbReversal($bill_no)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            Yii::$app->session->addFlash('error', 'Счет не найден');
            exit();
        }

        Bill::dao()->invoiceReversal($bill, true);

        return $this->redirect($bill->getUrl());
    }

    public function actionCache()
    {
        $tags = \Yii::$app->request->post('tags');
        if (in_array(DependecyHelper::ALL, $tags)) {
            \Yii::$app->cache->flush();
        } else {
            TagDependency::invalidate(Yii::$app->cache, $tags);
        }
        return $this->redirect(['/bill/publish/index']);
    }

    /**
     * Публикация счета
     *
     * @param string $bill_no
     * @return \yii\web\Response
     */
    public function actionBill($bill_no)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            throw new \InvalidArgumentException('Счет не найден ' . $bill_no);
        }
        $bill->is_show_in_lk = 1;
        if (!$bill->save()) {
            throw new ModelValidationException($bill);
        }

        return $this->redirect($bill->getUrl());
    }

    public function actionInvoiceDraft($bill_no, $type_id)
    {
        $typeId = $type_id;

        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            throw new \InvalidArgumentException('Счет не найден ' . $bill_no);
        }

        //@TODO
        // можно ли создать draft

        $lines = $bill->getLinesByTypeId($typeId);

        if (!$lines) {
            // не данных по этому типу документов
            return $this->redirect($bill->getUrl());
        }

        $sumData = BillLine::getSumsLines($lines);
        $invoiceDate = Invoice::getDate($bill, $typeId);


        $invoice = new Invoice();
        $invoice->isSetDraft = true;

        $invoice->bill_no = $bill->bill_no;
        $invoice->type_id = $typeId;

        $invoice->date = $invoiceDate->format(DateTimeZoneHelper::DATE_FORMAT);
        $invoice->is_reversal = 0;

        $invoice->sum = $sumData['sum'];
        $invoice->sum_tax = $sumData['sum_tax'];
        $invoice->sum_without_tax = $sumData['sum_without_tax'];

        if (!$invoice->save()) {
            throw new ModelValidationException($invoice);
        }

        return $this->redirect($bill->getUrl());
    }

    public function actionInvoiceDelete($bill_no, $type_id)
    {
        $typeId = $type_id;

        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            throw new \InvalidArgumentException('Счет не найден ' . $bill_no);
        }

        //@TODO
        // можно ли удалить draft

        $invoice = Invoice::findOne([
            'bill_no' => $bill->bill_no,
            'idx' => null,
            'type_id' => $typeId
        ]);

        if (!$invoice) {
            throw new NotFoundHttpException('Invoice not found');
        }

        if (!$invoice->delete()) {
            throw new ModelValidationException($invoice);
        }

        return $this->redirect($bill->getUrl());
    }

    public function actionInvoiceRegister($bill_no, $type_id)
    {
        $typeId = $type_id;

        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            throw new \InvalidArgumentException('Счет не найден ' . $bill_no);
        }

        //@TODO
        // можно ли удалить draft

        $invoice = Invoice::findOne([
            'bill_no' => $bill->bill_no,
            'idx' => null,
            'type_id' => $typeId
        ]);

        if (!$invoice) {
            throw new NotFoundHttpException('Invoice not found');
        }

        $invoice->isSetDraft = false; // already set false

        if (!$invoice->save()) {
            throw new ModelValidationException($invoice);
        }

        return $this->redirect($bill->getUrl());
    }

    public function actionInvoiceStorno($bill_no, $type_id, $id)
    {
        $typeId = $type_id;

        $bill = Bill::findOne(['bill_no' => $bill_no]);

        if (!$bill) {
            throw new \InvalidArgumentException('Счет не найден ' . $bill_no);
        }

        $invoice = Invoice::findOne([
            'bill_no' => $bill->bill_no,
            'id' => $id,
            'type_id' => $typeId,
            'is_reversal' => 0,
        ]);

        if (!$invoice) {
            throw new NotFoundHttpException('Invoice not found');
        }

        $isSetNumber = $bill->clientAccount->country_id != Country::RUSSIA;

        $revertInvoice = new Invoice();
        $revertInvoice->setAttributes($invoice->getAttributes(null, ['id', 'add_date', 'number', 'idx', 'reversal_date']), false);

        !$isSetNumber && $revertInvoice->isSetDraft = true;

        $revertInvoice->setReversal(true);

        return $this->redirect($bill->getUrl());
    }

    /**
     * Седактирование с/ф
     *
     * @param integer $invoice_id
     * @return string
     * @throws ModelValidationException
     */
    public function actionInvoiceEdit($invoice_id)
    {
        $invoice = Invoice::findOne(['id' => $invoice_id]);

        Assert::isObject($invoice);

        $lineAdd = new InvoiceLine();

        // сохранение
        if (\Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                // позиции счета
                $models = $invoice->lines;

                $delete = \Yii::$app->request->post('delete');

                Model::loadMultiple($models, \Yii::$app->request->post());

                foreach ($models as $idx => $model) {
                    if ($delete && in_array($idx, $delete)) {
                        if (!$model->delete()) {
                            throw new ModelValidationException($model);
                        }
                        continue;
                    }

                    if (!$model->save()) {
                        throw new ModelValidationException($model);
                    }
                }

                $lineAdd->setAttributes([
                    'invoice_id' => $invoice->id,
                ]);

                // Сохранение новой строки
                $lineAddData = \Yii::$app->request->post('InvoiceLineAdd');
                if ($lineAddData['item'] && $lineAdd->load($lineAddData, '')) {

                    $lineAdd->sort = ((int)InvoiceLine::find()->where(['invoice_id' => $invoice->id])->max('sort'))+1;
                    $lineAdd->setDates();

                    if (!$lineAdd->validate()) {
                        \Yii::$app->session->addFlash('error', implode("<br>", $lineAdd->getFirstErrors()));
                    } elseif (!$lineAdd->save()) {
                        throw new ModelValidationException($lineAdd);
                    } else {
                        // сохраненно. Сбрасываем модель.
                        $lineAdd = new InvoiceLine();
                    }
                }

                $invoice->refresh();
                $invoice->recalcSumCorrection();

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::$app->session->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('invoice_edit', [
            'invoice' => $invoice,
            'lineAdd' => $lineAdd
        ]);
    }

}