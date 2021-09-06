<?php

namespace app\controllers\bill;

use app\models\Bill;
use app\models\EventQueue;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\web\Response;


class BillController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => [
                    'set-invoice2-date-as-invoice1',
                    'mass-invoices',
                    'mass-invoices-status',
                ],
                'roles' => ['newaccounts_bills.edit'],
            ],
            [
                'allow' => false,
            ],
        ];
        return $behaviors;
    }

    public function actionSetInvoice2DateAsInvoice1($billId, $value)
    {
        $bill = $this->getBillOr404($billId);
        $bill->inv2to1 = $value;
        $bill->save();

        return $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);

    }

    public function actionMassInvoices($eventId = 0)
    {
        if (!$eventId) {
            $event = EventQueue::find()->where(['event' => EventQueue::INVOICE_MASS_CREATE])
                ->andWhere(['NOT', ['status' => [EventQueue::STATUS_OK]]])
                ->one();

            if (!$event) {
                $event = EventQueue::go(EventQueue::INVOICE_MASS_CREATE);
            }

            if (!$event) {
                \Yii::$app->session->addFlash('error', 'Задача не найдена');
                return $this->redirect('/bill/publish/index');
            }

            return $this->redirect('/bill/bill/mass-invoices?eventId='.$event->id);
        } else {
            $event = EventQueue::findOne(['id' => $eventId, 'event' => EventQueue::INVOICE_MASS_CREATE]);

            if (!$event) {
                \Yii::$app->session->addFlash('error', 'Задача не найдена');
                return $this->redirect('/bill/publish/index');
            }
        }


        $d = explode(PHP_EOL, trim($event->log_error));

        $countAll = 10000;
        $count = 0;
        $error = '';

        if (strpos($d[0], 'Count all: ') !== false) {
            $progressStyle = 'info';
            $countAll = str_replace('Count all: ', '', $d[0]);
            if (strpos($d[count($d)-1], 'count: ') !== false) {
                $count = str_replace('count: ', '', $d[count($d) - 1]);
            } else {
                $progressStyle = 'error';
                $error = $d[count($d)-1];
            }
        } else {
            $progressStyle = 'error';
            $error = $d[0];
        }

        if ($event->status == EventQueue::STATUS_OK) {
            $count = $countAll;
            $progressStyle = 'success';
            $error = '';
        }


        return $this->render('mass-invoices', [
            'eventId' => $event->id,
            'countAll' => $countAll,
            'count' => $count,
            'progressStyle' => $progressStyle,
            'progressValue' => round(($count/($countAll/100))),
            'error' => $error,
        ]);
    }
}