<?php
namespace app\commands;

use app\models\ClientContract;
use app\models\HistoryVersion;
use Yii;
use yii\console\Controller;

class VersionController extends Controller
{

    public function actionExportCurrentVersions()
    {
        $date = date('Y-m-d');
        Yii::info('Проверка наличия версий на '.$date);
        /** @var HistoryVersion[] $versions */
        $versions = HistoryVersion::find()->where(['date' => $date])->all();
        foreach($versions as $version){
            $res = $version->exportCurrentVersion();
            $msg = $version->model.'('.$version->model_id.') '.($res?'successfully saved':'not saved');
            Yii::info($msg);
        }
        Yii::info('Проверка закончена. Всего: '.count($versions));
    }

}
