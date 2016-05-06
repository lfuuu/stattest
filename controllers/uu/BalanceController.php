<?php
/**
 * Бухгалтерский баланс
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use Yii;
use yii\filters\AccessControl;

class BalanceController extends BaseController
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
            // Вернуть проводки клиента
            $accountEntryTableName = AccountEntry::tableName();
            $accountTariffTableName = AccountTariff::tableName();
            $accountEntries = AccountEntry::find()
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->andWhere(['>', $accountEntryTableName . '.price', 0])
                ->orderBy([
                    'date' => SORT_ASC,
                    'account_tariff_id' => SORT_ASC,
                    'type_id' => SORT_ASC,
                ])
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