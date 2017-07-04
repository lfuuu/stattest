<?php

namespace app\modules\nnp;

use app\classes\Connection;
use Yii;

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
 *      classes/traits/InsertUpdateUserTrait.php
 *      classes/grid/column/universal/   (Integer, String, Country, YesNo)
 */
class Module extends \yii\base\Module
{
    const EVENT_LINKER = 'nnp_linker';

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
}
