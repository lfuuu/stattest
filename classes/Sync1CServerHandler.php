<?php
use \ActiveRecord\RecordNotFound;

class Sync1CServerHandler
{
    /**
     * @var Sync1CHelper
     */
    protected $helper;

    public function __construct($helper)
    {
        $this->helper = $helper;
    }

    public function statCreateClientCard($data)
    {
        $data = $data->clientInfo;

        $clientCard = new ClientCard();
        $clientCard->company = $data->Наименование;
        $clientCard->company_full = $data->Наименование;
        $clientCard->inn = $data->ИНН;
        $clientCard->type = $data->ЮрЛицо ? 'org' : 'priv';
        $clientCard->save();

        if ($data->ЭлектроннаяПочта) {
            $contactEmail = new ClientContact();
            $contactEmail->type = 'email';
            $contactEmail->client_id = $clientCard->id;
            $contactEmail->data = $data->ЭлектроннаяПочта;
            $contactEmail->is_active = 1;
            $contactEmail->is_official = 1;
            $contactEmail->save();
        }

        if ($data->ТелефонОрганизации) {
            $contactPhone = new ClientContact();
            $contactPhone->type = 'phone';
            $contactPhone->client_id = $clientCard->id;
            $contactPhone->data = $data->ТелефонОрганизации;
            $contactPhone->is_active = 1;
            $contactPhone->is_official = 1;
            $contactPhone->save();
        }

        $clientCardData = $this->helper->getClientCardData($clientCard->id);
        return array('return' => $clientCardData);
    }

    public function statSaveStore($data)
    {
        $data = $data->store;
        try {
            $store = Store::find($data->Код1С);
        } catch (RecordNotFound $e) {
            $store = new Store();
            $store->id = $data->Код1С;
        }
        $store->name = $data->Наименование;
        $store->deleted = $data->Удален;
        $store->save();

        return array('return'=>true);
    }

    public function statSaveCurrency($data)
    {
        $data = $data->currency;
        try {
            $currency = Currency::find($data->Код);
        } catch (RecordNotFound $e) {
            $currency = new Currency();
            $currency->id = $data->Код;
        }
        $currency->name = $data->Наименование;
        $currency->save();

        return array('return'=>true);
    }


    public function statSaveOrganization($data)
    {
        $data = $data->organization;
        try {
            $organization = Organization::find($data->Код1С);
        } catch (RecordNotFound $e) {
            $organization = new Organization();
            $organization->id = $data->Код1С;
        }
        $organization->name = $data->Наименование;
        $organization->jur_name = $data->НаименованиеСокращенное;
        $organization->jur_name_full = $data->НаименованиеПолное;
        $organization->save();

        return array('return'=>true);
    }

    public function statSaveGoodsIncomeOrder($data)
    {
        $data = $data->order;
        try {
            $order = GoodsIncomeOrder::find($data->Код1С);
        } catch (RecordNotFound $e) {
            $order = new GoodsIncomeOrder();
            $order->id = $data->Код1С;
        }
        $order->active = $data->Проведен;
        $order->deleted = $data->Удален;
        $order->number = $data->Номер;
        $order->date = $data->Дата;
        $order->client_card_id = $data->КодКонтрагента;
        $order->external_number = $data->НомерПоДаннымПоставщика;
        $order->external_date = $data->ДатаПоДаннымПоставщика;
        $order->status = $data->Статус;
        $order->organization = $data->Организация;
        $order->store = $data->Склад;
        $order->currency = $data->Валюта;
        $order->price_includes_nds = $data->ЦенаВключаетНДС;
        $order->sum = $data->СуммаДокумента;
        $order->manager = $data->Менеджер;
        $order->comment = $data->Комментарий;
        $order->save();

        return array('return' => true);
    }
}