<?php

namespace app\modules\nnp\filters;

use app\classes\traits\GetListTrait;
use app\modules\nnp\models\Destination;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Destination
 */
class DestinationFilter extends Destination
{
    public $name = '';
    public $status_id = '';
    public $land_id = '';
    public $country_id = '';
    public $addition_prefix_destination = '';
    public $subtraction_prefix_destination = '';

    /**
     * @return array
     */
    public function rules()
    {
        return
            parent::rules() +
            [
                [['addition_prefix_destination', 'subtraction_prefix_destination'], 'integer'],
            ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Destination::find();
        $destinationTableName = Destination::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $destinationTableName . '.name', $this->name]);

        switch ($this->land_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere([$destinationTableName . '.land_id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', $destinationTableName . '.land_id', null]);
                break;
            default:
                $query->andWhere([$destinationTableName . '.land_id' => $this->land_id]);
                break;
        }

        switch ($this->status_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere([$destinationTableName . '.status_id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($destinationTableName . '. IS NOT NULL');
                $query->andWhere(['IS NOT', $destinationTableName . '.status_id', null]);
                break;
            default:
                $query->andWhere([$destinationTableName . '.status_id' => $this->status_id]);
                break;
        }

        if ($this->addition_prefix_destination) {
            $query->joinWith('prefixDestinations AS additionPrefixDestinations'); // специально джойн prefixDestinations, а не additionPrefixDestinations, ибо при двойном джойне where путается между двумя таблицами
            $query->andWhere(['additionPrefixDestinations.is_addition' => true]);
            $query->andWhere(['additionPrefixDestinations.prefix_id' => $this->addition_prefix_destination]);
        }

        if ($this->subtraction_prefix_destination) {
            $query->joinWith('prefixDestinations AS subtractionPrefixDestinations'); // специально джойн prefixDestinations, а не additionPrefixDestinations, ибо при двойном джойне where путается между двумя таблицами
            $query->andWhere(['subtractionPrefixDestinations.is_addition' => false]);
            $query->andWhere(['subtractionPrefixDestinations.prefix_id' => $this->subtraction_prefix_destination]);
        }

        $this->country_id !== '' && $query->andWhere(["{$destinationTableName}.country_id" => $this->country_id]);

        return $dataProvider;
    }
}
