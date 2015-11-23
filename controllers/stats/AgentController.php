<?php
namespace app\controllers\stats;

use app\classes\stats\AgentReport;
use app\classes\stats\PhoneSales;
use app\models\Business;
use app\models\ClientContract;
use app\models\ClientContragent;
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
        $partnerId = Yii::$app->request->get('partner_contract_id', 0);
        list($dateFrom, $dateTo) = explode(' - ', Yii::$app->request->get('date', 0));

        $partnerList = ArrayHelper::map(ClientContract::find()
            ->andWhere(['business_id' => Business::PARTNER])
            ->innerJoin(ClientContragent::tableName(), ClientContragent::tableName() . '.id = contragent_id')
            ->select([ClientContract::tableName() . '.id', ClientContragent::tableName() . '.name'])
            ->createCommand()
            ->queryAll(\PDO::FETCH_ASSOC), 'id', 'name');

        $partner = ClientContract::findOne($partnerId);
        $data = [];
        if ($partner) {
            $data = AgentReport::run($partnerId, $dateFrom, $dateTo);
        }

        if (Yii::$app->request->get('exportToCSV')) {
            $this->exportToCSV($data);
            Yii::$app->end();
        } else {
            return $this->render('report', [
                'data' => $data,
                'partnerList' => $partnerList,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'partner' => $partner
            ]);
        }
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