<?php

namespace app\classes\grid\column;

use Yii;
use yii\helpers\Html;
use app\models\Organization;

class PersonOrganizationColumn extends DataColumn
{
    public $label = 'Организации';

    protected function renderDataCellContent($model, $key, $index)
    {
        $result = [];
        $organizations = Organization::find()->byPerson($model->id)->actual()->all();

        foreach ($organizations as $organization) {
            $result[] = Html::a($organization->name, '/organization/edit?id=' . $organization->organization_id . '&date='  .$organization->actual_from);
        }

        if (sizeof($result))
            $model->canDelete = false;

        return implode(', ', $result);
    }
}