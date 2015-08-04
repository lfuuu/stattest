<?php
namespace app\dao;

use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientDocument;
use Yii;
use app\classes\Singleton;
use app\models\Contract;

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
        $file = $this->getFilePath($document);
        $content = file_get_contents($file);

        $design = \app\classes\Smarty::init();
        $design->assign($this->spawnDocumentData($document));

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

        $result = 'Адрес: ' . (
                $contragent->legal_type == 'person'
                    ? $contragent->person->registration_address
                    : $account->address_jur
            ) . '<br />';

        if ($contragent->legal_type == 'person') {
            if (!empty($account->bank_properties))
                return $result . nl2br($account->bank_properties);

            return
                $result .
                'Паспорт серия ' . $contragent->person->passport_serial .
                ' номер ' . $contragent->person->passport_number .
                '<br />Выдан: ' . $contragent->person->passport_issued .
                '<br />Дата выдачи: ' . $contragent->person->passport_date_issued . ' г.';
        }
        else {
            return
                $result .
                'Банковские реквизиты: ' . $account->bank_properties .
                ', БИК ' . $account->bik .
                ', ИНН ' . $contragent->inn .
                ', КПП ' . $contragent->kpp .
                (!empty($account->address_post_real) ? '<br />Почтовый адрес: ' . $account->address_post_real : '');
        }
    }

    private function spawnDocumentData(ClientDocument $document)
    {
        $account = $document->getAccount();
        $contractDate = $document->contract_date;
        $officialContacts = $account->getOfficialContact();

        if ($document->type == 'contract') {
            $lastContract = [
                'contract_no' => $document->contract_no,
                'contract_date' => $document->contract_date,
            ];
        } else {
            $contractDocument =
                ClientDocument::find()
                    ->andWhere(['type' => 'contract', 'contract_id' => $account->contract_id])
                    ->orderBy('is_active desc, contract_date desc, id desc')
                    ->one();
            $lastContract = [
                'contract_no' => $contractDocument->contract_no,
                'contract_date' => $contractDocument->contract_date,
                'contract_dop_no' => $document->contract_no,
                'contract_dop_date' => $document->contract_date,
            ];
        }

        $organization = $document->getContract()->getOrganization($contractDate);
        $firm = $organization->getOldModeInfo();
        return [
            'position' => $document->getContract()->getContragent()->position,
            'fio' => $document->getContract()->getContragent()->fio,
            'name' => $document->getContract()->getContragent()->name,
            'name_full' => $document->getContract()->getContragent()->name_full,
            'address_jur' => $document->getContract()->getContragent()->address_jur,
            'bank_properties' => str_replace("\n", '<br/>', $account->bank_properties),
            'bik' => $account->bik,
            'address_post_real' => $account->address_post_real,
            'address_post' => $account->address_post,
            'corr_acc' => $account->corr_acc,
            'pay_acc' => $account->pay_acc,
            'inn' => $document->getContract()->getContragent()->inn,
            'kpp' => $document->getContract()->getContragent()->kpp,
            'stamp' => $account->stamp,
            'legal_type' => $account->getContract()->getContragent()->legal_type,
            'old_legal_type' => $account->getContract()->getContragent()->legal_type !='person' ? 'org' : 'person',
            'address_connect' => $account->address_connect,
            'account_id' => $account->id,
            'bank_name' => $account->bank_name,
            'credit' => $account->credit,

            'contract_no' => $lastContract['contract_no'],
            'contract_date' => $lastContract['contract_date'],
            'contract_dop_date' => $lastContract['contract_dop_date'],
            'contract_dop_no' => $lastContract['contract_dop_no'],

            'contact' => ($c = ClientContact::findOne($account->admin_contact_id)) ? $c->comment : '',
            'emails' => implode('; ', $officialContacts['email']),
            'phones' => implode('; ', $officialContacts['phone']),
            'faxes' => implode('; ', $officialContacts['fax']),

            'organization_firma' => $firm->firma,
            'organization_director_post' => $firm->director_post,
            'organization_director' => $firm->director,
            'organization_name' => $firm->name,
            'organization_address' => $firm->address,
            'organization_inn' => $firm->inn,
            'organization_kpp' => $firm->kpp,
            'organization_corr_acc' => $firm->kor_acc,
            'organization_bik' => $firm->bik,
            'organization_bank' => $firm->bank,
            'organization_phone' => $firm->phone,
            'organization_email' => $firm->email,
            'organization_pay_acc' => $firm->acc,

            'firm_detail_block' => $this->generateFirmDetail($firm, ($account->bik && $account->bank_properties)),
            'payment_info' => $this->prepareContragentPaymentInfo($account),
        ];
    }
}
