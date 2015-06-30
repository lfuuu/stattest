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
        $organizations = Organization::find()->actual()->byPerson($model->id)->all();

        foreach ($organizations as $organization) {
            $result[] = Html::a($organization->name, '/organization/edit?id=' . $organization->id);
        }

        if (sizeof($result))
            $model->canDelete = false;

        return implode(', ', $result);
    }
}