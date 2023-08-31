<?php

namespace app\modules\nnp2\forms\import;

use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp2\classes\NumberRangeMassUpdater;
use app\modules\nnp2\models\City;
use app\modules\nnp2\models\GeoPlace;
use app\modules\nnp2\models\ImportHistory;
use app\modules\nnp2\models\NdcType;
use app\modules\nnp2\models\NumberRange;
use app\modules\nnp2\models\Operator;
use app\modules\nnp2\models\Region;
use Yii;
use yii\base\InvalidParamException;

class Form extends \app\classes\Form
{
    const VERSION_V1 = 10;
    const VERSION_V2 = 20;
    const VERSION_V1_AND_V2 = 100;

    public $countryCode;

    /** @var Country */
    public $country;

    public $version = self::VERSION_V1;

    protected static $versionNames = [
        self::VERSION_V1 => 'Импорт v1',
        self::VERSION_V2 => 'Импорт v2',
        self::VERSION_V1_AND_V2 => 'Импорт v1 + v2',
    ];

    /**
     * Конструктор
     *
     */
    public function init()
    {
        if (!$this->countryCode) {
            throw new InvalidParamException('Страна не выбрана');
        }

        $country = Country::findOne(['code' => $this->countryCode]);
        if (!$country) {
            throw new InvalidParamException('Неправильная страна');
        }

        $this->country = $country;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function getVersions()
    {
        return self::$versionNames;
    }

    /**
     * @param int $version
     * @return bool
     */
    public function isProcessedOld($version)
    {
        return
            is_numeric($version) && (
                $version == self::VERSION_V1 ||
                $version == self::VERSION_V1_AND_V2
            )
        ;
    }

    /**
     * @param int $version
     * @return bool
     */
    public function isProcessed($version)
    {
        return
            is_numeric($version) && (
                $version == self::VERSION_V2 ||
                $version == self::VERSION_V1_AND_V2
            )
        ;
    }

    /**
     * @return bool
     */
    public function approve()
    {
        $country = $this->country;

        $db = Yii::$app->dbPgNnp2;
        $transaction = $db->beginTransaction();
        try {
            City::updateAll(['is_valid' => true], ['country_code' => $country->code]);
            Region::updateAll(['is_valid' => true], ['country_code' => $country->code]);
            GeoPlace::updateAll(['is_valid' => true], ['country_code' => $country->code]);
            Operator::updateAll(['is_valid' => true], ['country_code' => $country->code]);

            // ndc type
            $tableName = NumberRange::tableName();
            $tableNdcType = NdcType::tableName();
            $sql = <<<SQL
UPDATE
    {$tableNdcType}
SET
    is_valid = true
WHERE id NOT IN (
    SELECT
        ndc_type_id
    FROM
        {$tableName}
    WHERE
        country_code <> :country_code
    GROUP BY
        ndc_type_id
)
SQL;
            $affectedNdcTypeRows = $db->createCommand($sql, [':country_code' => $country->code])->execute();

            // number ranges
            //NumberRange::updateAll(['is_valid' => true], ['country_code' => $country->code]);
            if ($affectedNdcTypeRows) {
                $ids = NumberRange::find()
                    ->from(NumberRange::tableName() . ' nr')
                    ->select('nr.ndc_type_id')
                    ->joinWith('ndcType ndc')
                    ->where(['nr.country_code' => $country->code])
                    ->where(['ndc.is_valid' => true])
                    ->groupBy('nr.ndc_type_id')
                    ->column();

                NumberRangeMassUpdater::me()->update(null, $ids, null, $country->code);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::$app->session->addFlash('error', $country->name_rus . '<br/ >Ошибка подтверждения: ' . $e->getMessage());
            return false;
        }

        Yii::$app->session->addFlash('success', $country->name_rus . '<br/ >Подтверждено успешно: города, регионы, местоположения, операторы.');
        return true;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $country = $this->country;

        // country files
        $countryFileIds = CountryFile::find()
            ->select('id')
            ->where(['country_code' => $country->code])
            ->column();

        $db = Yii::$app->dbPgNnp2;
        $transaction = $db->beginTransaction();
        try {
            $tableNameByCountry = sprintf("%s_%s", NumberRange::tableName(), $country->code);
            if ($db->getTableSchema($tableNameByCountry, true) !== null) {
                // if exists
                $db->createCommand()->truncateTable($tableNameByCountry)->execute();
            }

            GeoPlace::deleteAll(['country_code' => $country->code]);
            City::deleteAll(['country_code' => $country->code]);
            Region::deleteAll(['country_code' => $country->code]);
            Operator::deleteAll(['country_code' => $country->code]);

            // ndc type
            $maxFixedId = NdcType::getMaxFixed();
            $tableName = NumberRange::tableName();
            $tableNdcType = NdcType::tableName();
            $sqlChild = <<<SQL
DELETE
FROM
    {$tableNdcType}
WHERE id NOT IN (
    SELECT
        ndc_type_id
    FROM
        {$tableName}
    WHERE
        country_code <> :country_code
    GROUP BY
        ndc_type_id
)
AND id > {$maxFixedId}
AND parent_id IS NOT NULL;
SQL;
            $db->createCommand($sqlChild, [':country_code' => $country->code])->execute();

            $sqlParents = <<<SQL
DELETE
FROM
    {$tableNdcType}
WHERE id NOT IN (
    SELECT
        ndc_type_id
    FROM
        {$tableName}
    WHERE
        country_code <> :country_code
    GROUP BY
        ndc_type_id
)
AND id > {$maxFixedId}
AND parent_id IS NULL;
SQL;
            $db->createCommand($sqlParents, [':country_code' => $country->code])->execute();

            // clear history
            ImportHistory::deleteAll(['country_file_id' => $countryFileIds]);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            Yii::$app->session->addFlash('error', $country->name_rus . '<br/ >Ошибка удаления: ' . $e->getMessage());
            return false;
        }

        Yii::$app->session->addFlash(
            'warning',
            $country->name_rus
                . '<br/ >Удалено успешно: города, регионы, местоположения, операторы, диапазоны номеров, истории загрузок.'
        );
        return true;
    }
}
