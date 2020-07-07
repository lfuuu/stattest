<?php

namespace app\modules\nnp\forms\import;

use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp2\models\City;
use app\modules\nnp2\models\GeoPlace;
use app\modules\nnp2\models\ImportHistory;
use app\modules\nnp2\models\NumberRange;
use app\modules\nnp2\models\Operator;
use app\modules\nnp2\models\RangeShort;
use app\modules\nnp2\models\Region;
use Yii;
use yii\base\InvalidParamException;

class Form extends \app\classes\Form
{
    const CHUNK_SIZE_DELETE = 5000;

    public $countryCode;

    /** @var Country */
    public $country;

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

        // number ranges
        $nrIds = NumberRange::find()
            ->from(NumberRange::tableName() . ' nr')
            ->joinWith('geoPlace g', false)
            ->select('nr.id')
            ->where(['g.country_code' => $country->code])
            ->column();

        $db = Yii::$app->dbPgNnp2;
        $transaction = $db->beginTransaction();
        try {
            City::deleteAll(['country_code' => $country->code]);
            Region::deleteAll(['country_code' => $country->code]);
            GeoPlace::deleteAll(['country_code' => $country->code]);
            Operator::deleteAll(['country_code' => $country->code]);

            //RangeShort::deleteAll(['country_code' => $country->code]);
            if ($nrIds) {
                foreach (array_chunk($nrIds, self::CHUNK_SIZE_DELETE) as $chunk) {
                    NumberRange::deleteAll(['id' => $chunk]);
                }
            }

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
