<?php

namespace app\modules\nnp;

use app\classes\Connection;
use app\classes\helpers\ArrayHelper;
use BadMethodCallException;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Национальные номерные планы
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10977965
 *
 * Installation:
 *
 *      в postresql
 *          CREATE SCHEMA nnp
 *      cp config/db_pg_nnp.local.tpl.php config/db_pg_nnp.local.php
 *          указать нужный хост, порт, БД, юзера, пароль
 *
 * Миграции
 *      ./yii migrate --migrationPath='@app/modules/nnp/migrations'
 *
 * Импорт
 *      ./yii nnp/import
 *      ./yii nnp/operator
 *      ./yii nnp/region
 *      ./yii nnp/city
 *
 * Чтобы вынести модуль в другой репозиторий:
 *      из config/web.php и config/console.php скопировать код про "dbPgNnp"
 *      config/db_pg_nnp*
 *      classes/grid/column/universal/   (Integer, String, Country, YesNo)
 */
class Module extends \yii\base\Module
{
    const EVENT_LINKER = 'nnp_linker';
    const EVENT_IMPORT = 'nnp_import';
    const EVENT_FILTER_TO_PREFIX = 'nnp_filter_to_prefix';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\nnp\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\nnp\commands';
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
        $dbPgNnp = Yii::$app->dbPgNnp;
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
     * @param int $destinationId
     * @return int[]
     * @throws \BadMethodCallException
     * @throws InvalidConfigException
     */
    public function getPrefixListByDestinationID($destinationId)
    {
        $getPrefixListByDestinationID = $this->params['getPrefixListByDestinationID'];
        if (!$getPrefixListByDestinationID) {
            throw new InvalidConfigException('Не настроен getPrefixListByDestinationID');
        }

        $getPrefixListByDestinationID .= $destinationId;
        $jsonString = file_get_contents($getPrefixListByDestinationID);
        if (!$jsonString) {
            throw new BadMethodCallException($getPrefixListByDestinationID . ' не отвечает');
        }

        $jsonArray = json_decode($jsonString, true);
        if (!$jsonArray || !array_key_exists('list', $jsonArray)) {
            throw new BadMethodCallException('Неправильный ответ от ' . $getPrefixListByDestinationID);
        }

        if (!is_array($jsonArray['list'])) {
            return [];
        }

        return $jsonArray['list'];
    }
}
