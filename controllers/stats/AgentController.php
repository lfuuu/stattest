<?php
namespace app\controllers\stats;

use app\classes\stats\AgentReport;
use app\classes\stats\PhoneSales;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\filter\ClientAccountAgentFilter;
use Yii;
use app\classes\BaseController;
use yii\helpers\ArrayHelper;

class AgentController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['report','test'],
                'roles' => ['clients.read'],
            ],
        ];
        return $behaviors;
    }

    public function actionReport()
    {
        $partners = ClientContract::find()
                ->andWhere(['business_id' => Business::PARTNER])
                ->innerJoin(ClientContragent::tableName(), ClientContragent::tableName() . '.id = contragent_id')
                ->orderBy(ClientContragent::tableName() . '.name')
                ->all();

        $partnerList = [];
        foreach($partners as $partner) {
            $account = $partner->accounts[0];
            $partnerList[$account->id] = $partner->contragent->name . ' (#' . $account->id . ')';
        }

        return $this->render('report', [
            'filterModel' => (new ClientAccountAgentFilter)->load(),
            'partnerList' => $partnerList,
        ]);
    }

    private function exportToCSV($data)
    {

        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename="AgentReport.csv"');

        ob_start();

        $amount = 0;
        $amountIsPayed = 0;
        $oncet = 0;
        $fee = 0;
        $excess = 0;
        echo ";;;;;;;Сумма вознаграждения;;;\n";
        echo "Наименование клиента;Дата регистрации клиента;Услуга;Тариф;Дата включения услуги;Сумма оказанных услуг;Сумма оплаченных услуг;Разовое;% от абонентской платы;% от превышения;\n";
        foreach ($data as $line) {
            $amount += $line['amount'];
            $amountIsPayed += $line['amountIsPayed'];
            $oncet += $line['once'];
            $fee += $line['fee'];
            $excess += $line['excess'];

            echo $line['name'];
            echo ';';
            echo '"'.$line['created'].'"';
            echo ';';
            echo $line['usage'] == 'voip' ? 'Телефония' : $line['usage'] == 'virtpbx' ? 'ВАТС' : '';
            echo ';';
            echo $line['tariffName'];
            echo ';';
            echo '"'.$line['activationDate'].'"';
            echo ';';
            echo number_format($line['amount'], 2);
            echo ';';
            echo number_format($line['amountIsPayed'], 2);
            echo ';';
            echo number_format($line['once'], 2);
            echo ';';
            echo number_format($line['fee'], 2);
            echo ';';
            echo number_format($line['excess'], 2);
            echo ';';
            echo "\n";
        }

        echo 'Итого;;;;;';
        echo number_format($amount, 2);
        echo ';';
        echo number_format($amountIsPayed, 2);
        echo ';';
        echo number_format($oncet, 2);
        echo ';';
        echo number_format($fee, 2);
        echo ';';
        echo number_format($excess, 2);
        echo ';';
        echo "\n";

        echo iconv('utf-8', 'windows-1251', ob_get_clean());
    }

}
