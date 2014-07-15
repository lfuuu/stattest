<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";
	include PATH_TO_ROOT."include/MyDBG.php";
	
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
			echo "\n" . Encoding::toUtf8("Параметр =  get - загрузка нового файла\n\n");
			echo Encoding::toUtf8("Параметр = send - отправка email с diff-файлом\n");
			echo Encoding::toUtf8("Параметр = show - вывод на экран разницы\n");
			echo Encoding::toUtf8("Параметр = update - вывод на экран разницы и внести изменения в БД \n\n");
			exit;
	}

	if ($loadFile)
	{
		$file_path = LoadBikFile::getBikFile('http://www.cbr.ru/mcirabis/PluginInterface/GetBicCatalog.aspx');
		if ($file_path === false)
		{
			echo "\n" . Encoding::toUtf8("Файл не был обновлен\n\n");
			exit;
		} else {
			echo "\n" . Encoding::toUtf8("Файл был обновлен\n\n");
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
			echo "\n" . Encoding::toUtf8("Файл был отправлен\n\n");
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