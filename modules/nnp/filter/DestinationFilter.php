<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\Destination;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Destination
 */
class DestinationFilter extends Destination
{
    public $name = '';
    public $addition_prefix_destination = '';
    public $subtraction_prefix_destination = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
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

        return $dataProvider;
    }
}
