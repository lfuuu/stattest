<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";
	include PATH_TO_ROOT."include/MyDBG.php";
	//--------------------------------------------------------------------------------------------------
	if (date('j') != 5 && !empty($_SERVER['argv'])) 
	{
		echo 'Wrong day to update Bik';
		exit;
	}
	// проверка параметров от сервера
	if ($_SERVER['argv'][1] == '-h' || $_SERVER['argv'][1] == '-help') {
		echo "\n" . Encoding::toUtf8("Первый параметр true - загрузка нового файла, false - использовать старый файл (по умолчанию false)\n\n");
		echo Encoding::toUtf8("Второй параметр true - внести изменения в БД, false - без изменений в БД(по умолчанию false)\n");
		echo Encoding::toUtf8("Третий параметр true - вывод на экран, false - отправка email (по умолчанию false)\n\n");
		exit;
	}
	$loadFile = (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'true') ? true : false;
	$isUpdate = (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == 'true') ? true : false;
	$toScreen = (isset($_SERVER['argv'][3]) && $_SERVER['argv'][3] == 'true') ? true : false;
	
	// проверка парамтров из запроса
	if (!$loadFile && isset($_GET['load_file']) && $_GET['load_file'] == 1) 
	{
		$loadFile = true;
	}
	if (!$isUpdate && isset($_GET['make_update']) && $_GET['make_update'] == 1) 
	{
		$isUpdate = true;
	}
	if (!$toScreen && isset($_GET['to_screen']) && $_GET['to_screen'] == 1) 
	{
		$toScreen = true;
	}
	
	if ($loadFile)
	{
		$file_path = LoadBikFile::getBikFile('http://www.cbr.ru/mcirabis/PluginInterface/GetBicCatalog.aspx');
		if ($file_path === false)
		{
			echo 'incorrect day or no file';
			exit;
		}
	} else {
		$file_path = PATH_TO_ROOT.'design_c/bik.dbf';
	}
	
	$BikUpdaterDBF = new BikUpdaterDBF($file_path);

	if (!$BikUpdaterDBF->readMyDBF()) {die("Bad file format ($file_path)\n");}

	$BikUpdaterDBF->run();

	$BikUpdaterDBF->Logs($toScreen);

	if ($isUpdate) 
		$BikUpdaterDBF->updateBik();

?>