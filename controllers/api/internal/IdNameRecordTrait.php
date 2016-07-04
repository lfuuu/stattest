<?php

namespace app\controllers\api\internal;

use Yii;
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
     * @return array
     */
    private function getIdNameRecord($model, $idFieldName = 'id')
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getIdNameRecord($subModel, $idFieldName);
            }
            return $result;

        } elseif ($model) {

            return [
                'id' => $model->{$idFieldName},
                'name' => (string)$model,
            ];

        } else {

            return [];

        }
    }
}