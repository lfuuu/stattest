<?php
/**
 * Счет-фактура
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\Bill as sBill;
use app\models\ClientAccount;
use app\models\Invoice;
use app\modules\uu\models\Bill;
use app\modules\uu\models_light\InvoiceLight;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Response;

class InvoiceController extends BaseController
{

    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     *
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
                        'actions' => ['view', 'get',],
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param null|int $clientAccountId
     * @param null|string $month
     * @param null|string $langCode
     * @return bool|string
     */
    public function actionView($clientAccountId = null, $month = null, $langCode = null)
    {
        // Вернуть текущего клиента, если он есть
        !$clientAccountId && $clientAccountId = $this->_getCurrentClientAccountId();

        if ($month) {
            $date = $month;
        } else {
            $date = (new \DateTime)
                ->modify('first day of previous month')
                ->format('Y-m');
        }

        $clientAccount = $this->_checkClientAccount($clientAccountId);
        $invoice = new InvoiceLight($clientAccount);

        if (is_null($langCode)) {
            $langCode = $clientAccount->contract->contragent->lang_code;
        }


        if ($date) {
            $invoice->setDate($date);
        }

        return $this->render(
            'view',
            [
                'bills' => $invoice->getBills(),
                'langCode' => $langCode,
                'date' => $date,
            ]
        );
    }

    /**
     * @param int $billId
     * @param null $billNo
     * @param int $typeId
     * @param null|string $renderMode
     * @param null|string $langCode
     * @param bool $isShow
     * @param integer $invoiceId
     * @return string
     */
    public function actionGet($billId = null, $billNo = null, $typeId = 1, $renderMode = null, $langCode = null, $isShow = false, $invoiceId = null)
    {
        $invoice = $clientAccountId = null;

        /** @var Bill $bill */
        if ($billId) {
            if ($bill = Bill::findOne(['id' => $billId])) {
                $clientAccountId = $bill->client_account_id;
            } else {
                throw new InvalidParamException;
            }
        }

        if (!$bill && $billNo) {
            /** @var sBill $bill */
            if (!($bill = sBill::findOne(['bill_no' => $billNo]))) {
                throw new InvalidParamException;
            }

            $invoice = Invoice::findOne(
                $invoiceId ? ['id' => $invoiceId] : ['bill_no' => $bill->bill_no, 'type_id' => $typeId]
            );

            if (!$invoice) {
                return $this->renderPartial("//wrapper_html", ['content' => 'Документ не найден']);
            }

            $clientAccountId = $bill->client_id;
        }

        if (!$bill || !$clientAccountId) {
            throw new InvalidParamException;
        }

        $clientAccount = $this->_checkClientAccount($clientAccountId);

        $invoiceDocument = (new InvoiceLight($clientAccount))
            ->setBill($bill);

        if ($invoice) {
            $invoiceDocument->setInvoice($invoice);
        }

        if (!is_null($langCode)) {
            $invoiceDocument->setLanguage($langCode);
        }

        $bilDate = new \DateTime($bill->date);

        switch ($renderMode) {
            case 'pdf': {
                $pdfContent = $this->renderAsPDF(
                    'print',
                    ['invoiceContent' => $invoiceDocument->render(),],
                    [
                        'cssFile' => '@web/css/invoice/invoice.css',
                    ]
                );

                $attachmentName = $clientAccount->id . '-' . $invoice->number. '.pdf';

                if (!$isShow) {
                    \Yii::$app->response->sendContentAsFile($pdfContent, $attachmentName);
                } else {
//                    Yii::$app->response->headers->setDefault('Content-Type', 'application/pdf');
                    Yii::$app->response->format = Response::FORMAT_RAW;
                    Yii::$app->response->content = $pdfContent;
                    Yii::$app->response->setDownloadHeaders($attachmentName, 'application/pdf', true);

                }
                \Yii::$app->end();
            }

            case 'mhtml': {
                return $this->renderAsMHTML('print', [
                    'invoiceContent' => $invoiceDocument->render(),
                    'fileName' => $clientAccount->id . '-' . $bilDate->format('Ym') . '-' . $bill->id . '.doc'
                ]);
            }
        }

        $this->layout = 'empty';
        return $this->render(
            'print',
            ['invoiceContent' => $invoiceDocument->render(),]
        );
    }

    /**
     * @param int $clientAccountId
     * @return ClientAccount|\yii\web\Response
     */
    private function _checkClientAccount($clientAccountId)
    {
        /** @var ClientAccount $clientAccount */
        if (($clientAccount = ClientAccount::findOne($clientAccountId)) === null) {
            Yii::$app->session->setFlash('error',
                Yii::t(
                    'tariff', 'You should {a_start}select a client first{a_finish}',
                    ['a_start' => '<a href="/">', 'a_finish' => '</a>']
                )
            );
            $this->redirect('/');
            Yii::$app->end();
        }

        return $clientAccount;
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 25887428, 'message' => 'Счёт-фактура'];
    }
}