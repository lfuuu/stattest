<?php
namespace _1c;

function trr($var){

    if(!is_string($var)) return $var;

    $tr_anslation = array(
        'org'=>'ЮрЛицо',
        'priv'=>'ФизЛицо'
    );

    if($k=array_search($var, $tr_anslation))
        return $k;
    return $var;
}

function tr($var){
    $tr_anslation = array(
        'org'=>'ЮрЛицо',
        'priv'=>'ФизЛицо'
    );
    if(isset($tr_anslation[$var]))
        $var = $tr_anslation[$var];
    return $var;
}

function getFaultMessage(\SoapFault &$f){
    if(!$f)
        return null;
    $msg = explode('|||',trr($f->getMessage()),3);
    return \nl2br($msg[1]);
}

class clientSyncer{
    private $soap;
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
        $wsdl = \Sync1C::me()->utWsdlUrl;
        $login = \Sync1C::me()->utLogin;
        $pass = \Sync1C::me()->utPassword;
        $params = array('encoding'=>'UTF-8','trace'=>1);
        if($login && $pass){
            $params['login'] = $login;
            $params['password'] = $pass;
        }

        $this->soap = new \SoapClient($wsdl,$params);
    }

    public function delete($uidCli,$uidCon)
    {
        return $this->soap->utDelete(
            array('param'=>array(
                tr('ИдКонтрагента')=>$uidCli,
                tr('ИдСоглашения1С')=>$uidCon
            ))
        );
    }

    public function pushClientCard($client,&$fault=null)
    {
        $cc = \clCards\getCard($this->db, $client);
        $cli = $cc->getDetailsArr();

        global $user;

        try{
            $a =
                array('contract'=>array(
                    tr('ИдКлиентаСтат')=>tr($cli['client']),
                    tr('ИдКарточкиКлиентаСтат')=>tr($cli['card_id']),
                    tr('ИдКонтрагента')=>tr($cli['cli_1c']),
                    tr('ИдСоглашения1С')=>tr($cli['con_1c']),
                    tr('НаименованиеКомпании')=>tr($cli['company']),
                    tr('ПолноеНаименованиеКомпании')=>tr($cli['company_full']),
                    tr('ИНН')=>tr($cli['inn']),
                    tr('КПП')=>tr($cli['kpp']),
                    tr('БИК')=>tr($cli['bik']),
                    tr('РC')=>tr($cli['pay_acc']),
                    tr('КС')=>tr($cli['corr_acc']),
                    tr('НазваниеБанка')=>tr($cli['bank_name']),
                    tr('ГородБанка')=>tr($cli['bank_city']),
                    tr('ЮридическийАдрес')=>tr($cli['address_jur']),
                    tr('ПравоваяФорма')=>tr($cli['type']),
                    tr('Организация')=>tr($cli['firma']),
                    tr('ВалютаРасчетов')=>tr($cli['currency']),
                    tr('ВидЦен')=>tr($cli["price_type"])
                ),
                    tr('Пользователь')=>$user->Get("user")
                );

            //file_put_contents("/tmp/pushClientCard", var_export($a,true));
            file_put_contents("/tmp/utSaveClientContract", var_export($a,true));
            $oneC_ids = $this->soap->utSaveClientContract($a);
            file_put_contents("/tmp/utSaveClientContract_answer", var_export($oneC_ids,true));
        }catch(\SoapFault $e){
            $fault = $e;

            print1Cerror($e);
            return false;
        }

        $f = $oneC_ids->return;

        if($f)
            \clCards\setSync1c($this->db, $cc->getAtMask(\clCards\struct_cardDetails::card), true);
        return $f;
    }

    public function setClientBank($client)
    {
        $cc = \clCards\getCard($this->db,$client);
        $cli = $cc->getDetailsArr();

        try{
            $resp = $this->soap->utGetBank(array('bik'=>tr($cli['bik'])));
        }catch(\SoapFault $e){
            return false;
        }

        $cg = new \clCards\struct_cardDetails();
        $cg->setCorrAcc(trr($resp->return->{tr('КС')}));
        $cg->setBankName(trr($resp->return->{tr('НазваниеБанка')}));
        $cg->setBankCity(trr($resp->return->{tr('ГородБанка')}));

        if($cc->hasAnotherFields($cg) && $cc->merge($cg))
            return \clCards\saveCard($this->db, $cc);
        return false;
    }

    public function findBank($bik)
    {
        if(!$bik)
            return false;

        try{
            $ret = $this->soap->utGetBank(array('bik'=>tr($bik)))->return;
        }catch(\SoapFault $e){
            return false;
        }

        $cc = new \clCards\struct_cardDetails();
        $cc->setBankCity(trr($ret->{tr('ГородБанка')}));
        $cc->setBankName(trr($ret->{tr('НазваниеБанка')}));
        $cc->setBik(trr($ret->{tr('БИК')}));
        $cc->setCorrAcc(trr($ret->{tr('КС')}));

        return $cc;
    }

    public function findClient($card_tid=null,$cli_main_tid=null,$inn=null)
    {
        if(is_null($card_tid) && is_null($cli_main_tid) && is_null($inn))
            return false;

        try{
            $ret = $this->soap->utGetClient(array(
                tr('ИдКарточкиКлиентаСтат')=>tr($con_1c),
                tr('ИдКлиентаСтат')=>tr($cli_1c),
                tr('ИНН')=>tr($inn)
            ))->return;
        }catch(\SoapFault $e){
            return false;
        }

        $cc = new \clCards\struct_cardDetails();
        $cc->setAddressJur(trr($ret->{tr('ЮридическийАдрес')}));
        $cc->setBankCity(trr($ret->{tr('ГородБанка')}));
        $cc->setBankName(trr($ret->{tr('НазваниеБанка')}));
        $cc->setBik(trr($ret->{tr('БИК')}));
        $cc->setCompany(trr($ret->{tr('НаименованиеКомпании')}));
        $cc->setCompanyFull(trr($ret->{tr('ПолноеНаименованиеКомпании')}));
        $cc->setCorrAcc(trr($ret->{tr('КС')}));
        $cc->setCurrency(trr($ret->{tr('ВалютаРасчетов')}));
        $cc->setFirma(trr($ret->{tr('Организация')}));
        $cc->setInn(trr($ret->{tr('ИНН')}));
        $cc->setKpp(trr($ret->{tr('КПП')}));
        $cc->setPayAcc(trr($ret->{tr('РC')}));
        $cc->setType(trr($ret->{tr('ПравоваяФорма')}));

        return $cc;
    }

    public function pushClientBillService($bill_no,&$fault=null)
    {
        $bill = $this->db->GetRow("
				select
					cl.sync_1c,
					nb.bill_no,
					nb.bill_date,
					nb.currency,
					if(nb.currency = 'USD',
						if(
							nb.inv_rur>0,
							nb.inv_rur,
							if(
								nb.gen_bill_rur>0,
								nb.gen_bill_rur,
								ifnull(round(nb.`sum`*np.payment_rate,2),0)
							)
						),
						nb.`sum`
					) sum_rur,
					nb.sum,
					nb.comment,
					nb.is_payed
				from
					newbills nb
				left join
					newpayments np
				on
					np.bill_no = nb.bill_no
				left join
					clients cl
				on
					cl.id = nb.client_id
				where
					nb.bill_no = '".addcslashes($bill_no, "\\'")."'
			");
        if($bill['sync_1c'] == 'no')
            return false;
        $cl = \clCards\getClientByBillNo($this->db, $bill_no);

        if(!$cl)
            return false;

        if(!$cl->getAtMask(\clCards\struct_cardDetails::card))
            return false;

        global $user;

        try{
            $ret = $this->soap->utSaveOrderService(array(
                tr('ЗаказУслуги')=>array(
                    tr('ИдКарточкиКлиентаСтат')=>tr($cl->getAtMask(\clCards\struct_cardDetails::card)),
                    tr('Номер')=>tr($bill['bill_no']),
                    tr('Дата')=>tr($bill['bill_date']),
                    tr('ИдСоглашения1С')=>tr($cl->getAtMask(\clCards\struct_cardDetails::con_1c)),
                    tr('Валюта')=>tr($bill['currency']),
                    tr('СуммаВРублях')=>($bill['sum_rur']),
                    tr('Сумма')=>tr($bill['sum']),
                    tr('Комментарий')=>tr($bill['comment']),
                    tr('Закрыт')=>((int)$bill['is_payed'])>0
                ),
                tr('Пользователь')=>$user->Get("user")
            ))->return;
        }catch(\SoapFault $e){
            $fault = $e;
            return false;
        }

        if($ret)
            $this->db->Query("update newbills set sync_1c='yes' where bill_no='".$bill_no."'");
        return $ret;
    }

    public function pushClientPayment($payment_id,&$fault=null)
    {
        $pay = $this->db->GetRow($q="
				select
					cl.sync_1c,
					if(np.payment_date>0,np.payment_date,if(np.oper_date>0,np.oper_date,np.add_date)) pdate,
					if(np.oper_date>0,np.oper_date,if(np.payment_date>0,np.payment_date,np.add_date)) odate,
					np.*
				from
					newpayments np
				left join
					newbills nb
				on
					nb.bill_no = np.bill_no
				left join
					newbills nbv
				on
					nbv.bill_no = np.bill_vis_no
				left join
					clients cl
				on
					cl.id = nb.client_id
				where np.id=".(int)$payment_id
        );

        if(!$pay)
            return false;

        if($pay['push_1c'] == 'no')
            return false;

        global $user;

        try{
            $ret = $this->soap->utSavePayment(array(
                tr('ОплатаУслуги')=>array(
                    tr('ИдПлатежаСтат')=>$pay['id'],
                    tr('НомерСчетаСтат')=>tr($pay['bill_no']),
                    tr('НомерСчетаПривязкиСтат')=>tr($pay['bill_vis_no']),
                    tr('ИдПлатежа')=>'',
                    tr('ИдЗаказа')=>'',
                    tr('ИдЗаказаПривязка')=>'',
                    tr('ДатаДокумента')=>tr($pay['pdate']),
                    tr('НомерДокумента')=>tr($pay['payment_no']),
                    tr('ДатаОперации')=>tr($pay['odate']),
                    tr('Тип')=>tr($pay['type']),
                    tr('СуммаВРублях')=>tr($pay['sum_rub']),
                    tr('Валюта')=>tr($pay['currency']),
                    tr('Курс')=>tr($pay['payment_rate']),
                    tr('Комментарий')=>tr($pay['comment'])
                ),
                tr('Пользователь')=>$user->Get("user"),
            ))->return;
        }catch(\SoapFault $e){
            $fault = $e;
            return false;
        }

        if($ret)
            $this->db->Query('update newpayments set sync_1c = "yes" where id='.(int)$payment_id);
        return $ret;
    }

    public function checkBillExists($bill_no)
    {
        $resp = $this->soap->utOrderExists(array('number'=>$bill_no))->return;
        return $resp;
    }

    public function deleteBill($bill_no,&$fault=null)
    {
        $bill = $this->db->GetRow("select is_rollback from newbills where bill_no='".addcslashes($bill_no, "\\'")."'");
        global $user;
        try{
            $resp = $this->soap->utDeleteOrder(array(
                'number'=>$bill_no,
                tr('Пользователь')=>$user->Get("user"),
                'isRollback'=>(bool)$bill['is_rollback']
            ))->return;
        }catch(\SoapFault $e){

            $fault = $e;
            return false;
        }

        return $resp;
    }

    public function deletePayment($payment_no)
    {
        global $user;
        $resp = $this->soap->utDeletePayment(array(
            tr('ИдПлатежаСтат')=>$payment_no,
            tr('Пользователь')=>$user->Get("user")
        ))->return;
        return $resp;
    }
}

class reports{
    private $soap;
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
        $wsdl = \Sync1C::me()->utWsdlUrl;
        $login = \Sync1C::me()->utLogin;
        $pass = \Sync1C::me()->utPassword;
        $params = array('encoding'=>'UTF-8','trace'=>1);
        if($login && $pass){
            $params['login'] = $login;
            $params['password'] = $pass;
        }
        $this->soap = new \SoapClient($wsdl,$params);
    }

    public function test($goodNum, $from , $to)
    {
        try{
            $q = array(
                tr('КодТовара')=>$goodNum,
                tr('НачалоПериода')=>tr($from),
                tr('КонецПериода')=>tr($to)
            );

            $resp = $this->soap->utGetStoreAmount($q)->return;

            //printdbgu($resp);

        }catch(\SoapFault $e){
            printdbgu($e);//->getMessage());
            $fault = $e;
            return false;
        }

        $data = array();

        if(isset($resp->{tr("ПоДням")}))
        {
            foreach($resp->{tr("ПоДням")} as $r)
            {
                $p = $r->{tr("Период")};

                if(!isset($data[$p]))
                {
                    $data[$p] = array(
                        "start" => $r->{tr("ОстатокНаНачало")},
                        "income" => 0,
                        "outlay" => 0,
                        "end" => 0,
                        "orders" => array()
                    );
                }
                $data[$p]["income"] += $r->{tr("Приход")};
                $data[$p]["outlay"] += $r->{tr("Расход")};
                $data[$p]["end"] = $r->{tr("ОстатокНаКонец")};

                if(isset($r->{tr("НомерЗаказа")}))
                    $data[$p]["orders"][] = $r->{tr("НомерЗаказа")};
            }
        }

        //printdbg($data);

        return $data;
    }
}
class billMaker{
    private $soap;
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
        $wsdl = \Sync1C::me()->utWsdlUrl;
        $login = \Sync1C::me()->utLogin;
        $pass = \Sync1C::me()->utPassword;
        $params = array('encoding'=>'UTF-8','trace'=>1);
        if($login && $pass){
            $params['login'] = $login;
            $params['password'] = $pass;
        }
        $this->soap = new \SoapClient($wsdl,$params);
    }

    public function getPriceTypes($client_tid)
    {
        try{
            $resp = $this->soap->utGetPriceTypes(array(
                tr('ИдКарточкиКлиентаСтат')=>$client_tid
            ))->return;
        }catch(\SoapFault $e){
            return false;
        }

        $ret = array();
        $ret['default'] = trr($resp->{tr('ИдВидаЦенПоумолчанию')});
        $ret['list'] = array();

        foreach($resp->{tr('СписокВидовЦен')} as $p){
            $ret['list'][trr($p->{tr('ИдВидаЦен')})] = trr($p->{tr('Наименвоание')});
        }
        asort($ret['list']);
        return $ret;
    }

    public function findProduct($find_string,$id_pice_type,&$fault=null)
    {
        try{
            $resp = $this->soap->utFindProduct(array(
                tr('Поиск')=>tr($find_string),
                tr('ИдВидаЦен')=>tr($id_price_type)
            ))->return;
        }catch(\SoapFault $e){
            $fault = $e;
            return false;
        }

        $ret = array();

        if(is_array($resp->{tr('Список')}))
            foreach($resp->{tr('Список')} as $pos){
                $ret[] = array(
                    'id'=>trr($pos->{tr('ИдТовара1С')}),
                    'name'=>trr($pos->{tr('Наименование')}),
                    'price'=>trr($pos->{tr('Цена')}),
                    'quantity'=>trr($pos->{tr('КоличествоНаСкладе')}),
                    'is_service'=>$pos->{tr('ЭтоУслуга')}
                );
            }
        else
            $ret[] = array(
                'id'=>trr($resp->{tr('Список')}->{tr('ИдТовара1С')}),
                'name'=>trr($resp->{tr('Список')}->{tr('Наименование')}),
                'price'=>trr($resp->{tr('Список')}->{tr('Цена')}),
                'quantity'=>trr($resp->{tr('Список')}->{tr('КоличествоНаСкладе')}),
                'is_service'=>$resp->{tr('Список')}->{tr('ЭтоУслуга')}
            );

        return $ret;
    }

    public function calcGetedOrder(&$positions, $isRollback = false)
    {
        global $db;
        $positions["sum"] = 0;
        foreach($positions["list"] as &$p) {
            if($isRollback) $p["sum"] = $p["price"]*$p["quantity"];
            $positions["sum"] += @$p["sum"];//-$p["discount_set"]-$p["discount_auto"]);
            foreach(array("price", "discount_auto", "discount_set", "sum") as $f)
                if(isset($p[$f])) $p[$f] = round($p[$f],2);
        }
    }

    public function calcOrder($client_card_tid,$structFull,&$fault=null)
    {
        $s = array();

        $struct = $structFull["list"];

        foreach($struct as $p){
            list($p["id"], $p["descr_id"]) = explode(":", $p["id"]);
            if(!$p["descr_id"])
                $p["descr_id"] = "00000000-0000-0000-0000-000000000000";
            $s[] = array(
                tr('КодНоменклатура1С')=>$p['id'],
                tr('КодХарактеристика1С')=>$p["descr_id"],
                tr('Количество')=>$p['quantity'],
                tr('КодСтроки')=>$p['code_1c'],
                tr('Цена')=>$p["price"],
                tr('СуммаРучнойСкидки') => 0,
                tr('СуммаАвтоматическойСкидки') => 0,

            );
        }

        try{
            $a = array(
                tr('ИдКарточкиКлиентаСтат')=>tr($client_card_tid),
                tr('НомерЗаказа') => tr($structFull["bill_no"]),
                tr('СуммаИтого')=>$structFull["sum"],
                tr('СписокПозиций')=>array(
                    tr('Список')=>$s
                )
            );

            //file_put_contents("/tmp/calcOrder", var_export($a, true));
            $resp = $this->soap->utCalcOrder($a)->return;
        }catch(\SoapFault $e){
            \MyDBG::fout(trr(print_r($e,true)),true);
            $fault = $e;
            return false;
        }

        //file_put_contents("/tmp/calcOrder1", var_export($resp, true));
        $ret = array(
            'list'=>array(),
            'sum'=>$resp->{tr('СуммаИтого')}
        );

        if(!is_array($resp->{tr('Список')})){
            $resp->{tr('Список')} = array($resp->{tr('Список')});
        }

        foreach($resp->{tr('Список')} as $pos){
            $id = trr($pos->{tr('КодНоменклатура1С')});
            $descrId = trr($pos->{tr('КодХарактеристика1С')});
            if($descrId == "00000000-0000-0000-0000-000000000000")
                $descrId = "";

            $id = $id.":".$descrId;

            $ret['list'][] = array(
                'id'=>$id,
                'name'=>\Good::GetName($id),
                'quantity'=>trr($pos->{tr('Количество')}),
                'price'=>trr($pos->{tr('Цена')}),
                'discount_set' => trr($pos->{tr('СуммаРучнойСкидки')}),
                'discount_auto' => trr($pos->{tr('СуммаАвтоматическойСкидки')}),
                'sum'=>trr($pos->{tr('Сумма')}),
                'code_1c'=>trr($pos->{tr('КодСтроки')})
            );
        }

        return $ret;
    }

    public function saveOrder($data,&$fault=null, $isToTransilte = true)
    {
        //file_put_contents("/tmp/saveOrder.".date("H:i:s")."_".rand(1,1000), var_export($data,true));
        $client_tid = $data['client_tid'];
        $order_number = $data['order_number'];
        $items_list = $data['items_list'];
        $order_comment = $data['order_comment'];
        $is_rollback = $data['is_rollback'];
        $add_info = $data['add_info'];
        $storeId = $data["store_id"];

        if($isToTransilte)
            if(!is_null($add_info)){
                $buf = array();
                foreach($add_info as $k=>$v){
                    $buf[tr($k)] = tr($v);
                }
                $add_info = $buf;
            }


        $il = array();
        if($items_list !== false)
        {
            foreach($items_list as $i){
                @list($i["id"], $i["descr_id"]) = explode(":", $i["id"]);
                if(!$i["descr_id"])
                    $i["descr_id"] = "00000000-0000-0000-0000-000000000000";
                $buf = array(
                    tr('КодНоменклатура1С')=>$i['id'],
                    tr('КодХарактеристика1С')=>$i['descr_id'],
                    tr('Количество')=>$i['quantity'],
                    tr('КодСтроки')=>(int)$i['code_1c'],
                );
                //if($is_rollback)
                $buf[tr('Цена')]=$i['price'];
                $il[] = $buf;
            }
        }

        global $user;
        try{
            $q=array(
                tr('НомерЗаказа')=>tr($order_number),
                tr('ИдКарточкиКлиентаСтат')=>tr($client_tid),
                tr('Комментарий')=>tr($order_comment),
                tr('ЭтоВозврат')=>(bool)$is_rollback,
                tr('Пользователь')=>($user ? $user->Get("user") : "system"),
                tr('ДопИнформацияЗаказа')=>$add_info,
                tr('КодСклад1С') => $storeId
            );

            if($items_list !== false)
                $q[tr('СписокПозиций')]= array(tr('Список')=>$il);

            //printdbgu($q);

            $resp = $this->soap->utSaveOrder($q);

            $result = $resp->return;
            if(!$result) {throw new \Exception(trr($resp->{tr("СообщениеОбОшибке")}), 1000);}
            $resp = $resp->{tr("ЗаказТовара")};


        }catch(\Exception $e){

            echo "Ошибка 1с: ".str_replace("|||", "", $e->getMessage());
            exit();
            \MyDBG::fout(trr(print_r($e,true)),true);
            $fault = $e;
            return false;
        }

        if(!isset($resp->{tr('ДопИнформацияЗаказа')}))
            $resp->{tr('ДопИнформацияЗаказа')} = null;

        return $resp;
    }

    public function getStatOrder($bill_no,&$fault=null)
    {

        global $db;
        $bill = $db->GetRow("select * from newbills where bill_no='".addcslashes($bill_no, "\\'")."'");
        if(!$bill)
        {
            trigger_error("getStatOrder: счет не найден:". $bill_no);
            return false;
        }



        /*
        try{
            $q = array(
                tr('НомерЗаказа')=>$bill_no,
                tr('ЭтоВозврат')=>(bool)$bill['is_rollback']
            );
            $resp = $this->soap->utGetOrder($q)->return;
        }catch(\SoapFault $e){
            trigger_error(trr($e->getMessage()));
            return false;
        }
         */

        $ret = array(
            'bill_no'=>$bill_no,
            'client_id' => $bill["client_id"],
            'comment'=>$bill["comment"],
            'sum'=>$bill["sum"],
            'state_1c'=>$bill["state_1c"],
            'is_rollback'=>(bool)$bill['is_rollback'],
            'list'=>array()
        );

        $ret["list"] = $db->AllRecords("
                    SELECT concat(item_id,':',descr_id) as id, code_1c, item as name,
                    CAST(amount AS SIGNED) AS quantity,
                    round(price*1.18,4) as price,
                    discount_set, discount_auto,
                    if(`sum` is null,round(price*1.18*amount),`sum`) AS `sum`
                    FROM newbill_lines
                    WHERE bill_no = '".mysql_escape_string($bill_no)."'");

        /*
        foreach($bLines as $p){
            $ret['list'][] = array(
                'id'=>trr($p->{tr('ИдТовара1С')}),
                'name'=>trr($p->{tr('НаименованиеТовара')}),
                'quantity'=>trr($p->{tr('Количество')}),
                'price'=>trr($p->{tr('Цена')}),
                'discount'=>trr($p->{tr('Скидка')}),
                'sum'=>trr($p->{tr('СуммаИтого')}),
                'strCode'=>trr($p->{tr('КодСтроки')}),
                'articul'=>trr($p->{tr('АртикулТовара')})
            );
        }
        */

        return $ret;
    }

    public function getOrder($bill_no,&$fault=null)
    {
        global $db;
        $bill = $db->GetRow("select * from newbills where bill_no='".addcslashes($bill_no, "\\'")."'");
        if(!$bill)
            $bill = array('is_rollback'=>0);
        try{
            $q = array(
                tr('НомерЗаказа')=>$bill_no,
                tr('ЭтоВозврат')=>(bool)$bill['is_rollback']
            );
            $resp = $this->soap->utGetOrder($q)->return;
        }catch(\SoapFault $e){
            $fault = $e;
            return false;
        }

        $ret = array(
            'bill_no'=>$bill_no,
            'number'=>trr($resp->{tr('ИдЗаказа1С')}),
            'comment'=>trr($resp->{tr('Комментарий')}),
            'sum'=>trr($resp->{tr('СуммаИтого')}),
            'state_1c'=>trr($resp->{tr('СтатусЗаказа')}),
            'is_rollback'=>(bool)$bill['is_rollback'],
            'list'=>array()
        );

        $l = $resp->{tr('Список')};
        if(!is_array($l))
            $l = array($l);

        foreach($l as $p){
            $ret['list'][] = array(
                'id'=>trr($p->{tr('ИдТовара1С')}),
                'name'=>trr($p->{tr('НаименованиеТовара')}),
                'quantity'=>trr($p->{tr('Количество')}),
                'price'=>trr($p->{tr('Цена')}),
                'discount'=>trr($p->{tr('Скидка')}),
                'sum'=>trr($p->{tr('СуммаИтого')}),
                'strCode'=>trr($p->{tr('КодСтроки')}),
                'articul'=>trr($p->{tr('АртикулТовара')})
            );
        }
        return $ret;
    }

    public function setOrderStatus($bill_no,$state,&$fault=null)
    {
        global $db, $user;
        $bill = $db->GetRow("select is_rollback from newbills where bill_no='".addcslashes($bill_no,"\\'")."'");
        try{
            $resp = $this->soap->utSetOrderStatus(array(
                tr('НомерЗаказа')=>tr($bill_no),
                tr('Статус')=>tr($state),
                tr('Пользователь')=>$user->Get("user"),
                tr('ЭтоВозврат')=>(bool)$bill['is_rollback']
            ))->return;
        }catch(\SoapFault $e){
            $fault = $e;
            return false;
        }

        return $resp;
    }
}

class SoapHandler{
    public function statAuth($data){
        return array('return'=>true);
    }

    public function statSaveClientContract($data)
    {
        global $db;
        $cc = $data->contract;

        $str = "";
        $str .= serialize($data);
        //file_put_contents("/tmp/statSaveClientContract.".date("Y-m-d_H:i:s").".".rand(1,1000), serialize($data));

        $cname = $cc->{tr('ИдКарточкиКлиентаСтат')}?$cc->{tr('ИдКарточкиКлиентаСтат')}:$cc->{tr('ИдКлиентаСтат')};

        $isIdCard = isset($cc->{tr('ИдКарточкиКлиентаСтат')});

        $str .= " cname: ".$cname;
        $str .= " isIdCard: ".$isIdCard;

        //$cname = "";

        $cg = new \clCards\struct_cardDetails();

        $cg->setCard($cname);

        if(!$isIdCard)
        {
            $cg->setCon1c(trr($cc->{tr('ИдСоглашения1С')}));
            $cg->setCompany(trr($cc->{tr('НаименованиеКомпании')}));
            $cg->setCompanyFull(trr($cc->{tr('ПолноеНаименованиеКомпании')}));
            $cg->setInn(trr($cc->{tr('ИНН')}));
            $cg->setKpp(trr($cc->{tr('КПП')}));
            $cg->setBik(trr($cc->{tr('БИК')}));
            $cg->setPayAcc(trr($cc->{tr('РC')}));
            $cg->setCorrAcc(trr($cc->{tr('КС')}));
            $cg->setBankName(trr($cc->{tr('НазваниеБанка')}));
            $cg->setBankCity(trr($cc->{tr('ГородБанка')}));
            $cg->setAddressJur(trr($cc->{tr('ЮридическийАдрес')}));
            $cg->setType(trr($cc->{tr('ПравоваяФорма')}));
        }else{
            $cg->setCurrency(trr($cc->{tr('ВалютаРасчетов')}));
            $cg->setPriceType(trr($cc->{tr('ВидЦен')}));
            $cg->setFirma(trim(trr($cc->{tr('Организация')})));
        }

        $str .= " cg: ".serialize($cg);

        $c = \clCards\getCard($db, $cg->getAtMask(\clCards\struct_cardDetails::card));
        $str .= "\n\n c: ".serialize($c);
        //file_put_contents("/tmp/statSaveClientContract.".date("Y-m-d_H:i:s").".".rand(1,1000), $str);

        if($cname){
            if(!$c)
                return new \SoapFault('client',tr('Контрагент не найден в стате'));

            if($c->eq($cg, true))
            {
                return array('return'=>true, tr('ИдКарточкиКлиентаСтат') => $cname);
            }

            //$c->set($cg);

            $f = \clCards\saveCard($db, $cg);
            $cId = $cname;
        }else{
            list($f, $cId) = \clCards\saveCard($db, $cg);
            $cId = "id".$cId;
        }

        if(isset($cc->{tr("ЭлектроннаяПочта")}))
            $this->addClientContact($cId, "email", trr($cc->{tr("ЭлектроннаяПочта")}));

        if(isset($cc->{tr("ТелефонОрганизации")}))
            $this->addClientContact($cId, "phone", trr($cc->{tr("ТелефонОрганизации")}));

        if(!$f)
            return new \SoapFault('client',tr('Не удалось сохранить изменения'));

        //file_put_contents("/tmp/clCards", var_export($cg->getAtMask(\clCards\struct_cardDetails::client),true));

        return array('return'=>$f, tr('ИдКарточкиКлиентаСтат') => $cId);
    }

    private function addClientContact($client, $type, $value)
    {
        global $db;

        if(!$value) return;

        $cl = $db->GetRow("select id from clients where client = '".$client."'");
        if($cl) {
            $cl = $cl["id"];

            $clData = array(
                "client_id" => $cl,
                "type" => $type,
                "data" => $value,
                "comment" => "vitrina",
                "is_active" => 1,
                "is_official" => 1
            );

            if($c = $db->GetRow("
                            SELECT c.id
                            FROM client_contacts
                            where cc.type = '".$type."'
                                and data = '".mysql_escape_string($value)."'
                                and cc.client_id = '".$cl."'")){
                $clData["id"] = $c["id"];
                $db->QueryUpdate("client_contacts", "id", $clData);
            }else{
                $db->QueryInsert("client_contacts", $clData);
            }
        }
    }

    public function statSaveOrder($data,&$bill_no=null,&$error=null, $saveIds = array(), $addLines = true)
    {
        /*
        if(!defined("save_sql"))
            define("save_sql", 1);
            */

        global $db;

        //file_put_contents("/tmp/statSaveOrder_".date("Y-m-d_H_i_s"), serialize($data));

        $o = $data->order;
        $is_rollback = $data->isRollback;

        $bill_no = trr($o->{tr('Номер')});
        $bill_date = trr($o->{tr('Дата')});
        $client = trr($o->{tr('ИдКарточкиКлиентаСтат')});
        $currency = trr($o->{tr('Валюта')});
        $sum = trr($o->{tr('СуммаИтого')});
        $comment = trr($o->{tr('Комментарий')});
        $state_1c = trr($o->{tr('СтатусЗаказа')});
        $add_info = $o->{tr('ДопИнформацияЗаказа')};
        $storeId = $o->{tr('КодСклад1С')};

        if (strcmp($state_1c, 'Отказ')==0) {
            $sum = 0;
        }


        if($is_rollback && $sum > 0){$sum =-$sum;}



        if(strpos($bill_no,'-'))
            return array('return'=>true);

        $curbill = $db->GetRow("select newbills.*,(select count(*) from newbill_lines where bill_no=newbills.bill_no and `type`='good') good_count from newbills where bill_no = '".addcslashes($bill_no, "\\'")."'");
        $billLines = array();
        if($curbill){
            $billLines = $db->AllRecords("select * from newbill_lines where bill_no = '".mysql_escape_string($bill_no)."'");
            $curtt = $db->GetRow("select * from tt_troubles where bill_no='".addcslashes($bill_no, "\\'")."'");
            if($curtt){
                $curts = $db->GetRow("select * from tt_stages where stage_id=".$curtt['cur_stage_id']);
            }
            else
                $curts = null;
        }else{
            $curbill = array(
                'postreg'=>'0000-00-00',
                'courier_id'=>'null',
                'nal'=>'',
                'cleared_flag'=>false
            );
            $curtt = null;
            $curts = null;
        }

        if($curtt && $curtt["client"] != $client)
            $db->Query("update tt_troubles set client='".$client."' where id=".$curtt['id']);

        $l = $o->{tr('Список')};
        $list = array();
        if(!is_array($l))
            $l = array($l);
        /*
        s:20:"Количество";s:2:"20";
        s:8:"Цена";s:2:"30";
        s:16:"СуммаНДС";s:5:"91.53";
        s:34:"СуммаРучнойСкидки";s:1:"0";
        s:50:"СуммаАвтоматическойСкидки";s:1:"0";
        s:10:"Сумма";s:3:"600";
        s:18:"ЭтоУслуга";b:0;
          */
        /*
          +---------+--------+
              | amount  | price  |
              +---------+--------+
              | 20.0000 | 3.8784 |
              +---------+--------+
              */

        foreach($l as $p){
            $list[] = array(
                'item_id'=>trr($p->{tr('КодНоменклатура1С')}),
                'descr_id'=>trr($p->{tr('КодХарактеристика1С')}),
                'item' => \Good::GetName(trr($p->{tr('КодНоменклатура1С')}).":".trr($p->{tr('КодХарактеристика1С')})),
                'amount'=>$p->{tr('Количество')},
                'dispatch' => $p->{tr('КоличествоОтгружено')},
                'discount_set' => $p->{tr('СуммаРучнойСкидки')},
                'discount_auto' => $p->{tr('СуммаАвтоматическойСкидки')},
                //'price'=>round(($p->{tr('СуммаИтогоБезНДС')}+$p->{tr('СуммаНДС')})/$p->{tr('Количество')}/1.18,4),
                'price'=>round($p->{tr('Цена')}/1.18,4),
                'sum'=>$p->{tr('Сумма')},
                'type'=>$p->{tr('ЭтоУслуга')}?'service':'good',
                'code_1c'=>$p->{tr('КодСтроки')},
                "serial" => (isset($p->{tr('СерийныеНомера')}) ? $p->{tr('СерийныеНомера')}: false),
                "gtd" => trr($p->{tr('НомерГТД')}),
                "country_id" => trr($p->{tr('СтранаПроизводитель')}),
            );
        }

        checkLogisticItems($list, $add_info);


        $diff = getListDiff($billLines, $list);
        if($diff)
            saveListDiff($bill_no, $curtt["cur_stage_id"], $diff);


        $err = 0;
        $err_msg = '';
        $db->Query('start transaction');

        $db->Query("delete from newbills where bill_no='".addcslashes($bill_no, "\\'")."'");
        if($err |= mysql_errno())
            $err_msg = mysql_error();

        $db->Query("delete from g_serials where bill_no='".addcslashes($bill_no, "\\'")."'");
        if($err |= mysql_errno())
            $err_msg = mysql_error();

        if(!$err)
            $db->Query("delete from newbill_lines where bill_no='".addcslashes($bill_no, "\\'")."'");
        if(!$err && $err |= mysql_errno())
            $err_msg = mysql_error();


        if(!$err)
            $db->Query("
                        insert into
                        newbills
                        set
                        bill_no = '".addcslashes($bill_no, "\\'")."',
                        bill_date = '".addcslashes($bill_date, "\\'")."',
                        client_id = (select id from clients where client='".addcslashes($client, "\\'")."'),
                        currency = '".(($currency=='RUR')?'RUR':'USD')."',
                        `sum` = '".addcslashes($sum, "\\'")."',
                        comment = '".addcslashes($comment, "\\'")."',
                        sync_1c = 'yes',
                        `state_1c` = '".addcslashes($state_1c, "\\'")."',
                        is_rollback = ".(int)$is_rollback.",
                        postreg= '".$curbill['postreg']."',
                        courier_id = '".$curbill['courier_id']."',
                        nal = '".$curbill['nal']."'
                        ");
        if(!$err && $err |= mysql_errno())
            $err_msg = mysql_error();


        $q = "insert into newbill_lines (bill_no,sort,item,item_id,amount,price,service,type,code_1c, descr_id, discount_set, discount_auto, `sum`,dispatch,gtd,country_id) values";

        $qSerials = "";



        foreach($list as $sort=>$item){
            $q .= "('".
                addcslashes($bill_no, "\\'")."',".
                ($sort+1).",'".
                addcslashes($item['item'], "\\'")."',".
                "'".$item['item_id']."',".
                (float)$item['amount'].",".
                (float)$item['price'].",".
                "'1C','".$item['type']."',".
                "'".$item["code_1c"]."',".
                "'".$item["descr_id"]."',".
                "'".$item["discount_set"]."',".
                "'".$item["discount_auto"]."',".
                "'".$item["sum"]."',".
                "'".$item["dispatch"]."',".
                "'".$item["gtd"]."',".
                "'".$item["country_id"]."'".
                "),";

            if($item["serial"]) {
                $serials = (is_array($item["serial"]) ? $item["serial"] : array($item["serial"]));
                foreach($serials as $serial)
                {
                    $qSerials[] = "('".$bill_no."','".$item["code_1c"]."', '".trim($serial)."')";
                }
            }
        }

        if($qSerials)
            $db->Query("insert into g_serials (bill_no, code_1c, serial) values ".implode(',',$qSerials));

        if(!$err && $err |= mysql_errno()) {
            $err_msg = mysql_error();
            trigger_error($err_msg);
        }

        if(count($list) && $addLines){
            if(!$err)
                $db->Query(substr($q,0,-1));
            if(!$err && $err |= mysql_errno())
                $err_msg = mysql_error();
        }

        if(!$err && !is_null($add_info)){
            //if($_SESSION["_mcn_user_login_stat.mcn.ru"] == "adima")

            $idMetro = \ClientCS::GetIdByName("metro",trr($add_info->{tr('Метро')}), 0);
            $idLogistic = \ClientCS::GetIdByName("logistic",trr($add_info->{tr('Логистика')}), "none");

            $db->QueryDelete("newbills_add_info", array("bill_no" => $bill_no));
            $db->QueryInsert('newbills_add_info',$add_info_koi8r = array(
                'bill_no'=>trr($bill_no),
                'fio'=>trr($add_info->{tr('ФИО')}),
                'address'=>trr($add_info->{tr('Адрес')}),
                'req_no'=>trr($add_info->{tr('НомерЗаявки')}),
                'acc_no'=>trr($add_info->{tr('ЛицевойСчет')}),
                'connum'=>trr($add_info->{tr('НомерПодключения')}),
                'phone'=>trr($add_info->{tr('КонтактныйТелефон')}),
                'comment1'=>trr($add_info->{tr('Комментарий1')}),
                'comment2'=>trr($add_info->{tr('Комментарий2')}),
                'passp_series'=>trr($add_info->{tr('ПаспортСерия')}),
                'passp_num'=>trr($add_info->{tr('ПаспортНомер')}),
                'passp_whos_given'=>trr($add_info->{tr('ПаспортКемВыдан')}),
                'passp_when_given'=>trr($add_info->{tr('ПаспортКогдаВыдан')}),
                'passp_code'=>trr($add_info->{tr('ПаспортКодПодразделения')}),
                'passp_birthday'=>trr($add_info->{tr('ПаспортДатаРождения')}),
                'reg_city'=>trr($add_info->{tr('ПаспортГород')}),
                'reg_street'=>trr($add_info->{tr('ПаспортУлица')}),
                'reg_house'=>trr($add_info->{tr('ПаспортДом')}),
                'reg_housing'=>trr($add_info->{tr('ПаспортКорпус')}),
                'reg_build'=>trr($add_info->{tr('ПаспортСтроение')}),
                'reg_flat'=>trr($add_info->{tr('ПаспортКвартира')}),
                'email'=>trr($add_info->{tr('Email')}),
                'order_given'=>trr($add_info->{tr('ПроисхождениеЗаказа')}),
                'line_owner'=>trr($add_info->{tr('ВладелецЛинии')}),
                'metro_id'=>$idMetro,
                'logistic'=>$idLogistic,
                "store_id" => $storeId
            ));
        }

        if(!$err && $curtt && $curts && $curbill['state_1c']<>$state_1c){

            $newstate = $db->GetRow($q="
                        select
                        *
                        from
                        tt_states
                        where
                        pk & (
                            select
                            states
                            from
                            tt_types
                            where
                            code='".$curtt['trouble_type']."'
                            )
                        and
                        state_1c='".addcslashes($state_1c,"\\'")."'
                        ".
                /*($client == "DostavkaMTS" ? " and name = 'MTS'" :*/
                ($state_1c == "Новый" ?
                    ($client == "WiMaxComstar" ? " and name = 'WiMax'" :
                        ($client == "nbn" ? " and name = 'NetByNet'" :
                            ($client == "onlime" ? " and name = 'OnLime'" :
                                ($client == "onlime2" ? " and name = 'OnLime'" :
                                    "")))) : "")/*)*/."

                        order by
                        ".(($curtt['trouble_type']=='shop_orders')?'`oso`':($curtt['trouble_type']=='mounting_orders'?'`omo`':'`order`'))."
                        limit 1
                        ");

            if(!$newstate){
                $err = 1;
                $err_msg = "Unknown state!";
                $db->Query("select 'error: ".$err_msg."'");
            }else{
                if(in_array($client, array("nbn", "onlime", "onlime2", "DostavkaMTS")) && trim($_POST["comment"]))
                    $q = "update tt_stages set comment='".mysql_escape_string(trim($_POST["comment"]))."',date_edit=now(), where stage_id=".$curtt['cur_stage_id'];

                if($state_1c == 'Отгружен')
                    $q = "update tt_stages set comment='Товар Отгружен: ".nl2br(htmlspecialchars_(addcslashes(trr($o->{tr('КомментарийСклада')}), "\\'")))."',date_edit=now(),user_edit='1C' where stage_id=".$curtt['cur_stage_id'];
                elseif($state_1c == 'КОтгрузке')
                    $q = "update tt_stages set comment='Возврат товара: ".nl2br(htmlspecialchars_(addcslashes(trr($o->{tr('КомментарийСклада')}), "\\'")))."',date_edit=now(),user_edit='1C' where stage_id=".$curtt['cur_stage_id'];
                else{
                    $comment = isset($o->{tr('КомментарийСклада')}) ? nl2br(htmlspecialchars_(addcslashes(trr($o->{tr('КомментарийСклада')}), "\\'"))) : "";
                    $q = "update tt_stages set comment='".$comment."', date_edit=now(),user_edit='1C' where stage_id=".$curtt['cur_stage_id'];
                }

                if($curtt['trouble_type'] == 'mounting_orders'){
                    $out = $db->GetRow("select * from tt_doers where stage_id=".$curtt['cur_stage_id']);
                    if($out)
                        $newstate['id'] = 4;
                }

                $db->Query($q);
                if(!$err && $err |= mysql_errno())
                    $err_msg = mysql_error();

                if(!$err){
                    unset($curts['stage_id'],$curts['date_edit']);

                    // проводим ели ноавя стадия закрыт, отгружен, к отгрузке
                    include_once INCLUDE_PATH.'bill.php';
                    $oBill = new \Bill($bill_no);
                    if(in_array($newstate['id'], array(28, 23, 18, 7, 4,  17, 2, 20 ))){
                        $oBill->SetCleared();
                    }else{
                        $oBill->SetUnCleared();
                    }

                    $newts_id = $db->QueryInsert(
                        'tt_stages',
                        array(
                            'user_main'=>$curts['user_main'],
                            'state_id'=>$newstate['id'],
                            'trouble_id'=>$curtt['id'],
                            'date_start'=>array('now()'),
                            'date_edit'=>array('now()'),
                            'date_finish_desired'=>array('now()')
                        )
                    );
                }
                if(!$err && $err |= mysql_errno())
                    $err_msg = mysql_error();

                /*
                   if(!$err)
                   $db->Query("update tt_doers set stage_id=".$newts_id." where stage_id=".$curtt['cur_stage_id']);
                   if(!$err && $err |= mysql_errno())
                   $err_msg = mysql_error();
                 */

                if(!$err)
                    $db->Query("update tt_troubles set cur_stage_id=".$newts_id.",folder=".$newstate['folder']." where id=".$curtt['id']);
                if(!$err && $err |= mysql_errno())
                    $err_msg = mysql_error();
            }

        }elseif(
            !$err && !$curtt && (
                in_array($client,array('all4net','All4Net_new', 'wellconnect','WiMaxComstar','ComPapa','Compapa','nbn','onlime','onlime2', 'DostavkaMTS'))
                ||  strtolower(trr($add_info->{tr('ПроисхождениеЗаказа')})) == "welltime"
                ||  strtolower(trr($add_info->{tr('ПроисхождениеЗаказа')})) == "all4net"
            )){

            $from = strtolower(trr($add_info->{tr('ПроисхождениеЗаказа')}));

#1c-vitrina

            if(!$err)
                $newstate = $db->GetRow("select * from tt_states where pk & (select states from tt_types where code='shop_orders')
                        ".
                ($client == "DostavkaMTS" ? " and name = 'MTS'" :
                    ($state_1c == "Новый" ?
                        ($client == "WiMaxComstar" ? " and name = 'WiMax'" :
                            ($client == "nbn" ? " and name = 'NetByNet'" :
                                ($client == "onlime" ? " and name = 'OnLime'" :
                                    ($client == "onlime2" ? " and name = 'OnLime'" :
                                        "")))):""))."
                            order by oso limit 1");
            if(!$err && $err |= mysql_errno())
                $err_msg = mysql_error();

            if(isset($add_info_koi8r) && (!$comment || $client == "onlime")){
                $orig_comment = $comment;

                if($client == "onlime")
                {
                    $oo = \OnlimeOrder::find_by_external_id($add_info_koi8r["req_no"]);
                    if($oo)
                    {
                        if(isset($oo->coupon) && trim($oo->coupon))
                        {
                            $orig_comment .= "<br /><b>Акция!!</b> Купон: ".$oo->coupon." / ".$oo->seccode." / ".$oo->vercode;
                        }
                    }
                }


                $comment = "
                        %s<hr />
                        Телефон: %s<br />
                        Адрес доставки: %s<br />
                        Комментарий1: %s<br />
                        Комментарий2: %s<br />
                        Комментарий к заказу: %s
                        ";
                $comment = sprintf(
                    $comment,
                    $add_info_koi8r['order_given'],
                    $add_info_koi8r['phone'],
                    $add_info_koi8r['address'],
                    $add_info_koi8r['comment1'],
                    $add_info_koi8r['comment2'],
                    $orig_comment
                );
            }

            if(in_array($client,array("nbn", "onlime", "onlime2", "DostavkaMTS")) && trim($_POST["comment"]))
                $comment = trim($_POST["comment"]);

            if(!$err){
                $_curttId = $db->GetValue("select id from tt_troubles where bill_no = '".$bill_no."'");
                if(!$_curttId)
                {
                    $ttid = $db->QueryInsert('tt_troubles',array(
                        'trouble_type'=>'shop_orders',
                        'trouble_subtype' => "shop",
                        'client'=>$client,
                        'user_author'=>'1c-vitrina',
                        'date_creation'=>array('now()'),
                        'problem'=>$comment,
                        'bill_no'=>$bill_no,
                        'folder'=>$newstate['folder']
                    ));
                    if(!$err && $err |= mysql_errno())
                        $err_msg = mysql_error();

                    if(!$err)
                        $db->Query("insert into z_sync_admin set bill_no = '".$bill_no."'");

                    if(!$err){

                        // проводим ели ноавя стадия закрыт, отгружен, к отгрузке
                        include_once INCLUDE_PATH.'bill.php';
                        $oBill = new \Bill($bill_no);
                        if(in_array($newstate['id'], array(28, 23, 18, 7, 4,  17, 2, 20 ))){
                            $oBill->SetCleared();
                        }else{
                            $oBill->SetUnCleared();
                        }
                        $comment = "";
                        if(in_array($client, array("nbn", "onlime", "onlime2", "DostavkaMTS")) && trim($_POST["comment"]))
                            $comment = trim($_POST["comment"]);

                        $tsid = $db->QueryInsert('tt_stages',array(
                            'trouble_id'=>$ttid,
                            'state_id'=>$newstate['id'],
                            'user_main'=>'1c-vitrina',
                            'date_start'=>array('now()'),
                            'date_finish_desired'=>array('now()'),
                            'comment' => $comment
                        ));
                    }
                    if(!$err && $err |= mysql_errno())
                        $err_msg = mysql_error();

                    if(!$err)
                        $db->Query("update tt_troubles set cur_stage_id=".$tsid." where id=".$ttid);
                    if(!$err && $err |= mysql_errno())
                        $err_msg = mysql_error();
                }
            }
        }

        if($err){

            $db->Query('rollback');
            $error = $err_msg;
            return new \SoapFault('olol',$error);
        }else{
            $db->Query('commit');
            if(!$curbill || !$curbill['cleared_flag'])
                $db->Query('call switch_bill_cleared("'.addcslashes($bill_no, "\\\"").'")');
        }
        return array('return'=>!$err);
    }


    public function statSetOrderStatus($data)
    {
        global $db;
        $bill_no = $data->{tr('НомерЗаказа')};
        $state = trr($data->{tr('Статус')});

        $db->Query("update newbills set state_1c='".addcslashes($state,"\\'")."' where bill_no='".addcslashes($bill_no, "\\'")."'");
        return array('return'=>!mysql_errno());
    }

    public function statSaveBrend($data)
    {
        global $db;
        $code = $data->{tr('Производитель')}->{tr('Код')};
        $name = trr($data->{tr('Производитель')}->{tr('Наименование')});
        $isDel = $data->{tr('Производитель')}->{tr('Удален')};

        if($isDel)
        {
            $db->Query("delete from g_producers where id = '".$code."'");
        }else{
            $db->Query("insert into g_producers set id='".$code."', name='".mysql_escape_string($name)."'
                        on duplicate key update name='".mysql_escape_string($name)."'");
        }
        return array('return'=>!mysql_errno());
    }

    public function statSavePriceType($data)
    {
        global $db;

        $code = $data->{tr('ВидЦен')}->{tr('Код1С')};
        $name = trr($data->{tr('ВидЦен')}->{tr('Наименование')});
        $isDel = $data->{tr('ВидЦен')}->{tr('Удален')};

        $db->QueryDelete("g_price_type", array("id" => $code));
        if(!$isDel)
        {
            $db->QueryInsert("g_price_type", array(
                    "id"=>$code,
                    "name"=>$name)
            );
        }
        $err = mysql_errno();
        if($err) {
            return new \SoapFault('statSavePriceType',tr('ошибка создания типа цены: '.mysql_error()));
        }
        return array('return'=>true);
    }

    public function statSaveUnit($data)
    {
        global $db;

        $code = $data->{tr('ЕдиницаИзмерения')}->{tr('Код1С')};
        $okei = $data->{tr('ЕдиницаИзмерения')}->{tr('Код')};
        $name = trr($data->{tr('ЕдиницаИзмерения')}->{tr('Наименование')});
        $isDel = $data->{tr('ЕдиницаИзмерения')}->{tr('Удален')};

        $db->QueryDelete("g_unit", array("id" => $code));
        if(!$isDel)
        {
            $db->QueryInsert("g_unit", array(
                    "id"=>$code,
                    "name"=>$name,
                    "okei"=>$okei
                    )
            );
        }
        $err = mysql_errno();
        if($err) {
            return new \SoapFault('statSaveUnit',tr('ошибка создания Единица Измерения: '.mysql_error()));
        }
        return array('return'=>true);
    }

    public function statSavePriceList($data)
    {
        global $db;

        //return new \SoapFault('statSavePriceList', "test error");
        //file_put_contents("/tmp/statSavePriceList_test", var_export($data,true));

        if(!isset($data->{\_1c\tr('ЦеныТовара')}))
            return new \SoapFault('statSavePriceList',\_1c\tr('неожидаемые данные'));
        if(!isset($data->{\_1c\tr('ЦеныТовара')}->{\_1c\tr('Список')}))
            return array('return'=>true);


        $l = $data->{\_1c\tr('ЦеныТовара')}->{\_1c\tr('Список')};
        if(!is_array($l))
            $l = array($l);

        $d = array();
        foreach($l as $i){
            $goodId = $i->{tr('КодНоменклатура1С')};
            $descrId = $i->{\_1c\tr('КодХарактеристика1С')};
            $priceId = $i->{\_1c\tr('КодВидЦен1С')};
            $cost = $i->{\_1c\tr('Цена')};
            $isDel  = $i->{\_1c\tr('Удален')};

            if(!isset($d[$goodId])) $d[$goodId] = array();
            if(!isset($d[$goodId][$descrId])) $d[$goodId][$descrId] = array();
            if(!isset($d[$goodId][$descrId][$priceId])) $d[$goodId][$descrId][$priceId] = array();

            $d[$goodId][$descrId][$priceId] = $isDel ? false : $cost;
        }
        unset($i);

        $s = array();
        foreach($d as $goodId => &$goods) {
            foreach($goods as $descrId => &$descrs) {
                foreach($descrs as $priceId => $cost) {
                    $db->QueryDelete("g_good_price", array("good_id" => $goodId, "descr_id" => $descrId, "price_type_id" => $priceId));
                    if($cost !== false){
                        $s[] = "('".$goodId."', '".$descrId."', '".$priceId."','".$cost."')";

                        if(count($s) >= 1000){
                            $db->Query("insert into g_good_price value ".implode(",", $s));
                            if(mysql_errno()) return new \SoapFault('statSavePriceList', mysql_error());
                            $s = array();
                        }
                    }
                }
            }
        }
        if($s)
            $db->Query("insert into g_good_price value ".implode(",", $s));
        if(mysql_errno()) {
            return new \SoapFault('statSavePriceList', mysql_error());
        }

        return array('return'=>true);
    }


    public function statSaveStoreBalance($data)
    {
        global $db;

        /*
        if(!defined("save_sql"))
            define("save_sql",1);
            */

        file_put_contents("/tmp/statSaveStoreBalance", var_export($data, true));

        //return array('return'=>true);

        if(!(
            isset($data->{tr('ОстаткиТовара')}) &&
            isset($data->{tr('ОстаткиТовара')}->{tr('Список')})))
            return new \SoapFault('statSaveStoreBalance',tr('неожидаемые данные'));

        $l = $data->{tr('ОстаткиТовара')}->{tr('Список')};
        if(!is_array($l)) {
            $l = array($l);
        }

        $d = array();
        foreach($l as $i){
            $goodId = $i->{tr('КодНоменклатура1С')};
            $storeId = $i->{tr('КодСклад1С')};
            $descrId = $i->{\_1c\tr('КодХарактеристика1С')};
            $qty = array($i->{\_1c\tr('КоличествоДоступно')},
                $i->{\_1c\tr('КоличествоНаСкладе')},
                $i->{\_1c\tr('КоличествоОжидается')});
            $isDel  = $i->{\_1c\tr('Удален')};

            if(!isset($d[$goodId])) $d[$goodId] = array();
            if(!isset($d[$goodId][$descrId])) $d[$goodId][$descrId] = array();
            if(!isset($d[$goodId][$descrId][$storeId])) $d[$goodId][$descrId][$storeId] = array();

            $d[$goodId][$descrId][$storeId] = array("qty" => $qty, "is_del" => $isDel);
        }

        $s = array();
        foreach($d as $goodId => &$goods) {
            foreach($goods as $descrId => &$ss) {
                foreach($ss as $storeId => $q){
                    /*
                                        $db->QueryDelete("g_good_store",
                                                array(
                                                    "good_id" => $goodId,
                                                    "descr_id" => $descrId,
                                                    "store_id" => $storeId
                                                    )
                                                );
                                        */

                    if(!$q["id_del"])
                        $s[] = "('".$goodId."', '".$descrId."', '".$q["qty"][0]."', '".$q["qty"][1]."', '".$q["qty"][2]."','".$storeId."')";
                }
            }
        }


        if($s)
        {
            $db->Query("delete from g_good_store where good_id in ('".implode("','", array_keys($d))."')");
            $db->Query("insert into g_good_store values ".implode(",", $s));
        }

        if(mysql_errno()) {
            return new \SoapFault('statSaveStoreBalance', mysql_error());
        }

        return array('return'=>true);

    }

    public function statSaveGood($data)
    {
        file_put_contents("/tmp/statSaveGood", var_export($data, true));

        global $db;

        $f= array(
            "id" => "Код1С",
            "num_id" => "Код",
            "name" => "Наименование",
            "name_full" => "НаименованиеПолное",
            "art" => "Артикул",
            "price" => "Цена",
            "quantity" => "Количество",
            "quantity_store" => "КоличествоНаСкладе",
            "producer_id" =>  "Производитель",
            "description" => "Описание",
            "is_service" => "ЭтоУслуга",
            "group_id" => "КодГруппы",
            "is_allowpricezero" => "РазрешитьПродажуПоНулевойЦене",
            "is_allowpricechange" => "РазрешитьПроизвольныеЦены",
            "division_id" => "ОтвПодразделение",
            "store" => "ТипЗапаса",
            "nds" => "СтавкаНДС",
            "unit_id" => "КодЕдиницыИзмерения"
        );
        $d = array();
        foreach($f as $field => $_1cname) {
            $d[$field] = trr($data->{tr("Товар")}->{tr($_1cname)});
        }

        switch($d["store"])
        {
            case "Наш склад": $v = "yes";break;
            case "Дальний склад": $v = "remote"; break;
            case "Под заказ": $v = "no"; break;
            default: $v= "";
        }
        $d["store"] = $v;

        $this->GetDivisionId($d["division_id"]);

        $db->QueryDelete("g_goods", array("id" => $d["id"]));
        $db->QueryDelete("g_good_description", array("good_id" => $d["id"]));
        $db->QueryDelete("g_bonus", array("good_id" => $d["id"]));
        $db->QueryInsert("g_goods", $d);

        $err = mysql_errno();
        if($err)
        {
            return new \SoapFault('goods',tr('ошибка создания товара: '.$err));
        }

        if(isset($data->{tr("Товар")}->{tr("СписокХарактеристик")})) {
            $hs = &$data->{tr("Товар")}->{tr("СписокХарактеристик")};
            if(!is_array($hs)) {
                $hs = array($hs);
            }

            foreach($hs as $h){
                $dscr = array(
                    "id" => $h->{tr("Код1С")},
                    "good_id" => $h->{tr("КодНоменклатура1С")},
                    "name" => \_1c\trr($h->{tr("Наименование")})
                );

                $db->QueryInsert("g_good_description", $dscr);
                $err = mysql_errno();
                if($err)
                {
                    return new \SoapFault('goods',tr('ошибка создания характеристики товара: '.mysql_error()));
                }
            }
        }

        if(isset($data->{tr("Товар")}->{tr("СписокБонусов")})) {
            $bs = &$data->{tr("Товар")}->{tr("СписокБонусов")};
            if(!is_array($bs)) {
                $bs = array($bs);
            }


            foreach($bs as $b){

                /*
                stdClass::__set_state(array(
                            'ГруппаПользователей' => 'Менеджер',
                            'ТипВознаграждения' => 'Фиксированное',
                            'Вознаграждение' => '10',
                            )),
                            stdClass::__set_state(array(
                            'ГруппаПользователей' => 'Маркетинг',
                            'ТипВознаграждения' => 'Процент',
                            'Вознаграждение' => '20',
                            )),
                                                             */

                $group = "";
                switch(trr($b->{tr("ГруппаПользователей")})){
                    case 'Менеджер': $group = "manager"; break;
                    case 'Маркетинг': $group = "marketing"; break;
                }
                if(!$group) continue;

                $type = trr($b->{tr("ТипВознаграждения")}) == "Фиксированное" ? "fix" : "%";

                $db->QueryInsert("g_bonus", array(
                        "good_id" => $d["id"],
                        "group" => $group,
                        "type" => $type,
                        "value" => $b->{tr("Вознаграждение")}
                    )
                );
            }

        }

        return array('return'=>true);
    }

    private function GetDivisionId(&$d)
    {
        global $db;

        if(!$d) {$d = 0; return;}

        $r = $db->GetRow("select id from g_division where name = '".$d."'");
        if($r)
        {
            $d = $r["id"];
            return;
        }
        $db->Query("insert into g_division set name = '".mysql_escape_string($d)."'");
        $d = $db->GetInsertId();
        return;
    }

    public function statSaveGoodGroup($data)
    {
        global $db;

        $code = $data->{tr('ГруппаТовара')}->{tr('Код')};
        $name = trr($data->{tr('ГруппаТовара')}->{tr('Наименование')});
        $parentCode = $data->{tr('ГруппаТовара')}->{tr('КодГруппы')};


        $db->Query("insert into g_groups set id='".$code."', name='".mysql_escape_string($name)."', parent_id = '".$parentCode."'
                    on duplicate key update name='".mysql_escape_string($name)."', parent_id = '".$parentCode."'");

        return array('return'=>!mysql_errno());
    }
}

class server{
    public function __construct($wsdl,$login=null,$pass=null){
        $this->soap = new \SoapServer($wsdl,array('encoding'=>'UTF-8'));
        $sH = new SoapHandler();
        $this->soap->setObject($sH);
    }

    public function __call($method,$args){
        return call_user_func_array(array($this->soap,$method), $args);
    }
}

function checkLogisticItems(&$items_list, &$add_info, $from1cInt = true)
{
    $key = $from1cInt ? "item_id" : "id";

    $aLogisticType =
        array(
            "6b6709aa-8a8b-11df-866d-001517456eb1" => array("Доставка авто", "auto"),
            "132c7b5d-8a6c-11df-866d-001517456eb1" => array("Доставка ТК", "tk"),
            "132c7b5b-8a6c-11df-866d-001517456eb1" => array("Доставка курьером", "courier")
        );

    $logistic = $from1cInt ? $add_info->{tr('Логистика')} : $add_info["logistic"];

    foreach($items_list as $i)
    {
        list($id, ) = explode(":", $i[$key]);
        if(in_array($id , array_keys($aLogisticType)))
        {
            if($from1cInt){
                $add_info->{tr('Логистика')} = tr($aLogisticType[$id][0]);
            }else{
                $add_info["logistic"] = $aLogisticType[$id][1];
            }
            return true;
        }
    }
    return false;
}
function getListDiff($billLines, $list)
{
    $diff = array();
    if($billLines && $list){
        foreach($billLines as $bl){
            $found = false;
            foreach($list as &$l){
                // diff line
                if($l["code_1c"] == $bl["code_1c"]){
                    $fields = array();
                    foreach(array("amount", "dispatch", "item") as $f){
                        if($f == "amount") {$bl[$f] = (float)$bl[$f]; $l[$f] = (float)$l[$f];}
                        if($l[$f] != $bl[$f])
                            $fields[] = array("field" => $f, "from" => $bl[$f], "to" => $l[$f]);
                    }

                    if($fields)
                        $diff[] = array("action" => "change", "code_1c" => $bl["code_1c"], "item" => $l["item"], "fields" => $fields);

                    $l["checked"] = true;
                    $found = true;
                    break ;
                }
            }
            //line deleted
            if(!isset($l["checked"])){
                $diff[] = array("action" => "delete", "code_1c" => $bl["code_1c"], "item" => $bl["item"]);
            }
        }
        unset($l);
        foreach($list as &$l){
            // line added
            if(!isset($l["checked"])){
                $diff[] = array("action" => "add", "code_1c" => $l["code_1c"], "item" => $l["item"]);
            }
        }
    }
    return $diff;
}

function saveListDiff($bill_no, $stage_id, &$diff)
{
    global $db;
    foreach($diff as $d){
        $insId = $db->QueryInsert("newbill_change_log",
            array(
                "bill_no" => $bill_no,
                "stage_id" => $stage_id,
                "action" => $d["action"],
                "code_1c" => $d["code_1c"],
                "item" => $d["item"],
                "date" => date("Y-m-d H:i:s")
            )
        );

        if($d["action"] == "change"){
            foreach($d["fields"] as $f){
                $db->QueryInsert("newbill_change_log_fields",
                    array(
                        "change_id" => $insId,
                        "field" => $f["field"],
                        "from" => $f["from"],
                        "to" => $f["to"]
                    )
                );
            }
        }
    }
}
