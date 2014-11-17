<?php

class prices_parser
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
            self::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn1, 1);
            for ($n = $nn1 + 1; $n <= 9; $n = $n + 1) {
                $mres[] = $prefix . $n;
            }
        }
        if ($max == 2) {
            for ($n = 0; $n <= $nn2 - 1; $n = $n + 1) {
                $mres[] = $prefix . $n;
            }
            self::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn2, 2);
        }
        if ($max == 0) {
            if ($nn1 == $nn2) {
                self::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn1, 0);
            } else {
                if (strlen($number1) <= 1) {
                    $mres[] = $prefix . $nn1;
                } else
                    self::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn1, 1);
                for ($n = $nn1 + 1; $n <= $nn2 - 1; $n = $n + 1) {
                    $mres[] = $prefix . $n;
                }
                if (strlen($number2) <= 1) {
                    $mres[] = $prefix . $nn2;
                } else
                    self::make_numbers($mres, substr($number1, 1), substr($number2, 1), $prefix . $nn2, 2);
            }
        }
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
                                $table_row[$k] = number_format(floatval(str_replace(',', '.', $cell->getValue())), 4, '.', '');
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

        $objWorksheet = self::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('defcode' => array('t' => 'S', 'v' => 'Code/CNP'),
            'startdate' => array('t' => 'D', 'v' => 'SDate'),
            'price' => array('t' => 'F', 'v' => 'RATE'),
            'destination' => array('t' => 'S', 'v' => 'Group/Destination'),
            'type' => array('t' => 'S', 'v' => 'Type'),
            'currency_id' => array('t' => 'S', 'v' => 'CUR'),
        );
        $table = self::read_table($objWorksheet, $fields);
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
        $objWorksheet = self::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('destination' => array('t' => 'S', 'v' => 'DEST'),
            'defcode1' => array('t' => 'S', 'v' => 'COUNTRY CODE'),
            'defcode2' => array('t' => 'S', 'v' => 'ROLLUP'),
            'price' => array('t' => 'F', 'v' => 'RATE'),
            'startdate' => array('t' => 'D', 'v' => 'EFFECTIVED'),
        );
        $table = self::read_table($objWorksheet, $fields);
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
        $objWorksheet = self::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('destination' => array('t' => 'S', 'v' => 'DEST'),
            'defcode1' => array('t' => 'S', 'v' => 'COUNTRY CODE'),
            'defcode2' => array('t' => 'S', 'v' => 'ROLLUP'),
            'price' => array('t' => 'F', 'v' => 'RATE'),
            'comments' => array('t' => 'S', 'v' => 'COMMENTS'),
            'startdate' => array('t' => 'D', 'v' => 'EFFECTIVED'),
        );
        $table = self::read_table($objWorksheet, $fields);
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
        $objWorksheet = self::open_file($filename, 'Excel5', 0);
        if ($objWorksheet === false) return false;
        $fields = array(
            'defcode2' => array('t' => 'S', 'v' => 'Коды АВС abx'),
            'price' => array('t' => 'F', 'v' => 'РТ, руб./мин.'),
        );
        $table = self::read_table($objWorksheet, $fields);
        if ($table === false) return false;

        $objWorksheet = self::open_file($filename, 'Excel5', 1);
        if ($objWorksheet === false) return false;
        $fields = array(
            'defcode1' => array('t' => 'S', 'v' => 'Коды АВС'),
            'defcode2' => array('t' => 'S', 'v' => 'Коды АВС abx'),
            'price' => array('t' => 'F', 'v' => 'РТ, руб./мин.'),
        );
        $table2 = self::read_table($objWorksheet, $fields);
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
        $objWorksheet = self::open_file($filename);

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
                $table_row = array('startdate' => date('Y-m-d'), 'deleting' => 0, 'currency_id' => 1);
                foreach ($cellIterator as $cell) {
                    if ($cellIterator->key() == 0) {
                        $table_row['defcode'] = strip_tags(trim($cell->getCalculatedValue()));
                    }
                    if ($cellIterator->key() == $price_column) {
                        $table_row['price'] = number_format(floatval(str_replace(',', '.', $cell->getCalculatedValue())), 4, '.', '');
                    }
                }
                $table[] = $table_row;
            }
        }
        return $table;
    }

    public static function &read_networks($filename)
    {
        $objWorksheet = self::open_file($filename);

        $rowIterator = $objWorksheet->getRowIterator();
        $table = array();
        $isFindHeader = false;
        foreach ($rowIterator as $row) {
            if (!$isFindHeader) {
                $isFindHeader = true;
            } else {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                $table_row = array();
                foreach ($cellIterator as $cell) {
                    if ($cellIterator->key() == 0) {
                        $table_row['defcode'] = strip_tags(trim($cell->getCalculatedValue()));
                    }
                    if ($cellIterator->key() == 1) {
                        $table_row['price'] = number_format(floatval(str_replace(',', '.', $cell->getCalculatedValue())), 4, '.', '');
                    }
                }
                $table[] = $table_row;
            }
        }
        return $table;
    }


    public static function read_mgts_networks($filename)
    {
        $table = array();
        if (($handle = fopen($filename, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ";");
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($data) < 6) continue;
                $def = '7' . $data[0];
                $prefixFrom = $data[2];
                $prefixTo = $data[3];
                $group = $data[5];

                if ($group == '1') {
                    $network_type_id = 101;
                } elseif ($group == '2') {
                    $network_type_id = 102;
                } elseif ($group == '3') {
                    $network_type_id = 103;
                } elseif ($group == '4') {
                    $network_type_id = 104;
                } elseif ($group == '5_1' || $group == '5_01') {
                    $network_type_id = 201;
                } elseif ($group == '5_2' || $group == '5_02') {
                    $network_type_id = 202;
                } elseif ($group == '5_3' || $group == '5_03') {
                    $network_type_id = 203;
                } elseif ($group == '5_4' || $group == '5_04') {
                    $network_type_id = 204;
                } elseif ($group == '6') {
                    $network_type_id = 300;
                } else {
                    continue;
                }

                VoipUtils::explodeNumber($def, $prefixFrom, $prefixTo, function($prefix) use (&$table, $network_type_id) {
                    $table[] = array(
                        'prefix' => $prefix,
                        'network_type_id' => $network_type_id,
                    );
                });
            }
            fclose($handle);
        }

        return $table;
    }

    public static function read_beeline_networks($filename)
    {
        $objWorksheet = self::open_file($filename);

        $rowIterator = $objWorksheet->getRowIterator();
        $table = array();
        $isFindHeader = false;
        foreach ($rowIterator as $row) {
            if (!$isFindHeader) {
                $isFindHeader = true;
            } else {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                $def = '';
                $prefixFrom = '';
                $prefixTo = '';
                foreach ($cellIterator as $cell) {
                    if ($cellIterator->key() == 1) {
                        $def = '7' . strip_tags(trim($cell->getCalculatedValue()));
                    }
                    if ($cellIterator->key() == 3) {
                        $prefixFrom = strip_tags(trim($cell->getCalculatedValue()));;
                    }
                    if ($cellIterator->key() == 4) {
                        $prefixTo = strip_tags(trim($cell->getCalculatedValue()));;
                    }
                }
                if ($def && $prefixFrom && $prefixTo) {
                    VoipUtils::explodeNumber($def, $prefixFrom, $prefixTo, function($prefix) use (&$table) {
                        $table[] = array(
                            'prefix' => $prefix,
                            'network_type_id' => 101,
                        );
                    });
                }
            }
        }
        return $table;
    }


    public static function &read_mts_networks($filename)
    {
        $objWorksheet = self::open_file($filename);

        $rowIterator = $objWorksheet->getRowIterator();
        $table = array();
        $isFindHeader = false;
        $column = 1;
        foreach ($rowIterator as $row) {
            if (!$isFindHeader) {

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                foreach ($cellIterator as $cell) {
                    if (strip_tags(trim($cell->getValue())) == "КУ-72") {
                        $isFindHeader = true;
                        $column = $cellIterator->key();
                        break;
                    }
                }
            } else {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells
                $code = '';
                $group = '';
                foreach ($cellIterator as $cell) {
                    if ($cellIterator->key() == 0) {
                        $code = strip_tags(trim($cell->getCalculatedValue()));
                    }
                    if ($cellIterator->key() == $column) {
                        $group = strip_tags(trim($cell->getCalculatedValue()));;
                    }
                }

                if ($group == '101') {
                    $network_type_id = 101;
                } elseif ($group == '102') {
                    $network_type_id = 102;
                } elseif ($group == '103') {
                    $network_type_id = 103;
                } elseif ($group == '201') {
                    $network_type_id = 201;
                } elseif ($group == '202') {
                    $network_type_id = 202;
                } elseif ($group == '203') {
                    $network_type_id = 203;
                } elseif ($group == '204') {
                    $network_type_id = 204;
                } else {
                    continue;
                }

                if (preg_match('/\((\d{3})\)\s+(\d{7})-(\d{7})/', $code, $regs)) {
                    VoipUtils::explodeNumber('7' . $regs[1], $regs[2], $regs[3], function($prefix) use (&$table, $network_type_id) {
                        $table[] = array(
                            'prefix' => $prefix,
                            'network_type_id' => $network_type_id,
                        );
                    });
                }
            }
        }
        return $table;
    }


    public static function &read_orange_full($filename)
    {
        $objWorksheet = self::open_file($filename);
        if ($objWorksheet === false) return false;
        $fields = array('defcode' => array('t' => 'S', 'v' => 'КОД'),
            'price' => array('t' => 'F', 'v' => 'Tariff, RuR /min'),
        );
        $table = self::read_table($objWorksheet, $fields);
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
                    self::make_numbers($mres, $n, $n2);
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