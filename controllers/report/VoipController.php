<?php
namespace app\controllers\report;

use app\classes\report\LostCalls;
use Yii;
use app\classes\BaseController;
use yii\data\SqlDataProvider;

class VoipController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['lost-calls'],
                'roles' => ['clients.read'],
            ],
        ];
        //return $behaviors;
    }

    public function actionLostCalls($mode = LostCalls::CALL_MODE_OUTCOMING, $date = false, $region = 99)
    {
        if(!$date)
            $date = date('Y-m-d');
        $count = LostCalls::getCount($date, $region, $mode);
        if(!$count) {
            LostCalls::prepare($date, $region, $mode);
            $count = LostCalls::getCount($date, $region, $mode);
        }

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT * FROM tmp_calls_raw WHERE DATE(connect_time) = :date AND region = :region AND mode = :mode',
            'params' => [
                ':date' => $date,
                ':region' => $region,
                ':mode' => $mode,
            ],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $this->render('grid', ['dataProvider' => $dataProvider, 'date' => $date, 'region' => $region, 'mode' => $mode]);
    }
}