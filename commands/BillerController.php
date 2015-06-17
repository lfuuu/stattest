<?php
namespace app\commands;

use Yii;
use DateTime;
use app\classes\bill\ClientAccountBiller;
use app\models\ClientAccount;
use yii\console\Controller;

class BillerController extends Controller
{

    public function actionTariffication()
    {
        Yii::info("Запущен тариффикатор");

        $partSize = 500;
        $date = new DateTime();
        //$date->modify('+1 month');
        try {
            $count = $partSize;
            $offset = 0;
            while ($count >= $partSize) {
                $clientAccounts =
                    ClientAccount::find()
                        ->andWhere('status NOT IN ("closed","deny","tech_deny", "trash", "once")')
                        ->limit($partSize)->offset($offset)
                        ->orderBy('id')
                        ->all();

                foreach ($clientAccounts as $clientAccount) {
                    $offset++;
                    $this->tarifficateClientAccount($clientAccount, $date, $offset);
                }

                $count = count($clientAccounts);
            }


        } catch (\Exception $e) {
            Yii::error('ОШИБКА ТАРИФФИКАТОРА');
            Yii::error($e);
            return 1;
        }

        Yii::info("Тариффикатор закончил работу");
    }

    private function tarifficateClientAccount(ClientAccount $clientAccount, DateTime $date, $position)
    {
        Yii::info("Тариффикатор. $position. Лицевой счет: " . $clientAccount->id);

        try {

            ClientAccountBiller::create($clientAccount, $date, $connecting = false, $periodical = true, $resource = false)
                ->process();

            $resourceDate = clone $date;
            $resourceDate->modify('-1 day');

            ClientAccountBiller::create($clientAccount, $resourceDate, $connecting = false, $periodical = false, $resource = true)
                ->process();

        } catch (\Exception $e) {
            Yii::error('ОШИБКА ТАРИФФИКАТОРА. Лицевой счет: ' . $clientAccount->id);
            Yii::error($e);
        }
    }
}
