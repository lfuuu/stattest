<?php

namespace app\modules\nnp2;

use app\classes\Connection;
use app\classes\helpers\ArrayHelper;
use app\classes\Navigation;
use app\classes\NavigationBlock;
use app\modules\nnp2\models\NumberRange;
use Yii;

/**
 * Национальные номерные планы 2.0
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\nnp2\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\nnp2\commands';
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
     * @param \Closure $callback
     * @return string Пустая строка - ok, непустая - текст ошибки
     * @throws \yii\db\Exception
     */
    public static function transaction($callback)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp2;
        $transaction = $dbPgNnp->beginTransaction();
        try {
            $callback();
            $transaction->commit();
            return '';
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            return sprintf('%s %s', $e->getMessage(), $e->getTraceAsString());
        }
    }

    /**
     * @param Navigation $navigation
     */
    public function getNavigation(Navigation $navigation)
    {
        $navigation->addBlock(
            NavigationBlock::create()
                ->setId('nnp2')
                ->setTitle('ННП 2.0')
                ->addItem('Диапазон номеров', ['/nnp2/number-range/'], ['nnp.read'])
                ->addItem('Операторы', ['/nnp2/operator/'], ['nnp.read'])
                ->addItem('Страны', ['/nnp/country/'], ['nnp.read'])
                ->addItem('Местоположения', ['/nnp2/geo-place/'], ['nnp.read'])
                ->addItem('Регионы', ['/nnp2/region/'], ['nnp.read'])
                ->addItem('Города', ['/nnp2/city/'], ['nnp.read'])
                ->addItem('Типы NDC', ['/nnp2/ndc-type/'], ['nnp.read'])
                ->addItem('Диапазон номеров (готовый)', ['/nnp2/range-short/'], ['nnp.read'])
                ->addItem('Импорт', ['/nnp/import/'], ['nnp.write'])
        );
    }

    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return strpos(NumberRange::getDb()->username, 'readonly') === false;
    }
}
