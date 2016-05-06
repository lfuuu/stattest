<?php
/**
 * Счет-фактура
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
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
    public function actionView($clientAccountId = null)
    {
        // Вернуть текущего клиента, если он есть
        !$clientAccountId && $clientAccountId = $this->getCurrentClientAccountId();

        if ($clientAccountId) {
            // Вернуть проводки клиента за предыдущий календарный месяц для счета-фактуры
            $date = (new \DateTime())
                ->modify('first day of previous month')
                ->format('Y-m-d');

            $accountEntryTableName = AccountEntry::tableName();
            $accountTariffTableName = AccountTariff::tableName();
            $accountEntries = AccountEntry::find()
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->orderBy([
                    'account_tariff_id' => SORT_ASC,
                    'type_id' => SORT_ASC,
                ])
                ->andWhere(['>', $accountEntryTableName . '.vat', 0])
                ->andWhere([$accountEntryTableName . '.date' => $date])
                ->all();
        } else {
            $accountEntries = [];
        }

        return $this->render('view', [
            'clientAccountId' => $clientAccountId,
            'accountEntries' => $accountEntries,
        ]);
    }
}