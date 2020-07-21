<?php

namespace app\modules\nnp2\media\related;

use app\helpers\DateTimeZoneHelper;
use app\modules\nnp2\media\Related;
use app\modules\nnp2\models\GeoPlace;

class GeoRelated extends Related
{
    /** @var RegionRelated */
    protected $regionRelated;

    /** @var CityRelated */
    protected $cityRelated;

    /** @var bool */
    protected bool $inTransaction = false;
    protected array $transactionData = [];

    /**
     * @return void
     */
    protected function init()
    {
        $this->list = [];
    }

    /**
     * @param RegionRelated $regionRelated
     */
    public function setRegionRelated($regionRelated): void
    {
        $this->regionRelated = $regionRelated;
    }

    /**
     * @param CityRelated $cityRelated
     */
    public function setCityRelated(CityRelated $cityRelated): void
    {
        $this->cityRelated = $cityRelated;
    }

    /**
     * @param string $ndc
     * @param string $regionName
     * @param string $cityName
     * @return string|null
     */
    public function checkToAdd($ndc, $regionName, $cityName)
    {
        $value = $ndc;
        $ndc = null;
        if ($this->checkNatural($value, $isEmptyAllowed = true, $isConvertToInt = false)) {
            $ndc = $value;
        }

        if ($ndc) {
            if (!isset($this->list[$ndc][$regionName][$cityName]) && !isset($this->toAdd[$ndc][$regionName][$cityName])) {
                // new one
                if ($this->inTransaction) {
                    //
                    $this->transactionData = [
                        $ndc,
                        $regionName,
                        $cityName,
                        [$ndc, $regionName, $cityName]
                    ];
                } else {
                    $this->toAdd[$ndc][$regionName][$cityName] = [$ndc, $regionName, $cityName];
                }
            }
        }

        return $ndc;
    }

    /**
     * @param $ndc
     * @param $regionName
     * @param $cityName
     * @return string|null
     */
    public function startAdd($ndc, $regionName, $cityName)
    {
        $this->inTransaction = true;
        $this->transactionData = [];

        return $this->checkToAdd($ndc, $regionName, $cityName);
    }

    public function commitAdd()
    {
        if (!empty($this->transactionData)) {
            list($ndc, $regionName, $cityName, $data) = $this->transactionData;
            $this->toAdd[$ndc][$regionName][$cityName] = $data;
        }

        $this->inTransaction = false;
    }

    /**
     * @param string $ndc
     * @param string $regionName
     * @param string $cityName
     * @return bool|null
     */
    public function getIdByName($ndc, $regionName, $cityName)
    {
        return $cityName && isset($this->list[$ndc][$regionName][$cityName]) ? $this->list[$ndc][$regionName][$cityName] : null;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function prepareInsertValues()
    {
        $insertValues = [];

        $nowString = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        foreach ($this->toAdd as $ndc => $ndcData) {
            foreach ($ndcData as $regionName => $regionData) {
                foreach ($regionData as $id) {
                    list($ndc, $regionName, $cityName) = $id;
                    $regionId = $this->regionRelated->getIdByName($regionName);
                    $cityId = $this->cityRelated->getIdByRegionAndName($regionName, $cityName);

                    $insertValues[] = [
                        $this->countryCode,
                        $ndc,
                        $regionId,
                        $cityId,
                        $nowString,
                    ];
                }
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
                GeoPlace::tableName(),
                [
                    'country_code',
                    'ndc',
                    'region_id',
                    'city_id',
                    'insert_time',
                ],
                $batchInsertValues
            )->execute();
        }
    }

    protected function syncLists()
    {
        foreach ($this->list as $ndc => $ndcData) {
            foreach ($ndcData as $regionName => $regionData) {
                foreach ($regionData as $cityName => $id) {
                    if (isset($this->toAdd[$ndc][$regionName][$cityName])) {
                        unset($this->toAdd[$ndc][$regionName][$cityName]);

                        if (empty($this->toAdd[$ndc][$regionName])) {
                            unset($this->toAdd[$ndc][$regionName]);
                        }

                        if (empty($this->toAdd[$ndc])) {
                            unset($this->toAdd[$ndc]);
                        }
                    }
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

        $geoCondition = [];
        foreach ($listToLoad as $ndc => $ndcData) {
            foreach ($ndcData as $regionName => $regionData) {
                foreach ($regionData as $values) {
                    list($ndc, $regionName, $cityName) = $values;

                    $regionId = $this->regionRelated->getIdByName($regionName);
                    $cityId = $this->cityRelated->getIdByRegionAndName($regionName, $cityName);

                    $geoCondition[] = [
                        'AND',
                        ['ndc' => $ndc],
                        ['region_id' => $regionId],
                        ['city_id' => $cityId],
                    ];
                }
            }
        }

        foreach (array_chunk($geoCondition, static::CHUNK_SIZE_LOAD) as $chunk) {
            array_unshift($chunk, 'OR');
            $list = GeoPlace::find()
                ->with(['region', 'city'])
                ->where([
                    'AND',
                    ['country_code' => $this->countryCode],
                    $chunk,
                ]);
            foreach ($list->all() as $geoPlace) {
                /** @var GeoPlace $geoPlace */
                $this->list[$geoPlace->ndc][$geoPlace->region ? $geoPlace->region->name : ''][$geoPlace->city ? $geoPlace->city->name : ''] = $geoPlace->id;
            }
        }

        $this->syncLists();
    }
}