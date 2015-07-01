<?php
use app\classes\StatModule;

class m_incomegoods extends IModule{

    public function incomegoods_default()
    {
        $this->incomegoods_order_list();
    }

    public function incomegoods_order_list()
    {
        global $design, $user, $fixclient_data;

        $filter = array(
            'state' => 'all',
            'manager' => $user->Get('id'),
        );

        $filter = array_merge($filter, isset($_GET['filter']) ? $_GET['filter'] : array());

        $where = 'true';
        $whereData = array();

        if($fixclient_data && $fixclient_data['id'])
        {
            $where .= ' and client_card_id = ?';
            $whereData[] = $fixclient_data['id'];
        }


        if (isset($filter['manager']) && $filter['manager'] && $filter['manager'] != 'all') {
            $where .= ' and manager_id=? ';
            $whereData[] = $filter['manager'];
        }

        if (isset($filter['organization']) && $filter['organization'] && $filter['organization'] != 'all') {
            $where .= ' and organization_id=? ';
            $whereData[] = $filter['organization'];
        }

        $statesCounter = array('all' => array('name' => 'Все', 'count' => 0));
        foreach(GoodsIncomeOrder::$stat_states as $key => $name)
            $statesCounter[$key] = array('name' => $name, 'count' => 0);

        $sqlBody = "
            FROM 
                g_income_order gio, 
                tt_troubles t, 
                tt_stages s, 
                tt_states st
            WHERE 
                    gio.id = t.bill_id
                AND s.stage_id = t.cur_stage_id
                AND st.id = s.state_id
                ";


        $states = GoodsIncomeOrder::find_by_sql($q = "
            SELECT 
                IF(gio.deleted, 'Отказ', st.`name`) AS `name`,
                IF(gio.deleted, 40, st.`id`) AS `id`,
                COUNT(st.`id`) AS count
            ".$sqlBody."
                AND {$where}
            GROUP BY `id`
                ", $whereData);

        foreach($states as $state) {
            $statesCounter['all']['count'] += $state->count;
            if (!isset($statesCounter[$state->id])) continue;
            $statesCounter[$state->id]['count'] += $state->count;
        }

        if ( 40 == $filter['state'] ) {
            // 40 - статус отказа, сюда же мы прибиваем заказы,
            // удалённые в 1Ц, но оставшиеся по каким-то причинам у нас в stat
            $where .= ' AND (gio.deleted or s.state_id=?) ';
            $whereData[] = $filter['state'];
        } else {
            if ($filter['state'] != 'all') {
                $where .= ' AND (NOT gio.deleted) AND s.state_id=? ';
                $whereData[] = $filter['state'];
            }
        }

        $list = GoodsIncomeOrder::find_by_sql($q = "
            SELECT 
                gio.*,
                st.name as state_name
            ".$sqlBody."
                AND {$where}
            order by date desc
            limit 100
            ", $whereData);

        $organizations = GoodsOrganization::all(['order' => 'name']);

        $design->assign('list', $list);
        $design->assign('statesCounter', $statesCounter);
        $design->assign('organizations', $organizations);
        $design->assign('qfilter', $filter);
        $design->assign('users', User::find('all', array('order' => 'name', 'conditions' => array("enabled='yes'"))));
        $design->AddMain('incomegoods/order_list.tpl');
    }

    public function incomegoods_order_view()
    {
        global $design;

        if (!isset($_GET['id']) && !isset($_GET["number"])) throw new IncorrectRequestParametersException();

        if(isset($_GET["number"]))
        {
            $order = GoodsIncomeOrder::find_by_number($_GET["number"], array("order" => "date desc"));
        }else{
            $order = GoodsIncomeOrder::find($_GET['id']);
        }

        $design->assign('order', $order);
        $design->AddMain('incomegoods/order_view.tpl');

        if($order)
        {
            $trouble  = Trouble::find_by_bill_id($order->id);
            if($trouble){
                StatModule::tt()->dont_filters = true;
                #StatModule::tt()->showTroubleList(0,'top',$fixclient,null,null,$tt['id']);
                StatModule::tt()->cur_trouble_id = $trouble->id;
                StatModule::tt()->tt_view($trouble->client);
                StatModule::tt()->dont_again = true;
            }
        }
    }

    public function incomegoods_order_edit()
    {
        global $design, $fixclient_data, $user;

        if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();

        if ($_GET['id'] == '') {
            if (!isset($fixclient_data['id'])) {
                trigger_error2('Выберите клиента...'); return;
            }
            $order = new GoodsIncomeOrder();
            $order->active = true;
            $order->currency = \app\models\Currency::RUB;
            $order->client_card_id = $fixclient_data['id'];
            $order->organization_id = GoodsOrganization::DEFAULT_FOR_INCOMES;
            $order->store_id = Store::MAIN_STORE;
            $order->status = GoodsIncomeOrder::STATUS_AGREED;
            $order->price_includes_nds = true;
            $order->manager_id = $user->Get('id');
        } else {
            $order = GoodsIncomeOrder::find($_GET['id']);
        }


        $design->assign('order', $order);
        $design->assign('statuses', GoodsIncomeOrder::$statuses);
        $design->assign('organizations', GoodsOrganization::find('all', array('order' => 'name')));
        $design->assign('users', User::find('all', array('order' => 'name', 'conditions' => array("enabled='yes'"))));
        $design->assign('stores', Store::find('all', array('order' => 'name')));
        $design->assign('currencies', Currency::find('all'));
        $design->AddMain('incomegoods/order_edit.tpl');
    }

    public function incomegoods_order_save()
    {
        if (!isset($_POST) || !isset($_POST['id']) || !isset($_POST['item']))
            throw new IncorrectRequestParametersException();

        $list = array();
        foreach($_POST['item'] as $line) {
            if ($incoming_date = DateTime::createFromFormat('d.m.Y', $line['incoming_date']))
                $incoming_date = $incoming_date->format(DateTime::ATOM);
            else
                $incoming_date = '0001-01-01T00:00:00';

            $list[] = array(
                'Номенклатура' => $line['good_id'],
                'Количество' => str_replace(",", ".", $line['amount']),
                'Цена' => str_replace(",", ".", $line['price']),
                'КодСтроки' => $line['line_code'],
                'ДатаПоступления' => $incoming_date,
            );
        }

        if ($external_date = DateTime::createFromFormat('d.m.Y', $_POST['external_date']))
            $external_date = $external_date->format(DateTime::ATOM);
        else
            $external_date = '0001-01-01T00:00:00';

        if(!isset($_POST["status"])) $_POST['status'] = 'Согласован';
        if(!isset($_POST["active"])) $_POST['active'] = 1;

        if($_POST["id"]) // add new
        {
            $gio = GoodsIncomeOrder::find($_POST['id']);
            if(!$gio)
                throw new Exception("Заказ не найден!");

            $status = $gio->status;
            $active = $gio->active;
            $organizationId = $gio->organization_id;

        }else{
            $status = 'Согласован';
            $active = 1;
            $organizationId = $_POST["organization_id"];
        }



        $data = array(
            'Код1С' => $_POST['id'],
            'Проведен' => (bool)$active,
            'КодКонтрагента' => $_POST['client_card_id'],
            'НомерПоДаннымПоставщика' => $_POST['external_number'],
            'ДатаПоДаннымПоставщика' => $external_date,
            'Статус' => $status,
            'Организация' => $organizationId,
            'Склад' => $_POST['store_id'],
            'Валюта' => $_POST['currency'],
            'ЦенаВключаетНДС' => (bool)$_POST['price_includes_nds'],
            'Менеджер' => $_POST['manager_id'],
            'СписокПозиций' => $list,
        );


        try{
            $order = Sync1C::getClient()->saveGoodsIncomeOrder($data);
        }catch(Exception $e)
        {
            die($e->getMessage());
        }

        header('Content-Type: application/json');
        die(json_encode(array(
            'url' => '?module=incomegoods&action=order_view&id=' . $order->id
        )));
    }

    public function incomegoods_document_view()
    {
        global $design;

        if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();

        $document = GoodsIncomeDocument::find($_GET['id']);

        $design->assign('document', $document);
        $design->assign('order', $document->order);
        $design->assign('lines', $this->getDocumentLines($document));
        $design->AddMain('incomegoods/document_view.tpl');
    }


    public function incomegoods_document_edit()
    {
        global $design;

        if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();

        if ($_GET['id'] == '') {
            $document = new GoodsIncomeDocument();
            $order = GoodsIncomeOrder::find($_GET['order_id']);
            $document->active = true;
            $document->order_id = $order->id;
            $document->currency = $order->currency;
            $document->client_card_id = $order->client_card_id;
            $document->organization_id = $order->organization_id;
            $document->store_id = $order->store_id;
            $document->price_includes_nds = $order->price_includes_nds;
        } else {
            $document = GoodsIncomeDocument::find($_GET['id']);
        }

        $design->assign('document', $document);
        $design->assign('order', $document->order);
        $design->assign('lines', $this->getDocumentLines($document));
        $design->assign('countries', Country::find('all', array('order' => 'name asc')));
        $design->AddMain('incomegoods/document_edit.tpl');
    }

    public function incomegoods_document_save()
    {
        $list = array();
        foreach($_POST['item'] as $line) {
            $list[] = array(
                'Номенклатура' => $line['good_id'],
                'Количество' => $line['amount'],
                'Цена' => $line['price'],
                'КодСтроки' => $line['line_code'],
                'КодНомерГТД' => $line['gtd_id'],
            );
        }

        $data = array(
            'Код1С' => $_POST['id'],
            'ЗаказКод1С' => $_POST['order_id'],
            'Проведен' => (bool)$_POST['active'],
            'СписокПозиций' => $list,
        );

        $document = Sync1C::getClient()->saveGoodsIncomeDocument($data);

        header('Content-Type: application/json');
        die(json_encode(array(
            'url' => '?module=incomegoods&action=document_view&id=' . $document->id
        )));
    }

    public function incomegoods_store_view()
    {
        global $design;

        $document = GoodsIncomeStore::find($_GET['id']);

        $design->assign('document', $document);
        $design->AddMain('incomegoods/store_view.tpl');
    }

    public function incomegoods_add_gtd()
    {
        $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
        $country = isset($_REQUEST['country']) ? $_REQUEST['country'] : '';
        if (!$code && !$country) {
            throw new IncorrectRequestParametersException();
        }

        $data = array(
            'Код1С' => '',
            'Код' => $code,
            'Страна' => str_pad($country, 3, '0', STR_PAD_LEFT),
        );
        $gtd = Sync1C::getClient()->saveGtd($data);

        header('Content-Type: application/json');
        die(json_encode(array(
            'id' => $gtd->id,
            'code' => $gtd->code,
            'country' => $gtd->country->name,
        )));
    }

    private function getDocumentLines($document)
    {
        $pos = 0;

        $orderLines = array();
        $documentLines = array();

        foreach($document->order->lines as $line) {
            $orderLines[$line->line_code] = $line;
        }

        $items = array();
        if (!$document->is_new_record()) {
            foreach($document->lines as $line) {
                if ($line->line_code > 0) {
                    $documentLines[$line->line_code] = $line;

                    $items[] = array(
                        'pos' => $pos++,
                        'line_code' => $line->line_code,
                        'good_id' => $line->good_id,
                        'good_num_id' => $line->good->num_id,
                        'good_name' => $line->good->name,
                        'amount' => $line->amount,
                        'price' => $line->price,
                        'sum' => $line->sum,
                        'sum_nds' => $line->sum_nds,
                        'gtd' => $line->gtd,
                        'order_amount' => isset($orderLines[$line->line_code]) ? $orderLines[$line->line_code]->amount : 0,
                        'order_price' => isset($orderLines[$line->line_code]) ? $orderLines[$line->line_code]->price : 0,
                        'order_line' => isset($orderLines[$line->line_code]) ? $orderLines[$line->line_code] : null,
                    );
                }
            }
        }

        if ($document->order->ready) {
            foreach($document->order->lines as $line) {
                if (!isset($documentLines[$line->line_code])) {
                    $items[] = array(
                        'pos' => $pos++,
                        'line_code' => $line->line_code,
                        'good_id' => $line->good_id,
                        'good_num_id' => $line->good->num_id,
                        'good_name' => $line->good->name,
                        'amount' => 0,
                        'price' => $line->price,
                        'sum' => 0,
                        'sum_nds' => 0,
                        'gtd' => null,
                        'order_amount' => $line->amount,
                        'order_price' => $line->price,
                        'order_line' => $line,
                    );
                }
            }
        }

        if (!$document->is_new_record()) {
            foreach($document->lines as $line) {
                if ($line->line_code <= 0) {
                    $items[] = array(
                        'pos' => $pos++,
                        'line_code' => $line->line_code,
                        'good_id' => $line->good_id,
                        'good_num_id' => $line->good->num_id,
                        'good_name' => $line->good->name,
                        'price' => $line->price,
                        'amount' => $line->amount,
                        'sum' => $line->sum,
                        'sum_nds' => $line->sum_nds,
                        'gtd' => $line->gtd,
                        'order_amount' => 0,
                        'order_price' => 0,
                        'order_line' => null,
                    );
                }
            }
        }

        return $items;
    }
}
