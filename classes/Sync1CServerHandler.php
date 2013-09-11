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

    public function statSaveGtd($data)
    {
        $this->parseGtd($data->gtd);
        return array('return'=>true);
    }


    public function parseGtd($data)
    {
        try {
            $gtd = Gtd::find($data->Код1С);
        } catch (RecordNotFound $e) {
            $gtd = new Gtd();
            $gtd->id = $data->Код1С;
        }
        $gtd->code = $data->Код;
        $gtd->country_id = $data->Страна;
        $gtd->save();

        return $gtd;
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
        $this->parseGoodsIncomeOrder($data->order);
        return array('return' => true);
    }

    public function parseGoodsIncomeOrder($data)
    {
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
        $order->external_date = $data->ДатаПоДаннымПоставщика != '0001-01-01T00:00:00' ? $data->ДатаПоДаннымПоставщика : null;
        $order->status = $data->Статус;
        $order->organization_id = $data->Организация;
        $order->store_id = $data->Склад;
        $order->currency = $data->Валюта;
        $order->price_includes_nds = $data->ЦенаВключаетНДС;
        $order->sum = $data->СуммаДокумента;
        $order->manager_id = $data->Менеджер ?: null;
        $order->comment = $data->Комментарий;
        $order->save();

        GoodsIncomeOrderLine::table()->delete(array('order_id' => $order->id));

        $lines = $data->СписокПозиций;
        $lines = is_array($lines) ? $lines : array($lines);
        foreach($lines as $line) {
            $item = new GoodsIncomeOrderLine();
            $item->order_id = $order->id;
            $item->good_id = $line->Номенклатура;
            $item->good_ext_id = $line->Характеристика;
            $item->price = $line->Цена;
            $item->amount = $line->Количество;
            $item->sum = $line->Сумма;
            $item->sum_nds= $line->СуммаНДС;
            $item->incoming_date = $line->ДатаПоступления;
            $item->line_code = $line->КодСтроки;
            $item->save();
        }

        return $order;
    }

    public function statSaveGoodsIncomeDocument($data)
    {
        $this->parseGoodsIncomeDocument($data->document);
        return array('return' => true);
    }

    public function parseGoodsIncomeDocument($data)
    {
        try {
            $document = GoodsIncomeDocument::find($data->Код1С);
        } catch (RecordNotFound $e) {
            $document = new GoodsIncomeDocument();
            $document->id = $data->Код1С;
        }
        $document->order_id = $data->ЗаказКод1С;
        $document->active = $data->Проведен;
        $document->deleted = $data->Удален;
        $document->number = $data->Номер;
        $document->date = $data->Дата;
        $document->client_card_id = $data->КодКонтрагента;
        $document->organization_id = $data->Организация;
        $document->store_id = $data->Склад;
        $document->currency = $data->Валюта;
        $document->price_includes_nds = $data->ЦенаВключаетНДС;
        $document->sum = $data->СуммаДокумента;
        $document->comment = $data->Комментарий;
        $document->save();

        GoodsIncomeDocumentLine::table()->delete(array('document_id' => $document->id));

        $lines = $data->СписокПозиций;
        $lines = is_array($lines) ? $lines : array($lines);
        foreach($lines as $line) {
            $item = new GoodsIncomeDocumentLine();
            $item->document_id = $document->id;
            $item->order_id = $document->order_id;
            $item->good_id = $line->Номенклатура;
            $item->good_ext_id = $line->Характеристика;
            $item->price = $line->Цена;
            $item->amount = $line->Количество;
            $item->sum = $line->Сумма;
            $item->sum_nds= $line->СуммаНДС;
            $item->line_code = $line->КодСтроки;
            $item->gtd_id = $line->КодНомерГТД ?: null;
            $item->save();
        }

        return $document;
    }

    public function statSaveGoodsIncomeStore($data)
    {
        $this->parseGoodsIncomeStore($data->document);
        return array('return' => true);
    }

    public function parseGoodsIncomeStore($data)
    {
        try {
            $document = GoodsIncomeStore::find($data->Код1С);
        } catch (RecordNotFound $e) {
            $document = new GoodsIncomeStore();
            $document->id = $data->Код1С;
        }
        $document->order_id = $data->РаспоряжениеКод1С;
        $document->active = $data->Проведен;
        $document->deleted = $data->Удален;
        $document->number = $data->Номер;
        $document->date = $data->Дата;
        $document->status = $data->Статус;
        $document->store_id = $data->Склад;
        $document->responsible = $data->Ответственный;
        $document->comment = $data->Комментарий;
        $document->save();

        GoodsIncomeStoreLine::table()->delete(array('document_id' => $document->id));

        $lines = $data->СписокПозиций;
        $lines = is_array($lines) ? $lines : array($lines);
        foreach($lines as $line) {
            $item = new GoodsIncomeStoreLine();
            $item->document_id = $document->id;
            $item->order_id = $document->order_id;
            $item->good_id = $line->Номенклатура;
            $item->good_ext_id = $line->Характеристика;
            $item->amount = $line->Количество;

            $serialNumbers = $line->СерийныеНомера;
            if (!$serialNumbers) $serialNumbers = array();
            $serialNumbers = is_array($serialNumbers) ? $serialNumbers : array($serialNumbers);
            $item->serial_numbers = implode("\n", $serialNumbers);

            $item->save();
        }

        return $document;
    }
}