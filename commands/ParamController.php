<?php
namespace app\commands;

use app\models\Param;
use Yii;
use yii\console\Controller;
use app\helpers\DateTimeZoneHelper;
use app\models\HistoryVersion;

class ParamController extends Controller
{

    public function actionGet($param)
    {
        echo PHP_EOL . 'Param: ' . $param. ' is ';
        $value = Param::getParam($param, '???');

        if ($value === '???') {
            echo '<not set>';
        } else {
            echo var_export($value, true);
//            echo $value;
        }
        echo PHP_EOL;
    }

    public function actionSet($param, $value)
    {
        if (!in_array($param, Param::all)) {
            throw new \InvalidArgumentException('Unknown parameter');
        }

        Param::setParam($param, $value, true);

        $this->actionGet($param);
    }
}
