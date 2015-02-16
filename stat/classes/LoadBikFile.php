<?php
/**
 * class LoadBikFile
 * 
 * предназначен для сохранения на сервере файла с БИК'ами банков
 * файл загружает с сайта ЦБ РФ
 */
class LoadBikFile 
{	
	/**
	 * Возвращает имя DBF файла с информацией о БИК
	 * @param string $file_name имя XML файла с данными о архивах с БИК
	 */
	static public function getBikFile($file_name)
	{
		list($zip_file_name, $tm) = self::getLastFileName($file_name);
		
		$zip_file_name = 'http://www.cbr.ru/mcirabis/BIK/' . $zip_file_name;
		if (($file_path = self::getBikZipFile($zip_file_name)) === false) return false;
		
		if (($file_path = self::getBikBdfFile($file_path)) === false) return false;
		return $file_path;
	}
	/**
	 * Возвращает имя ZIP-архива и timestamp актуальности данного архива
	 * @param string $file_name имя XML файла с данными о архивах с БИК
	 */
	static private function getLastFileName($file_name)
	{
		$xml_file = file_get_contents($file_name);
		$bik_files = new SimpleXMLElement($xml_file);
		list($d, $m, $y) = explode('-', date('d-m-Y'));
		$max_time = 0;
		$cur_time = time();
		foreach ($bik_files->item as $item) {
			$data = array();
			foreach ($item->attributes() as $k => $v) 
			{
				$data[$k] = (string) $v; 
			}
			list($d, $m, $y) = explode('.', $data['date']);
			$time = mktime(0,0,0,$m,$d,$y);
			if ($time > $max_time && $time <= $cur_time) 
			{
				$file = $data['file'];
				$max_time = $time;
			}
		}
		return array($file, $max_time);
	}
	/**
	 * Возвращает путь к ZIP-архиву
	 * @param string $file_name имя ZIP-архива
	 */
	static private function getBikZipFile($file_name)
	{
		$zip_file = file_get_contents($file_name);
		$file_path = PATH_TO_ROOT.'design_c/bik.zip';
		file_put_contents($file_path, $zip_file);
		if (!sizeof($file_path)) return false;
		return $file_path;
	}
	/**
	 * Возвращает путь к DBF файлу с информацией о БИК
	 * @param string $file_path путь к ZIP-архиву
	 */
	static private function getBikBdfFile($file_path)
	{
		$zip = new ZipArchive;
		$res = $zip->open($file_path);
		if ($res === TRUE) {
			$dbf_file = $zip->getFromName('BNKSEEK.DBF') ?: $zip->getFromName('bnkseek.dbf');
			$zip->close();
		} else {
			echo $res;
		}
		unlink($file_path);
		$file_path = PATH_TO_ROOT.'design_c/bik.dbf';
		file_put_contents($file_path, $dbf_file);
		if (!sizeof($file_path)) return false;
		return $file_path;
	}
}
