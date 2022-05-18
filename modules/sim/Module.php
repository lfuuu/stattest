<?php

namespace app\modules\sim;

use app\classes\Navigation;
use app\classes\NavigationBlock;
use app\classes\helpers\ArrayHelper;
use Yii;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\sim\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\sim\commands';
        }

        // подключить конфиги
        $params = require __DIR__ . '/config/params.php';

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            $params = ArrayHelper::merge($params, require $localConfigFileName);
        }

        Yii::configure($this, $params);
    }

    /**
     * @param Navigation $navigation
     */
    public function getNavigation(Navigation $navigation)
    {
        $navigation->addBlock(
            NavigationBlock::create()
                ->setId('sim')
                ->setTitle('SIM-карты')
                ->addItem('SIM-карты', ['/sim/card/'], ['sim.read'])
                ->addItem('Статусы SIM-карт', ['/sim/card-status/'], ['sim.read'])
                ->addItem('Статусы IMSI', ['/sim/imsi-status/'], ['sim.read'])
//                ->addItem('MVNO-партнеры IMSI', ['/sim/imsi-partner/'], ['sim.read'])
                ->addItem('Реестр SIM-карт', ['/sim/registry'], ['sim.read'])
                ->addItem('Портирование отчёт', ['/sim/porting/'], ['sim.read'])
                ->addItem('Импорт данных из БДПН', ['/sim/porting/import/'], ['sim.write'])
        );
    }
}
