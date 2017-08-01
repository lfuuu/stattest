<?php

namespace app\controllers\api\internal;

use yii\db\ActiveRecord;

/**
 * @SWG\Definition(definition = "idNameRecord", type = "object",
 *   @SWG\Property(property = "id", type = "integer", description = "ID"),
 *   @SWG\Property(property = "name", type = "string", description = "Название"),
 * ),
 */
trait IdNameRecordTrait
{

    /**
     * @param ActiveRecord|ActiveRecord[] $model
     * @param string $idFieldName
     * @return array
     */
    private function _getIdNameRecord($model, $idFieldName = 'id')
    {
        if (is_array($model)) {
            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getIdNameRecord($subModel, $idFieldName);
            }

            return $result;
        }

        if ($model) {
            return [
                'id' => $model->{$idFieldName},
                'name' => (string)$model,
            ];

        }

        return [];
    }
}