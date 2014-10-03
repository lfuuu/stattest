<?
class PaymentParser {
    private static $types=array(
            '1CClientBankExchange' => array(
                'pay_acc' => 'РасчСчет',
                'begin'	=> 'СекцияДокумент',//Платежное поручение',
                'end'	=> 'КонецДокумента',
                'ops'	=> array(
                    'Сумма'					=> 'sum_rub',
                    'Дата'					=> 'date_dot',
                    'Номер'					=> 'pp',
                    'НазначениеПлатежа'		=> 'comment',
                    'ПлательщикИНН'			=> 'inn',
                    'Плательщик'			=> 'payer',
                    'Плательщик1'			=> 'payer',
                    'ДатаПоступило'			=> 'oper_date',

                    "ПлательщикСчет"        => "account",
                    "ПолучательСчет"        => "geter_acc",

                    "ПолучательИНН"         => "geter_inn",
                    "Получатель1"           => "geter",
                    "Получатель"            => "geter",
                    "ПолучательБанк1"       => "geter_bank",
                    "ПолучательБИК"         => "geter_bik",

                    'ПлательщикРасчСчет'    => 'account',
                    'ПлательщикБанк1'       => 'a2',
                    'ПлательщикБИК'         => 'bik',

                    ),
                ),
            '$OPERS_LIST'			=> array(
                'pay_acc' => 'ACCOUNT',
                'begin' => '$OPERATION',
                'end'	=> '$OPERATION_END',
                'ops'	=> array(
                    'AMOUNT'				=> 'sum_rub',
                    'DOC_DATE'				=> 'date_dot',
                    'DOC_NUM'				=> 'pp',
                    'OPER_DETAILS'			=> 'comment',
                    'CORR_INN'				=> 'inn',
                    'CORR_NAME'				=> 'payer',
                    'OPER_DATE'				=> 'oper_date',

                    'CORR_ACCOUNT'          => 'account',
                    'CORR_BANK_NAME'        => 'a2',
                    'CORR_BANK_BIC'         => 'bik',
                    ),
                ),
            );

	public static function Parse($file) {
		$f=fopen($file,'r');
		$mode=0;
		$R=array();
        $payAcc = array();
		while (!feof($f)) {
			$line=fgets($f);
			$line=str_replace(array("\r","\n"),array("",""),$line);
			$line=convert_cyr_string($line,'w','k');
//			$l=explode('=',$line);

            switch ($mode) {
                case 0:
                    foreach (PaymentParser::$types as $tkey=>&$tval) {
                        if (stripos($line,$tkey)!==false) {$type=&PaymentParser::$types[$tkey]; $mode=1; break;}
                    }
                    break;
                case 1:
                    if (stripos($line,$type['pay_acc'])!==false) {$mode=12; list(,$_payAcc) = explode("=", $line); $payAcc[$_payAcc] = $_payAcc;}
                    break;
                case 12:
                    if (stripos($line,$type['begin'])!==false) {$mode=3; $C=array();}else
                    if (stripos($line,$type['pay_acc'])!==false && stripos($line,"=") !== false) {$mode=12; list(,$_payAcc) = explode("=", $line); $payAcc[$_payAcc] = $_payAcc;}
                    break;
                case 2:
                    if (stripos($line,$type['begin'])!==false) {$mode=3; $C=array();}
                    break;
                case 3:
                    if (stripos($line,$type['end'])!==false) {
                        $mode=2; if (count($C)) $R[]=$C;
                    } else {
                        $l=explode('=',$line);
                        if (isset($type['ops'][$l[0]])) $C[$type['ops'][$l[0]]]=trim($l[1]);
                    }
                    break;
            }
		}
		fclose($f);
		if ($tkey=='1CClientBankExchange') $v='markomnet'; else $v='mcn';
		return array($v,$payAcc, $R);
	}
}

?>
