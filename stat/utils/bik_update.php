<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";

	$loadFile = $sendDiff = $toScreen = $isUpdate = false;
	
	switch ($_SERVER['argv'][1]) {
		case 'get':
			$loadFile = true;
			break;
		case 'send': 
			$sendDiff = true;
			break;
		case 'show':
			$toScreen = true;
			break;
		case 'update':
			$isUpdate = true;
			$toScreen = true;
			break;
		default: 
			echo "\n" . "Параметр =  get - загрузка нового файла\n\n";
			echo "Параметр = send - отправка email с diff-файлом\n";
			echo "Параметр = show - вывод на экран разницы\n";
			echo "Параметр = update - вывод на экран разницы и внести изменения в БД \n\n";
			exit;
	}

	if ($loadFile)
	{
		$file_path = LoadBikFile::getBikFile('http://www.cbr.ru/mcirabis/PluginInterface/GetBicCatalog.aspx');
		if ($file_path === false)
		{
			echo "\n" . "Файл не был обновлен\n\n";
			exit;
		} else {
			echo "\n" . "Файл был обновлен\n\n";
			exit;
		}
	} else {
		$file_path = PATH_TO_ROOT.'design_c/bik.dbf';
	}
	
	$BikUpdaterDBF = new BikUpdaterDBF($file_path);

	if (!$BikUpdaterDBF->readMyDBF()) 
	{
		die("Bad file format ($file_path)\n");
	}

	$BikUpdaterDBF->run();
	
	if ($sendDiff) 
	{
		if ($BikUpdaterDBF->sendLogs()) 
		{
			echo "\n" . "Файл был отправлен\n\n";
		}
	}
	
	if ($toScreen) 
	{
		$BikUpdaterDBF->showLog();
	}
	
	if ($isUpdate) 
	{
		$BikUpdaterDBF->updateBik();
	}
?>