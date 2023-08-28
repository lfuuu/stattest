<?php

namespace app\modules\sorm;

use app\classes\helpers\ArrayHelper;
use app\classes\NavigationBlock;
use Yii;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\sorm\controllers';

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\sorm\commands';
        }

        // подключить конфиги
//        $params = require __DIR__ . '/config/params.php';
//
//        $localConfigFileName = __DIR__ . '/config/params.local.php';
//        if (file_exists($localConfigFileName)) {
//            $params = ArrayHelper::merge($params, require $localConfigFileName);
//        }
//
//        Yii::configure($this, $params);
    }

    public function getNavigation($nav)
    {
            $nav->addBlock(
                NavigationBlock::create()
                    ->setId('sorm')
                    ->setTitle('СОРМ')
                    ->addItem('Клиенты. Адреса. Физ.', ['/sorm/clients/person'])
                    ->addItem('Клиенты. Адреса. Юр./ИП', ['/sorm/clients/legal'])
            );
    }
}
