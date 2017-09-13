<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\Prefix;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Prefix
 */
class PrefixFilter extends Prefix
{
    public $id = '';
    public $name = '';
    public $addition_prefix_destination = '';
    public $subtraction_prefix_destination = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['addition_prefix_destination', 'subtraction_prefix_destination', 'id'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Prefix::find();
        $prefixTableName = Prefix::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id && $query->andWhere([$prefixTableName . '.id' => $this->id]);
        $this->name && $query->andWhere(['LIKE', $prefixTableName . '.name', $this->name]);

        if ($this->addition_prefix_destination) {
            $query->joinWith('prefixDestinations AS additionPrefixDestinations'); // специально джойн prefixDestinations, а не additionPrefixDestinations, ибо при двойном джойне where путается между двумя таблицами
            $query->andWhere(['additionPrefixDestinations.is_addition' => true]);
            $query->andWhere(['additionPrefixDestinations.destination_id' => $this->addition_prefix_destination]);
        }

        if ($this->subtraction_prefix_destination) {
            $query->joinWith('prefixDestinations AS subtractionPrefixDestinations'); // специально джойн prefixDestinations, а не additionPrefixDestinations, ибо при двойном джойне where путается между двумя таблицами
            $query->andWhere(['subtractionPrefixDestinations.is_addition' => false]);
            $query->andWhere(['subtractionPrefixDestinations.destination_id' => $this->subtraction_prefix_destination]);
        }

        return $dataProvider;
    }
}
