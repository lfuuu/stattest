<?php
namespace app\forms\tariffication;

use app\classes\ListForm;
use app\models\tariffication\Feature;
use yii\db\Query;

class FeatureListForm extends ListForm
{
    public $id;
    public $name;
    public $service_type_id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['service_type_id'], 'string'],
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return Feature::find()->joinWith('serviceType');
    }

    public function applyFilter(Query $query)
    {
        if ($this->id) {
            $query->andWhere(['tariffication_feature.id' => $this->id]);
        }
        if ($this->name) {
            $query->andWhere("tariffication_feature.name like :name", [':name' => '%' . $this->name . '%']);
        }
        if ($this->service_type_id) {
            $query->andWhere(['tariffication_feature.service_type_id' => $this->service_type_id]);
        }
    }
}