<?php
namespace app\dao;

use app\models\ClientAccount;
use app\models\ClientDocument;
use app\models\Organization;
use Yii;
use app\classes\Singleton;
use app\models\Contract;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * @method static ClientDocumentDao me($args = null)
 * @property
 */
class ClientDocumentDao extends Singleton
{
    public static $folders = [
        'mcn' => 'MCN',

        'mcntelefonija' => 'MCN Телефония',
        'mcninternet' => 'MCN Интернет',
        'mcndatacenter' => 'MCN Дата-центр',

        'interop' => 'Межоператорка',
        'partners' => 'Партнеры',
        'internetshop' => 'Интернет-магазин',

        'welltime' => 'WellTime',
        'arhiv' => 'Arhiv',
    ];

    public static function templateList($isWithType = false)
    {
        $R = array();
        foreach (glob(Yii::$app->params['STORE_PATH'] . 'contracts/template_*.html') as $s) {
            $t = str_replace(array('template_', '.html'), array('', ''), basename($s));

            list($group,) = explode('_', $t);

            if ($isWithType) {
                $R[$group][] = $t;
            } else {
                $R[$group][] = substr($t, strlen($group) + 1);
            }
        }

        foreach (static::$folders as $key => $folderName)
            $_R[$key] = isset($R[$key]) ? $R[$key] : array();

        if ($isWithType) {
            $R = ['contract' => [], 'blank' => [], 'agreement' => []];

            foreach ($_R as $folder => $rr) {
                foreach ($rr as $k => $r) {
                    $contract = Contract::findOne(['name' => $r]);

                    if ($contract) {
                        $type = $contract->type;
                    } else {
                        $type = 'contract';
                    }
                    list($group,) = explode('_', $r);
                    $R[$type][$folder][] = substr($r, strlen($group) + 1);
                }
            }
        } else {
            $R = $_R;
        }

        return $R;
    }

    public function deleteFile(ClientDocument $document)
    {
        $file = $this->getFilePath($document);

        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }


    public function generateFile(ClientDocument $document, $contractGroup, $contractTemplate)
    {
        $content = $this->getTemplate('template_' . $contractGroup . "_" . $contractTemplate);
        file_put_contents($this->getFilePath($document), $content);
        return $this->generateDefault($document);
    }

    public function updateFile(ClientDocument $document)
    {
        return file_put_contents($this->getFilePath($document), $document->content);
    }

    public function getFileContent(ClientDocument $document)
    {
        $file = $this->getFilePath($document);

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return '';
    }

    private function generateDefault(ClientDocument $document)
    {
        $contractDate = $document->contract_date;
        $file = $this->getFilePath($document);
        $content = file_get_contents($file);

        $account = $document->getAccount();
        $design = \app\classes\Smarty::init();

        if ($document->type == 'contract') {
            $lastContract = [
                'contract_no' => $document->contract_no,
                'contract_date' => $document->contract_date,
            ];
        } else {
            $contractDocument =
                ClientDocument::find()
                    ->andWhere(['type' => 'contract'])
                    ->andWhere('contract_date <= :date', [':date' => $document->contract_dop_date ? $document->contract_dop_date : date('Y-m-d')])
                    ->orderBy('is_active desc, contract_date desc, id desc')
                    ->one();
            $lastContract = [
                'contract_no' => $contractDocument->contract_no,
                'contract_date' => $contractDocument->contract_date,
                'contract_dop_no' => $document->contract_no,
                'contract_dop_date' => $document->contract_date,
            ];
        }

        $account->payment_info = $this->prepareContragentPaymentInfo($account);
        $account->bank_properties = nl2br($account->bank_properties);

        $design->assign('client', $account);
        $design->assign('contract', $lastContract);
        $organization = Organization::find()->byId($account->contract->organization_id)->actual($contractDate)->one();
        $design->assign('firm', $organization->getOldModeInfo());
        $design->assign('firm_detail', $this->generateFirmDetail($organization->getOldModeInfo()), ($account->bik && $account->bank_properties));
        $design->assign('firm_director', $organization->director->getOldModeInfo());

        $content = $this->contract_fix_static_parts_of_template($design, $content);
        if (strpos($content, "{*#blank_zakaz#*}") !== false) {
            $content = str_replace("{*#blank_zakaz#*}", $this->makeBlankZakaz($document, $design), $content);
        }
        file_put_contents($file, $content);
        $newDocument = $design->fetch($file);
        return file_put_contents($file, $newDocument);
    }

    private function getTemplate($name)
    {
        $name = preg_replace('[^\w\d\-\_]', '', $name);

        if (file_exists(Yii::$app->params['STORE_PATH'] . 'contracts/' . $name . '.html')) {
            $data = file_get_contents(Yii::$app->params['STORE_PATH'] . 'contracts/' . $name . '.html');
        } else {
            $data = file_get_contents(Yii::$app->params['STORE_PATH'] . 'contracts/template_mcn_default.html');
        }

        $this->fix_style($data);

        return $data;
    }

    private function getFilePath(ClientDocument $document)
    {
        $contractId = $document->account_id ? $document->account_id : $document->contract_id;
        return Yii::$app->params['STORE_PATH'] . 'contracts/' . $contractId . '-' . $document->id . '.html';
    }

    private function fix_style(&$content)
    {
        if (strpos($content, '{/literal}</style>') === false) {
            $content = preg_replace('/<style([^>]*)>(.*?)<\/style>/six', '<style\\1>{literal}\\2{/literal}</style>', $content);
        }
    }

    private function makeBlankZakaz(ClientDocument $document, &$design)
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = $document->contract->accounts[0];
        $client = $clientAccount->client;

        $data = ['voip' => [], 'ip' => [], 'colocation' => [], 'vpn' => [], 'welltime' => [], 'vats' => [], 'sms' => [], 'extra' => []];

        $taxRate = $clientAccount->getTaxRate();

        foreach (\app\models\UsageVoip::find()->client($client)->andWhere("actual_to > NOW()")->all() as $usage) {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->getAbonPerMonth(), $taxRate);

            $data['voip'][] = [
                'from' => strtotime($usage->actual_from),
                'address' => $usage->address ?: $usage->datacenter->address,
                'description' => "Телефонный номер: " . $usage->E164,
                'number' => $usage->E164,
                'lines' => $usage->no_of_lines,
                'free_local_min' => $usage->currentTariff->free_local_min * ($usage->currentTariff->freemin_for_number ? 1 : $usage->no_of_lines),
                'connect_price' => (string)$usage->voipNumber->price,
                'tarif_name' => $usage->currentTariff->name,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        foreach (\app\models\UsageIpPorts::find()->client($client)->actual()->all() as $usage) {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->currentTariff->pay_month, $taxRate);

            switch ($usage->currentTariff->type) {
                case 'C':
                    $block = 'colocation';
                    break;
                case 'V':
                    $block = 'vpn';
                    break;
                case 'I':
                default:
                    $block = 'ip';
            }

            $data[$block][] = [
                'from' => strtotime($usage->actual_from),
                'id' => $usage->id,
                'tarif_name' => $usage->currentTariff->name,
                'pay_once' => $usage->currentTariff->pay_once,
                'gb_month' => $usage->currentTariff->mb_month / 1024,
                'pay_mb' => $usage->currentTariff->pay_mb,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        foreach (\app\models\UsageVirtpbx::find()->client($client)->andWhere("actual_to > NOW()")->all() as $usage) {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->currentTariff->price, $taxRate);

            $data['vats'][] = [
                'from' => strtotime($usage->actual_from),
                'description' => "ВАТС " . $usage->id,
                'tarif_name' => $usage->currentTariff->description,
                'space' => $usage->currentTariff->space,
                'over_space_per_gb' => $usage->currentTariff->overrun_per_gb,
                'num_ports' => $usage->currentTariff->num_ports,
                'overrun_per_port' => $usage->currentTariff->overrun_per_port,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        /*
        foreach(\app\models\UsageSms::find()->client($client)->actual()->all() as $usage)
        {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->currentTariff->per_month_price, $taxRate);

            $data['sms'][] = [
                'from' => $usage->actual_from,
                'description' => "SMS-рассылка",
                'tarif_name' => $usage->currentTariff->description,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }
        */

        foreach (\app\models\UsageExtra::find()->client($client)->actual()->all() as $usage) {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->currentTariff->price * $usage->amount, $taxRate);

            $data['extra'][] = [
                'from' => strtotime($usage->actual_from),
                'tarif_name' => $usage->currentTariff->description,
                'amount' => $usage->amount,
                'pay_once' => 0,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        $design->assign("blank_data", $data);
        return $design->fetch("tarifs/blank.htm");
    }

    private function contract_fix_static_parts_of_template(&$design, $content)
    {
        if (($pos = strpos($content, '{\$include_')) !== false) {
            $c = substr($content, $pos);
            $templateName = substr($c, 10, strpos($c, '}') - 10);

            $fname = Yii::$app->params['STORE_PATH'] . 'contracts/template_' . $templateName . '.html';

            if (file_exists($fname)) {
                $c = file_get_contents($fname);
                $design->assign('include_' . $templateName, $c);
            }

            $fname = Yii::$app->params['STORE_PATH'] . 'contracts/' . $templateName . '.html';
            if (file_exists($fname)) {
                $c = file_get_contents($fname);
                $design->assign('include_' . $templateName, $c);
            }
        }


        if (strpos($content, '{*#voip_moscow_tarifs_mob#*}') !== false) {
            $repl = ''; // москва(моб.)
            $content = str_replace('{*#voip_moscow_tarifs_mob#*}', $repl, $content);
        }

        $this->fix_style($content);

        return $content;
    }

    private function generateFirmDetail($f, $b = true)
    {
        $d = $f["name"] . "<br /> Юридический адрес: " . $f["address"] .
            (isset($f["post_address"]) ? "<br /> Почтовый адрес: " . $f["post_address"] : "")
            . "<br /> ИНН " . $f["inn"] . ", КПП " . $f["kpp"]
            . ($b ?
                "<br /> Банковские реквизиты:"
                . "<br /> р/с:&nbsp;" . $f["acc"] . " в " . $f["bank_name"]
                . "<br /> к/с:&nbsp;" . $f["kor_acc"]
                . "<br /> БИК:&nbsp;" . $f["bik"]
                : '')
            . "<br /> телефон: " . $f["phone"]
            . (isset($f["fax"]) && $f["fax"] ? "<br /> факс: " . $f["fax"] : "")
            . "<br /> е-mail: " . $f["email"];
        return $d;
    }

    private function prepareContragentPaymentInfo(ClientAccount $account)
    {
        $contragent = $account->contract->contragent;

        if ($contragent->legal_type == 'person') {
            if (!empty($account->bank_properties))
                return nl2br($account->bank_properties);

            return
                'Паспорт серия ' . $contragent->passport_serial .
                ' номер ' . $contragent->passport_number .
                ';<br />Выдан: ' . $contragent->passport_issued .
                ';<br />Дата выдачи: ' . $contragent->passport_date_issued . ' г.';
        }
        else {
            return
                'Банковские реквизиты: ' . $account->bank_properties .
                ', БИК ' . $account->bik .
                ', ИНН ' . $contragent->inn .
                ', КПП ' . $contragent->kpp .
                '<br />Почтовый адрес: ' . $account->address_post_real;
        }
    }

}

