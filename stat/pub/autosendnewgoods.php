<?php
	define('NO_WEB',1);
	define('PATH_TO_ROOT','./../');
	include PATH_TO_ROOT."conf.php";
	
	global $db;
	$rows = $db->AllRecords(
				"SELECT u.email, l.bill_no, l.item_id, l.descr_id, g.name good, l.amount, ifnull(o.last_free, 0) as last_free, ifnull(gs.qty_free, 0) as free
				FROM tt_troubles t
				LEFT JOIN newbill_lines l on l.bill_no=t.bill_no
				LEFT JOIN newbills_add_info i on t.bill_no=i.bill_no
				LEFT JOIN g_order_free_goods o on o.bill_no=l.bill_no and o.good_id=l.item_id and o.descr_id=l.descr_id
				LEFT JOIN g_good_store gs on gs.good_id=l.item_id and gs.descr_id=l.descr_id and gs.store_id=i.store_id
				LEFT JOIN g_goods g on g.id=l.item_id
				LEFT JOIN tt_stages st on st.stage_id=t.cur_stage_id
				LEFT JOIN user_users u on u.user=st.user_main
				WHERE t.trouble_type='shop_orders' and t.folder=3 and l.amount>0");
	$q = '';
	$mails = array();
	foreach ($rows as $r){
		if ($r['amount'] > $r['last_free'] && $r['free'] >= $r['amount']){
			$mail = array();
			if (isset($mails[$r['email']]))
				$mail = $mails[$r['email']];
			else 
				$mail = array();

			$order = array();
			if (isset($mail[$r['bill_no']]))
				$order = $mail[$r['bill_no']];
			else 
				$order = array();
				
			$order[] = array('good'=>$r['good'],'need'=>intval($r['amount']),'free'=>$r['free']);
			$mail[$r['bill_no']] = $order;
			$mails[$r['email']] = $mail;
			 
		}
		if ($q == '') $q = 'insert into g_order_free_goods(bill_no,good_id,descr_id,last_free) values ';	
		else $q .= ',';
		$q .= "('{$r['bill_no']}','{$r['item_id']}','{$r['descr_id']}','{$r['free']}')";
	}
	
	$db->Query('delete from g_order_free_goods');
	$db->Query($q);
		
	function send_mime_mail($name_from, // имя отправителя
	                        $email_from, // email отправителя
	                        $name_to, // имя получателя
	                        $email_to, // email получателя
	                        $data_charset, // кодировка переданных данных
	                        $send_charset, // кодировка письма
	                        $subject, // тема письма
	                        $body, // текст письма
	                        $html = FALSE // письмо в виде html или обычного текста
	                        ) {
	  $to = mime_header_encode($name_to, $data_charset, $send_charset)
	                 . ' <' . $email_to . '>';
	  $subject = mime_header_encode($subject, $data_charset, $send_charset);
	  $from =  mime_header_encode($name_from, $data_charset, $send_charset)
	                     .' <' . $email_from . '>';
	  if($data_charset != $send_charset) {
	    $body = iconv($data_charset, $send_charset, $body);
	  }
	  $headers = "From: $from\r\n";
	  $type = ($html) ? 'html' : 'plain';
	  $headers .= "Content-type: text/$type; charset=$send_charset\r\n";
	  $headers .= "Mime-Version: 1.0\r\n";
	
	  return mail($to, $subject, $body, $headers);
	}
	
	function mime_header_encode($str, $data_charset, $send_charset) {
	  if($data_charset != $send_charset) {
	    $str = iconv($data_charset, $send_charset, $str);
	  }
	  return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
	}
	
	foreach ($mails as $email => $mail){
		$subject = 'Появились товары ожидающие поступления';
		$message = '';
		foreach ($mail as $bill => $order){
			$message .= "Заказ $bill\n";
			foreach ($order as $good){
				$message .= "     {$good['good']}\n";
				$message .= "     Нужно: {$good['need']}    Доступно: {$good['free']}\n";
			}
			$message .= "\n";
		}
//		$email2 = 'victor@mcn.ru';
//		send_mime_mail('STAT','no-reply@mcn.ru',$email,$email2,'UTF-8','UTF-8',$subject, $message);
		$email2 = 'sdn@mcn.ru';
		send_mime_mail('STAT','no-reply@mcn.ru',$email,$email2,'UTF-8','UTF-8',$subject, $message);
//		$email2 = 'kratorman@gmail.com';
//		send_mime_mail('STAT','no-reply@mcn.ru',$email,$email2,'UTF-8','UTF-8',$subject, $message);
	}	


