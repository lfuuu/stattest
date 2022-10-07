<?php

namespace app\models\filter\voip;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\models\billing\CallsCdr;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class MonitorFilter extends Form
{
    public $range;
    public $number_a;
    public $number_b;
    public $is_with_session_time = 1;


    public function rules()
    {
        return [
            ['range', 'required'],
            ['range', 'in', 'range' => $this->getRanges()],
            [['number_a', 'number_b'], 'string'],
            [['number_a', 'number_b'], FormFieldValidator::class],
            ['is_with_session_time', 'integer'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'charge_time' => 'Дата/время',
            'src_number' => 'Номера А',
            'dst_number' => 'Номера В',
            'dst_route' => 'Исходящий транк',
            'cost' => 'Стоимость',
            'cost_gr' => 'Цена',
            'rate' => 'Ставка',
            'count' => 'Кол-во частей',

            'range' => 'За последние: ',
            'connect_time' => 'Время',
            'session_time' => 'Длительность',
            'is_with_session_time' => 'Звонки с длительностью',
            'number_a' => 'Номера А',
            'number_b' => 'Номера В',
        ];
    }

    public function search()
    {
        $query = CallsCdr::find()->orderBy(['connect_time' => SORT_DESC]);

        if ($this->range) {
            $query->where([
                'between', 'connect_time',
                new Expression("NOW() - interval '" . $this->range . "'"), new Expression('NOW()')]);
        } else {
            $query->where('0=1');
        }

        if ($this->number_a) {
            if (preg_match('/^\d+$', $this->number_a)) {
                $query->andWhere(['src_number' => $this->number_a]);
            }else{
                $query->andWhere(['like', 'src_number', preg_replace("/[^\d]+/", '%', $this->number_a), false]);
            }
        }


        if ($this->number_b) {
            if (preg_match('/^\d+$', $this->number_b)) {
                $query->andWhere(['dst_number' => $this->number_b]);
            }else{
                $query->andWhere(['like', 'dst_number', preg_replace("/[^\d]+/", '%', $this->number_b), false]);
            }
        }

        if ($this->is_with_session_time) {
            $query->andWhere(['>', 'session_time', 0]);
        }

        return new ActiveDataProvider([
            'db' => CallsCdr::getDb(),
            'query' => $query,
        ]);
    }

    public function getRanges()
    {
        return [
            '10 second' => '10 сек',
            '30 second' => '30 сек',
            '1 minute' => '1 минут',
            '3 minute' => '3 минут',
            '5 minute' => '5 минут',
            '10 minute' => '10 минут',
            '15 minute' => '15 минут',
            '30 minute' => '30 минут',
            '1 hour' => '1 час',
            '3 hour' => '3 часа',
            '12 hour' => '12 часа',
            '1 day' => '1 день',
            '3 day' => '3 дня',
            '7 day' => '7 дней',
        ];
    }
}
