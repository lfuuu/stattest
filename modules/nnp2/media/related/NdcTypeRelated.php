<?php

namespace app\modules\nnp2\media\related;

use app\modules\nnp2\media\Related;
use app\modules\nnp2\models\NdcType;

class NdcTypeRelated extends Related
{
    protected $realList = [];

    /**
     * @return void
     */
    protected function init()
    {
        $this->list = array_flip(
            NdcType::getList($isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $isMainOnly = false)
        );

        $this->realList = NdcType::find()
            ->select('parent_id')
            ->where([
                'name' => array_keys($this->list),
            ])
            ->indexBy('id')
            ->column();
    }

    /**
     * @param string $name
     * @param int $parentId
     * @return string|null
     */
    public function checkToAdd($name, $parentId)
    {
        $resultName = null;
        if ($this->checkString($name)) {
            $resultName = $name;
            if (!$name) {
                $resultName = null;
            } else if (!isset($this->list[$name]) && !isset($this->toAdd[$name])) {
                // new one
                if (
                    !$this->checkNatural($parentId, $isEmptyAllowed = false)
                    || !in_array($parentId, $this->list)
                ) {
                    $parentId = null;
                }

                $this->toAdd[$name] = [$name, $parentId];
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
     * @param int $ndcTypeId
     * @return int
     */
    public function getRealNdcTypeId($ndcTypeId)
    {
        return
            empty($this->realList[$ndcTypeId]) ?
                $ndcTypeId :
                $this->realList[$ndcTypeId];
    }

    /**
     * @return array
     */
    protected function prepareInsertValues()
    {
        $insertValues = [];
        foreach ($this->toAdd as $ndcTypeData) {
            $insertValues[] = $ndcTypeData;
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
                NdcType::tableName(),
                [
                    'name',
                    'parent_id',
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

        $list = NdcType::find()
            ->where([
                'name' => array_keys($listToLoad),
            ]);

        foreach ($list->each() as $ndcType) {
            /** @var NdcType $ndcType */
            $this->list[$ndcType->name] = $ndcType->id;

            // real ndc
            $this->realList[$ndcType->id] = $ndcType->parent_id;
        }
    }
}