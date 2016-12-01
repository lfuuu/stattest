<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\helpers\DateTimeZoneHelper;
use app\models\HistoryVersion;

class VersionController extends Controller
{

    public function actionExportCurrentVersions()
    {
        $date = date(DateTimeZoneHelper::DATE_FORMAT);
        Yii::info('Проверка наличия версий на ' . $date);
        /** @var HistoryVersion[] $versions */
        $versions = HistoryVersion::find()->where(['date' => $date])->all();
        foreach ($versions as $version) {
            $res = $version->exportCurrentVersion();
            $msg = $version->model . '(' . $version->model_id . ') ' . ($res ? 'successfully saved' : 'not saved');
            Yii::info($msg);
            echo PHP_EOL . $msg;
        }
        Yii::info('Проверка закончена. Всего: ' . count($versions));
    }

}
