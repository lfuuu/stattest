<?php
namespace app\commands;

use Yii;
use DateTime;
use app\classes\bill\ClientAccountBiller;
use app\models\ClientAccount;
use yii\console\Controller;

class BillerController extends Controller
{

    /**
     * @return int
     */
    public function actionTariffication()
    {
        define('MONTHLY_BILLING', 1);

        Yii::info("Запущен тарификатор");

        $partSize = 500;
        $date = new DateTime();
        //$date->modify('+1 month');
        try {
            $count = $partSize;
            $offset = 0;
            while ($count >= $partSize) {
                $clientAccounts =
                    ClientAccount::find()
                        ->andWhere(['NOT IN', 'status', ['closed', 'deny', 'tech_deny', 'trash', 'once']])
                        ->limit($partSize)
                        ->offset($offset)
                        ->orderBy('id')
                        ->all();

                foreach ($clientAccounts as $clientAccount) {
                    $offset++;
                    $this->tarifficateClientAccount($clientAccount, $date, $offset);
                }

                $count = count($clientAccounts);
            }


        } catch (\Exception $e) {
            Yii::error('ОШИБКА ТАРИФИКАТОРА');
            Yii::error($e);
            return Controller::EXIT_CODE_ERROR;
        }

        Yii::info("Тарификатор закончил работу");
    }

    /**
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @param int $position
     * @return int
     */
    private function tarifficateClientAccount(ClientAccount $clientAccount, DateTime $date, $position)
    {
        Yii::info("Тарификатор. $position. Лицевой счет: " . $clientAccount->id);

        try {

            ClientAccountBiller::create($clientAccount, $date, $onlyConnecting = false, $connecting = false, $periodical = true, $resource = false)
                ->process();

            $resourceDate = clone $date;
            $resourceDate->modify('-1 day');

            ClientAccountBiller::create($clientAccount, $resourceDate, $onlyConnecting = false, $connecting = false, $periodical = false, $resource = true)
                ->process();

        } catch (\Exception $e) {
            Yii::error('ОШИБКА ТАРИФИКАТОРА. Лицевой счет: ' . $clientAccount->id);
            Yii::error($e);
            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }
}
