<?php
/**
 * Счет-фактура
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\ClientAccount;
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
     * @param null|string $renderMode
     * @param null|string $langCode
     * @return string
     * @throws InvalidParamException
     */
    public function actionGet($billId, $renderMode = null, $langCode = null, $isShow = false)
    {
        /** @var Bill $bill */
        if (!($bill = Bill::findOne(['id' => $billId]))) {
            throw new InvalidParamException;
        }

        $clientAccount = $this->_checkClientAccount($bill->client_account_id);

        $invoice = (new InvoiceLight($clientAccount))
            ->setBill($bill);

        if (!is_null($langCode)) {
            $invoice->setLanguage($langCode);
        }

        $bilDate = new \DateTime($bill->date);

        switch ($renderMode) {
            case 'pdf': {
                $pdfContent = $this->renderAsPDF(
                    'print',
                    ['invoiceContent' => $invoice->render(),],
                    [
                        'cssFile' => '@web/css/invoice/invoice.css',
                    ]
                );

                if (!$isShow) {
                    \Yii::$app->response->sendContentAsFile($pdfContent, $clientAccount->id . '-' . $bilDate->format('Ym') . '-' . $bill->id . '.pdf');
                } else {
                    Yii::$app->response->headers->setDefault('Content-Type', 'application/pdf');
                    Yii::$app->response->format = Response::FORMAT_RAW;
                    Yii::$app->response->content = $pdfContent;

                }
                \Yii::$app->end();
            }

            case 'mhtml': {
                return $this->renderAsMHTML('print', [
                    'invoiceContent' => $invoice->render(),
                    'fileName' => $clientAccount->id . '-' . $bilDate->format('Ym') . '-' . $bill->id . '.doc'
                    ]);
            }
        }

        $this->layout = 'empty';
        return $this->render(
            'print',
            ['invoiceContent' => $invoice->render(),]
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