<?php
namespace app\modules\nnp\commands;

use app\classes\Connection;
use app\modules\nnp\models\NumberRange;
use UnexpectedValueException;
use Yii;
use yii\console\Controller;

/**
 * Импорт из справочников
 */
class ImportController extends Controller
{
    /**
     * Ссылки на файлы для скачивания
     * [url => is_mob]
     * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
     */
    protected $rusUrls = [
        'http://www.rossvyaz.ru/docs/articles/Kody_ABC-3kh.csv' => false,
        'http://www.rossvyaz.ru/docs/articles/Kody_ABC-4kh.csv' => false,
        'http://www.rossvyaz.ru/docs/articles/Kody_ABC-8kh.csv' => false,
        'http://www.rossvyaz.ru/docs/articles/Kody_DEF-9kh.csv' => true,
    ];

    /**
     * Импортировать всё
     * @return int
     */
    public function actionIndex()
    {
        $this->actionRus();

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Импортировать RUS (из Россвязи)
     * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
     */
    public function actionRus()
    {

        /** @var Connection $dbPgNnp */
        $dbPgNnp = Yii::$app->dbPgNnp;
        $transaction = $dbPgNnp->beginTransaction();
        try {

            echo PHP_EOL . 'Импортировать RUS. ' . date(DATE_ATOM) . PHP_EOL;

            $this->preImport($dbPgNnp);

            foreach ($this->rusUrls as $rusUrl => $isMob) {
                $this->rusByUrl($dbPgNnp, $rusUrl, $isMob);
            }

            $this->postImport($dbPgNnp, NumberRange::COUNTRY_CODE_RUSSIA);

            $transaction->commit();

            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка импорта RUS');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Импортировать RUS (из Россвязи) конкретный файл
     * @param Connection $dbPgNnp
     * @param string $url
     * @param bool $isMob
     */
    protected function rusByUrl($dbPgNnp, $url, $isMob)
    {
        $handle = fopen($url, "r");
        if (!$handle) {
            throw new UnexpectedValueException('Error fopen ' . $url);
        }

        $tableName = 'number_range_tmp';
        $insertValues = [];

        fgets($handle, 4096); // заголовок нам не нужен
        while (($buffer = fgets($handle, 4096)) !== false) {
            $buffer = trim($buffer);
            if (!$buffer) {
                continue;
            }
            $buffer = iconv("cp1251", "utf-8//TRANSLIT", $buffer);
            $bufferArray = explode(';', $buffer);
            if (count($bufferArray) < 6) {
                echo 'Wrong string ' . $buffer;
                continue;
            }

            $insertValues[] = [
//                NumberRange::COUNTRY_CODE_RUSSIA,
                (int)$bufferArray[0], // ndc
                (int)$bufferArray[1], // number_from
                (int)$bufferArray[2], // number_to
                $isMob, // is_mob
                trim($bufferArray[4]), // operator_source
                trim($bufferArray[5]), // region_source
            ];

            if (count($insertValues) % 1000 === 0) {
                echo '. ';
                $dbPgNnp->createCommand()->batchInsert(
                    $tableName,
                    ['ndc', 'number_from', 'number_to', 'is_mob', 'operator_source', 'region_source'],
                    $insertValues
                )->execute();
                $insertValues = [];
            }
        }

        if (!feof($handle)) {
            throw new UnexpectedValueException('Error fgets ' . $url);
        }
        fclose($handle);

        if (count($insertValues)) {
            echo '. ';
            $dbPgNnp->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'is_mob', 'operator_source', 'region_source'],
                $insertValues
            );
        }
        
        echo PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Перед импортом
     * Создать временную таблицу для записи в нее всех новых значений
     *
     * @param Connection $dbPgNnp
     * @throws \yii\db\Exception
     */
    protected function preImport($dbPgNnp)
    {
        $sql = <<<SQL
CREATE TEMPORARY TABLE number_range_tmp
(
  ndc integer,
  number_from integer,
  number_to integer,
  is_mob boolean NOT NULL,
  operator_source character varying(255),
  region_source character varying(255)
)
SQL;
        $dbPgNnp->createCommand($sql)->execute();
    }

    /**
     * После импорта
     * Из временной таблицы перенести в постоянную
     *
     * @param Connection $dbPgNnp
     * @param string $countryCode
     * @throws \yii\db\Exception
     */
    protected function postImport($dbPgNnp, $countryCode)
    {
        $tableName = NumberRange::tableName();

        // всё выключить
        $sql = <<<SQL
    UPDATE {$tableName}
    SET is_active = false
    WHERE country_code = :country_code
SQL;
        $dbPgNnp->createCommand($sql, [':country_code' => $countryCode])->execute();

        // обновить и включить
        $sql = <<<SQL
    UPDATE
        {$tableName} number_range
    SET
        is_active = true,
        operator_source = number_range_tmp.operator_source,
        region_source = number_range_tmp.region_source,
        is_mob = number_range_tmp.is_mob,
        operator_id = CASE WHEN number_range.operator_source = number_range_tmp.operator_source THEN number_range.operator_id ELSE NULL END,
        region_id = CASE WHEN number_range.region_source = number_range_tmp.region_source THEN number_range.region_id ELSE NULL END
    FROM
        number_range_tmp
    WHERE
        number_range.country_code = :country_code
        AND number_range.ndc = number_range_tmp.ndc
        AND number_range.number_from = number_range_tmp.number_from
        AND number_range.number_to = number_range_tmp.number_to
SQL;
        $affectedRows = $dbPgNnp->createCommand($sql, [':country_code' => $countryCode])->execute();
        printf("Updated: %d\n", $affectedRows);

        // удалить из временной таблицы уже обработанное
        $sql = <<<SQL
    DELETE FROM
        number_range_tmp
    USING
        {$tableName} number_range
    WHERE
        number_range.country_code = :country_code
        AND number_range.ndc = number_range_tmp.ndc
        AND number_range.number_from = number_range_tmp.number_from
        AND number_range.number_to = number_range_tmp.number_to
SQL;
        $dbPgNnp->createCommand($sql, [':country_code' => $countryCode])->execute();

        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
    INSERT INTO
        {$tableName}
    (
        country_code,
        ndc,
        number_from,
        number_to,
        is_mob,
        operator_source,
        region_source
    )
    SELECT 
        :country_code as country_code, 
        ndc,
        number_from,
        number_to,
        is_mob,
        operator_source,
        region_source
    FROM
        number_range_tmp
SQL;
        $affectedRows = $dbPgNnp->createCommand($sql, [':country_code' => $countryCode])->execute();
        printf("Added: %d\n", $affectedRows);

        $sql = <<<SQL
DROP TABLE number_range_tmp
SQL;
        $dbPgNnp->createCommand($sql)->execute();
    }
}
