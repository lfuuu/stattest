<?php
namespace app\dao;

use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContract;
use app\models\ClientDocument;
use app\models\document\DocumentTemplate;
use Yii;
use app\classes\Singleton;
use app\models\TariffVoip;

/**
 * @method static ClientDocumentDao me($args = null)
 * @property
 */
class ClientDocumentDao extends Singleton
{
    public static function templateList()
    {
        $templates = DocumentTemplate::find()
            ->joinWith(['folder'])
            ->orderBy(['document_template.name' => SORT_ASC])
            ->asArray()
            ->all();

        $res = [];
        foreach ($templates as $template) {
            $res[] = [
                'id' => $template['id'],
                'name' => $template['name'],
                'type' => $template['type'],
                'folder' => $template['folder']['name'],
                'folder_id' => $template['folder_id'],
            ];
        }
        return $res;
    }

    public function deleteFile(ClientDocument $document)
    {
        $file = $this->getFilePath($document);

        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }


    public function generateFile(ClientDocument $document, $contractTemplateId)
    {
        $content = DocumentTemplate::findOne($contractTemplateId)['content'];
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
        $data['has8800'] = false;

        $taxRate = $clientAccount->getTaxRate();

        foreach (\app\models\UsageVoip::find()->client($client)->andWhere("actual_to > NOW()")->all() as $usage) {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->getAbonPerMonth(), $taxRate);

            $currentTariff = $usage->getCurrentLogTariff();

            $row = [
                'from' => strtotime($usage->actual_from),
                'address' => $usage->address ?: $usage->datacenter->address,
                'description' => "Телефонный номер: " . $usage->E164,
                'number' => $usage->E164.' x '.$usage->no_of_lines,
                'lines' => $usage->no_of_lines,
                'free_local_min' => $usage->currentTariff->free_local_min * ($usage->currentTariff->freemin_for_number ? 1 : $usage->no_of_lines),
                'connect_price' => (string)$usage->voipNumber->price,
                'tarif_name' => $usage->currentTariff->name,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2),
                'month_min_payment' => $usage->currentTariff->month_min_payment,
            ];

            if (!$data['has8800'] && in_array($usage->currentTariff->id, [226, 263, 264, 321, 322, 323, 448]))
                $data['has8800'] = true;

            if (
                $currentTariff->id_tarif_local_mob
                && ($tarifLocalMob = TariffVoip::findOne($currentTariff->id_tarif_local_mob)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifLocalMob */
                $row['voip_current_tariff']['tarif_local_mob'] = $tarifLocalMob->name;
            }

            if (
                $currentTariff->id_tarif_russia_mob
                && ($tarifRussiaMob = TariffVoip::findOne($currentTariff->id_tarif_russia_mob)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifRussiaMob */
                $row['voip_current_tariff']['tarif_russia_mob'] = $tarifRussiaMob->name;
            }

            if (
                $currentTariff->id_tarif_russia
                && ($tarifRussia = TariffVoip::findOne($currentTariff->id_tarif_russia)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifRussia */
                $row['voip_current_tariff']['tarif_russia'] = $tarifRussia->name;
            }

            if (
                $currentTariff->id_tarif_intern
                && ($tarifIntern = TariffVoip::findOne($currentTariff->id_tarif_intern)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifIntern */
                $row['voip_current_tariff']['tarif_intern'] = $tarifIntern->name;
            }

            if ($currentTariff->dest_group != 0 && $currentTariff->minpayment_group) {
                $group = preg_split('//', $currentTariff->dest_group, -1, PREG_SPLIT_NO_EMPTY);
                $minpayment = ['value' => $currentTariff->minpayment_group, 'variants' => [0, 0, 0, 0,]];
                for ($i = 0, $s = sizeof($group); $i < $s; $i++) {
                    switch ($group[$i]) {
                        case 1:
                            $minpayment['variants'][1] = 1;
                            $minpayment['variants'][2] = 1;
                            break;
                        case 2:
                            $minpayment['variants'][3] = 1;
                            break;
                        case 5:
                            $minpayment['variants'][0] = 1;
                            break;
                    }
                }
                $row['minpayments'][] = $minpayment;
            }

            if ($currentTariff->minpayment_local_mob > 0)
                $row['minpayments'][] = ['value' => $currentTariff->minpayment_local_mob, 'variants' => [1, 0, 0, 0,]];
            if ($currentTariff->minpayment_russia > 0)
                $row['minpayments'][] = ['value' => $currentTariff->minpayment_russia, 'variants' => [0, 1, 1, 0,]];
            if ($currentTariff->minpayment_intern > 0)
                $row['minpayments'][] = ['value' => $currentTariff->minpayment_intern, 'variants' => [0, 0, 0, 1,]];

            $data['voip'][] = $row;
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

        foreach (\app\models\UsageWelltime::find()->client($client)->actual()->all() as $usage) {
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->currentTariff->price * $usage->amount, $taxRate);

            $data['welltime'][] = [
                'from' => $usage->actual_from,
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

        $result = $contragent->name_full . '<br />Адрес: ' . (
            $contragent->legal_type == 'person'
                ? $contragent->person->registration_address
                : $account->address_jur
            ) . '<br />';

        if ($contragent->legal_type == 'person') {
            if ($contragent->person
                && !empty(
                    $contragent->person->passport_serial
                    . $contragent->person->passport_number
                    . $contragent->person->passport_issued
                    . $contragent->person->passport_date_issued
                )
            )
                return
                    $result .
                    'Паспорт серия ' . $contragent->person->passport_serial .
                    ' номер ' . $contragent->person->passport_number .
                    '<br />Выдан: ' . $contragent->person->passport_issued .
                    '<br />Дата выдачи: ' . $contragent->person->passport_date_issued . ' г.';

        } else {
            return
                $result .
                'ИНН ' . $contragent->inn .
                ', КПП ' . $contragent->kpp . '<br/>' .
                'Банковские реквизиты: ' .
                'р/с ' . ($account->pay_acc ?: '') . '<br />' .
                $account->bank_name . ' ' . $account->bank_city .
                ($account->corr_acc ? '<br />к/с ' . $account->corr_acc : '') .
                ', БИК ' . $account->bik .
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
            $contractId = $account->contract_id;

            if ($document->getContract()->state == ClientContract::STATE_CHECKED_COPY) {
                $originContract =
                    ClientContract::find()
                        ->where(['contragent_id' => $document->getContract()->getContragent()->id])
                        ->andWhere(['state' => ClientContract::STATE_CHECKED_ORIGINAL])
                        ->orderBy('id ASC')
                        ->one();
                if ($originContract->id) {
                    $contractId = $originContract->id;
                }
            }

            $contractDocument =
                ClientDocument::find()
                    ->andWhere(['type' => 'contract', 'contract_id' => $contractId])
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
            'position' => $document->getContract()->getContragent()->legal_type == 'legal'
                ? $document->getContract()->getContragent()->position
                : '',
            'fio' => $document->getContract()->getContragent()->legal_type == 'legal'
                ? $document->getContract()->getContragent()->fio
                : $document->getContract()->getContragent()->name_full,
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
            'old_legal_type' => $account->getContract()->getContragent()->legal_type != 'person' ? 'org' : 'person',
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

            'organization_firma' => $firm['firma'],
            'organization_director_post' => $firm['director_post'],
            'organization_director' => $firm['director'],
            'organization_name' => $firm['name'],
            'organization_address' => $firm['address'],
            'organization_inn' => $firm['inn'],
            'organization_kpp' => $firm['kpp'],
            'organization_corr_acc' => $firm['kor_acc'],
            'organization_bik' => $firm['bik'],
            'organization_bank' => $firm['bank'],
            'organization_phone' => $firm['phone'],
            'organization_email' => $firm['email'],
            'organization_pay_acc' => $firm['acc'],

            'firm_detail_block' => $this->generateFirmDetail($firm, $account->bik),
            'payment_info' => $this->prepareContragentPaymentInfo($account),
        ];
    }
}
