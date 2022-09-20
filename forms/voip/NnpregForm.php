<?php

namespace app\forms\voip;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Form;
use app\classes\grid\ActiveDataProvider;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\models\City;
use app\models\Country;
use app\models\voip\Registry;
use app\models\voip\Source;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;

class NnpregForm extends Form
{

    public
        $country_id = Country::RUSSIA,
        $source = VoipRegistrySourceEnum::OPERATOR,
        $ndc_type_id = NdcType::ID_GEOGRAPHIC,
        $operator_id = ''
    ;


    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['country_id', 'source', 'ndc_type_id', 'operator_id',],
                'required',
            ],            [
                ['country_id', 'source', 'ndc_type_id', 'operator_id',],
                'string',
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return (new Registry)->attributeLabels() + [
                'operator_id' => 'Оператор',
            ];
    }

    public function search()
    {
        $query = NumberRange::find()->where(['is_active' => true])->limit(100);
        $data = new \yii\data\ActiveDataProvider([
            'query'=> $query,
        ]);

        if (!$this->operator_id) {
            $query->where('0=1');
        } else {
            $query->andWhere([
                'country_code' => $this->country_id,
                'ndc_type_id' => $this->ndc_type_id,
                'operator_id' => $this->operator_id,
            ]);
        }

        $data->pagination->params = ['NnpregForm' => $this->getAttributes()];
        $data->pagination->page = isset($_GET['page']) ? $_GET['page']-1 : 0;

        return $data;
    }

}