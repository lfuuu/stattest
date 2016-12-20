<?php
namespace app\modules\nnp\commands;

use app\modules\nnp\models\City;
use app\modules\nnp\models\NumberRange;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\db\Expression;

/**
 * Группировка городов
 */
class CityController extends Controller
{
    /** @var City[] */
    protected $cities = [];

    protected $regionSourceToCityId = [];

    /**
     * @return int
     */
    public function actionIndex()
    {
        // Группированные значение
        $this->cities = City::find()->all();

        // уже сделанные соответствия
        $this->regionSourceToCityId = NumberRange::find()
            ->distinct()
            ->select([
                'id' => 'city_id',
                'name' => new Expression('CONCAT(country_prefix, region_source)'),
            ])
            ->where('city_id IS NOT NULL')
            ->andWhere(['IS NOT', 'region_source', null])
            ->andWhere(['!=', 'region_source', ''])
            ->indexBy('name')
            ->column();

        $numberRangeQuery = NumberRange::find()
            ->where('is_active')
            ->andWhere('city_id IS NULL')
            ->andWhere(['IS NOT', 'region_source', null])
            ->andWhere(['!=', 'region_source', '']);
        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            $city_id = $this->findCityByRegionSource($numberRange->country_prefix, $numberRange->region_source);
            if (!$city_id) {
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $numberRange->city_id = $city_id;
                if (!$numberRange->save()) {
                    throw new InvalidParamException(implode('. ', $numberRange->getFirstErrors()));
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Ошибка Region');
                Yii::error($e);
                printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            }
        }

        echo PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Найти город
     *
     * @param string $countryPrefix
     * @param string $regionSource
     * @return int|null
     */
    protected function findCityByRegionSource($countryPrefix, $regionSource)
    {
        if (!$regionSource) {
            return null;
        }

        if (array_key_exists($countryPrefix . $regionSource, $this->regionSourceToCityId)) {
            // уже обрабатывали
            return $this->regionSourceToCityId[$countryPrefix . $regionSource];
        }

        // поискать вхождения города в регион
        foreach ($this->cities as $city) {
            if ($city->country_prefix == $countryPrefix
                && strpos($regionSource, $city->name) !== false // strpos для быстрого поиска
                && ($regionSource == $city->name || preg_match('/\b' . $city->name . '\b/ui', $regionSource))  // preg_match для детального уточнения, чтобы не спутать "новосибирск" и "новосибирская область"
            ) {
                return $this->regionSourceToCityId[$countryPrefix . $regionSource] = $city->id;
            }
        }

        // создать город из региона
        list($cityName) = explode('|', $regionSource);
        $cityName = str_replace(['г. ', 'город '], '', $cityName);

        $city = new City;
        $city->name = $cityName;
        $city->country_prefix = $countryPrefix;
        if (!$city->save()) {
            throw new InvalidParamException(implode('. ', $city->getFirstErrors()));
        }

        return $this->regionSourceToCityId[$countryPrefix . $regionSource] = $city->id;
    }
}
