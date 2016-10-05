<?php
/**
 * Счет-фактура
 */

namespace app\controllers\uu;

use app\classes\Smarty;
use app\forms\templates\uu\InvoiceForm;
use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\models\ClientAccount;
use app\models\Language;
use app\models\light_models\uu\InvoiceLight;

class InvoiceController extends BaseController
{
    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     * @return []
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionView($clientAccountId = null, $renderMode = null, $month = null, $langCode = null)
    {
        // Вернуть текущего клиента, если он есть
        !$clientAccountId && $clientAccountId = $this->getCurrentClientAccountId();

        if ($month) {
            $date = $month . '-01';
        } else {
            $date = (new \DateTime)
                ->modify('first day of previous month')
                ->format(DateTimeZoneHelper::DATE_FORMAT);
        }

        /** @var ClientAccount $clientAccount */
        if (($clientAccount = ClientAccount::findOne($clientAccountId)) === null) {
            Yii::$app->session->setFlash('error', Yii::t('tariff', 'You should {a_start}select a client first{a_finish}', ['a_start' => '<a href="/">', 'a_finish' => '</a>']));
            return $this->redirect('/');
        }

        $invoice = new InvoiceLight($clientAccount);

        if (!is_null($langCode)) {
            $invoice->setLanguage($langCode);
        }

        if ($date) {
            $invoice->setDate($date);
        }

        $invoiceData = $invoice->getProperties();

        switch ($renderMode) {
            case 'pdf': {
                return $this->renderAsPDF('print', [
                    'invoiceContent' => $invoice->render(),
                ], [
                    'cssFile' => '@web/css/invoice/invoice.css',
                ]);
            }
            case 'mhtml': {
                return $this->renderAsMHTML('print', [
                    'invoiceContent' => $invoice->render(),
                ]);
            }
            case 'print': {
                $this->layout = 'empty';
                return $this->render('print', [
                    'invoiceContent' => $invoice->render(),
                ]);
            }
            default: {
                return $this->render('view', [
                    'invoice' => $invoiceData,
                    'langCode' => $langCode,
                    'date' => $date,
                ]);
            }
        }
    }
}