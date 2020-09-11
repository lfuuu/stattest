<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\models\Country;
use yii\web\NotFoundHttpException;

/**
 * <list>
 * <list_item><phone_number>19191080</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:25</update_ts></list_item>
 * <list_item><phone_number>19191081</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191082</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191083</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191084</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191085</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191086</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191087</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191088</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191089</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 */
class PortedHungaryController extends PortedController
{
    private $_operators = [
        '602' => 'Greencom Hungary Kft.',
        '605' => 'H1 Komm Kft.',
        '606' => 'Comnica Kft.',
        '607' => 'Raystorm Kft.',
        '608' => 'Rebell Telecommunication Zrt.',
        '609' => 'HBCom Kábel Nonprofit Kft.',
        '611' => 'MCNtelecom GmbH',
        '612' => 'Gelka Hirtech Kft.',
        '614' => 'nfon GmbH',
        '615' => 'Dialoga Servicios Interactivos S.A.',
        '616' => 'Medveczky Cégcsoport Kft.',
        '618' => 'Kis Fal Kft.',
        '619' => 'Premium Net International S.R.L.',
        '620' => 'KIFÜ',
        '621' => 'United Telecom Zrt.',
        '622' => 'Copy-Data Kft.',
        '623' => 'MosonTelecom System Kft.',
        '625' => 'Calliotel Kft.',
        '626' => 'VM Telecom HU Kft.',
        '627' => 'Invinetwork Kft.',
        '701' => 'FCS Group Kft.',
        '707' => 'SKAWA Innovation Kft.',
        '708' => 'PENDOLA INVEST Kft.',
        '709' => 'VNM Zrt.',
        '711' => 'GERGI HÁLÓ Kft.',
        '712' => 'DUAL-PLUS Kft.',
        '716' => 'BORSODWEB Kft.',
        '717' => 'R-Voice Hungary Kft.',
        '721' => 'Rendszerinformatika Zrt.',
        '724' => 'NISZ Zrt.',
        '726' => 'Last-Mile Kft.',
        '730' => 'Techno-Tel Kft.',
        '731' => 'Virtual Communications Kft.',
        '732' => 'CG-SYSTEMS Kft.',
        '735' => 'HIR-SAT Kft.',
        '737' => 'Giganet Internet Kft.',
        '740' => 'Hungária Informatikai Kft.',
        '741' => 'Printer-fair Kft.',
        '742' => 'DELTAKON Kft.',
        '743' => 'CBN Telekom Kft.',
        '747' => 'Földesi és Társa 2002 Kft.',
        '752' => 'NET-TV Zrt.',
        '753' => 'Netfone Telecom Kft.',
        '765' => 'KábelszatNet-2002 Kft.',
        '766' => 'MVM NET Zrt.',
        '767' => 'Inphone Kft.',
        '768' => 'Kalásznet Kft.',
        '769' => 'Vidékháló Kft.',
        '770' => 'i3 Rendszerház Kft.',
        '771' => 'TEVE TÉVÉ Kft.',
        '774' => 'Cogitnet Kft.',
        '776' => 'Wavecom Informatikai Kft.',
        '778' => 'Banktel Kommunikációs Zrt.',
        '779' => 'Calgo Kft.',
        '781' => 'ExpertCom Kft.',
        '782' => 'ADERTIS Kft.',
        '783' => 'MCN telecom Kft.',
        '784' => 'HelloVoip Kft.',
        '786' => 'Cost Consulting Kft.',
        '789' => 'VCC Live Hungary Kft.',
        '791' => '42NETMedia Kft.',
        '792' => 'Ephone Schweiz GmbH.',
        '794' => 'PIVo Telecom Kft.',
        '795' => 'Net-Connect Communications SRL',
        '796' => 'Epax Kft.',
        '798' => 'BEROTEL NETWORKS Kft.',
        '805' => 'PICKUP Kft.',
        '807' => 'ViDaNet Zrt.',
        '810' => '"PÁZMÁNY-KÁBEL" Kft.',
        '814' => 'EuroCable Magyarország Kft.',
        '816' => 'Microsystem-Kecskemét Kft.',
        '817' => 'Intellihome Kft.',
        '820' => 'AMTEL Kft.',
        '822' => 'Media Exchange Kft.',
        '823' => 'Voxbone SA',
        '826' => 'IP-Telekom Kft.',
        '830' => 'SÁGHY-SAT Kft.',
        '833' => 'Kapos-NET Kft.',
        '834' => 'KÁBLEX Kft.',
        '835' => 'BTM 2003 Kft.',
        '838' => 'ZNET Telekom Zrt.',
        '839' => 'CORVUS TELECOM Kft.',
        '841' => 'ACE TELECOM Kft.',
        '842' => 'Balmaz InterCOM Kft.',
        '844' => 'COMTEST Kft.',
        '845' => 'INVITEL Zrt.',
        '846' => 'FONIO-VOIP Kft.',
        '850' => 'ANTENNA HUNGÁRIA Zrt.',
        '855' => 'NET-PORTAL Kft.',
        '857' => 'Celldömölki Kábeltelevízió Kft.',
        '861' => 'MICRO-WAVE Kft.',
        '862' => 'SolarTeam Energia Kft.',
        '863' => 'OPTICON Kft.',
        '867' => 'DRÁVANET Zrt.',
        '869' => '"KÁBELSAT-2000" Kft.',
        '870' => 'ES INNOTEL Kft.',
        '879' => 'SZAMOSNET Kft.',
        '880' => '"TLT Telecom" Kft.',
        '883' => 'NOVI-COM Kft.',
        '886' => 'Magyar Telekommunikációs és Informatikai Kft.',
        '891' => 'Elektronet Zrt.',
        '894' => 'Satelit Kft.',
        '896' => 'Toldinet Kft.',
        '899' => 'NARACOM Kft.',
        '902' => '3C Kft.',
        '903' => 'BT Limited Magyarországi Fióktelepe',
        '904' => 'MACROgate IPsystems Magyarország Kft.',
        '905' => '4VOICE Kft.',
        '916' => 'Magyar Telekom Nyrt.',
        '917' => 'Vodafone Magyarország Zrt.',
        '918' => 'Invitech ICT Services Kft.',
        '923' => 'TARR Kft.',
        '927' => 'OROS-COM Kft.',
        '932' => 'INVITEL Zrt.',
        '933' => 'PENDOLA TeleCom Kft.',
        '934' => 'Magyar Telekom Nyrt.',
        '935' => 'Biatorbágyi Kábeltévé Kft.',
        '936' => 'Internet-X Magyarország Kft.',
        '940' => 'Vodafone Magyarország Zrt.',
        '941' => 'ON LINE SYSTEM Kft.',
        '943' => 'Isis-Com Kft.',
        '948' => 'DIGI Kft.',
        '953' => 'PR-TELECOM Zrt.',
        '956' => 'Magyar Telekom Nyrt.',
        '958' => 'N-System Távközlési Kft.',
        '961' => 'OPENNETWORKS Kft.',
        '967' => 'Mikroháló Kft.',
        '976' => 'ARRABONET Kft.',
        '979' => 'ZALASZÁM Kft.',
        '981' => 'Xyton Kft.',
        '983' => 'VCC Live Hungary Kft.',
        '984' => 'NORDTELEKOM Nyrt.',
        '986' => 'TRIOTEL Kft.',
        '989' => 'PARISAT Kft.',
        '993' => 'RLAN Internet Kft.',
        '603' => 'TARR Kft.',
        '604' => 'DIGI Kft.',
        '617' => 'Netfone Telecom Kft.',
        '760' => 'Vodafone Magyarország Zrt.',
        '777' => 'Netfone Telecom Kft.',
        '919' => 'Telenor Magyarország Zrt.',
        '926' => 'Vodafone Magyarország Zrt.',
        '928' => 'Magyar Telekom Nyrt.',
        '613' => 'Tiszafüredi Kábeltévé Szövetkezet ',
        '790' => 'UPC DTH S.á.r.l. ',
    ];
/**/
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function readData()
    {
        $fileUrl = \Yii::getAlias('@runtime/' . $this->fileName);
        $fp = fopen($fileUrl, 'r');
        if (!$fp) {
            throw new NotFoundHttpException('Ошибка чтения файла ' . $fileUrl);
        }

        $insertValues = [];
        while (($row = fgets($fp)) !== false) {

//            $row = trim(str_replace(['<list_item>', '</list_item>'], '', $row));
            $row = trim($row);

            if (strpos($row, '</list>') !== false) {
                $row = str_replace('</list>', '', $row);
            }

            if (!$row || $row == '<list>' || $row == '</list>') {
                continue;
            }

            try {
                $xml = simplexml_load_string($row);
            } catch (\Exception $e) {
                echo PHP_EOL . 'error: ' . $e->getMessage();
                echo PHP_EOL . $row;
                continue;
            }

            if (!$xml) {
                echo 'Неправильные данные: ' . $row . PHP_EOL;
                continue;
            }

            $number = (string)$xml->phone_number;
            if (!$number || !is_numeric($number)) {
                throw new \LogicException('Неправильный номер: ' . $row);
            }

            $number = Country::HUNGARY_PREFIX . $number;

            $operatorName = (string)$xml->actual_provider;
            if ($operatorName && !isset($this->_operators[$operatorName])) {
                echo PHP_EOL . 'operator not found: ' . $operatorName;
            }

            if ($operatorName && isset($this->_operators[$operatorName]) && $this->_operators[$operatorName]) {
                $operatorName = $this->_operators[$operatorName];
            }

            $insertValues[] = [$number, $operatorName];

            if (count($insertValues) >= self::CHUNK_SIZE) {
                $this->insertValues(Country::HUNGARY, $insertValues);
            }
        }

        fclose($fp);

        if ($insertValues) {
            $this->insertValues(Country::HUNGARY, $insertValues);
        }
    }
}
