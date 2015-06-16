<?php
namespace app\commands;

use Yii;
use yii\console\Controller;

class VersionController extends Controller
{

    public function actionExportCurrentVersions()
    {
        $date = date('Y-m-d');
        Yii::info('Проверка наличия версий на '.$date);
        echo 'Проверка наличия версий на '.$date;
        echo "\n";
        $versions =  \app\models\HistoryVersion::find()->where(['date' => $date])->all();
        foreach($versions as $version){
            $res = $version->exportCurrentVersion();
            $msg = $version->model.'('.$version->model_id.') '.($res?'successfully saved':'not saved');
            echo $msg;
            echo "\n";
            Yii::info($msg);
        }
        echo 'Проверка закончена. Всего: '.count($versions);
        echo "\n";
        Yii::info('Проверка закончена. Всего: '.count($versions));
    }

}
