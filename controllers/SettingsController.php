<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\Param;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;


class SettingsController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['dictionary.read'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index',[
            'isLogOn' => Param::getParam(Param::IS_LOG_AAA, false),
        ]);
    }

    public function actionSetLog($isOn)
    {
        $isOn = (int)(bool)$isOn;

        $filePath = Yii::getAlias('@runtime/log_aaa.config.php');

        $content = '<?php return ' . ($isOn ? 'true' : 'false').';';

        if (!file_put_contents($filePath, $content)) {
            throw new InvalidConfigException('unable to write to file: ' . $filePath);
        }

        Param::setParam(Param::IS_LOG_AAA, $isOn);

        return $this->redirect(['/settings/']);
    }

}
