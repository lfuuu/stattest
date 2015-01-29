<?php
namespace app\commands;

use app\classes\bill\ClientAccountBiller;
use app\classes\enum\TicketStatusEnum;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use app\models\support\Ticket;
use app\models\Transaction;
use app\models\Trouble;
use yii\console\Controller;

class DevController extends Controller
{
    public function actionZzz()
    {
        $count = 1000;
        $offset = 0;
        while ($count >= 1000) {
            echo "Loading...\n";
            $payments = Payment::find()->limit(1000)->offset($offset)->orderBy('id')->all();

            $count = count($payments);

            foreach ($payments as $payment) {
                $offset++;
                echo "$offset/$count\n";
                Transaction::dao()->updateByPayment($payment);
            }
        }
    }

    public function actionYyy()
    {
        $clientAccounts =
            ClientAccount::find()
//                ->andWhere('client != "" and status NOT IN ("closed","deny","tech_deny") ')
                ->andWhere('client != "" and status = "work" ')
                ->andWhere(['id' => 4550])
                ->orderBy('id')
                ->all();

        $n = 1;
        foreach ($clientAccounts as $clientAccount) {

            echo $n . "\t\t\t" . $clientAccount->id . "\n";
            $n++;

            $biller = ClientAccountBiller::create($clientAccount, new \DateTime('2015-02-05 23:59:59'));
            $biller->createTransactions();
            $biller->saveTransactions();

//            $biller->createAndSaveBill();

/*
            foreach ($biller->getErrors() as $error) {
                $exception = $error['exception'];
                echo $exception->getMessage() . "\n";
                echo $exception->getFile() . '[' . $exception->getLine() . "]\n";
                echo $exception->getTraceAsString();
            }
*/
        }

    }
}
