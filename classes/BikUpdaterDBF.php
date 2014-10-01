<?php 
/**
 * class BikUpdaterDBF
 * 
 * предназначен для просмотра и обновления в базе данных информации о БИК
 */
class BikUpdaterDBF
{
	/**
	 * @var string $dbf_file путь к DBF файлу c данными
	 */
	private $dbf_file = '';
	/**
	 * @var array $data массив, в котором хранится информация загруженная из файла
	 */
	private $data = array();
	/**
	 * @var array $final_data массив, в котором хранится информация для обновления базы данных
	 */
	private $final_data = array('insert'=>array(), 'update'=>array());
	/**
	 * @var int $all_cnt количество записей в файле
	 */
	private $all_cnt = 0;
	/**
	 * @var array $log массив, в котором хранится информация о разнице между данными файла и базы 
	 */
	public $log = array('insert'=>array(),'update'=>array());
	
	public function __construct($fn = '')
	{
		$this->dbf_file = $fn;
	}
    
	/** 
	 *	Чтение файла и заполнение массива данных
	 */
	public function readMyDBF()
	{
		$dbf = new dbf_class($this->dbf_file);
		
		$record_numbers = $dbf->dbf_num_rec;
		for ($i = 1; $i <= $record_numbers; $i++) {
			$row = $dbf->getRowAssoc($i);
			$row_data = $this->parseRow($row);
			if (!isset($this->data[$row_data['bik']])) 
			{
				$this->data[$row_data['bik']] = $row_data;
			} else {
				if (strtotime($row_data['date_izm']) > strtotime($this->data[$row_data['bik']]['date_izm'])) 
				{
					$this->data[$row_data['bik']] = $row_data;
				}
			}
		}
		$this->all_cnt = $record_numbers;
	
		return true;

	}
	/** 
	 *	Разбор и преобразование строки с данными 
	 */
	private function parseRow($d = array())
	{
		$res = array(
			'bank_city'=>convert_cyr_string(trim($d['NNP']), 'm', 'k'), 
			'bank_name'=>convert_cyr_string(trim($d['NAMEP']), 'm', 'k'), 
			'bik'=>convert_cyr_string(trim($d['NEWNUM']), 'm', 'k'), 
			'corr_acc'=>convert_cyr_string(trim($d['KSNP']), 'm', 'k'),
			'date_in'=>convert_cyr_string(trim($d['DATE_IN']), 'm', 'k'),
			'date_izm'=>convert_cyr_string(trim($d['DATE_IZM']), 'm', 'k')
		);
		
		return $res;
	}
	/** 
	 *	Обновление базы данных
	 */
	public function updateBik()
	{
		global $db;

		//Insert
		if (count($this->final_data['insert']) > 0) {
			foreach ($this->final_data['insert'] as $d) {
				$db->QueryInsert('bik', $d);
			}
		}
		//Update
		if (count($this->final_data['update']) > 0) {
			foreach ($this->final_data['update'] as $d) {
				$db->QueryUpdate('bik','bik', $d);
			}
		}
	}
	/** 
	 *	Сравнение БИК'ов из файла и БИК'ов из базы, составление результирующего массива данных
	 */
	public function run()
	{
		global $db;
		if (count($this->data)==0) return false;
		
		foreach ($this->data as $d) {
			unset($d['date_in'], $d['date_izm']);
			$bik = $db->GetRow("SELECT * FROM bik WHERE bik='" . $d['bik'] . "'");
			
			if ($bik) {
				if ($bik['corr_acc'] != $d['corr_acc']) {
					$this->final_data['update'][$d['bik']]['corr_acc'] = $d['corr_acc'];
					$this->log['update'][$d['bik']]['corr_acc'] = array($bik['corr_acc'],$d['corr_acc']);
				}
				if ($bik['bank_name'] != $d['bank_name']) {
					$this->final_data['update'][$d['bik']]['bank_name'] = $d['bank_name'];
					$this->log['update'][$d['bik']]['bank_name'] = array($bik['bank_name'],$d['bank_name']);
				}
				if ($bik['bank_city'] != $d['bank_city']) {
					$this->final_data['update'][$d['bik']]['bank_city'] = $d['bank_city'];
					$this->log['update'][$d['bik']]['bank_city'] = array($bik['bank_city'],$d['bank_city']);
				}
				if (isset($this->final_data['update'][$d['bik']])) $this->final_data['update'][$d['bik']]['bik'] = $d['bik'];
				
			} else {
				$this->final_data['insert'][$d['bik']] = $d;
				$this->log['insert'][$d['bik']] = $d;
			}
		}
		$this->data = array();
		
	}
	/** 
	 *	Отправка данных о изменениях БИК на ADMIN_EMAIL
	 *	@param string $log данные о изменениях
	 */
	private function sendLog($log)
	{
		include_once INCLUDE_PATH."class.phpmailer.php";
		include_once INCLUDE_PATH."class.smtp.php";

		$Mail = new PHPMailer();
		$Mail->SetLanguage("en", INCLUDE_PATH);
		$Mail->CharSet = "utf-8";
		$Mail->IsHTML(true);
		$Mail->From = "info@mcn.ru";
		$Mail->FromName="МСН Телеком";
		$Mail->Mailer='mail';
		$Mail->Host=SMTP_SERVER;
		$Mail->AddAddress(ADMIN_EMAIL);
		$Mail->Body = $log;
		$Mail->Subject = 'Изменения в БИК от ' . date('d-m-Y');
		return $Mail->Send();
	}
	/** 
	 *	Составление таблицы о изменениях в БИК'ах и отправка этой таблицы на email
	 */
	public function sendLogs()
	{
		//Insert
		$log = '<table style="text-align: centr;" border="1" width="100%"><tr><td colspan="5">Новые</td></tr>';
		if (count($this->log['insert']) > 0) {
			$first  = true;
			foreach ($this->log['insert'] as $d) {
				if ($first)
				{
					$log .= "<tr>";
					$keys = array_keys($d);
					foreach ($keys as $v) 
					{
						$log .= '<td>' . $v . '</td>';
					}
					$log .= "</tr>";
					$first = false;
				}
				$log .= "<tr>";
				foreach ($d as $k=>$v) $log .= '<td>' .  $v . '</td> ';
				$log .= "</tr>";
			}
		}
		//Update
		$log .= '</table><table style="text-align: centr;" border="1" width="100%">';
		$log .= '<tr><td colspan="5">Измененные</td></tr>';
		if (count($this->log['update']) > 0) {
			$log .= "<tr><td>bik</td><td>bank_name (new)</td><td>bank_city (new)</td><td>corr_acc (new)</td></tr>";
			foreach ($this->log['update'] as $bik=>$d) {
				$log .= '<tr><td>' . $bik . "</td>";
				$text = (empty($d['bank_name'])) ? '---' : $d['bank_name'][0] . ' ('.$d['bank_name'][1].')';
				$log .= '<td>' . $text . '</td>';
				$text = (empty($d['bank_city'])) ? '---' : $d['bank_city'][0] . ' ('.$d['bank_city'][1].')';
				$log .= '<td>' . $text . '</td>';
				$text = (empty($d['corr_acc'])) ? '---' : $d['corr_acc'][0] . ' ('.$d['corr_acc'][1].')';
				$log .= '<td>' . $text . '</td></tr>';
			}
		}
		
		$log .= '<tr><td colspan="5">' . 'Всего: ' . $this->all_cnt . "</td></tr>";
		$log .= '<tr><td colspan="5">Новых' . count($this->log['insert']) . "</td></tr>";
		$log .= '<tr><td colspan="5">Измененных: ' . count($this->log['update'])."</td></tr></table>";
		$this->sendLog($log);
		return $log;
	}
	/** 
	 *	Вывод данных о новых и изменненых БИК'ах
	 */
	public function showLog()
	{
		//Insert
		echo 'Новые' . ":\n";
		if (count($this->log['insert']) > 0) 
		{
			foreach ($this->log['insert'] as $d) 
			{
				foreach ($d as $k=>$v) echo $k . ': ' . $v . '; ';
				echo "\n";
			}
		}
		//Update
		echo 'Измененные' . ":\n";
		if (count($this->log['update']) > 0) 
		{
			foreach ($this->log['update'] as $bik=>$d) 
			{
				echo 'bik - ' . $bik . ":\n";
				foreach ($d as $k=>$v) echo $k . ': ' . $v[0] . ' ('.$v[1].'); ';
				echo "\n";
			}
		}
		echo "\n" . 'Всего: ' . $this->all_cnt . "\n";
		echo 'Новых: ' . count($this->log['insert']) . "\n";
		echo 'Измененных: ' . count($this->log['update'])."\n\n";
	}
}