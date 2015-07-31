<?php
namespace app\controllers;

use app\models\pg\CallsRaw;
use \Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;


class ReportController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['clients.read'],
                    ],
                ],
            ],
        ];
    }

    public function actionCallsToFile()
    {

        $request = Yii::$app->request->post();

        $serverId = $request['f_server_id'] > 0 ? "AND r.server_id = '{$request['f_server_id']}'" : '';
        $operatorId = $request['f_operator_id'] > 0 ? "AND r.operator_id = '{$request['f_operator_id']}'" : '';
        $orig = $request['f_direction_out'] == 't' ? "AND r.orig = true" : "AND r.orig = false";

        $query = Yii::$app->dbPg->createCommand("
        SELECT
          r.connect_time,
          r.src_number,
          r.dst_number,
          r.billed_time
        FROM calls_raw.calls_raw r
        WHERE
          billed_time > 0 AND r.connect_time >= '{$request['date_from']}' AND r.connect_time <= '{$request['date_to']} 23:59:59' $serverId $orig $operatorId ORDER BY connect_time
        ");

        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename="'.iconv("utf-8", "windows-1251", "Отчет по звонкам").'.csv"');

        $rows = $query->queryAll();
        ob_start();
        foreach($rows as &$row) {
            echo implode(';', $row)."\n";
            unset($row);
        }
        echo iconv('utf-8', 'windows-1251', ob_get_clean());
        exit();
    }

}
