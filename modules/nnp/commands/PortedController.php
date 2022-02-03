<?php

namespace app\modules\nnp\commands;

use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Number;
use app\modules\nnp\models\Number as nnpNumber;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;

abstract class PortedController extends Controller
{
    const CHUNK_SIZE = 500000;

    /** @var Connection */
    private $_db = null;

    public $fileName = '';

    /**
     * Список возможных параметров при вызове метода
     *
     * @param string $actionID
     * @return string[]
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['fileName']);
    }

    /**
     * Импортировать данные
     *
     * @return int
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    public function actionImport()
    {
//        if (NumberRange::isTriggerEnabled()) {
//            throw new \LogicException('Импорт невозможен, потому что триггер включен');
//        }

        if (!$this->fileName) {
            throw new \LogicException('Не указан fileName');
        }

        $this->_db = Yii::$app->dbPgNnp;
        $this->_db->enableLogging = false; // чтобы память не утекала
        // $transaction = $this->_db->beginTransaction();
        try {
            echo PHP_EOL . 'Начало импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL;

            $this->readData();
            // $transaction->commit();
            echo PHP_EOL . 'Окончание импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL;
            return ExitCode::OK;

        } catch (\Exception $e) {
            // $transaction->rollBack();
            Yii::error('Ошибка импорта');
            Yii::error($e);
            echo 'Ошибка: ' . $e->getMessage();
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Читать данные
     *
     * @return void
     */
    abstract protected function readData();

    /**
     * Импорт данных
     *
     * @param int $countryCode
     * @param array $insertValues
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function insertValues($countryCode, &$insertValues)
    {
        echo PHP_EOL;

        // Создать временную таблицу
        $sql = <<<SQL
            CREATE TEMPORARY TABLE number_tmp
            (
                id SERIAL NOT NULL,
                full_number BIGINT NOT NULL,
                operator_source CHARACTER VARYING(255)
            )
SQL;
        // CONSTRAINT number_tmp_pkey PRIMARY KEY (id)
        $this->_db->createCommand($sql)->execute();

        // Добавить в нее данные
        $this->_db->createCommand()
            ->batchInsert('number_tmp', ['full_number', 'operator_source'], $insertValues)
            ->execute();
        $insertValues = [];

        // создать индекс
//        $sql = <<<SQL
//            CREATE INDEX number_tmp_full_number_idx ON number_tmp USING btree (full_number)
//SQL;
//        $this->_db->createCommand($sql)->execute();
//        echo '# ';

        // удалить дубли
        $sql = <<<SQL
            WITH t1 AS (SELECT MAX(id) AS max_id, full_number FROM number_tmp GROUP BY full_number HAVING COUNT(*) > 1)
            DELETE FROM number_tmp
            USING t1
            WHERE number_tmp.full_number = t1.full_number AND number_tmp.id < t1.max_id
SQL;
        $affectedRows = $this->_db->createCommand($sql)->execute();
        echo sprintf('Дублей: %d' . PHP_EOL, $affectedRows);


        // обновить
        $tableName = nnpNumber::tableName();
        $sql = <<<SQL
            UPDATE
                {$tableName} number
            SET
                operator_source = number_tmp.operator_source,
                operator_id = CASE WHEN number.operator_source = number_tmp.operator_source THEN number.operator_id ELSE NULL END
            FROM
                number_tmp
            WHERE
                number.full_number = number_tmp.full_number
SQL;
        $affectedRows = $this->_db->createCommand($sql)->execute();
        echo sprintf('Обновлено: %d' . PHP_EOL, $affectedRows);

        // удалить из временной таблицы уже обработанное
        $sql = <<<SQL
            DELETE FROM
                number_tmp
            USING
                {$tableName} number
            WHERE
                number.full_number = number_tmp.full_number
SQL;
        $this->_db->createCommand($sql)->execute();

        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
            INSERT INTO
                {$tableName}
            (
                country_code,
                full_number,
                operator_source
            )
            WITH t1 AS (SELECT MAX(id) as id, full_number FROM number_tmp GROUP BY full_number HAVING COUNT(*) > 1)
            SELECT 
                :country_code as country_code, 
                full_number,
                operator_source
            FROM
                number_tmp
SQL;
        $affectedRows = $this->_db->createCommand($sql, [':country_code' => $countryCode])->execute();
        echo sprintf('Добавлено: %d' . PHP_EOL, $affectedRows);

        $sql = <<<SQL
            DROP TABLE number_tmp
SQL;
        $this->_db->createCommand($sql)->execute();
    }

    public function actionNotifyEventPortedNumber()
    {
        Number::notifySync();

        return ExitCode::OK;
    }
}
