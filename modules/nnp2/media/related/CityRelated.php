<?php

namespace app\modules\nnp2\media\related;

use app\helpers\TranslitHelper;
use app\modules\nnp2\media\Related;
use app\modules\nnp2\media\related\Transformer\CityTransformer;
use app\modules\nnp2\models\City;

class CityRelated extends Related
{
    /** @var RegionRelated */
    protected $regionRelated;

    /**
     * @return void
     */
    protected function init()
    {
        $this->list = [];

        $this->transformer = new CityTransformer();
    }

    /**
     * @param RegionRelated $regionRelated
     */
    public function setRegionRelated($regionRelated): void
    {
        $this->regionRelated = $regionRelated;
    }

    /**
     * @param string $regionName
     * @param string $name
     * @return string|null
     */
    public function checkToAdd($regionName, $name)
    {
        $resultName = null;
        if ($this->checkString($name)) {
            $name = $this->prepareValue($name);

            $resultName = $name;
            if (!$name) {
                $resultName = null;
            } else if (!isset($this->list[$regionName][$name]) && !isset($this->toAdd[$regionName][$name])) {
                // new one
                $this->toAdd[$regionName][$name] = [$regionName, $name];
            }
        }

        return $resultName;
    }

    /**
     * @param string $regionName
     * @param string $cityName
     * @return bool|null
     */
    public function getIdByRegionAndName($regionName, $cityName)
    {
        return $cityName && isset($this->list[$regionName][$cityName]) ? $this->list[$regionName][$cityName] : null;
    }

    /**
     * @return array
     */
    protected function prepareInsertValues()
    {
        $insertValues = [];
        foreach ($this->toAdd as $regionName => $regionData) {
            foreach ($regionData as $values) {
                list($regionName, $cityName) = $values;

                $regionId = $this->regionRelated->getIdByName($regionName);
                $insertValues[] = [$cityName, TranslitHelper::t($cityName), $this->countryCode, $regionId];
            }
        }

        return $insertValues;
    }

    /**
     * @param $batchInsertValues
     * @throws \yii\db\Exception
     */
    protected function batchInsertValues($batchInsertValues)
    {
        if (count($batchInsertValues)) {
            $this->db->createCommand()->batchInsert(
                City::tableName(),
                [
                    'name',
                    'name_translit',
                    'country_code',
                    'region_id',
                ],
                $batchInsertValues
            )->execute();
        }
    }

    protected function syncLists()
    {

        foreach ($this->list as $regionName => $regionData) {
            foreach ($regionData as $cityName => $id) {
                if (isset($this->toAdd[$regionName][$cityName])) {
                    unset($this->toAdd[$regionName][$cityName]);
                }
            }
        }
        $this->toAdd = array_filter($this->toAdd);
    }

    /**
     * @param array|null $listToLoad
     */
    public function loadNew($listToLoad = null): void
    {
        if (is_null($listToLoad)) {
            $listToLoad = $this->toAdd;
        }

        if (empty($listToLoad)) {
            return;
        }

        $citiesCondition = [];
        foreach ($listToLoad as $regionName => $regionData) {
            foreach ($regionData as $values) {
                list($regionName, $cityName) = $values;
                $regionId = $this->regionRelated->getIdByName($regionName);

                $citiesCondition[] = [
                    'AND',
                    ['region_id' => $regionId],
                    ['name' => $cityName],
                ];
            }
        }

        foreach (array_chunk($citiesCondition, static::CHUNK_SIZE_LOAD) as $chunk) {
            array_unshift($chunk, 'OR');
            $list = City::find()
                ->with('region')
                ->where([
                    'AND',
                    ['country_code' => $this->countryCode],
                    $chunk,
                ]);
            foreach ($list->all() as $city) {
                /** @var City $city */
                $this->list[$city->region ? $city->region->name : ''][$city->name] = $city->id;
            }
        }

        $this->syncLists();
    }
}