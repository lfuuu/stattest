<?php

use \app\models\Contract;
use \app\models\ClientContract;

class m_tarifs{
    var $actions=array(
            'default'             => array('tarifs','read'),
            'view'                => array('tarifs','read'),
            'edit'                => array('tarifs','read'),
            'delete'              => array('tarifs','edit'),
            'itpark'              => array('services_itpark','full'),
            'welltime'            => array('services_welltime','full'),
            'wellsystem'          => array('services_wellsystem','full'),
            'contracts'           => array('tarifs','read'),
            'voip'                => array('tarifs','read'),
            'voip_edit'           => array('tarifs','edit'),
            'price_tel'           => array('tarifs','edit'),
            'virtpbx'             => array('tarifs','edit'),
            'sms'                 => array('tarifs','edit'),
        );

    var $menu=array(
            array('IP-телефония',            'voip'),
            array('Интернет',                'view','&m=internet'),
            array('Collocation',            'view','&m=collocation'),
            array('VPN',                    'view','&m=vpn'),
//            array('Хостинг',                'view','&m=hosting'),
//             array('Междугородняя связь',    'view','&m=russia'),
//             array('Международная связь',    'view','&m=world'),
            array('Дополнительные услуги',    'view','&m=extra'),
            //array('IT Park',                'view','&m=itpark'),
            array('IT Park',                'itpark',''),
            array('Welltime',                'welltime',''),
            array('Виртуальная АТС',        'virtpbx',''),
            array('СМС',                   'sms',''),
            array('WellSystem',                'wellsystem',''),
//            array('Старые доп.услуги',        'view','&m=add'),
            array('Договора',            'contracts',''),
            array('Договор-Прайс-Телефония',            'price_tel',''),
        );
    function m_tarifs(){
    }

    function GetPanel($fixclient){
        $R=array(); $p=0;
        foreach($this->menu as $val){
            if ($val=='') {
                $p++;
                $R[]='';
            } else {
                $act=$this->actions[$val[1]];
                if (access($act[0],$act[1])) $R[]=array($val[0],'module=tarifs&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
            }
        }
        if (count($R)>$p){
            return array('Тарифы',$R);
        }
    }

    function GetMain($action,$fixclient){
        global $design,$db,$user;
        if (!isset($this->actions[$action])) return;
        $act=$this->actions[$action];
        if (!access($act[0],$act[1])) return;
        call_user_func(array($this,'tarifs_'.$action),$fixclient);
    }
    function tarifs_default(){
        $this->tarifs_voip();    
    }
    function GetTableFilter($m) {
        $p=''; $q=''; 
        if ($m=='internet') {$p='internet'; $q='i';}
        elseif ($m=='vpn') {$p='internet'; $q='v';}
        elseif ($m=='collocation') {$p='internet'; $q='c';}
        elseif ($m=='hosting') {$p='hosting'; $q='z';}
//         elseif ($m=='russia') {$p='price_voip'; $q='r';}
//         elseif ($m=='world') {$p='price_voip'; $q='w';}
        elseif ($m=='extra') {$p='extra'; $q='z';}
        elseif ($m=='itpark') {$p='itpark'; $q='z';}
        elseif ($m=='welltime') {$p='welltime'; $q='z';}
        elseif ($m=='virtpbx') {$p='virtpbx'; $q='z';}
        elseif ($m=='wellsystem') {$p='wellsystem'; $q='z';}
        elseif ($m=='add') {$p='bill_monthlyadd_reference'; $q='z';}
        elseif ($m=='voip') {$p='voip'; $q='z';}
        elseif ($m=='sms') {$p='sms'; $q='z';}
        else return false;
        return array($p,$q);
    }
    function tarifs_edit(){
        global $db,$design;
        if(
            !(
                $v = $this->GetTableFilter( $m=get_param_raw('m',''))
            )
        ) return;

        include INCLUDE_PATH.'db_view.php';
        $view = DbViewFactory::Get($v[0]);
        $dbf = DbViewFactory::GetForm($v[0],$v[1]);

        if(
            ($id=get_param_integer('id'))
        &&
            !($dbf->Load($id))
        )return;

        if( ($dbf->Process()) == "add"){
            $db->QueryUpdate("price_voip", "id", array("id" => $dbf->data["id"], "idExt" => $dbf->data["id"]));
        }
        if(!isset($_SESSION['trash']) || !is_array($_SESSION['trash']))
            $_SESSION['trash'] = array();
        $_SESSION['trash']['price_voip'] = 1;

        $dbf->Display(
            array(
                'module'=>'tarifs',
                'action'=>'edit',
                'm'=>$m,
                'id'=>$id
            ),
            $view->Headers[$view->fieldset],
            $id ?'Редактирование' :'Добавление'
        );
    }
    function tarifs_view(){
        global $db,$design;
        $m=get_param_raw('m','');
        $v=$this->GetTableFilter($m);

        if(!$v)
            return;

        include INCLUDE_PATH.'db_view.php';
        $view=DbViewFactory::Get($v[0]);
        $view->SetFilters(get_param_raw('filter'));
        $view->SetFieldSet($v[1]);

        $view->Display('module=tarifs&action=view&m='.$m,'module=tarifs&action=edit&m='.$m);
    }
    function tarifs_voip(){
        global $db, $pg_db, $design;

        $f_region = get_param_integer('f_region', '99');
        $f_dest = get_param_protected('f_dest', '');
        $f_currency = get_param_protected('f_currency', 'RUB');
        $f_show_archive = get_param_integer('f_show_archive', 0);
        $design->assign('f_region',$f_region);
        $design->assign('f_dest',$f_dest);
        $design->assign('f_currency',$f_currency);
        $design->assign('f_show_archive',$f_show_archive);

        $where = 'where t.region='.(int)$f_region;
        if ($f_dest != '')
            $where .= ' and t.dest='.(int)$f_dest;
        $where .= " and t.currency='$f_currency'";
        if ($f_show_archive == 0)
          $where .= ' and t.status!="archive"';


        $res = $db->AllRecords("select t.* from tarifs_voip t ".$where.' order by case t.dest >= 4 when true then t.dest else t.dest + 10 end, name');
        $tarifs_by_dest = array();
        foreach ($res as $r) {
            $tarifs_by_dest[$r['dest']][] = $r;
        }

        $design->assign('tarifs_by_dest',$tarifs_by_dest);
        $design->assign('regions',$db->AllRecords("select * from regions",'id'));
        $design->assign('pricelists', $pg_db->AllRecords("select p.id, p.name from voip.pricelist p", 'id'));
        $design->assign('dests',array('4'=>'Местные Стационарные','5'=>'Местные Мобильные','1'=>'Россия','2'=>'Международка','3'=>'СНГ'));
        $design->AddMain('tarifs/voip_list.tpl');
    }
    function tarifs_voip_edit(){
        global $db, $pg_db, $design, $user;
        $id = get_param_integer('id', 0);

        if ($_POST){
            $data['name'] = $_POST['name'];
            $data['name_short'] = $_POST['name_short'];
            $data['status'] = $_POST['status'];
            $data['month_line'] = (int)$_POST['month_line'];
            $data['month_number'] = (int)$_POST['month_number'];
            $data['month_min_payment'] = (int)$_POST['month_min_payment'];
            $data['once_line'] = (int)$_POST['once_line'];
            $data['once_number'] = (int)$_POST['once_number'];
            $data['free_local_min'] = (int)$_POST['free_local_min'];
            $data['freemin_for_number'] = (get_param_integer('freemin_for_number', 0) > 0 ? 1 : 0);
            $data['pricelist_id'] = (int)$_POST['pricelist_id'];
            $data['paid_redirect'] = (get_param_integer('paid_redirect', 0) > 0 ? 1 : 0);
            $data['tariffication_by_minutes'] = (get_param_integer('tariffication_by_minutes', 0) > 0 ? 1 : 0);
            $data['tariffication_full_first_minute'] = (get_param_integer('tariffication_full_first_minute', 0) > 0 ? 1 : 0);
            $data['tariffication_free_first_seconds'] = (get_param_integer('tariffication_free_first_seconds', 0) > 0 ? 1 : 0);
            $data['is_virtual'] = (get_param_integer('is_virtual', 0) > 0 ? 1 : 0);
            $data['is_testing'] = (get_param_integer('is_testing', 0) > 0 ? 1 : 0);
            $data['edit_user'] = $user->Get('id');
            $data['edit_time'] = date('Y.m.d H:i:s');
            $data['id'] = $id;
            if ($data['id']=='0'){
                $data['region'] = (int)$_POST['region'];
                $data['dest'] = (int)$_POST['dest'];
                $data['currency'] = $_POST['currency'];
                $id = $db->QueryInsert('tarifs_voip', $data);
            }else{
                $db->QueryUpdate('tarifs_voip', 'id', $data);
            }
            //header('location: index.php?module=tarifs&action=voip_edit&id='.$id);
            header('location: index.php?module=tarifs&action=voip&f_region='.$_POST['region']);
            exit;
        }

        $data = $db->AllRecords("select t.*, u.name as user from tarifs_voip t left join user_users u on u.id=t.edit_user where t.id=".$id);
        if (count($data) == 0) if ($id != 0) die('tarif not found');
        if (count($data) == 0){
            $data = array('id'=>0,'month_line'=>0,'month_number'=>0,'month_min_payment'=>0,'once_line'=>0,'once_number'=>0,'free_local_min'=>0,'freemin_for_number'=>1,'paid_redirect'=>1);
        }else{
            $data = $data[0];
        }



        $design->assign('data',$data);
        $design->assign('regions',$db->AllRecords("select * from regions",'id'));
        $design->assign('pricelists',$pg_db->AllRecords("select id, name from voip.pricelist where operator_id=999"));
        $design->assign('id',$id);
        $design->assign('dests',array('4'=>'Местные Стационарные','5'=>'Местные Мобильные','1'=>'Россия','2'=>'Международка','3'=>'СНГ'));
        $design->AddMain('tarifs/voip_edit.tpl');
    }
    function tarifs_delete(){
        global $db,$design;
        $m=get_param_raw('m','');
        $id=get_param_integer('id','');
        if (!$id) return;
        if (!in_array($m,$this->possible_params)) $m='internet';
        $db->Query('delete from '.$this->tables[$m].' where id='.$id);
        $this->tarifs_view();
    }

    function tarifs_contracts($fixclient){

        \app\assets\TinymceAsset::register(Yii::$app->view);

        global $design, $db, $user;;
        $templates = ClientContract::dao()->contract_listTemplates();
        $info = "";
        $contract_body = "";
        $isOpened = false;

        $group = get_param_raw("contract_template_group", "MCN");
        $contract = get_param_raw("contract_template", get_param_raw("contract_template_add", "default"));
        $contractType = get_param_raw("contract_type", "contract");

        $contract = preg_replace("/[^a-zA-Z0-9_]/", "", $contract);

        $name = ClientContract::dao()->contract_getFolder($group)."_".$contract;

        $filePath = STORE_PATH."contracts/template_".$name.".html";

        if(get_param_raw("new", "") == "true")
        {
            if(!$contract){
                trigger_error2("Имя не должно быть пустым. Только цифры, латинские буквы, и _");
                return;
            }else{
                if(file_put_contents($filePath, "договор ".$group.": ".$contract))
                {
                    $db->QueryInsert(
                        "log_contract_template_edit", 
                        array(
                            "group" => $group,
                            "contract" => $contract,
                            "user" => $user->Get("id"),
                            "action" => "new",
                            "date" => date("Y-m-d H:i:d"),
                            "length" => 0
                        )
                    );

                    $oContract = new Contract();
                    $oContract->name = $name;
                    $oContract->type = $contractType;
                    $oContract->save();
                }
                $templates = ClientContract::dao()->contract_listTemplates();
            }
        }


        if(get_param_raw("save_text", "") != "") {
            $contract_body_s = trim(get_param_raw("text", ""));

            if($contract_body_s)
            {
                $db->QueryInsert(
                    "log_contract_template_edit", 
                    array(
                        "group" => $group,
                        "contract" => $contract,
                        "user" => $user->Get("id"),
                        "action" => "save",
                        "date" => date("Y-m-d H:i:d"),
                        "length" => strlen($contract_body_s)
                    )
                );

                $oContract = Contract::findOne(["name" => $name]);
                if (!$oContract){
                    $oContract = new Contract();
                    $oContract->name = $name;
                }

                if ($oContract->type != $contractType)
                    $oContract->type = $contractType;

                $oContract->save();

                file_put_contents($filePath, $contract_body_s);
            }
        }


        if(get_param_raw("do", "") == "open") {
            $isOpened = true;

            $oContract = Contract::findOne(["name" => $name]);
            if ($oContract){
                $contractType = $oContract->type;
            } else {
                //default = "contract" (in get_param_raw set)
            }

            $contract_body = file_get_contents($filePath);

            $l =$db->GetRow("select u.name,l.* from user_users u, (SELECT * FROM `log_contract_template_edit` where `group` = '".$group."' and contract='".$contract."' order by date desc limit 1) l where l.user = u.id");

            if($l){
                $info = "Договор ".$group." ".$contract." сохранен ".$l["date"]." пользователем: ".$l["name"];
            }else{
                $info = "Договор ".$group." ".$contract." сохранен ".date("Y-m-d H:i:s", filemtime($filePath));
            }

        }

        $design->assign("contract_body", $contract_body);
        $design->assign("is_opened", $isOpened);
        $design->assign("info", $info);
        $design->assign("contract_template_group", $group);
        $design->assign("contract_template", $contract);
        $design->assign("contract_type", $contractType);
        $design->assign("contract_types", [
            'contract' => 'Договор',
            'agreement' => 'Дополнительное соглашение',
            'blank' => 'Бланк заказа'
        ]);

        $design->assign("templates", $templates);
        $design->AddMain("tarifs/contract.tpl");
    }

    function tarifs_itpark(){
        Header('Location: ?module=tarifs&action=view&m=itpark');
        exit();
    }

    function tarifs_welltime(){
        Header('Location: ?module=tarifs&action=view&m=welltime');
        exit();
    }

    function tarifs_wellsystem(){
        Header('Location: ?module=tarifs&action=view&m=wellsystem');
        exit();
    }

    function get_ranges($n1,$n2){
        $nl = "<br />";
        if(strlen($n1) <> strlen($n2))
            return false;
        if((int)$n1 > (int)$n2){
            $buf = $n1;
            $n1 = $n2;
            $n2 = $buf;
            unset($buf);
        }
        $a1 = str_split($n1);
        $a2 = str_split($n2);

        $ac = 0;
        for($i=count($a1)-1 ; $i>=0 ; $i--){
            if($a1[$i]==0 && $a2[$i]==9){
                $ac++;
            }else
                break;
        }

        if($ac == count($a1))
            return array('');
        $pref1 = substr($n1,0,strlen($n1)-$ac);
        $pref2 = substr($n2,0,strlen($n2)-$ac);

        $prefses = array();
        $pref1_zero = 0;
        $pref2_zero = 0;
        $pa1 = str_split($pref1);
        $pa2 = str_split($pref2);
        for($i=0 ; $i<count($pa1) ; $i++){
            if($pa1[$i] == 0)
                $pref1_zero++;
            else
                break;
        }
        for($i=0 ; $i<count($pa2) ; $i++){
            if($pa2[$i]==0)
                $pref2_zero++;
            else
                break;
        }

        $pref_zero = '';
        for($i=0 ; $i<$pref2_zero ; $i++)
            $pref_zero .= '0';

        if($ac+$pref2_zero == strlen($n1))
            return array($pref_zero);

        $pref1 = substr($pref1,$pref2_zero);
        $pref2 = substr($pref2,$pref2_zero);

        for($i=(int)$pref1 ; $i<=(int)$pref2 ; $i++){
            if(strlen($i)<strlen($pref1)){
                $len = strlen($pref1)-strlen($i);
                for($j=0 ; $j<$len ; $j++)
                    $i = (String)'0'.$i;
            }
            $prefses[] = (string)$pref_zero.$i;
        }

        $max_len = 0;
        foreach($prefses as $pref){
            if(strlen($pref)>$max_len)
                $max_len = strlen($pref);
        }

        for($xp=0 ; $xp<(int)$max_len ; $xp++){
            $last_gen = array();
            foreach($prefses as $pref){
                $midp = substr($pref,0,strlen($pref)-1);

                if(in_array((String)$midp,$last_gen,true))
                    continue;

                $flag = true;
                for($i=0 ; $i<10 ; $i++){
                    if(!in_array((String)$midp.$i,$prefses,true)){
                        $flag = false;
                        break;
                    }
                }

                if($flag){
                    $last_gen[] = (String)$midp;
                }else{
                    $last_gen[] = (String)$pref;
                }
            }
            $prefses = $last_gen;
        }
        return $prefses;
    }


    function tarifs_price_tel()
    {
        if(get_param_raw("gen", "") == "true") {
            PriceTel::gen();
        }

        if(get_param_raw("save", "") == "true") {
            PriceTel::save();
        }

        PriceTel::view();
    }

    function tarifs_virtpbx()
    {
        Header('Location: ?module=tarifs&action=view&m=virtpbx');
        exit();
    }

    function tarifs_sms()
    {
        Header('Location: ?module=tarifs&action=view&m=sms');
        exit();
    }
}

class PriceTel
{
    public static function view()
    {
        global $design, $db;

        $data = array(
                "990" => array("city" => "Москва (старый прайс)", "time" => false),
                "991" => array("city" => "Присоединение сетей", "time" => false),
                );

        foreach($db->AllRecords('select id, name from regions order by id desc', 'id') as $r)
            $data[$r["id"]] = array("city" => $r["name"], "time" => false);
            


        foreach (glob(STORE_PATH.'contracts/region_*.html') as $s) {
            if(preg_match("/\d+/", $s, $o))
            {
                $region = $o[0];
                $data[$region]["time"] = date("Y-m-d H:i", filemtime($s));
            }
        }

        $design->assign("data", $data);
        $design->AddMain('tarifs/price_tel.htm');
    }

    public static function gen()
    {
        global $design;

        $region = get_param_integer("region", 0);

        if(!$region) 
            die("Ошибка");

        $pp = array();
        foreach(array(5,4,3,2,1) as $p)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://stat.mcn.ru/operator/get_prices.php?region=$region&dest=$p");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \yii\base\Exception($error);
            }
            curl_close($ch);

            $pp[$p] = explode("\n", $result);

            //file_put_contents("/tmp/a".$p.".dat", serialize($pp[$p]));
            //$pp[$p] = unserialize(file_get_contents("/tmp/a".$p.".dat"));
        }

        $d = array();

        if($region != 990 && $region != 991)
        {
            self::__addTitle("Местные стационарные", $d);
            self::__parsePrice($pp[4], $d);
            self::__addTitle("Местные мобильные", $d);
            self::__parsePrice($pp[5], $d);
        }
        self::__addTitle("Россия", $d);
        self::__parsePrice($pp[1], $d);
        self::__addTitle("Ближнее зарубежье", $d);
        self::__parsePrice($pp[3], $d);
        self::__addTitle("Дальнее зарубежье", $d);
        self::__parsePrice($pp[2], $d);

        $design->assign("d", $d);
        $design->assign("region", $region);

        echo ($region == 991) ? $design->display("tarifs/price_tel__gen991.htm") : $design->display("tarifs/price_tel__gen.htm");
        exit();

    }

    static function save()
    {
        $region = get_param_integer("region", 0);
        if(!$region)
        {
            echo "Ошибка";
            exit();
        }


        if(file_put_contents(STORE_PATH."contracts/region_".$region.".html", $_POST["html"]))
        {
            echo "ok";
        }else{
            echo "Ошибка сохранения";
        }
        exit();
    }

    static function __addTitle($title, &$d)
    {
        $d[] = array("type" => "title", "title" => $title);
    }

    static function __parsePrice(&$cc, &$d)
    {
        foreach($cc as $idx => $c)
        {
            if($idx == 0) continue;

            $c = trim($c);
            if ($c == '') continue;

            $aa = explode(";", $c);
            foreach($aa as &$a)
            {
                $a = str_replace("\"", "", $a);
            }

            $d[] = array(
                    "type" => "price",
                    "code1" => $aa[0],
                    "code2" => $aa[1],
                    "name" => $aa[2],
                    "zone" => $aa[3],
                    "price1" => $aa[4],
                    "price2" => $aa[5],
                    "price3" => $aa[6],
                    );

        }
    }
}
?>
