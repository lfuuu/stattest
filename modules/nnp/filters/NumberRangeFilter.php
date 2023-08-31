<?php

namespace app\modules\nnp\filters;

use app\classes\grid\ActiveDataProvider;
use app\classes\Html;
use app\classes\traits\GetListTrait;
use app\models\EventQueue;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\Module;
use Yii;
use yii\db\ActiveQuery;
use yii\web\Application;

/**
 * Фильтрация для NumberRange
 */
class NumberRangeFilter extends NumberRange
{
    public $country_code = '';
    public $ndc_str = '';
    public $full_number_from = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это full_number
    public $full_number_mask = '';
    public $operator_source = '';
    public $operator_id = '';
    public $region_source = '';
    public $region_id = '';
    public $city_source = '';
    public $city_id = '';
    public $ndc_type_id = '';
    public $is_active = '';
    public $numbers_count_from = '';
    public $numbers_count_to = '';
    public $insert_time = ''; // чтобы не изобретать новое поле, названо как существующее. Хотя фактически это месяц добавления (insert_time) ИЛИ выключения (date_stop)
    public $date_resolution_from = '';
    public $date_resolution_to = '';
    public $is_valid = '';

    public $prefix_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_source', 'ndc_str', 'region_source', 'city_source', 'full_number_from', 'insert_time', 'full_number_mask'], 'string'],
            [['country_code', 'ndc_type_id', 'is_active', 'operator_id', 'region_id', 'city_id', 'prefix_id', 'is_valid'], 'integer'],
            [['numbers_count_from', 'numbers_count_to'], 'integer'],
            [['date_resolution_from', 'date_resolution_to'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = NumberRange::find();
        $numberRangeTableName = NumberRange::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db' => NumberRange::getDb(),
        ]);

        $this->country_code && $query->andWhere([$numberRangeTableName . '.country_code' => $this->country_code]);
        $this->ndc_str && $query->andWhere([$numberRangeTableName . '.ndc_str' => $this->ndc_str]);

        $this->is_active !== '' && $query->andWhere([$numberRangeTableName . '.is_active' => (bool)$this->is_active]);

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->ndc_type_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.ndc_type_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.ndc_type_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.ndc_type_id' => $this->ndc_type_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.city_id' => $this->city_id]);
                break;
        }

        switch ($this->is_valid) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.is_valid IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.is_valid IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.is_valid' => (bool)$this->is_valid]);
                break;
        }

        $this->operator_source && $query->andWhere(['LIKE', $numberRangeTableName . '.operator_source', $this->operator_source]);
        $this->region_source && $query->andWhere(['LIKE', $numberRangeTableName . '.region_source', $this->region_source]);
        $this->city_source && $query->andWhere(['LIKE', $numberRangeTableName . '.city_source', $this->city_source]);

        if ($this->full_number_from) {
            $query->andWhere(['<=', $numberRangeTableName . '.full_number_from', $this->full_number_from]);
            $query->andWhere(['>=', $numberRangeTableName . '.full_number_to', $this->full_number_from]);
        }

        if ($this->full_number_mask) {
            $this->full_number_mask = strtr($this->full_number_mask, ['.' => '_', '*' => '%']);
            $query->andWhere($numberRangeTableName . '.full_number_from::VARCHAR LIKE :full_number_mask', [':full_number_mask' => $this->full_number_mask]);
        }

        if ($this->insert_time) {
            $query->andWhere([
                'OR',
                ["DATE_TRUNC('month', {$numberRangeTableName}.insert_time)::date" => $this->insert_time . '-01'],
                ["DATE_TRUNC('month', {$numberRangeTableName}.date_stop)::date" => $this->insert_time . '-01']
            ]);
        }

        $this->numbers_count_from && $query->andWhere('1 + ' . $numberRangeTableName . '.number_to - ' . $numberRangeTableName . '.number_from >= :numbers_count_from', [':numbers_count_from' => $this->numbers_count_from]);
        $this->numbers_count_to && $query->andWhere('1 + ' . $numberRangeTableName . '.number_to - ' . $numberRangeTableName . '.number_from <= :numbers_count_to', [':numbers_count_to' => $this->numbers_count_to]);

        $this->date_resolution_from && $query->andWhere(['>=', $numberRangeTableName . '.date_resolution', $this->date_resolution_from]);
        $this->date_resolution_to && $query->andWhere(['<=', $numberRangeTableName . '.date_resolution', $this->date_resolution_to]);

        if ($this->prefix_id) {
            $query->joinWith('numberRangePrefixes');
            $query->andWhere([NumberRangePrefix::tableName() . '.prefix_id' => $this->prefix_id]);
        }

        return $dataProvider;
    }

    /**
     * Для всех отфильтрованных записей операторов/регионов/городов отвязать их и автоматически привязать заново.
     *
     * @param array $resetOptions
     * @return bool
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    public function resetLinks($resetOptions)
    {
        $attributes = [];
        in_array('operator', $resetOptions, true) && $attributes['operator_id'] = null;
        in_array('region', $resetOptions, true) && $attributes['region_id'] = null;
        in_array('city', $resetOptions, true) && $attributes['city_id'] = null;
        if (!$attributes) {
            return false;
        }

        /** @var ActiveQuery $query */
        $query = $this->search()->query;
        NumberRange::updateAll($attributes, $query->where);

        // поставить в очередь для пересчета операторов, регионов и городов
        $eventQueue = EventQueue::go(Module::EVENT_LINKER, [
            'notified_user_id' => Yii::$app->user->id,
        ]);
        if (Yii::$app instanceof Application) {
            Yii::$app->session->setFlash('success', 'Операторы, регионы, города будут пересчитаны через несколько минут. ' . Html::a('Проверить', $eventQueue->getUrl()));
        }

        return true;
    }
}
