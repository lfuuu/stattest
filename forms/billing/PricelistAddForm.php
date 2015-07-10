<?php
namespace app\forms\billing;

use app\models\billing\Pricelist;

class PricelistAddForm extends PricelistForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name','connection_point_id','currency_id'], 'required'];
        return $rules;
    }


    public function save()
    {
        if ($this->orig && $this->type == Pricelist::TYPE_LOCAL) {
            $this->addError('orig', 'Оригинация для местных не поддерживается');
            return false;
        }

        if ($this->type == Pricelist::TYPE_LOCAL && !$this->local_network_config_id) {
            $this->addError('orig', 'Поле Местные префиксы должно быть заполнено');
            return false;
        }

        $pricelist = new Pricelist();
        $pricelist->name = $this->name;
        $pricelist->region = $this->connection_point_id;
        $pricelist->currency_id = $this->currency_id;
        $pricelist->type = $this->type;
        $pricelist->orig = $this->orig;
        $pricelist->initiate_mgmn_cost = $this->initiate_mgmn_cost;
        $pricelist->initiate_zona_cost = $this->initiate_zona_cost;
        $pricelist->tariffication_by_minutes = $this->tariffication_by_minutes;
        $pricelist->tariffication_full_first_minute = $this->tariffication_full_first_minute;
        $pricelist->local_network_config_id = $this->local ? $this->local_network_config_id : null;

        $pricelist->save();

        $this->id = $pricelist->id;

        return true;
    }
}