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
        global $user;

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

        $trouble = Trouble::find_by_bill_no($order->number);
        if(!$trouble)
        {
            $now = new ActiveRecord\DateTime();
            $now = $now->format("db");

            $state = TroubleState::find(35); // tt_states.id=35 -> first state for new income goods order;

            $trouble = new Trouble();
            $trouble->bill_no = $order->number;

            $client = clientCard::find($order->client_card_id);
            $trouble->client = $client->client;

            $trouble->trouble_type = "incomegoods";
            $trouble->trouble_subtype = "incomegoods";
            $trouble->user_author = '1c-vitrina';

            $trouble->date_creation = $now;
            $trouble->cur_stage_id = 0;

            $trouble->folder = $state->folder;

            $trouble->save();


            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = $state->id;
            $stage->user_main = $user->Get("user");
            $stage->date_start = $now;
            $stage->date_finish_desired = $now;
            $stage->save();

            $trouble->cur_stage_id = $stage->id;
            $trouble->save();
        }else{


            $cur_state = $trouble->current_stage->state;

            // switch states
            if($order->status != $cur_state->state_1c)
            {
                // folder for incomegoods, section pre-income && close
                $states = TroubleState::find('all', 
                        array("conditions" => array("folder & ( 100931731456 | 34359738368) and state_1c = ?", $order->status))
                        );

                $statuses = array();
                foreach($states as $state)
                {
                    $statuses[] = $state->name;
                }


                if(in_array($cur_state->state_1c, $statuses))
                {
                    // Пришедший и текущий статус одинаковы
                    // nothing
                }else{
                    // нужна новая стадия
                    $to_state = $states[0];

                    $now = new ActiveRecord\DateTime();
                    $now = $now->format("db");

                    if($trouble->current_stage)
                    {
                        $trouble->current_stage->date_edit = $now;
                        $trouble->current_stage->user_edit = "1C";
                        $trouble->current_stage->save();
                    }

                    $new_stage = new TroubleStage();
                    $new_stage->trouble_id = $trouble->id;
                    $new_stage->user_main = "1c-vitrina";
                    $new_stage->state_id = $to_state->id?:35;
                    $new_stage->date_start = $now;
                    $new_stage->date_edit = $now;
                    $new_stage->date_finish_desired = $now;
                    $new_stage->save();

                    $trouble->folder = $to_state->folder;
                    $trouble->cur_stage_id = $new_stage->id;
                    $trouble->save();

                }
            }

            // reject
            // close
        }

        GoodsIncomeOrder::checkClose($order->id);

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
            $item->good_id = $line->Номенклатура?: 0;
            $item->good_ext_id = $line->Характеристика;
            $item->price = $line->Цена?: 0.00;
            $item->amount = $line->Количество?: 0;
            $item->sum = $line->Сумма ?: 0.00;
            $item->sum_nds= $line->СуммаНДС ?: 0.00;
            $item->line_code = $line->КодСтроки?: 0;
            $item->gtd_id = $line->КодНомерГТД ?: null;
            $item->save();
        }

        GoodsIncomeOrder::checkClose($document->order_id);
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

        GoodsIncomeOrder::checkClose($document->order_id);

        return $document;
    }
}
