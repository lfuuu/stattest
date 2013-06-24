<?php
/**
 * Логгер.
 */

/**
 * Ведет лог событий.
 * Так же есть возможность отправки на email.
 */

class Logger{
	/**
	 * Помещает в лог запись.
	 * Принимает 3 параметра. Первый параметр может быть любым типом,
	 * либо объектом, определяющим метод _toLog. Если данный метод не определен,
	 * объект сериализуется в строку.<br />
	 * Второй параметр должен содержать идентификатор лога. По идентификатору,
	 * в последствии, можно будет выбрать сообщения, относящиеся только к нему.<br />
	 * Третий параметр может быть равен либо false, либо строке с email адресами,
	 * разделенными запятой, на которые необходимо отправить сообщение записываемое
	 * в лог.<br />
	 * Возвращает либо булев тип - удалось ли записать сообщение в лог.
	 * Если указан третий параметр, не равный false, возвращается массив
	 * с 2мя значениями - array('log'=>true|false,'mail'=>array(...)).
	 * В данном массива ключ log имеет булево значение - удалось ли записать в лог.
	 * Ключ mail ссылается на массив, который имеет ключи, соответствующие
	 * email адресам, перечисленным через запятую, ссылающиеся на булевы значения -
	 * удалось ли отправить письмо на email.
	 * @param mixed $logObj что-то, что необходимо поместить в лог
	 * @param String $log_type тип лога
	 * @param boolean|String $sendmail отправлять ли письмо на email
	 * @return boolean|array
	 */
	public static function put($logObj,$log_type='just',$sendmail=false){
		$log = self::write($logObj, $log_type);
		if($sendmail !== false && preg_match('/^([a-z0-9\-_\.]+@[a-z0-9\-_\.]+,?)+$/i', $sendmail)){
			return array('log'=>$log,'mail'=>self::sendmail(explode(',',$sendmail),$log_type,$logObj));
		}
		return $log;
	}
	private static function write($msg,$type){
		return file_put_contents(/*dirname(__FILE__)."/../log/log.dat"*/ "/tmp/log.dat", $type."\t".date("Y-m-d H:i:s")."\t".self::getString($msg).";\n",FILE_APPEND);
	}
	private static function sendmail($mails,$subj,$body){
		if(!class_exists('PHPMailer',false)){
			require_once dirname(__FILE__)."/class.phpmailer.php";
		}
		$mailer = new PHPMailer();
		$mailer->From		= "operator@mcn.ru";
		$mailer->FromName	= "Logger";
		$mailer->Subject	= $subj;
		$mailer->Body		= self::getString($body);
		$mailer->IsMail();

		$ret = array();
		foreach($mails as $mail){
			$mailer->AddAddress($mail);
			$ret[$mail] = $mailer->Send();
			$mailer->ClearAddresses();
		}
		return $ret;
	}
	private static function getString($objectORstring){
		if(is_object($objectORstring)){
			if(method_exists($objectORstring, '_toLog')){
				$objectORstring = $objectORstring->_toLog();
			}else
			$objectORstring = serialize($objectORstring);
		}elseif(!is_string($objectORstring)){
			$objectORstring = var_export($objectORstring, true);
		}
		return $objectORstring;
	}
}
?>
