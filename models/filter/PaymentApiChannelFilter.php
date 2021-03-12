<?php

namespace app\models\filter;

use app\models\PaymentApiChannel;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для PaymentApiInfo
 */
class PaymentApiChannelFilter extends PaymentApiChannel
{
    public $id = '';
    public $code = '';
    public $name = '';
    public $is_active = '';

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = PaymentApiChannel::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);
        $this->code !== '' && $query->andWhere(['code' => $this->code]);
        $this->is_active !== '' && $query->andWhere(['is_active' => $this->is_active]);

        return $dataProvider;
    }
}
