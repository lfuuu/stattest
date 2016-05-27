<?php
namespace app\modules\nnp\commands;

use app\models\City;
use app\modules\nnp\models\NumberRange;
use Yii;
use yii\console\Controller;

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
                'name' => 'region_source',
                'id' => 'city_id',
            ])
            ->where('city_id IS NOT NULL')
            ->indexBy('name')
            ->asArray()
            ->all();

        $numberRangeQuery = NumberRange::find()
            ->where('city_id IS NULL');
        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            $city_id = $this->findCityByRegionSource($numberRange->region_source);
            if (!$city_id) {
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $numberRange->city_id = $city_id;
                $numberRange->save();

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
     * @param string $regionSource
     * @return int|null
     */
    protected function findCityByRegionSource($regionSource)
    {
        if (!$regionSource) {
            return null;
        }

        if (isset($this->regionSourceToCityId[$regionSource])) {
            // уже обрабатывали
            return $this->regionSourceToCityId[$regionSource];
        }

        foreach ($this->cities as $city) {
            // strpos для быстрого поиска
            // preg_match для детального уточнения, чтобы не спутать "новосибирск" и "новосибирская область"
            if (strpos($regionSource, $city->name) !== false
                && preg_match('/\b' . $city->name . '\b/ui', $regionSource)
            ) {
                return $this->regionSourceToCityId[$regionSource] = $city->id;
            }
        }

        return $this->regionSourceToCityId[$regionSource] = null;
    }
}
