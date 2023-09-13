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
        if (Yii::$app->user->can('sorm.read') || Yii::$app->user->can('sorm.edit')) {
            $nav->addBlock(
                NavigationBlock::create()
                    ->setId('sorm_b2c')
                    ->setTitle('СОРМ. B2C.')
                    ->addItem('Номера', ['/sorm/numbers/numbers-b2c'], ['sorm.edit'])
//                    ->addItem('Клиенты', ['/sorm/old/sorm-clients'], ['sorm.edit'])
                    ->addItem('Адреса. Клиенты. Физ.', ['/sorm/clients/person-b2c'], ['sorm.edit'])
                    ->addItem('Адреса. Клиенты. Юр./ИП', ['/sorm/clients/legal-b2c'], ['sorm.edit'])
            );

            $nav->addBlock(
                NavigationBlock::create()
                    ->setId('sorm')
                    ->setTitle('СОРМ. B2B/ОТТ.')
                    ->addItem('Номера', ['/sorm/numbers/numbers'], ['sorm.edit'])
//                    ->addItem('Клиенты', ['/sorm/old/sorm-clients'], ['sorm.edit'])
                    ->addItem('Адреса. Клиенты. Физ.', ['/sorm/clients/person'], ['sorm.edit'])
                    ->addItem('Адреса. Клиенты. Юр./ИП', ['/sorm/clients/legal'], ['sorm.edit'])
            );
        }
    }
}
