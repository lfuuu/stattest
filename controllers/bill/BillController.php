<?php

namespace app\controllers\bill;

use app\models\EventQueue;
use app\classes\BaseController;
use yii\helpers\Url;


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

    public function actionMassInvoices($eventId = 0, $doCreate = 0)
    {
        $event = null;

        if (!$eventId) {
            $event = EventQueue::find()->where(['event' => EventQueue::INVOICE_MASS_CREATE])
                ->andWhere(['NOT', ['status' => [EventQueue::STATUS_OK]]])
                ->one();

            if ($event) {
                return $this->redirect(Url::to(['/bill/bill/mass-invoices', 'eventId' => $event->id]));
            }
        }

        if ($doCreate) {
            if (!$event) {
                $event = EventQueue::go(EventQueue::INVOICE_MASS_CREATE);
            }
            return $this->redirect(Url::to(['/bill/bill/mass-invoices', 'eventId' => $event->id]));
        }elseif (!$event) {
            $event = EventQueue::find()->where(['event' => EventQueue::INVOICE_MASS_CREATE])->orderBy(['id' => SORT_DESC])->one();
            if ($event && !$eventId) {
                return $this->redirect(Url::to(['/bill/bill/mass-invoices', 'eventId' => $event->id]));
            }
        }

        $countAll = 0;
        $count = 0;
        $error = '';

        if ($event) {
            $d = explode(PHP_EOL, trim($event->log_error));

            if (strpos($d[0], 'Count all: ') !== false) {
                $progressStyle = 'info';
                $countAll = str_replace('Count all: ', '', $d[0]);
                if (strpos($d[count($d) - 1], 'count: ') !== false) {
                    $count = str_replace('count: ', '', $d[count($d) - 1]);
                } else {
                    $progressStyle = 'error';
                    $error = $d[count($d) - 1];
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
        }


        return $this->render('mass-invoices', [
            'event' => $event,
            'countAll' => $countAll,
            'count' => $count,
            'progressStyle' => $progressStyle,
            'progressValue' => round(($count/($countAll/100))),
            'error' => $error,
        ]);
    }
}