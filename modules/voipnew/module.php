<?php
include_once 'definfo.php';

class _voipnew_prices_parser
{
    public static function make_numbers(&$mres, $number1, $number2, $prefix = '', $max = 0)
    {
        if ($number1 == '' && $number2 == '') {
            $mres[] = $prefix;
            return;
        }

        $nn1 = (int)substr($number1, 0, 1);
        $nn2 = (int)substr($number2, 0, 1);

        if (($nn1 == 0) && $nn2 == 9) {
            return;
        }

        if ($max == 1) {
            _voipnew_prices_parser::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn1, 1);
            for ($n = $nn1 + 1; $n <= 9; $n = $n + 1) {
                $mres[] = $prefix . $n;
            }
        }
        if ($max == 2) {
            for ($n = 0; $n <= $nn2 - 1; $n = $n + 1) {
                $mres[] = $prefix . $n;
            }
            _voipnew_prices_parser::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn2, 2);
        }
        if ($max == 0) {
            if ($nn1 == $nn2) {
                _voipnew_prices_parser::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn1, 0);
            } else {
                if (strlen($number1) <= 1) {
                    $mres[] = $prefix . $nn1;
                } else
                    _voipnew_prices_parser::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn1, 1);
                for ($n = $nn1 + 1; $n <= $nn2 - 1; $n = $n + 1) {
                    $mres[] = $prefix . $n;
                }
                if (strlen($number2) <= 1) {
                    $mres[] = $prefix . $nn2;
                } else
                    _voipnew_prices_parser::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn2, 2);
            }
        }
    }

    public static function &xls_read($fname)
    {
        require_once INCLUDE_PATH . 'exel/excel_reader2.php';
        @$xlsreader = new Spreadsheet_Excel_Reader($fname, false, 'koi8-r');
        return $xlsreader;
    }

    public static function &csv_read($fname)
    {
        $f = fopen($fname, 'r');
        $csv = array();
        while (($row = fgetcsv($f, 1 * 1024 * 1024, "\t", '"'))) {
            $csv[] = $row;
        }
        fclose($f);
        return $csv;
    }

    public static function &open_file($filename, $format = 'Excel5', $sheet = -1)
    {
        require_once INCLUDE_PATH . 'exel/PHPExcel.php';
        require_once INCLUDE_PATH . 'exel/PHPExcel/IOFactory.php';
        $excelReader = PHPExcel_IOFactory::createReader($format);
        $excelReader->setReadDataOnly(true);

        $objExcel = $excelReader->load($filename);
        if (!$objExcel) {
            $objExcel = false;
            return $objExcel;
        }
        if ($sheet == -1)
            $objWorksheet = @$objExcel->getActiveSheet();
        else
            $objWorksheet = @$objExcel->getSheet($sheet);
        if (!$objWorksheet) {
            $objWorksheet = false;
            return $objWorksheet;
        }

        return $objWorksheet;
    }

    public static function &read_table(&$objWorksheet, $fields)
    {
        $rowIterator = $objWorksheet->getRowIterator();
        $table = array();

        foreach ($fields as $k => $f)
            $fields[$k]['col'] = false;
        $isFindHeader = false;
        foreach ($rowIterator as $row) {
            if (!$isFindHeader) {
                $cellIterator = $row->getCellIterator();
                foreach ($cellIterator as $cell) {
                    foreach ($fields as $k => $f) {
                        if ($cell->getValue() == $f['v']) {
                            $fields[$k]['col'] = $cellIterator->key();
                            $isFindHeader = true;
                        }
                    }
                }
                if ($isFindHeader) {
                    foreach ($fields as $k => $f) {
                        if ($f['col'] === false) {
                            $table = false;
                            return $table;
                        }
                    }
                }
            } else {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                $table_row = array();
                foreach ($fields as $k => $f)
                    $table_row[$k] = '';
                foreach ($cellIterator as $cell) {
                    foreach ($fields as $k => $f) {
                        if ($cellIterator->key() == $f['col']) {
                            if ($f['t'] == 'F') {
                                $table_row[$k] = number_format(floatval(str_replace(',', '.', $cell->getValue())), 4);
                            } elseif ($f['t'] == 'D') {
                                $table_row[$k] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP(trim($cell->getValue())));
                            } else {
                                $table_row[$k] = strip_tags(trim($cell->getValue()));
                            }
                        }
                    }
                }
                $table[] = $table_row;
            }
        }

        return $table;
    }

    public static function &read_beeline_full1($filename)
    {

        global $pg_db;

        $objWorksheet = _voipnew_prices_parser::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('defcode' => array('t' => 'S', 'v' => 'Code/CNP'),
            'startdate' => array('t' => 'D', 'v' => 'SDate'),
            'price' => array('t' => 'F', 'v' => 'RATE'),
            'destination' => array('t' => 'S', 'v' => 'Group/Destination'),
            'type' => array('t' => 'S', 'v' => 'Type'),
            'currency_id' => array('t' => 'S', 'v' => 'CUR'),
        );
        $table = _voipnew_prices_parser::read_table($objWorksheet, $fields);
        if ($table === false) return $table;


        foreach ($table as $k => &$v) {
            $table[$k]['deleting'] = 0;
            if ($table[$k]['currency_id'] != 'RUB') die('bad currency'); else $table[$k]['currency_id'] = 1;

            unset($table[$k]['type']);
        }
        return $table;
    }

    public static function &read_beeline_full2($filename)
    {

        $objWorksheet = _voipnew_prices_parser::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('destination' => array('t' => 'S', 'v' => 'DEST'),
            'defcode1' => array('t' => 'S', 'v' => 'COUNTRY CODE'),
            'defcode2' => array('t' => 'S', 'v' => 'ROLLUP'),
            'price' => array('t' => 'F', 'v' => 'RATE'),
            'startdate' => array('t' => 'D', 'v' => 'EFFECTIVED'),
        );
        $table = _voipnew_prices_parser::read_table($objWorksheet, $fields);
        if ($table === false) return $table;
        $defs = array();
        foreach ($table as $row) {
            $codes_list = explode(',', $row['defcode2']);

            foreach ($codes_list as $code) {
                $period = explode('-', $code);

                if (count($period) == 1) {
                    $n = trim($period[0]);
                    if (!preg_match('#^\d*$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    $defs[] = array('defcode' => $row['defcode1'] . $n,
                        'deleting' => 0,
                        'startdate' => $row['startdate'],
                        'price' => $row['price'],
                        'currency_id' => 1,
                        'destination' => $row['destination']);

                } elseif (count($period) == 2) {
                    $n = trim($period[0]);
                    $n2 = trim($period[1]);
                    if (!preg_match('#^\d+$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    if (!preg_match('#^\d+$#', $n2)) {
                        $table = false;
                        return $table;
                    }
                    $l = strlen($n);
                    $n = (int)$n;
                    $n2 = (int)$n2;
                    while ($n <= $n2) {
                        while (strlen($n) < $l) $n = '0' . $n;
                        $defs[] = array('defcode' => $row['defcode1'] . $n,
                            'deleting' => 0,
                            'startdate' => $row['startdate'],
                            'price' => $row['price'],
                            'currency_id' => 1,
                            'destination' => $row['destination']);
                        $n = $n + 1;
                    }
                } else {
                    $table = false;
                    return $table;
                }
            }
        }
        return $defs;
    }

    public static function &read_beeline_changes($filename)
    {

        $objWorksheet = _voipnew_prices_parser::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('destination' => array('t' => 'S', 'v' => 'DEST'),
            'defcode1' => array('t' => 'S', 'v' => 'COUNTRY CODE'),
            'defcode2' => array('t' => 'S', 'v' => 'ROLLUP'),
            'price' => array('t' => 'F', 'v' => 'RATE'),
            'comments' => array('t' => 'S', 'v' => 'COMMENTS'),
            'startdate' => array('t' => 'D', 'v' => 'EFFECTIVED'),
        );
        $table = _voipnew_prices_parser::read_table($objWorksheet, $fields);
        if ($table === false) return $table;
        $defs = array();
        foreach ($table as $row) {
            $codes_list = explode(',', $row['defcode2']);
            foreach ($codes_list as $code) {
                $period = explode('-', $code);

                if (count($period) == 1) {
                    $n = trim($period[0]);
                    if (!preg_match('#^\d*$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    $defs[] = array('defcode' => $row['defcode1'] . $n,
                        'startdate' => $row['startdate'],
                        'price' => $row['price'],
                        'deleting' => 0,
                        'currency_id' => 1,
                        'destination' => $row['destination']);

                } elseif (count($period) == 2) {
                    $n = trim($period[0]);
                    $n2 = trim($period[1]);
                    if (!preg_match('#^\d+$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    if (!preg_match('#^\d+$#', $n2)) {
                        $table = false;
                        return $table;
                    }
                    $l = strlen($n);
                    $n = (int)$n;
                    $n2 = (int)$n2;

                    while ($n <= $n2) {
                        while (strlen($n) < $l) $n = '0' . $n;
                        $defs[] = array('defcode' => $row['defcode1'] . $n,
                            'startdate' => $row['startdate'],
                            'price' => $row['price'],
                            'deleting' => 0,
                            'currency_id' => 1,
                            'destination' => $row['destination']);
                        $n = $n + 1;
                    }
                } else {
                    $table = false;
                    return $table;
                }
            }
            $del_list = explode('Del: ' . $row['defcode1'], $row['comments']);
            if (count($del_list) < 2) continue;
            $del_list = trim($del_list[1]);

            $del_list = explode('Add:', $del_list);
            $del_list = trim($del_list[0]);

            $del_list = explode(',', $del_list);
            foreach ($del_list as $code) {
                $period = explode('-', $code);

                if (count($period) == 1) {
                    $n = trim($period[0]);
                    if (!preg_match('#^\d*$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    $defs[] = array('defcode' => $row['defcode1'] . $n,
                        'startdate' => $row['startdate'],
                        'price' => 0,
                        'deleting' => 1,
                        'currency_id' => 1,
                        'destination' => $row['destination']);

                } elseif (count($period) == 2) {
                    $n = trim($period[0]);
                    $n2 = trim($period[1]);
                    if (!preg_match('#^\d+$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    if (!preg_match('#^\d+$#', $n2)) {
                        $table = false;
                        return $table;
                    }
                    $n = (int)$n;
                    $n2 = (int)$n2;

                    while ($n <= $n2) {
                        $defs[] = array('defcode' => $row['defcode1'] . $n,
                            'startdate' => $row['startdate'],
                            'price' => 0,
                            'deleting' => 1,
                            'currency_id' => 1,
                            'destination' => $row['destination']);
                        $n = $n + 1;
                    }
                } else {
                    $table = false;
                    return $table;
                }
            }

        }
        return $defs;
    }


    public static function &read_mtt_full($filename)
    {

        $objWorksheet = _voipnew_prices_parser::open_file($filename, 'Excel5', 0);
        if ($objWorksheet === false) return false;
        $fields = array(
            'defcode2' => array('t' => 'S', 'v' => 'Коды АВС abx'),
            'price' => array('t' => 'F', 'v' => 'РТ, руб./мин.'),
        );
        $table = _voipnew_prices_parser::read_table($objWorksheet, $fields);
        if ($table === false) return false;

        $objWorksheet = _voipnew_prices_parser::open_file($filename, 'Excel5', 1);
        if ($objWorksheet === false) return false;
        $fields = array(
            'defcode1' => array('t' => 'S', 'v' => 'Коды АВС'),
            'defcode2' => array('t' => 'S', 'v' => 'Коды АВС abx'),
            'price' => array('t' => 'F', 'v' => 'РТ, руб./мин.'),
        );
        $table2 = _voipnew_prices_parser::read_table($objWorksheet, $fields);
        if ($table2 === false) return false;

        $table = array_merge($table, $table2);


        $defs = array();
        foreach ($table as $row) {
            if (!isset($row['defcode1'])) $row['defcode1'] = '7';
            $row['defcode2'] = str_replace(' ', '', $row['defcode2']);
            $i = strpos($row['defcode2'], '(');
            if ($i !== FALSE) {
                $row['defcode1'] .= substr($row['defcode2'], 0, $i);
                $row['defcode2'] = substr($row['defcode2'], $i + 1);
                $row['defcode2'] = str_replace(')', '', $row['defcode2']);
            }
            if ($row['defcode2'] == '') {
                $row['defcode2'] = $row['defcode1'];
                $row['defcode1'] = '';
            }
            $codes_list = explode(',', $row['defcode2']);
            foreach ($codes_list as $code) {
                $period = explode('-', $code);

                if (count($period) == 1) {
                    $n = trim($period[0]);
                    if (!preg_match('#^\d*$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    $defs[] = array('defcode' => $row['defcode1'] . $n,
                        'deleting' => 0,
                        'startdate' => '2000-01-01',
                        'price' => $row['price'],
                        'currency_id' => 1,);

                } elseif (count($period) == 2) {
                    $n = trim($period[0]);
                    $n2 = trim($period[1]);
                    if (!preg_match('#^\d+$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    if (!preg_match('#^\d+$#', $n2)) {
                        $table = false;
                        return $table;
                    }
                    $l = strlen($n);
                    $n = (int)$n;
                    $n2 = (int)$n2;

                    while ($n <= $n2) {
                        while (strlen($n) < $l) $n = '0' . $n;
                        $defs[] = array('defcode' => $row['defcode1'] . $n,
                            'deleting' => 0,
                            'startdate' => '2000-01-01',
                            'price' => $row['price'],
                            'currency_id' => 1);
                        $n = $n + 1;
                    }
                } else {
                    $table = false;
                    return $table;
                }
            }
        }
        return $defs;
    }

    public static function &read_mcn_prime_full($filename)
    {

        global $pg_db;

        $objWorksheet = _voipnew_prices_parser::open_file($filename);

        $rowIterator = $objWorksheet->getRowIterator();
        $table = array();
        $isFindHeader = false;
        $price_column = 1;
        foreach ($rowIterator as $row) {
            if (!$isFindHeader) {
                $isFindHeader = true;
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                foreach ($cellIterator as $cell) {
                    if (strip_tags(trim($cell->getValue())) == "Новая  цена") {
                        $price_column = $cellIterator->key();
                        break;
                    }
                }
            } else {
                $table_row = array();
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                $table_row = array('startdate' => date('Y-m-d'), 'deleting' => 0, 'description' => '', 'currency_id' => 1);
                foreach ($cellIterator as $cell) {
                    if ($cellIterator->key() == 0) {
                        $table_row['defcode'] = strip_tags(trim($cell->getCalculatedValue()));
                    }
                    if ($cellIterator->key() == $price_column) {
                        $table_row['price'] = number_format(floatval(str_replace(',', '.', $cell->getCalculatedValue())), 4);
                    }
                }
                $table[] = $table_row;
            }
        }
        return $table;
    }


    public static function &read_orange_full($filename)
    {

        global $pg_db;

        $objWorksheet = _voipnew_prices_parser::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('defcode' => array('t' => 'S', 'v' => 'КОД'),
            'price' => array('t' => 'F', 'v' => 'Tariff, RuR /min'),
        );
        $table = _voipnew_prices_parser::read_table($objWorksheet, $fields);
        if ($table === false) return $table;

        $defs = array();
        foreach ($table as $k => &$v) {
            $table[$k]['startdate'] = date('Y-m-d');
            $table[$k]['deleting'] = 0;
            $table[$k]['currency_id'] = 1;


            $row = $table[$k];

            $codes_list = explode(',', $row['defcode']);
            foreach ($codes_list as $code) {
                $period = explode('-', $code);

                if (count($period) == 1) {
                    $n = trim($period[0]);
                    if (!preg_match('#^\d*$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    $defs[] = array('defcode' => $n,
                        'deleting' => 0,
                        'startdate' => $row['startdate'],
                        'price' => $row['price'],
                        'currency_id' => 1);

                } elseif (count($period) == 2) {
                    $n = trim($period[0]);
                    $n2 = trim($period[1]);
                    if (!preg_match('#^\d+$#', $n)) {
                        $table = false;
                        return $table;
                    }
                    if (!preg_match('#^\d+$#', $n2)) {
                        $table = false;
                        return $table;
                    }

                    $mres = array();
                    _voipnew_prices_parser::make_numbers($mres, $n, $n2);
                    foreach ($mres as $n) {
                        $defs[] = array('defcode' => $n,
                            'deleting' => 0,
                            'startdate' => $row['startdate'],
                            'price' => $row['price'],
                            'currency_id' => 1);
                    }
                } else {
                    $table = false;
                    return $table;
                }
            }

        }
        /*		echo "<pre>";
                print_r($defs);
                echo "</pre>";
                die();*/
        return $defs;
    }


    public static function mcn_read_price($str)
    {
        $l = explode("\n", $str);
        $len = count($l);
        $defs = array();
        for ($i = 0; $i < $len; $i++) {
            $d = array_map('trim', explode("\t", $l[$i]));
            if (!is_numeric($d[0]))
                continue;
            $defs[$d[0]] = $d[1];
        }
        return $defs;
    }
}

class _voipnew_export_csv
{
    public static function put($str, $name)
    {
        $str = iconv('koi8r', 'cp1251', $str);
        header("Content-Type: application/force-download");
        header("Content-Length: " . strlen($str));
        header('Content-Disposition: attachment; filename="' . $name . '.csv"');
        header("Cache-Control: public, must-revalidate");
        header("Pragma: hack");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Content-Transfer-Encoding: binary");
        echo $str;
        exit();
    }
}

include_once 'analyze_pricelist_report.php';
include_once 'operator_report.php';
include_once 'routing_report.php';


class m_voipnew extends IModule
{
    private $_inheritances = array();

    public function __call($method, array $arguments = array())
    {
        foreach ($this->_inheritances as $inheritance) {
            $inheritance->invoke($method, $arguments);
        }
    }

    protected function _addInheritance(Inheritance $inheritance)
    {
        $this->_inheritances[get_class($inheritance)] = $inheritance;
    }

    public function __construct()
    {
        $this->_addInheritance(new m_voipnew_analyze_pricelist_report);
        $this->_addInheritance(new m_voipnew_operator_report);
        $this->_addInheritance(new m_voipnew_routing_report);
    }

    public function voipnew_raw_files()
    {
        global $pg_db, $design;

        $f_pricelist_id = get_param_protected('pricelist', '0');

        $query = "  select f.id,p.operator_id, o.name as operator,f.date,f.full,f.format,f.filename,f.active,f.startdate,f.rows
                    from voip.raw_file f
                    left join voip.pricelist p on p.id=f.pricelist_id
                    left join voip.operator o on o.id=p.operator_id and o.region=p.region
                    where f.pricelist_id=$f_pricelist_id
                    order by f.startdate desc, f.date desc";
        $design->assign('files_list', $pg_db->AllRecords($query));

        $query = "select * from voip.pricelist where id=$f_pricelist_id ";
        $design->assign('pricelist', $pg_db->GetRow($query));


        $design->AddMain('voipnew/raw_files.html');
    }

    public function voipnew_view_raw_file()
    {
        global $pg_db, $design;


        $id = get_param_protected('id', 0);
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');

        $query = "  select o.name as operator, p.name as pricelist,f.id,f.date,f.format,f.filename,f.active,f.startdate, f.rows ,c.name as currency
                    from voip.raw_file f
                    left join voip.pricelist p on p.id=f.pricelist_id
                    left join voip.operator o on o.id=p.operator_id
                    left join public.currency c on c.id=f.currency_id
                    WHERE f.id=" . $id;
        $file = $pg_db->GetRow($query);
        $design->assign('file', $file);

        $filter = '';
        if ($f_dest_group >= 0) $filter .= ' and g.dest=' . intval($f_dest_group);
        if ($f_country_id > 0) $filter .= ' and g.country=' . intval($f_country_id);
        if ($f_region_id > 0) $filter .= ' and g.region=' . intval($f_region_id);

        $pg_db->Query('BEGIN');
        try {
            $query = "
                        SELECT d.defcode, r.deleting, r.price,
                            dgr.shortname as dgroup,
                            g.name as destination, d.mob
                        FROM voip.raw_price r
                            LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                            LEFT JOIN geo.geo g ON g.id=d.geo_id
                            LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                        WHERE rawfile_id={$id} {$filter}
                        order by g.dest, g.name, d.mob, d.defcode";
            $page = get_param_integer("page", 1);
            $recCount = 0;
            $recPerPage = 100;

            $pg_db->Query('DECLARE curs CURSOR FOR ' . $query);
            if ($page > 1) {
                $pg_db->Query('MOVE FORWARD ' . (($page - 1) * $recPerPage) . ' IN curs');
                $recCount = $recCount + $pg_db->AffectedRows();
            }
            $pg_db->Query('FETCH ' . $recPerPage . ' FROM curs');
            $recCount = $recCount + $pg_db->AffectedRows();
            $defs = $pg_db->AllRecords('');
            $pg_db->Query('MOVE FORWARD ALL IN curs');
            $recCount = $recCount + $pg_db->AffectedRows();
            util::pager_pg($recCount, $recPerPage);
        } catch (Exception $e) {
        }
        $pg_db->Query('END');

        $design->assign('defs_list', $defs);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));


        $design->AddMain('voipnew/view_raw_file.html');
    }

    public function voipnew_compare_raw_file()
    {
        global $pg_db, $design;

        $id = get_param_protected('id', 0);
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');

        $query = "  select o.name as operator, p.name as pricelist,f.id,f.date,f.format,f.filename,f.active,f.startdate, f.rows ,c.name as currency
                    from voip.raw_file f
                    left join voip.pricelist p on p.id=f.pricelist_id
                    left join voip.operator o on o.id=p.operator_id
                    left join public.currency c on c.id=f.currency_id
                    WHERE f.id=" . $id;
        $design->assign('file', $pg_db->GetRow($query));

        $filter = 'where 1=1';
        if ($f_dest_group >= 0) $filter .= ' and g.dest=' . intval($f_dest_group);
        if ($f_country_id > 0) $filter .= ' and g.country=' . intval($f_country_id);
        if ($f_region_id > 0) $filter .= ' and g.region=' . intval($f_region_id);

        $pg_db->Query('BEGIN');
        try {
            $query = "
                        SELECT d.defcode, r.*,
                            r.new_price - r.old_price price_diff,
                            CASE WHEN r.old_price = 0 THEN 0
                            ELSE
                            CAST((r.new_price - r.old_price) * 100 / r.old_price as NUMERIC(6,2))
                            END price_diff_pr,

                            dgr.shortname as dgroup,
                            g.name as destination, d.mob
                        FROM select_rawfile_diff($id) r
                        LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                        LEFT JOIN geo.geo g ON g.id=d.geo_id
                        LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                        {$filter}
                        order by g.dest, g.name, d.mob, d.defcode ";
            $page = get_param_integer("page", 1);
            $recCount = 0;
            $recPerPage = 100;

            $pg_db->Query('DECLARE curs CURSOR FOR ' . $query);
            if ($page > 1) {
                $pg_db->Query('MOVE FORWARD ' . (($page - 1) * $recPerPage) . ' IN curs');
                $recCount = $recCount + $pg_db->AffectedRows();
            }
            $pg_db->Query('FETCH ' . $recPerPage . ' FROM curs');
            $recCount = $recCount + $pg_db->AffectedRows();
            $defs = $pg_db->AllRecords('');
            $pg_db->Query('MOVE FORWARD ALL IN curs');
            $recCount = $recCount + $pg_db->AffectedRows();
            util::pager_pg($recCount, $recPerPage);
        } catch (Exception $e) {
        }
        $pg_db->Query('END');

        $design->assign('defs_list', $defs);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));

        $design->AddMain('voipnew/compare_raw_file.html');

    }

    public function voipnew_delete_raw_file()
    {
        global $pg_db, $design;

        $id = get_param_protected('id', 0);


        $query = "delete from voip.raw_file where id=" . $id;
        $pg_db->Query($query);
        if ($pg_db->mError == '') {
            header('location: index.php?module=voipnew&action=raw_files');
            exit;
        }
        $this->voip_view_raw_file();
    }

    public function voipnew_activatedeactivate()
    {
        global $pg_db, $design;
        $id = get_param_protected('id', 0);

        set_time_limit(0);

        if (isset($_POST['activate'])) {
            $req = $pg_db->QueryUpdate('voip.raw_file', 'id', array('id' => $id, 'active' => 1));
        } elseif (isset($_POST['deactivate'])) {
            $req = $pg_db->QueryUpdate('voip.raw_file', 'id', array('id' => $id, 'active' => 0));
        }
        if (!$req) {
            trigger_error('Ошибка Активации/Деактивации');
        } else {
            header('location: index.php?module=voipnew&action=view_raw_file&id=' . $id);
        }

    }

    public function insert_raw_prices($new_rows)
    {
        global $pg_db;
        $q = "INSERT INTO voip.raw_price (rawfile_id, ndef, deleting, price, mob) VALUES ";
        $is_first = true;
        foreach ($new_rows as $row) {
            if ($is_first == false) $q .= ","; else $is_first = false;
            if (strpos($row['destination'], '(mob)') !== false)
                $mob = "TRUE";
            else
                $mob = 'NULL';

            $q .= "('" . pg_escape_string($row['rawfile_id']) . "','" . pg_escape_string($row['defcode']) . "','" . pg_escape_string($row['deleting']) . "','" . pg_escape_string($row['price']) . "'," . $mob . ")";
        }

        //echo "|<pre>".$q."</pre>|";

        $pg_db->Query($q);

        echo $pg_db->mError;
        return ($pg_db->mError == '');
    }

    public function save_price_file($raw_file, $defs)
    {
        global $pg_db;
        $correct = $pg_db->GetValue('select correct from voip.pricelist where id=' . intval($raw_file['pricelist_id']));
        if ($correct === false) $correct = 0;
        $pg_db->Begin();
        $rawfile_id = $pg_db->QueryInsert('voip.raw_file', $raw_file);
        if ($rawfile_id > 0 && $raw_file['rows'] > 0) {
            $new_rows = array();
            foreach ($defs as $row) {
                $row['rawfile_id'] = $rawfile_id;
                $row['pricelist_id'] = $raw_file['pricelist_id'];
                $row['price'] = $row['price'] + $correct;
                $new_rows[] = $row;
                if (count($new_rows) >= 10000) {
                    if (!$this->insert_raw_prices($new_rows)) {
                        echo $pg_db->mError;
                        $pg_db->Rollback();
                        die('error');
                        return 0;
                    }
                    $new_rows = array();
                }
            }
            if (count($new_rows) >= 0) {
                if (!$this->insert_raw_prices($new_rows)) {
                    echo $pg_db->mError;
                    $pg_db->Rollback();
                    return 0;
                }
            }
            $pg_db->Query("select new_destinations({$rawfile_id})");
            echo $pg_db->mError;
            $pg_db->Commit();

        } else {

            echo $pg_db->mError;
            $pg_db->Rollback();
            return 0;
        }
        return $rawfile_id;
    }

    public function voipnew_upload()
    {
        global $pg_db, $design;

        set_time_limit(0);
        if (isset($_POST['step']) && $_POST['step'] == 'upfile') {
            if (!$_FILES['upfile']) {
                trigger_error('Пожалуйста, загрузите файл для обработки');
                $design->AddMain('voipnew/upload.html');
                return;
            } elseif ($_FILES['upfile']['error']) {
                trigger_error('При загрузке файла произошла ошибка. Пожалуйста, попробуйте еще раз');
                $design->AddMain('voipnew/upload.html');
                return;
            }

            $f = & $_FILES['upfile'];
            if (in_array($_POST['ftype'], array('xls_beeline', 'xls_beeline_change', 'xls_arktel', 'xls_arktel_change'))
                &&
                $f['type'] <> 'application/vnd.ms-excel'
            ) {
                trigger_error('Формат файла указан не правильно');
                $design->AddMain('voipnew/upload.html');
                return;
            }
            $pricelist_id = get_param_protected('pricelist_id', '0');

            $raw_file = array('date' => date('Y-m-d H:i:s'),
                'format' => $_POST['ftype'],
                'filename' => $f['name'],
                'full' => 0,
                'active' => 0);


            if ($_POST['ftype'] == 'xls_beeline_full1') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 1;
                $defs = _voipnew_prices_parser::read_beeline_full1($f['tmp_name']);

            } elseif ($_POST['ftype'] == 'xls_beeline_full2') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 1;
                $defs = _voipnew_prices_parser::read_beeline_full2($f['tmp_name']);

            } elseif ($_POST['ftype'] == 'xls_beeline_changes') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $defs = _voipnew_prices_parser::read_beeline_changes($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_mtt_full') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 1;
                $defs = _voipnew_prices_parser::read_mtt_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_arktel_changes') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 0;
                $defs = _voipnew_prices_parser::read_arktel_changes($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_mcn_prime_full') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 1;
                $defs = _voipnew_prices_parser::read_mcn_prime_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_mcn_prime_changes') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 0;
                $defs = _voipnew_prices_parser::read_mcn_prime_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_orange_full') {
                $raw_file['pricelist_id'] = $pricelist_id;
                $design->assign('operator_id', $raw_file['pricelist_id']);

                $raw_file['full'] = 1;
                $defs = _voipnew_prices_parser::read_orange_full($f['tmp_name']);
            }

            if ($defs === false) {
                trigger_error('Ошибка чтения файла');
                $design->AddMain('voipnew/upload.html');
                return;
            }

            if ($raw_file['full'] == 1) {
                function cmp($a, $b)
                {
                    return strcmp($a["defcode"], $b["defcode"]);
                }

                usort($defs, "cmp");

                $definfo = new DefInfo();
                $defs2 = array();
                $pre_def = '';
                $pre_country_id = '';
                $pre_city_region_id = '';
                $pre_mob = '';
                $pre_price = '';
                $pre_l = 0;
                $m_def = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $m_country_id = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $m_city_region_id = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
                //			$m_mob = array('','','','','','','','','','','','','','','','','','','','','');
                $m_price = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
                foreach ($defs as $k => $v) {
                    //if (substr($v['defcode'], 0, 4) != '7903') continue;

                    $def = $v['defcode'];
                    $country_id = $definfo->get_country($v['defcode']);
                    $city_region_id = $definfo->get_region($v['defcode']);
                    //				$mob = $definfo->get_mob($v['defcode']);
                    $price = $v['price'];

                    $cur_l = strlen($def);
                    if ($pre_l <> $cur_l || substr($def, 0, $cur_l - 1) <> substr($def, 0, $pre_l - 1)) {
                        if ($pre_l > $cur_l) $n = $pre_l; else $n = $cur_l;
                        while ($n > 0) {
                            if ($m_def[$n] == '' || $m_def[$n] <> substr($def, 0, strlen($m_def[$n]))) {
                                $m_def[$n] = '';
                                $m_country_id[$n] = '';
                                $m_city_region_id[$n] = '';
                                //							$m_mob[$n] = '';
                                $m_price[$n] = '';
                            }
                            $n = $n - 1;
                        }
                        $pre_def = '';
                        $pre_country_id = '';
                        $pre_city_region_id = '';
                        //					$pre_mob = '';
                        $pre_price = '';
                        $n = $cur_l - 1;
                        while ($n > 0) {
                            if ($pre_def === '' && $m_def[$n] !== '')
                                $pre_def = $m_def[$n];
                            if ($pre_country_id === '' && $m_country_id[$n] !== '')
                                $pre_country_id = $m_country_id[$n];
                            if ($pre_city_region_id === '' && $m_city_region_id[$n] !== '')
                                $pre_city_region_id = $m_city_region_id[$n];
                            //						if ($pre_mob === '' && $m_mob[$n] !== '')
                            //							$pre_mob = $m_mob[$n];
                            if ($pre_price === '' && $m_price[$n] !== '')
                                $pre_price = $m_price[$n];
                            $n = $n - 1;
                        }
                    }
                    $m_def[$cur_l] = $def;
                    $m_country_id[$cur_l] = $country_id;
                    $m_city_region_id[$cur_l] = $city_region_id;
                    //				$m_mob[$cur_l] = $mob;
                    $m_price[$cur_l] = $price;

                    if (strpos($def, $pre_def) === 0 &&
                        $country_id == $pre_country_id &&
                        $city_region_id == $pre_city_region_id &&
                        //					$mob == $pre_mob &&
                        $pre_price == $price
                    ) {
                        continue;
                    }

                    $defs2[] = $v;
                }
                $defs3 = $defs2;

                $nnn = 1;
                while ($nnn > 0) {
                    $nnn = 0;
                    $defs2 = $defs3;
                    //echo "---------<br>\n"; flush();
                    $defs3 = array();
                    $m = array();
                    $pre_len = 0;
                    $pre_subdef = '';
                    $pre_country_id = '';
                    $pre_city_region_id = '';
                    //				$pre_mob = '';
                    $pre_price = '';

                    foreach ($defs2 as $v) {
                        $len = strlen($v['defcode']);
                        $subdef = substr($v['defcode'], 0, $len - 1);
                        $country_id = $definfo->get_country($v['defcode']);
                        $city_region_id = $definfo->get_region($v['defcode']);
                        //					$mob = $definfo->get_mob($v['defcode']);
                        $price = $v['price'];

                        if ($len != $pre_len || $subdef != $pre_subdef ||
                            $country_id != $pre_country_id || $city_region_id != $pre_city_region_id ||
                            //$mob != $pre_mob ||
                            $price != $pre_price
                        ) {
                            if (count($m) < 10)
                                foreach ($m as $mm) {
                                    $defs3[] = $mm;
                                    //echo $mm['defcode']." / $pre_country_id / $pre_city_region_id / $pre_mob / $pre_price <br>\n"; flush();
                                }
                            else {
                                $mm = $m[0];
                                $mm['defcode'] = substr($mm['defcode'], 0, strlen($mm['defcode']) - 1);
                                $defs3[] = $mm;
                                //echo $mm['defcode']." *<br>\n"; flush();
                                $nnn = $nnn + 1;
                            }

                            $m = array($v);
                        } else {
                            $m[] = $v;
                        }
                        $pre_len = $len;
                        $pre_subdef = $subdef;
                        $pre_country_id = $country_id;
                        $pre_city_region_id = $city_region_id;
                        //					$pre_mob = $mob;
                        $pre_price = $price;
                    }
                    if (count($m) < 10)
                        foreach ($m as $mm) {
                            $defs3[] = $mm;
                            //echo $mm['defcode']."<br>\n"; flush();
                        }
                    else {
                        $mm = $m[0];
                        $mm['defcode'] = substr($mm['defcode'], 0, strlen($mm['defcode']) - 1);
                        $defs3[] = $mm;
                        //echo $mm['defcode']." *<br>\n"; flush();
                        $nnn = $nnn + 1;
                    }
                }
                $defs = $defs3;
            }

            $raw_file['rows'] = count($defs);
            if ($raw_file['rows'] > 0) {
                $raw_file['startdate'] = $defs[0]['startdate'];
                $raw_file['currency_id'] = $defs[0]['currency_id'];
            }

            if ($this->save_price_file($raw_file, $defs) <= 0) {
                die('error');
                $design->AddMain('voipnew/upload.html');
                return;
            }

            header('location: ./index.php?module=voipnew&action=raw_files&pricelist=' . $pricelist_id);
            exit;
            return true;

        }

        $design->AddMain('voipnew/upload.html');
    }

    public function voipnew_defs()
    {
        global $pg_db, $design;

        $pricelist_id = get_param_protected('pricelist', '');
        $f_date = get_param_raw('f_date', date('Y-m-d', time()));
        $f_date = pg_escape_string($f_date);
        $f_short = get_param_raw('f_short', '');
        $f_print = get_param_raw('f_print', '');

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');

        $query = "select o.id, o.name from voip.pricelist o";
        $design->assign('pricelists', $pg_db->AllRecords($query));

        if ($pricelist_id != '') {
            $filter = 'WHERE 1=1';
            if ($f_dest_group >= 0) $filter .= ' and g.dest=' . intval($f_dest_group);
            if ($f_country_id > 0) $filter .= ' and g.country=' . intval($f_country_id);
            if ($f_region_id > 0) $filter .= ' and g.region=' . intval($f_region_id);

            $pg_db->Query('BEGIN');
            try {
                $query = "
                        select d.defcode, r.date_from, r.date_to, r.price,
                                    g.dest, dgr.shortname as dgroup,
                                    g.name as destination, d.mob,
                                    r.price as price
                        from select_defs_price('$pricelist_id', '$f_date') r
                                                    LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                                        LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                    LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                        {$filter}
                        order by g.dest, g.name, d.mob, r.price, d.defcode";
                $page = get_param_integer("page", 1);
                $recCount = 0;
                $recPerPage = 1000000;

                $pg_db->Query('DECLARE curs CURSOR FOR ' . $query);
                if ($page > 1) {
                    $pg_db->Query('MOVE FORWARD ' . (($page - 1) * $recPerPage) . ' IN curs');
                    $recCount = $recCount + $pg_db->AffectedRows();
                }
                $pg_db->Query('FETCH ' . $recPerPage . ' FROM curs');
                $recCount = $recCount + $pg_db->AffectedRows();
                $res = $pg_db->AllRecords('');
                $pg_db->Query('MOVE FORWARD ALL IN curs');
                $recCount = $recCount + $pg_db->AffectedRows();
                util::pager_pg($recCount, $recPerPage);
            } catch (Exception $e) {
            }
            $pg_db->Query('END');

            if ($f_short != '') {
                $res2 = array();
                $dest = '';
                $destination = '';
                $ismob = '';
                $price = '';
                $i = -1;

                $resgroups = array();
                $resgroup = array();
                foreach ($res as $r) {
                    if ($dest != $r['dest'] ||
                        $destination != $r['destination'] ||
                        $ismob != $r['mob'] ||
                        $price != $r['price']
                    ) {
                        $dest = $r['dest'];
                        $destination = $r['destination'];
                        $ismob = $r['mob'];
                        $price = $r['price'];

                        if (count($resgroup) > 0) {
                            $resgroups[] = $resgroup;
                        }
                        $resgroup = $r;
                        $resgroup['defs'] = array();
                        $resgroup['defcode'] = '';

                    }
                    $resgroup['defs'][] = $r['defcode'];
                }
                if (count($resgroup) > 0) {
                    $resgroups[] = $resgroup;
                }

                foreach ($resgroups as $k => $resgroup) {
                    while (true) {
                        $can_trim = false;
                        $first = true;
                        $char = '';
                        $defs = array();
                        foreach ($resgroups[$k]['defs'] as $d) {
                            if ($first == true) {
                                $can_trim = true;
                                $first = false;
                                $char = substr($d, 0, 1);
                            } else {
                                if ($char != substr($d, 0, 1)) {
                                    $can_trim = false;
                                }
                            }
                        }

                        if ($can_trim == true) {
                            foreach ($resgroups[$k]['defs'] as $d) {
                                $dd = substr($d, 1);
                                if (strlen($dd) > 0)
                                    $defs[] = $dd;
                                else if (strlen($dd) == 0) {
                                    $defs = array();
                                    break;
                                }
                            }
                            $resgroups[$k]['defcode'] = $resgroups[$k]['defcode'] . $char;
                            $resgroups[$k]['defs'] = $defs;
                        } else {
                            break;
                        }
                    }
                }

                $res = array();
                foreach ($resgroups as $resgroup) {
                    $defs = '';
                    foreach ($resgroup['defs'] as $d) {
                        if ($defs == '') {
                            $defs .= $d;
                        } else {
                            $defs .= ', ' . $d;
                        }
                    }
                    $resgroup['def2'] = ''; //$defs;

                    if ($defs != '') {
                        $resgroup['defcode'] = $resgroup['defcode'] . ' </b>' . '(' . $defs . ')<b>';
                    }
                    $res[] = $resgroup;
                }

            }

            function defs_cmp($a, $b)
            {
                return strcmp(iconv('koi8-r', 'windows-1251', $a["destination"]) . $a["defcode"], iconv('koi8-r', 'windows-1251', $b["destination"]) . $b["defcode"]);
            }

            usort($res, "defs_cmp");

            $design->assign('defs', $res);
        }
        $query = "select o.id, o.name from voip.pricelist o";
        $design->assign('pricelists', $pg_db->AllRecords($query));

        $design->assign('pricelist_id', $pricelist_id);
        $design->assign('f_date', $f_date);
        $design->assign('f_short', $f_short);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));

        if ($f_print != '') {
            $design->display('voipnew/defs_print.html');
            exit;
        } else {
            $design->AddMain('voipnew/defs.html');
        }

    }

    public function voipnew_pricelists()
    {
        global $db, $pg_db, $design;

        $res = $pg_db->AllRecords("select p.*, o.name as operator, c.code as currency from voip.pricelist p
											left join public.currency c on c.id=p.currency_id
											left join voip.operator o on o.id=p.operator_id and o.region=p.region 
                                    order by p.operator_id, p.region desc, p.name");

        $design->assign('pricelists', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->AddMain('voipnew/pricelists.html');
    }

    public function voipnew_priority_list()
    {
        global $db, $pg_db, $design;

        if (isset($_POST['add_priority'])) {
            $region_operator = explode('_', $_POST['region_operator_id']);
            $region_id = intval($region_operator[0]);
            $operator_id = intval($region_operator[1]);
            $prefix = intval($_POST['prefix']);
            $priority = intval($_POST['priority']);
            if ($region_id && $operator_id > 0 && $prefix > 0 && access('voip', 'admin')) {
                $pg_db->QueryDelete('voip.priority_codes', array('region_id' => $region_id, 'operator_id' => $operator_id, 'prefix' => $prefix));
                $pg_db->QueryInsert('voip.priority_codes', array('region_id' => $region_id, 'operator_id' => $operator_id, 'prefix' => $prefix, 'priority' => $priority), false);
            }

            header('location: ./index.php?module=voipnew&action=priority_list');
            exit;
        }
        if (isset($_GET['del_code'])) {
            $del_code = explode('_', $_GET['del_code']);
            $region_id = intval($del_code[0]);
            $operator_id = intval($del_code[1]);
            $prefix = intval($del_code[2]);
            if ($region_id && $operator_id > 0 && $prefix > 0 && access('voip', 'admin')) {
                $pg_db->QueryDelete('voip.priority_codes', array('region_id' => $region_id, 'operator_id' => $operator_id, 'prefix' => $prefix));
            }
            header('location: ./index.php?module=voipnew&action=priority_list');
            exit;
        }

        $list = $pg_db->AllRecords("select p.region_id, p.operator_id, o.short_name as operator, p.prefix, p.priority, p.created, g.name as geo, d.mob from voip.priority_codes p
                                    left join voip.operator o on o.id=p.operator_id and o.region=p.region_id
                                    left join voip_destinations d on d.defcode=p.prefix
                                    left join geo.geo g on g.id=d.geo_id
                                    order by p.region_id desc, p.operator_id, p.prefix");
        $design->assign('list', $list);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->assign('operators', $pg_db->AllRecords('select id, short_name as name, region from voip.operator where region!=0 order by region desc, id'));
        $design->AddMain('voipnew/priority_list.html');
    }

    public function voipnew_set_lock_prefix()
    {
        global $pg_db;
        $report_id = intval($_REQUEST['report_id']);
        $region_id = $pg_db->GetValue("select region from voip.routing_report where id={$report_id}");
        $prefix = $_REQUEST['prefix'];
        $value = $_REQUEST['value'];
        $pg_db->QueryDelete('voip.lock_prefix', array('region_id' => $region_id, 'prefix' => $prefix));
        echo $pg_db->mError;
        if ($value == 't') {
            $pg_db->QueryInsert('voip.lock_prefix', array('region_id' => $region_id, 'prefix' => $prefix, 'locked' => 'true'), false);
            echo $pg_db->mError;
        } elseif ($value == 'f') {
            $pg_db->QueryInsert('voip.lock_prefix', array('region_id' => $region_id, 'prefix' => $prefix, 'locked' => 'false'), false);
            echo $pg_db->mError;
        }
        die('{}');
    }

    public function voipnew_lock_by_price()
    {
        global $pg_db;
        $report_id = intval($_REQUEST['report_id']);
        $region_id = $pg_db->GetValue("select region from voip.routing_report where id={$report_id}");
        $price = intval($_REQUEST['price']);
        $report = $pg_db->AllRecords("
                                        select r.prefix AS defcode, r.prices[1] as price
										from voip.prepare_routing_report({$report_id}) r");


        $lock_prefix = array();


        $pre_def = '';
        $pre_locked = '';
        $pre_l = 0;
        $m_def = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $m_locked = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        foreach ($report as $k => $v) {

            $def = $v['defcode'];
            $locked = ($v['price'] >= $price ? 'true' : 'false');

            $cur_l = strlen($def);
            if ($pre_l <> $cur_l || substr($def, 0, $cur_l - 1) <> substr($def, 0, $pre_l - 1)) {
                if ($pre_l > $cur_l) $n = $pre_l; else $n = $cur_l;
                while ($n > 0) {
                    if ($m_def[$n] == '' || $m_def[$n] <> substr($def, 0, strlen($m_def[$n]))) {
                        $m_def[$n] = '';
                        $m_locked[$n] = '';
                    }
                    $n = $n - 1;
                }
                $pre_def = '';
                $pre_locked = '';
                $n = $cur_l - 1;
                while ($n > 0) {
                    if ($pre_def === '' && $m_def[$n] !== '')
                        $pre_def = $m_def[$n];
                    if ($pre_locked === '' && $m_locked[$n] !== '')
                        $pre_locked = $m_locked[$n];
                    $n = $n - 1;
                }
            }
            $m_def[$cur_l] = $def;
            $m_locked[$cur_l] = $locked;


            if (($pre_locked !== '' || $locked == 'true') && $locked !== $pre_locked) {
                $lock_prefix[$def] = $locked;
            }

        }

        $pg_db->Query('BEGIN');
        echo $pg_db->mError;
        $pg_db->QueryDelete('voip.lock_prefix', array('region_id' => $region_id));
        echo $pg_db->mError;
        foreach ($lock_prefix as $prefix => $locked) {
            $pg_db->QueryInsert('voip.lock_prefix', array('region_id' => $region_id, 'prefix' => $prefix, 'locked' => $locked), false);
            echo $pg_db->mError;
        }
        $pg_db->Query('COMMIT');
        echo $pg_db->mError;
        exit;
    }

    public function voipnew_calc_volume()
    {
        global $db, $pg_db, $design;

        $report_type = get_param_protected('report_type');
        $report_id = get_param_integer('report_id');
        $region_id = get_param_integer('region_id', 0);
        if ($region_id <= 0) $region_id = null;
        $volume = $pg_db->GetRow("select * from voip.volume_calc_task where report_type='$report_type' and report_id='$report_id'");


        if ($volume && $volume['calc_running'] != 't' && isset($_POST['calc'])) {
            $pg_db->QueryUpdate(
                'voip.volume_calc_task', 'id',
                array(
                    'id' => $volume['id'],
                    'calc_running' => true,
                    'date_from' => $_POST['volume_date_from'],
                    'date_to' => $_POST['volume_date_to'],
                    'region_id' => $region_id,
                )
            );
            if ($pg_db->mError) die($pg_db->mError);
            set_time_limit(60 * 30);
            session_write_close();

            $pg_db->Query("select * from voip.calc_volumes({$volume['id']})");
            if ($pg_db->mError) die($pg_db->mError);
            header("location: ./?module=voipnew&action=calc_volume&report_type=$report_type&report_id=$report_id");
            exit;
        }

        if (!$volume) {
            if ($report_type == 'routing') {
                if ($pg_db->GetRow("select * from voip.routing_report where id='$report_id'")) {
                    $volume_id =
                        $pg_db->QueryInsert(
                            'voip.volume_calc_task',
                            array(
                                'report_type' => $report_type,
                                'report_id' => $report_id,
                                'region_id' => $region_id,
                            )
                        );
                    if ($pg_db->mError) die($pg_db->mError);
                    $pg_db->QueryUpdate(
                        'voip.routing_report', 'id',
                        array(
                            'id' => $report_id,
                            'volume_calc_task_id' => $volume_id,
                        )
                    );
                    if ($pg_db->mError) die($pg_db->mError);
                }
            } elseif ($report_type == 'analyze_pricelist') {
                if ($pg_db->GetRow("select * from voip.analyze_pricelist_report where id='$report_id'")) {
                    $volume_id = $pg_db->QueryInsert('voip.volume_calc_task', array('report_type' => $report_type, 'report_id' => $report_id));
                    if ($pg_db->mError) die($pg_db->mError);
                    $pg_db->QueryUpdate('voip.analyze_pricelist_report', 'id', array('id' => $report_id, 'volume_calc_task_id' => $volume_id));
                    if ($pg_db->mError) die($pg_db->mError);
                }
            } else {
                die('unknown report type');
            }

            $volume = $pg_db->GetRow("select * from voip.volume_calc_task where report_type='$report_type' and report_id='$report_id'");
        }

        if (!$volume) die('volume calc task not found');

        $design->assign('volume', $volume);
        $design->assign('regions', $db->AllRecords("select id, name from regions order by id desc"));
        $design->AddMain('voipnew/calc_volume.html');

    }

    function voipnew_calls_recalc()
    {
        global $design, $pg_db, $db;

        if (isset($_REQUEST['region_id'])) {
            $region_id = (int)$_REQUEST['region_id'];
            if ($_REQUEST['t'] == 'current')
                $task = 'recalc_current_month';
            elseif ($_REQUEST['t'] == 'last')
                $task = 'recalc_last_month'; else
                $task = '';

            $running_task = $pg_db->GetValue("select id from billing.tasks where task in ('recalc_current_month','recalc_last_month') and region_id={$region_id}");
            if (!$running_task && $task != '') {
                $pg_db->Query("insert into billing.tasks(region_id, task)values('{$region_id}','{$task}')");
            }

            header('Location: ?module=voipnew&action=calls_recalc');
        }

        $regions = $db->AllRecords('select id, name from regions order by id desc');
        $design->assign('regions', $regions);

        $tasks = $pg_db->AllRecords("select region_id, max(id) as id, max(task) as task, max(status) as status, max(created) as created from billing.tasks where task in ('recalc_current_month','recalc_last_month') group by region_id", 'region_id');
        $design->assign('tasks', $tasks);

        $design->AddMain('voipnew/calls_recalc.html');
    }

    public function voipnew_billing_settings()
    {
        global $pg_db, $design;

        $instances = array();

        $query = "  select i.id, r.name as region_name, i.city_prefix
                    from billing.instance_settings i
                    left join geo.region r on r.id::varchar = ANY(i.region_id)
                    order by i.id desc, r.name asc ";
        foreach($pg_db->AllRecords($query) as $r) {
            if (!isset($instances[$r['id']])) {
                $r['city_prefix'] = str_replace('{', '', $r['city_prefix']);
                $r['city_prefix'] = str_replace('}', '', $r['city_prefix']);
                $r['city_prefix'] = str_replace(',', ', ', $r['city_prefix']);
                $instances[$r['id']] = $r;
                $instances['regions'] = array();

            }
            $instances[$r['id']]['regions'][] = $r['region_name'];
        }
        $design->assign('instances', $instances);

        $design->AddMain('voipnew/billing_settings.html');
    }
}
