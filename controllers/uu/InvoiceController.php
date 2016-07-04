<?php
/**
 * Счет-фактура
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\models\ClientAccount;
use Yii;
use yii\filters\AccessControl;

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
    public function actionView($clientAccountId = null, $renderMode = null, $month = null)
    {
        // Вернуть текущего клиента, если он есть
        !$clientAccountId && $clientAccountId = $this->getCurrentClientAccountId();

        $accountEntries = [];

        if ($month) {
            $date = $month . '-01';
        } else {
            $date = (new \DateTime)
                ->modify('first day of previous month')
                ->format('Y-m-d');
        }

        if (($clientAccount = ClientAccount::findOne($clientAccountId)) === null) {
            Yii::$app->session->setFlash('error',
                Yii::t('tariff', 'You should {a_start}select a client first{a_finish}',
                    ['a_start' => '<a href="/">', 'a_finish' => '</a>']));
            $renderMode = null;
        } else {

            // Вернуть проводки клиента за предыдущий календарный месяц для счета-фактуры
            $accountEntryTableName = AccountEntry::tableName();
            $accountTariffTableName = AccountTariff::tableName();
            $accountEntries = AccountEntry::find()
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccount->id])
                ->orderBy([
                    'account_tariff_id' => SORT_ASC,
                    'type_id' => SORT_ASC,
                ])
                ->andWhere(['>', $accountEntryTableName . '.vat', 0])
                ->andWhere([$accountEntryTableName . '.date' => $date])
                ->all();
        }

        switch ($renderMode) {
            case 'pdf': {
                return $this->renderAsPDF('print', [
                    'clientAccount' => $clientAccount,
                    'accountEntries' => $accountEntries,
                    'date' => $date,
                    'modePDF' => true,
                ], [
                    'cssFile' => '@web/invoice.css',
                ]);
            }
            case 'mhtml': {
                return $this->renderAsMHTML('print', [
                    'clientAccount' => $clientAccount,
                    'accountEntries' => $accountEntries,
                    'date' => $date,
                    'inline_img' => false,
                ]);
            }
            case 'print': {
                $this->layout = 'empty';
                return $this->render('print', [
                    'clientAccount' => $clientAccount,
                    'accountEntries' => $accountEntries,
                    'date' => $date,
                    'modePDF' => false,
                ]);
            }
            default: {
                return $this->render('view', [
                    'clientAccount' => $clientAccount,
                    'accountEntries' => $accountEntries,
                    'date' => $date,
                ]);
            }
        }
    }
}