<?php

namespace app\modules\nnp2\media\related;

use app\helpers\TranslitHelper;
use app\modules\nnp2\media\Related;
use app\modules\nnp2\media\related\Transformer\RegionTransformer;
use app\modules\nnp2\models\Region;

class RegionRelated extends Related
{
    /**
     * @return void
     */
    protected function init()
    {
        $this->list = array_flip(
            Region::getList($isWithEmpty = false,
                $isWithNullAndNotNull = false,
                $this->countryCode,
                $isMainOnly = false)
        );

        $this->transformer = new RegionTransformer();
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function checkToAdd($name)
    {
        $resultName = null;
        if ($this->checkString($name)) {
            $name = $this->prepareValue($name);

            $resultName = $name;
            if (!$name) {
                $resultName = null;
            } else if (!isset($this->list[$name]) && !isset($this->toAdd[$name])) {
                // new one
                $this->toAdd[$name] = $name;
            }
        }

        return $resultName;
    }

    /**
     * @param string $value
     * @return bool|null
     */
    public function getIdByName($value)
    {
        return $value && isset($this->list[$value]) ? $this->list[$value] : null;
    }

    /**
     * @return array
     */
    protected function prepareInsertValues()
    {
        $insertValues = [];
        foreach ($this->toAdd as $value) {
            $insertValues[] = [$value, TranslitHelper::t($value), $this->countryCode];
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
                Region::tableName(),
                [
                    'name',
                    'name_translit',
                    'country_code',
                ],
                $batchInsertValues
            )->execute();
        }
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


        $list = Region::find()
            ->where([
                'country_code' => $this->countryCode,
                'name' => $listToLoad,
            ]);
        foreach ($list->each(static::CHUNK_SIZE_LOAD) as $region) {
            /** @var Region $region */
            $this->list[$region->name] = $region->id;
        }
    }
}