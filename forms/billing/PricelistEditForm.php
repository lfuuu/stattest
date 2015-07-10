<?php
namespace app\forms\billing;

use app\models\billing\Pricelist;

class PricelistEditForm extends PricelistForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id', 'name'], 'required'];
        return $rules;
    }

    public function save()
    {
        if ($this->type == 'network_prices' && !$this->local_network_config_id) {
            $this->addError('orig', 'Поле Местные префиксы должно быть заполнено');
            return false;
        }

        $pricelist = Pricelist::findOne($this->id);

        $pricelist->name = $this->name;
        $pricelist->initiate_mgmn_cost = $this->initiate_mgmn_cost;
        $pricelist->initiate_zona_cost = $this->initiate_zona_cost;
        $pricelist->tariffication_by_minutes = $this->tariffication_by_minutes;
        $pricelist->tariffication_full_first_minute = $this->tariffication_full_first_minute;
        $pricelist->price_include_vat = $this->price_include_vat;
        $pricelist->local_network_config_id = $this->type == 'network_prices' ? $this->local_network_config_id : null;

        $pricelist->save();

        return true;
    }
}