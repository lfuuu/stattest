<?php
namespace app\dao;

use Yii;
use app\classes\Assert;
use app\classes\Company;
use app\classes\Singleton;
use app\classes\BillContract;
use app\models\Contract;
use app\models\ClientContract;
use app\models\ClientAccount;

class ClientContractDao extends Singleton
{
    private $design = null;

    public function addContract(
        $accountId, 
        $contractType, $contractGroup, $contractTemplate,
        $_contractNo,  $_contractDate, 
        $content,      $comment, 
        $userId = null
    )
    {
        Assert::isNotFalse(in_array($contractType, ["contract", "blank", "agreement"]));

        $group = $this->contract_getFolder($contractGroup);

        if(!$content)
            $content = $this->getTemplate('template_'.$group."_".$contractTemplate);

        $lastContract = BillContract::getLastContract($accountId, time());

        $contractNo = $lastContract["no"];
        $contractDate = date("d.m.Y", $lastContract["date"]);
        $contractDopDate = "01.01.2012";
        $contractDopNo = "0";

        if ($contractType == "contract")
        {
            $contractDate = $_contractDate;
            $contractNo = $_contractNo;
        } else {

            if ($contractType == "agreement")
            {
                $contractDopNo = $_contractNo;
                $contractDopDate = $_contractDate;

                $lastContract = BillContract::getLastContract($accountId, (strtotime($contractDopDate) ?: time()));

                $contractNo = $lastContract["no"];
                $contractDate = date("d.m.Y", $lastContract["date"]);
            } else { //blank
                $contractDopDate = date("d.m.Y");
            }
        }

        list($d, $m, $y) = explode(".", $contractDate);
        $contractDate = $y."-".$m."-".$d;

        list($d, $m, $y) = explode(".", $contractDopDate);
        $contractDopDate = $y."-".$m."-".$d;

        $contractId = $this->saveContract(
            $accountId,
            $content,
            $contractType,
            $contractNo,
            $contractDate,
            $contractDopNo,
            $contractDopDate,
            $comment,
            $userId
        );

        $this->fix_contract($accountId, $contractId, $contractDate);

        return $contractId;
    }

    public function saveContract(
        $accountId, 
        $content, $type, 
        $no, 
        $date, $dop_no, $dop_date, 
        $comment,
        $userId = null
    )
    {
        if(!$no)
            $no = $accountId.'-'.date('y');

        //save in DB
        $c = new ClientContract;
        $c->type = $type;
        $c->contract_no = $no;
        $c->contract_date = trim($date);
        $c->contract_dop_no = $dop_no;
        $c->contract_dop_date = trim($dop_date);
        $c->ts = (new \DateTime())->format(\DateTime::ATOM);
        $c->client_id = $accountId;
        $c->comment = $comment;
        $c->user_id = $userId ?: Yii::$app->user->getId();
        $c->save();

        $cno = $c->id;


        //save content
        $contractFileName = $accountId.'-'.$cno;
        $contractFileName = preg_replace('[^\w\d\-\_]','',$contractFileName);
        file_put_contents(Yii::$app->params['STORE_PATH'].'contracts/'.$contractFileName.'.html', $content);


        return $cno;
    }

    public function contract_getFolder($folder = null)
    {
        $f = array(
                "MCN" => "mcn",
                "MCN-СПб" => "mcn98",
                "MCN-Краснодар" => "mcn97",
                "MCN-Самара" => "mcn96",
                "MCN-Екатеринбург" => "mcn95",
                "MCN-Новосибирск" => "mcn94",
                "MCN-Ростов-на-Дону" => "mcn87",
                "MCN-НижнийНовгород" => "mcn88",
                "MCN-Казань" => "mcn93",
                "MCN-Владивосток" => "mcn89",
                "WellTime" => "welltime",
                "IT-Park" => "itpark",
                "Arhiv" => "arhiv"
                );

        return $folder === null ? $f : $f[$folder];
    }

    public function contract_listTemplates($isWithType = false) {
        $R = array();
        foreach (glob(Yii::$app->params['STORE_PATH'].'contracts/template_*.html') as $s) {
            $t = str_replace(array('template_','.html'),array('',''),basename($s));

            list($group,) = explode("_", $t);

            if ($isWithType)
            {
                $R[$group][] = $t;
            } else {
                $R[$group][] = substr($t, strlen($group)+1);
            }
        }

        foreach($this->contract_getFolder() as $folderName => $key )
            $_R[$folderName] = isset($R[$key]) ? $R[$key] : array();

        if ($isWithType)
        {
            $R = ["contract" => [], "blank" => [], "agreement" => []];

            foreach ($_R as $folder => $rr)
            {
                foreach($rr as $k => $r)
                {
                    $contract = Contract::findOne(["name" => $r]);

                    if ($contract)
                    {
                        $type = $contract->type;
                    } else {
                        $type = "contract";
                    }
                    list($group,) = explode("_", $r);
                    $R[$type][$folder][] = substr($r, strlen($group)+1);
                }
            }
        } else {
            $R = $_R;
        }

        return $R;
    }

    public function getTemplate($name) 
    {
        $name = preg_replace('[^\w\d\-\_]','',$name);

        if (file_exists(Yii::$app->params['STORE_PATH'].'contracts/'.$name.'.html')) 
        {
            $data = file_get_contents(Yii::$app->params['STORE_PATH'].'contracts/'.$name.'.html');
        } else {
            $data = file_get_contents(Yii::$app->params['STORE_PATH'].'contracts/template_mcn_default.html');
        }

        $this->fix_style($data);

        return $data;
    }

	private function fix_contract($clientId, $contractId, $contractDate)
    {
		$file = 'contracts/'.$clientId.'-'.$contractId.'.html';
		$fileTemplate = 'contracts/'.$clientId.'-'.$contractId.'-tpl.html';

		if(file_exists(Yii::$app->params['STORE_PATH'].$fileTemplate)) //already
            return true;

        $c = ClientContract::findOne($contractId)->toArray();
		if (!$c) {
			trigger_error2('Такого договора не существует');
			return;
        }

        $r = ClientAccount::dao()->getAccountPropertyOnDate($clientId, $c["contract_date"]);
		
        if (!$r) {
			trigger_error2('Такого клиента не существует');
			return;
        }

        $c["contract_dop_date"] = strtotime($c["contract_dop_date"]);
        $c["contract_date"] = strtotime($c["contract_date"]);


        $this->design = \app\classes\Smarty::init();
        $this->design->assign("client", $r);
        $this->design->assign("contract", $c);


		$content = $this->contract_fix_static_parts_of_template(file_get_contents(Yii::$app->params['STORE_PATH'].$file), $clientId);
		$this->contract_apply_firma($r["firma"], $contractDate);
        $this->contract_apply_support_phone($r["region"]);

        file_put_contents(Yii::$app->params['STORE_PATH'].$file, $content);//шаманство...

        $c = $this->design->fetch(Yii::$app->params['STORE_PATH'].$file);

		if(copy(Yii::$app->params['STORE_PATH'].$file, Yii::$app->params['STORE_PATH'].$fileTemplate))
		{
			file_put_contents(Yii::$app->params['STORE_PATH'].$file, $c);
			return true;
		}


		return false;
    }

    private function fix_style(&$content)
    {
        if(strpos($content, "{/literal}</style>") === false)
        {
            $content = preg_replace("/<style([^>]*)>(.*?)<\/style>/six", "<style\\1>{literal}\\2{/literal}</style>", $content);
        }
    }


    private function contract_fix_static_parts_of_template(&$content, $clientId=0)
    {
        if(($pos = strpos($content, "{\$include_")) !== false)
        {
        	$c = substr($content, $pos);
        	$templateName = substr($c, 10, strpos($c, "}")-10);

        	$fname =Yii::$app->params['STORE_PATH']."contracts/template_".$templateName.".html";

        	if(file_exists($fname))
        	{
        		$c = file_get_contents($fname);
        		$this->design->assign("include_".$templateName, $c);
        	}

        	$fname =Yii::$app->params['STORE_PATH']."contracts/".$templateName.".html";
        	if(file_exists($fname))
        	{
        		$c = file_get_contents($fname);
        		$this->design->assign("include_".$templateName, $c);
        	}
        }

        if (strpos($content, "{*#blank_zakaz#*}") !== false)
        {
            $content = str_replace("{*#blank_zakaz#*}", $this->makeBlankZakaz($clientId), $content);
        }


		if(strpos($content, '{*#voip_moscow_tarifs_mob#*}')!==false){
			$repl = '';
			// москва(моб.)
			$query = "
				select
					`destination_name`,
					`destination_prefix`,
					substring(`destination_prefix` from 2 for 3) `code`,
					`rate_RUB`
				from
					`price_voip`
				where
					`dgroup`=0
				and
					`dsubgroup`=0
				order by
					`destination_prefix`
			";
            foreach(ClientContract::getDB()->createCommand($query)->queryAll() as $row)
            {
				$repl .= "<tr>\n\t<td>".$row['destination_name']." - ".$row['code']."</td>\n\t<td>".$row['destination_prefix']."</td>\n\t<td width='30'>".$row['rate_RUB']."</td>\n</tr>";
			}
			$content = str_replace('{*#voip_moscow_tarifs_mob#*}', $repl, $content);
        }

        $this->fix_style($content);

		return $content;
    }

    private function contract_apply_firma($firma, $date = null)
    {
        $this->design->assign("firm_detail", Company::getDetail($firma, $date));
        $this->design->assign("firm", Company::getProperty($firma, $date));
    }

    private function contract_apply_support_phone($region)
    {
        switch($region)
        {
            case '97': $phone = "(861) 204-00-99"; break;
            case '98': $phone = "(812) 372-69-99"; break;
            case '95': $phone = "(343) 302-00-99"; break;
            case '94': $phone = "(383) 312-00-99"; break;
            case '96': $phone = "(846) 215-00-99"; break;
            case '87': $phone = "(863) 309-00-99"; break;
            case '93': $phone = "(843) 207-00-99"; break;
            case '88': $phone = "(831) 235-00-99"; break;
            case '99':
            default: 
                $phone = "(495) 105-99-95";
        }

        $this->design->assign("support_phone", $phone);
    }

    private function makeBlankZakaz($clientId)
    {
        $client = ClientAccount::findOne(["id" => $clientId])->client;

        $data = ['voip' => [], 'ip' => [], 'welltime' => [], 'vats' => [], 'sms' => [], 'extra' => []];


        foreach(\app\models\UsageVoip::find()->client($client)->actual()->all() as $a)
        {
            $data['voip'][] = [
                'from' => $a->actual_from,
                'description' => "Телефонный номер: " . $a->E164,
                'number' => $a->E164,
                'lines' => $a->no_of_lines,
                'free_local_min' => $a->currentTariff->free_local_min,
                'connect_price' => (string)$a->voipNumber->price,
                'tarif_name' => $a->currentTariff->name,
                'per_month' => round($a->currentTariff->month_number, 2),
                'per_month_with_tax' => round($a->currentTariff->month_number * 1.18, 2)
            ];
        }

        foreach(\app\models\UsageIpPorts::find()->client($client)->actual()->all() as $a)
        {
            $data['ip'][] = [
                'from' => $a->actual_from,
                'id' => $a->id,
                'tarif_name' => $a->currentTariff->name,
                'pay_once' => $a->currentTariff->pay_once,
                'gb_month' => $a->currentTariff->mb_month/1024,
                'pay_mb' => $a->currentTariff->pay_mb,
                'per_month' => round($a->currentTariff->pay_month, 2),
                'per_month_with_tax' => round($a->currentTariff->pay_month * 1.18, 2)
            ];
        }

        foreach(\app\models\UsageVirtpbx::find()->client($client)->actual()->all() as $a)
        {
            $data['vats'][] = [
                'from' => $a->actual_from,
                'description' => "ВАТС #".$a->id,
                'tarif_name' => $a->currentTariff->description,
                'space' => $a->currentTariff->space,
                'over_space_per_gb' => $a->currentTariff->overrun_per_gb,
                'num_ports' => $a->currentTariff->num_ports,
                'overrun_per_port' => $a->currentTariff->overrun_per_port,
                'per_month' => round($a->currentTariff->price, 2),
                'per_month_with_tax' => round($a->currentTariff->price * 1.18, 2)
            ];
        }

        /*
        foreach(\app\models\UsageSms::find()->client($client)->actual()->all() as $a)
        {
            $data['sms'][] = [
                'from' => $a->actual_from,
                'description' => "SMS-рассылка",
                'tarif_name' => $a->currentTariff->description,
                'per_month' => round($a->currentTariff->per_month_price/1.18, 2),
                'per_month_with_tax' => round($a->currentTariff->per_month_price, 2)
            ];
        }

        foreach(\app\models\UsageExtra::find()->client($client)->actual()->all() as $a)
        {
            $data['extra'][] = [
                'from' => $a->actual_from,
                'description' => "Доп. услуга", 
                'amount' => $a->amount,
                'tarif_name' => $a->currentTariff->description,
                'per_month' => round($a->currentTariff->price * $a->amount, 2),
                'per_month_with_tax' => round($a->currentTariff->price * 1.18 * $a->amount, 2)
            ];
        }
         */

        $this->design->assign("blank_data", $data);
        return $this->design->fetch("tarifs/blank.htm");
    }

    public function getFilePath($clientId, $contractId)
    {
        return Yii::$app->params['STORE_PATH'].'contracts/'.$clientId.'-'.$contractId.'.html';
    }

    public function getContent($clientId, $contractId)
    {
        $file = $this->getFilePath($clientId, $contractId);

        if(file_exists($file)) 
        {
            return file_get_contents($file);
        } else {
            return "File not found";
        }
    }
}
