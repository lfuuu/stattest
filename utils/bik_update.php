<?php
/*
    Params: 
    1 - file_name (csv file with data);
    2 - update - (true - with update, false - without update) - default false;
*/
//--------------------------------------------------------------------------------------------------
define("PATH_TO_ROOT",'../');
include PATH_TO_ROOT."conf.php";
include PATH_TO_ROOT."include/MyDBG.php";
//--------------------------------------------------------------------------------------------------
if (!isset($_SERVER['argv'][1]) || $_SERVER['argv'][1] == '-h' || $_SERVER['argv'][1] == '-help') {
    echo "\n" . Encoding::toUtf8("Первый пареметр - путь к csv файлу\n");
    echo Encoding::toUtf8("Второй параметр true - внести изменения в БД и отобразить различия, false - только отобразить различия (по умолчанию false)\n\n");
    exit;
}
$file_name = (isset($_SERVER['argv'][1])) ? $_SERVER['argv'][1] : '';
$isUpdate = (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == 'true') ? true : false;
if (!strlen($file_name) || !file_exists($file_name)) {
    echo "File ($file_name) doesn't exist!\n";
    exit;
}

$BikUpdater = new BikUpdater($file_name, $update);

if (!$BikUpdater->readMyCSV()) {die("Bad file format ($file_name)\n");}

$BikUpdater->run();

$BikUpdater->showLog();

if ($isUpdate) 
    $BikUpdater->updateBik();

//--------------------------------------------------------------------------------------------------
class BikUpdater
{
    private $csv_file = '';
    private $cols_idx = array('bank_city'=>0, 'bank_name'=>0, 'bik'=>0, 'corr_acc'=>0,'date_in'=>0,'date_izm'=>0);
    private $data = array();
    private $final_data = array('insert'=>array(), 'update'=>array());
    private $all_cnt = 0;
    public $log = array('insert'=>array(),'update'=>array());
    
//--------------------------------------------------------------------------------------------------
    public function __construct($fn = '')
    {
        $this->csv_file = $fn;
    }
    
//--------------------------------------------------------------------------------------------------
/* Чтение файла и заполнение массива данных */
    public function readMyCSV()
    {
        $row = 1;
        if (($handle = fopen($this->csv_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($row == 1 && !$this->checkFirstRow($data)) {
                    fclose($handle);
                    return false;
                }
                if ($row > 1) {
                    $row_data = $this->parseRow($data);
                    if (!isset($this->data[$row_data['bik']])) $this->data[$row_data['bik']] = $row_data;
                    else if (strtotime($row_data['date_izm']) > strtotime($this->data[$row_data['bik']]['date_izm'])) {
                        $this->data[$row_data['bik']] = $row_data;
                    }
                }
                
                
                $row++;
            }
            fclose($handle);
            
            $this->all_cnt = $row-2;
            
            return true;
        }
    }
//--------------------------------------------------------------------------------------------------
/* Проверка файла на корректность (по первой строке) и индексирование используемых столбцов */
    private function checkFirstRow($d = array())
    {
        foreach ($d as $k=>$v) {
            if ($v == 'NNP') $this->cols_idx['bank_city'] = $k;
            if ($v == 'NAMEP') $this->cols_idx['bank_name'] = $k;
            if ($v == 'NEWNUM') $this->cols_idx['bik'] = $k;
            if ($v == 'DT_IZM') $this->cols_idx['date_izm'] = $k;
            if ($v == 'KSNP') $this->cols_idx['corr_acc'] = $k;
            if ($v == 'DATE_IN') $this->cols_idx['date_in'] = $k;
            
        }
        if (in_array(0, array_values($this->cols_idx))) return false;	
        
        return true;
    }
//--------------------------------------------------------------------------------------------------
/* Разбор строки с данными */
    private function parseRow($d = array())
    {
        $res = array(
                        'bank_city'=>$d[$this->cols_idx['bank_city']], 
                        'bank_name'=>$d[$this->cols_idx['bank_name']], 
                        'bik'=>$d[$this->cols_idx['bik']], 
                        'corr_acc'=>$d[$this->cols_idx['corr_acc']],
                        'date_in'=>$d[$this->cols_idx['date_in']],
                        'date_izm'=>$d[$this->cols_idx['date_izm']]
        );
        
        return $res;
    }
//--------------------------------------------------------------------------------------------------
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
//--------------------------------------------------------------------------------------------------
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
//--------------------------------------------------------------------------------------------------
    public function showLog()
    {
        //Insert
        echo Encoding::toUtf8('Новые') . ":\n";
        if (count($this->log['insert']) > 0) {
            foreach ($this->log['insert'] as $d) {
                foreach ($d as $k=>$v) echo $k . ': ' . Encoding::toUtf8($v) . '; ';
                echo "\n";
            }
        }
        //Update
        echo Encoding::toUtf8('Измененные') . ":\n";
        if (count($this->log['update']) > 0) {
            foreach ($this->log['update'] as $bik=>$d) {
                echo 'bik - ' . $bik . ":\n";
                foreach ($d as $k=>$v) echo $k . ': ' . Encoding::toUtf8($v[0]) . ' ('.Encoding::toUtf8($v[1]).'); ';
                echo "\n";
            }
        }
        
        echo "\n" . Encoding::toUtf8('Всего: ') . $this->all_cnt . "\n";
        echo Encoding::toUtf8('Новых: ') . count($this->log['insert']) . "\n";
        echo Encoding::toUtf8('Измененных: ') . count($this->log['update'])."\n\n";
    }
//--------------------------------------------------------------------------------------------------
}

?>
