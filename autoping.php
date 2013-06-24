<?
	define('NO_WEB',1);
	define('NUM',20);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf.php";

function ping_val($adr){
	if(PLATFORM=='windows'){
		$r = `ping -n 1 -w 1000 $adr`;
		$m = preg_match('/time=(\d+)ms/',$r,$ma);
		if($m)
			return $ma[1];
		return -1;
	}else{
		$r = `ping -s 32 -c 1 -w 1 $adr 2> /dev/null`;
		$m = preg_match('/time=(\d+)(?:\.(\d+))? ms/',$r,$ma); // тут косяк. Парень считает, что время всегда с плавающей точкой. Иногда же мы получаем время целым числом
		if($m){
			if(strpos($ma[1],'.')===false)
				$ma[1] .= '.0';
			return $ma[1];
		}
		return -1;
	}
}
function try_register(){
	global $db,$THIS_NUM;
	@$db->Query('insert into monitor_5min_ins values (0,'.$THIS_NUM.',0)');
	if($db->AffectedRows()==0){
		echo "i'm a second copy of ".$THIS_NUM.".\n";
		exit;
	}
}

	if(!$db->Connect())
		exit;

	if(!isset($argv[1])){ // если не fork
		echo "BEGIN;#######################".date("Y-m-d H:i:s")."#######################\n";
		$THIS_NUM=0;
		try_register();

		$r = $db->GetRow('
			select
				max(time300) as tmax,
				FLOOR(UNIX_TIMESTAMP()/300) as tcur
			from
				monitor_5min
			where
				ip_int!=0
		');	//min(time300) as tmin,

		if($r['tmax']==$r['tcur']) // если не прошло 5 минут с последнего запуска - завершаем работу скрипта
			die("Wait some minutes plz. max(time300)=".$r['tmax']);

		$T=$r['tcur'];

		if(($T % 288) == 240){ // если остаток от деления текущего timestamp на 288 равен 4м минутам - выполняется кусок этого кода.
			echo "Summarizing...\n";
			$t = $T-288; //($T%288)-288;
			$A = $db->GetRow('
				select
					max(time3600) as A
				from
					monitor_1h
			');
			$db->Query('
				insert into
					monitor_1h
				select
					ip_int,
					FLOOR(time300/12) as time3600,
					sum(IF(value=0,1,0)) + 12 - count(*) as bad_count,
					sum(value) as good_sum
				FROM
					monitor_5min
				where
					ip_int>0
				and
					time300<'.$t.'
				GROUP BY
					ip_int,
					time3600
			');
			$db->Query('
				delete from
					monitor_5min
				where
					ip_int > 0
				and
					time300 < '.$t
			);
			$db->Query('truncate table monitor_ips');
			$db->Query('update monitor_clients set period_use=0');
		}

		$r = $db->GetRow('
			select
				value
			from
				monitor_5min
			where
				ip_int = 0
			and
				time300 = 0
			limit 1
		');

		if(!$r){
			$db->Query('insert into monitor_5min (ip_int,time300,value) values(0,0,1)');
			$ITERATION=1;
		}else{
			$db->Query('update monitor_5min set value=value+1 where ip_int=0 and time300=0');
			$ITERATION=$r['value']+1;
		}
		echo "Iteration: ".$ITERATION."\n";

		$db->Query('truncate monitor_5min_ins');
		/**
		 * запускаем 20 форков себя, которые будут что-то делать параллельно,
		 * передавая им 2 аргумента:
		 * 1 - порядковый номер форка (в форке это - $THIS_NUM)
		 * 2 - время текущего вычисления (в форке это - $T)
		 *
		 * вывод форков перенаправляется в /dev/null
		 */
		for($i=1;$i<NUM;$i++){
			if(PLATFORM=='windows'){
				system("php -f ".PATH_TO_ROOT."autoping.php $i $T");
			}else{
				if(SERVER=="tiberis"){
					system("php -c /etc/ -f ".PATH_TO_ROOT."autoping.php $i $T > /dev/null &");
				}else{
					system("php -f ".PATH_TO_ROOT."autoping.php $i $T > /dev/null &");
				}
			}
		}
	}else{ // если текущий процесс - fork
		$ITERATION=-1;
		$THIS_NUM = $argv[1];
		try_register();
		$T=$argv[2];
		if(!$T)
			return;
	}

	$R=array();
	$db->Query( // выборка клиентов и их подсетей
		'select
			usage_ip_routes.net,
			usage_ip_ports.client
		from
			usage_ip_routes
		INNER JOIN
			usage_ip_ports
		ON
			usage_ip_ports.id = usage_ip_routes.port_id
		where
			usage_ip_routes.actual_from <= NOW()
		and
			usage_ip_routes.actual_to > NOW()
	');
	while($r=$db->NextRecord()) // выборка первых 2х(или, если подсеть только из одного ip - тогда 1) ip'шников сети в массив $R
		add_ip($R,$r[0],'');

	$db->Query( // выборка клиентов и их ip адресов с ppp подключениями
		'select
			ip,
			client
		from
			usage_ip_ppp
		where
			actual_from <= NOW()
		and
			actual_to >= NOW()
	');
	while($r=$db->NextRecord()) // помещение ip адресов в массив $R
		add_ip($R,$r[0],'');

	$db->Query( // выботка ip адресов и клиентов
		'select
			ip,
			client
		from
			tech_cpe
		where
			actual_from <= NOW()
		and
			actual_to >= NOW()
		and
			ip != ""
	');
	while($r=$db->NextRecord()) // помещение в массив $R ip адреса и клиента, им владеющего
		add_ip($R,$r[0],$r[1]);

	$db->Query( // выборка подсетей и роутеров, которым они принадлежат
		'select
			net,
			router
		from
			tech_routers
		where
			actual_from <= NOW()
		and
			actual_to >= NOW()
	');
	while($r=$db->NextRecord()) // добавление в массив $R ip адресов и роутеров
		add_ip($R,$r[0],'mcn.'.$r[1]);

	$IPs_bad = array();
	$db->Query('select INET_NTOA(ip_int) as ip from monitor_ips'); // выборка ip адресов, по которым был обнаружен процент потерь
	while($r=$db->NextRecord())
		$IPs_bad[$r['ip']]=$r['ip'];

	$clients=array();
	$db->Query('select id,client from monitor_clients'); // выборка клиентов, за которыми ведется мониторинг на email
	while($r=$db->NextRecord())
		$clients[$r['client']]=$r['id'];

	$cnt_thread=0;
	$cnt_total=0;

	ksort($R); // сортировка массива по ключам(ip адресам), сохраняя связи между ключами и значениями
	foreach($R as $ip=>$client){
		if(($cnt_total % NUM) == $THIS_NUM){// если остаток от деления на количество форков равняется идентификатору текущего форка - обрабатываем, иначе пропускаем
			$v=ping_val($ip); // пингуем ip адрес системно (system("ping...")). Если пропинговался - $v = количеству милисекунд пинга, если нет = -1

			if($v<0)
				$v=0;
			elseif($v==0)
				$v=1;

			if(isset($IPs_bad[$ip])){ // если по этому ip адресу был обнаружен процент потерь
				if($v){
//					$db->Query('delete from monitor_ips where ip_int=INET_ATON("'.$ip.'")');
				}else{
					$db->Query( // увеличиваем количество потерь на 1
						'update
							monitor_ips
						set
							count=count+1
						where
							ip_int = INET_ATON("'.$ip.'")
					');
				}
			}elseif(!$v && isset($clients[$client])){ // если по ip адресу еще не было обнаружено потерь, но это 1я, и за клиентом ведется мониторинг - добавляем в таблицу с плохими ip адресами
				$db->Query('
					insert into
					monitor_ips (
						ip_int,
						monitor_id,
						count
					)
					values (
						INET_ATON("'.$ip.'"),
						'.$clients[$client].',
						1
					)
				');
			}
			$db->Query( // наполняем таблицу статистики. поле value - это время пинга
				'insert into
				monitor_5min_ins (
					ip_int,
					time300,
					value
				)
				values (
					INET_ATON("'.$ip.'"),
					'.$T.',
					'.$v.'
				)
			');
			$cnt_thread++;
		}
		$cnt_total++;
	}
	@$db->Lock('monitor_5min_ins');
	$db->Query( // удаляем текущий форк из таблицы - этим разрешаем форкать скрипт, с текущим порядковым номером, в будущем
		'delete from
			monitor_5min_ins
		where
			ip_int=0
		and
			time300='.$THIS_NUM
	);
	$C=$db->GetRow( // выбираем из таблицы форков, которые запущены в настоящее время.
		'select
			count(*) as C
		from
			monitor_5min_ins
		where
			ip_int=0
		and
			value=0
	');
	@$db->Unlock();
	if($C['C']==0){ // блок finally - если текущий процесс последний из всех форков - выполняем
		$db->Query( // перемещаем все записи статистики в боевую таблицу
			'insert into
				monitor_5min
			select
				*
			from
				monitor_5min_ins
		');
		$db->Query('truncate monitor_5min_ins'); // подчищаем за собой таблицу с временной статистикой

		$M=$db->AllRecords( // получаем клиентов, на которых выставлены флаги мониторинга.
			'select
				monitor_clients.*
			from
				monitor_clients
			inner join
				monitor_ips
			ON
				monitor_ips.monitor_id = monitor_clients.id
			where
				monitor_ips.count > monitor_clients.allow_bad
			group by
				monitor_clients.id
		');
		foreach($M as $r){
			if($r['period_use']<=0){ // если пришло время слать спам, тогда отправляем статистику по этому клиенту
				$r['period_use'] = $r['period_mail'];
				$body = "мЕСДЮВМШЕ ОХМЦХ С ЙКХЕМРЮ ".$r['client']." (ПЮГПЕЬЕМН - ".$r['allow_bad']."):\n"; // "Неудачные пинги у клиента ".$r['client']." (разрешено - ".$r['allow_bad']."):\n"
				$body = "IP \tЙНКХВЕЯРБН МЕСДЮВМШУ ОХМЦНБ\n"; // "IP \tколичество неудачных пингов\n"
				$db->Query( // выбираем ip адрес и количество потерь, на конкретного клиента
					'select
						INET_NTOA(ip_int) as ip,
						count
					from
						monitor_ips
					where
						monitor_id='.$r['id']
				);
				while($r2=$db->NextRecord()){
					$body .= $r2['ip']."\t".$r2['count']."\n"; // ip \t количество
				}

				$r['email'] = str_replace(';',',',$r['email']);
				if(
					(
						defined('MAIL_TEST_ONLY')
					&&
						(MAIL_TEST_ONLY==1)
					)
				||
					$is_test
				) $r['email']='dnsl48@gmail.com';//'shepik@yandex.ru, mak@mcn.ru';

				$headers  = "From: MCN Info <info@mcn.ru>\n";
				$headers .= "Content-Type: text/plain; charset=windows-1251\n";
				$subj = "лНМХРНПХМЦ: ЙКХЕМР ".$r['client']; // Мониторинг: клиент ".$r['client'];
				if($r['email'])
					mail($r['email'],$subj,$body,$headers);
			}else
				$r['period_use']--;
			
			$db->Query('
				update
					monitor_clients
				set
					period_use = '.$r['period_use'].'
				where
					id='.$r['id']
			);
		}
	}
	echo "Processed: ".$cnt_total." (this thread - ".$cnt_thread.")\n";
	echo "END;#########################".date("Y-m-d H:i:s")."#######################\n\n\n";
?>