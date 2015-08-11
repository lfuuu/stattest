<?php
namespace app\forms\voip\prefixlist;

use app\classes\Form;
use app\models\voip\Prefixlist;
use app\models\voip\DestinationPrefixes;

class PrefixlistForm extends Form
{

    public
        $id,
        $name,
        $type_id = 1,
        $sub_type = 'all',
        $prefixes = '',
        $country_id = 0,
        $region_id = 0,
        $city_id = 0,
        $exclude_operators = '',
        $operators = '';

    public function rules()
    {
        return [
            [['name',], 'required'],
            [['type_id','country_id','region_id','city_id','exclude_operators',], 'integer'],
            ['operators', 'each', 'rule' => ['integer']],
            ['prefixes', 'match', 'pattern' => '/^[\d\[\],]+$/'],
            [
                'country_id', 'required',
                'when' => function($model) { return $model->type_id == 3; },
                'whenClient' => 'function(attribute, value) { return $(\'[name*="type_id"]:checked\').val() == 3; }'
            ],
            [['sub_type'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'type_id' => 'Тип',
            'country_id' => 'Страна',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'prefixes' => 'Префиксы',
            'operators' => 'Операторы',
            'exclude_operators' => 'Выбор операторов',
        ];
    }

    public function save($prefixlist = false)
    {
        if (!($prefixlist instanceof Prefixlist))
            $prefixlist = new Prefixlist;
        $prefixlist->setAttributes($this->getAttributes(), false);

        $prefixlist->operators = implode(',', $prefixlist->operators);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $prefixlist->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $prefixlist->id;

        return true;
    }

    public function delete(Prefixlist $prefixlist)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            DestinationPrefixes::deleteAll(['prefixlist_id' => $prefixlist->id]);

            $prefixlist->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}