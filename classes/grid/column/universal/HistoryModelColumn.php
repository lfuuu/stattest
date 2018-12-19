<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use kartik\grid\GridView;


class HistoryModelColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = [
            '' => '----',

            \app\models\Bill::class => 'Счёт',
            \app\models\BillLine::class => 'Строчка счёта',
            \app\models\ClientAccount::class => 'Аккаунт',
            \app\models\ClientContact::class => 'Контакт',
            \app\models\ClientContract::class => 'Контракт',
            \app\models\ClientContragent::class => 'Контрагент',
            \app\models\voip\Registry::class => 'Реестр телефонии',

            \app\modules\nnp\models\Operator::class => 'ННП. Оператор',
            \app\modules\nnp\models\Region::class => 'ННП. Регион',
            \app\modules\nnp\models\City::class => 'ННП. Город',

            \app\modules\sim\models\Card::class => 'Sim. Карта',
            \app\modules\sim\models\CardStatus::class => 'Sim. Карта. Статус',
            \app\modules\sim\models\Imsi::class => 'Sim. IMSI',
            \app\modules\sim\models\ImsiStatus::class => 'Sim. IMSI. Статус',
            \app\modules\sim\models\ImsiPartner::class => 'Sim. Партнёр',

            \app\modules\uu\models\AccountTariff::class => 'УУ. Услуга',
            \app\modules\uu\models\AccountTariffLog::class => 'УУ. Услуга. Лог',
            \app\modules\uu\models\Tariff::class => 'УУ. Тариф',
            \app\modules\uu\models\TariffCountry::class => 'УУ. Тариф. Страна',
            \app\modules\uu\models\TariffOrganization::class => 'УУ. Тариф. Организация',
            \app\modules\uu\models\TariffPeriod::class => 'УУ. Тариф. Период',
            \app\modules\uu\models\TariffResource::class => 'УУ. Тариф. Ресурс',
            \app\modules\uu\models\TariffVoipCountry::class => 'УУ. Тариф. Voip. Страна',
            \app\modules\uu\models\TariffVoipCity::class => 'УУ. Тариф. Voip. Город',
            \app\modules\uu\models\TariffVoipNdcType::class => 'УУ. Тариф. Тип NDC',
        ];
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' history-model-column';
    }
}