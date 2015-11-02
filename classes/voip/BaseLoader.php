<?php

namespace app\classes\voip;

use app\classes\Event;
use Yii;
use app\models\billing\PricelistFile;
use yii\base\Object;

abstract class BaseLoader extends Object
{
    /**
     * @var PricelistFile;
     */
    public $file;

    /**
     * @return string
     */
    public static function getName()
    {
        return '';
    }

    /**
     * @return array
     */
    public static function overrideSettings()
    {
        return [];
    }

    public function load(PricelistFile $file)
    {
        $this->file = $file;
    }

    /**
     * @return bool|\PHPExcel_Worksheet
     */
    protected function openFile()
    {
        if (preg_match('/\.csv$/', $this->file->filename)) {
            $reader = \PHPExcel_IOFactory::createReader('CSV');
        } elseif (preg_match('/\.xls$/', $this->file->filename)) {
            $reader = \PHPExcel_IOFactory::createReader('Excel5');
        } elseif (preg_match('/\.xlsx$/', $this->file->filename)) {
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        } else {
            return false;
        }

        $excel = $reader->load($this->file->getStorageFilePath());
        if (!$excel) {
            return false;
        }

        $worksheet = $excel->getActiveSheet();
        if (!$worksheet) {
            return false;
        }

        return $worksheet;
    }

    public function readRaw($nCount = 0)
    {
        $worksheet = $this->openFile();
        if (!$worksheet) {
            return false;
        }

        $result = [];

        $count = 0;
        $emptyRowsCount = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $data = [];
            $emptyRow = true;
            $emptyColsCount = 0;
            foreach ($row->getCellIterator() as $cell) { /** @var \PHPExcel_Cell $cell */
                $value = $cell->getFormattedValue();

                $data[] = $value;

                if ($value == '') {
                    $emptyColsCount++;
                    if ($emptyColsCount == 5) {
                        break;
                    }
                } else {
                    $emptyRow = false;
                }

            }
            while ($emptyColsCount-- > 0) array_pop($data);

            $result[] = $data;

            if ($emptyRow) {
                $emptyRowsCount++;
                if ($emptyRowsCount == 10) {
                    break;
                }
            }


            $count++;

            if ($nCount > 0 && $count == $nCount) {
                break;
            }
        }

        while ($emptyRowsCount-- > 0) array_pop($result);

        return $result;
    }

    public function read(array $settings)
    {
        $skipRows        = isset($settings['skip_rows']) ? $settings['skip_rows'] : 0;
        $prefix0         = isset($settings['prefix']) ? $settings['prefix'] : null;
        $nPrefix1        = isset($settings['cols']['prefix1']) ? $settings['cols']['prefix1'] : null;
        $nPrefix2_smart  = isset($settings['cols']['prefix2_smart']) ? $settings['cols']['prefix2_smart'] : null;
        $nPrefix2_from   = isset($settings['cols']['prefix2_from']) ? $settings['cols']['prefix2_from'] : null;
        $nPrefix2_to     = isset($settings['cols']['prefix2_to']) ? $settings['cols']['prefix2_to'] : null;
        $nRate           = isset($settings['cols']['rate']) ? $settings['cols']['rate'] : null;
        $nDestination    = isset($settings['cols']['destination']) ? $settings['cols']['destination'] : null;
        $nComment        = isset($settings['cols']['comment']) ? $settings['cols']['comment'] : null;

        $result = [];
        foreach ($this->readRaw() as $row) {
            if ($skipRows > 0) {
                $skipRows--;
                continue;
            }

            $prefix1 = '';
            $prefix2_smart = '';
            $prefix2_from = '';
            $prefix2_to = '';
            $rate = '';
            $destination = '';
            $comment = '';

            $nCol = 1;
            foreach ($row as $value) { /** @var \PHPExcel_Cell $cell */

                if ($nPrefix1 == $nCol) {
                    $prefix1 = trim($value);
                } elseif ($nPrefix2_smart == $nCol) {
                    $prefix2_smart = trim($value);
                } elseif ($nPrefix2_from == $nCol) {
                    $prefix2_from = $value;
                } elseif ($nPrefix2_to == $nCol) {
                    $prefix2_to = $value;
                } elseif ($nRate == $nCol) {
                    $rate = $value;
                } elseif ($nDestination == $nCol) {
                    $destination = $value;
                } elseif ($nComment == $nCol) {
                    $comment = $value;
                }

                $nCol++;
            }

            if (!$prefix1 && !$prefix2_smart && !$prefix2_from && !$prefix2_to &!$rate) {
                continue;
            }

            if (substr($prefix0 . $prefix1, 0, 2) == '70') {
                continue;
            }

            $rate = str_replace(',', '.', $rate);

            if ($prefix2_smart) {
                if (substr($prefix2_smart, 0, 1) != 'D') {
                    foreach ($this->explodePrefix($prefix2_smart) as $subPrefix) {
                        $prefix = $prefix0 . $prefix1 . $subPrefix;
                        $result[] = [
                            'prefix' => $prefix,
                            'rate' => $rate,
                            'deleting' => false,
                            'mob' => false,
                        ];
                    };
                }
            } elseif ($prefix2_to) {
                $prefix2_from = trim($prefix2_from);
                $prefix2_to = trim($prefix2_to);
                foreach ($this->explodePrefixRange($prefix2_from, $prefix2_to) as $subPrefix) {
                    $prefix = $prefix0 . $prefix1 . $subPrefix;
                    $result[] = [
                        'prefix' => $prefix,
                        'rate' => $rate,
                        'deleting' => false,
                        'mob' => false,
                    ];
                };
            } else {
                $result[] = [
                    'prefix' => $prefix0 . $prefix1,
                    'rate' => $rate,
                    'deleting' => false,
                    'mob' => false,
                ];
            }
        }

        return $result;
    }

    private function explodePrefix($prefixSource)
    {
        $result = [];

        $prefixSource = str_replace(';', ',', $prefixSource);
        $prefixParts = explode(',', $prefixSource);

        foreach ($prefixParts as $prefixPart) {
            $prefixPart = trim($prefixPart);
            $prefixRange = explode('-', $prefixPart);

            if (count($prefixRange) == 1) {

                $prefix = trim($prefixRange[0]);
                if (!preg_match('#^\d+$#', $prefix)) {
                    throw new \Exception('Match error: "^\d+$", "' . $prefix . '"');
                }
                $result[] = $prefix;

            } elseif (count($prefixRange) == 2) {

                $prefixFrom = trim($prefixRange[0]);
                $prefixTo = trim($prefixRange[1]);

                $result = array_merge($result, $this->explodePrefixRange($prefixFrom, $prefixTo));

            } else {
                throw new \Exception('Bad range: "' . $prefixPart. '"');
            }
        }

        return $result;
    }

    private function explodePrefixRange($prefixFrom, $prefixTo)
    {
        $result = [];

        if (!preg_match('#^\d+$#', $prefixFrom)) {
            throw new \Exception('Match error: "^\d+$", "' . $prefixFrom . '"');
        }

        if (!preg_match('#^\d+$#', $prefixTo)) {
            throw new \Exception('Match error: "^\d+$", "' . $prefixTo . '"');
        }

        if (strlen($prefixFrom) != strlen($prefixTo)) {
            throw new \Exception('Len '. $prefixFrom .' <> Len ' . $prefixTo);
        }

        self::make_numbers($result, $prefixFrom, $prefixTo);

        return $result;
    }

    public static function make_numbers(&$result, $prefixFrom, $prefixTo, $prefix = '')
    {
        while (strlen($prefixFrom) > 0 && $prefixFrom[strlen($prefixFrom) - 1] == '0' && $prefixTo[strlen($prefixTo) - 1] == '9') {
            $prefixFrom = substr($prefixFrom, 0, strlen($prefixFrom) - 1);
            $prefixTo = substr($prefixTo, 0, strlen($prefixTo) - 1);
        }

        while (strlen($prefixFrom) > 0 && $prefixFrom[0] == $prefixTo[0]) {
            $prefix .= $prefixFrom[0];
            $prefixFrom = substr($prefixFrom, 1);
            $prefixTo = substr($prefixTo, 1);
        }

        if ($prefixFrom == '' && $prefixTo == '') {
            $result[] = $prefix;
            return;
        }

        $nn1 = (int)substr($prefixFrom, 0, 1);
        $nn2 = (int)substr($prefixTo, 0, 1);

        if ($nn1 == $nn2) {

            self::make_numbers($result, substr($prefixFrom, 1), substr($prefixTo, 1), $prefix . $nn1, 0);

        } else {

            $from = str_pad('', strlen($prefixFrom) - 1, '0');
            $to   = str_pad('', strlen($prefixTo) - 1, '9');

            self::make_numbers($result, substr($prefixFrom, 1), $to, $prefix . $nn1, 0);

            for ($n = $nn1 + 1; $n <= $nn2 - 1; $n = $n + 1) {
                $result[] = $prefix . $n;
            }

            self::make_numbers($result, $from, substr($prefixTo, 1), $prefix . $nn2, 0);

        }
    }

    public function compress($defs)
    {
        usort($defs, function($a, $b){
            return strcmp($a["prefix"], $b["prefix"]);
        });

        $definfo = new DefInfo();
        $defs2 = array();
        $pre_def = '';
        $pre_country_id = '';
        $pre_city_region_id = '';
        $pre_price = '';
        $pre_l = 0;
        $m_def = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $m_country_id = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $m_city_region_id = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $m_price = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        foreach ($defs as $k => $v) {

            $def = $v['prefix'];
            $country_id = $definfo->get_country($v['prefix']);
            $city_region_id = $definfo->get_region($v['prefix']);
            $price = $v['rate'];

            $cur_l = strlen($def);
            if ($pre_l <> $cur_l || substr($def, 0, $cur_l - 1) <> substr($def, 0, $pre_l - 1)) {
                if ($pre_l > $cur_l) $n = $pre_l; else $n = $cur_l;
                while ($n > 0) {
                    if ($m_def[$n] == '' || $m_def[$n] <> substr($def, 0, strlen($m_def[$n]))) {
                        $m_def[$n] = '';
                        $m_country_id[$n] = '';
                        $m_city_region_id[$n] = '';
                        $m_price[$n] = '';
                    }
                    $n = $n - 1;
                }
                $pre_def = '';
                $pre_country_id = '';
                $pre_city_region_id = '';
                $pre_price = '';
                $n = $cur_l - 1;
                while ($n > 0) {
                    if ($pre_def === '' && $m_def[$n] !== '')
                        $pre_def = $m_def[$n];
                    if ($pre_country_id === '' && $m_country_id[$n] !== '')
                        $pre_country_id = $m_country_id[$n];
                    if ($pre_city_region_id === '' && $m_city_region_id[$n] !== '')
                        $pre_city_region_id = $m_city_region_id[$n];
                    if ($pre_price === '' && $m_price[$n] !== '')
                        $pre_price = $m_price[$n];
                    $n = $n - 1;
                }
            }
            $m_def[$cur_l] = $def;
            $m_country_id[$cur_l] = $country_id;
            $m_city_region_id[$cur_l] = $city_region_id;
            $m_price[$cur_l] = $price;

            if (strpos($def, $pre_def) === 0 &&
                $country_id == $pre_country_id &&
                $city_region_id == $pre_city_region_id &&
                $pre_price == $price
            ) {
                continue;
            }

            $defs2[] = $v;
        }
        unset($defs);

        $defs3 = $defs2;

        $nnn = 1;
        while ($nnn > 0) {
            $nnn = 0;
            $defs2 = $defs3;
            $defs3 = array();
            $m = array();
            $pre_len = 0;
            $pre_subdef = '';
            $pre_country_id = '';
            $pre_city_region_id = '';
            $pre_price = '';

            foreach ($defs2 as $v) {
                $len = strlen($v['prefix']);
                $subdef = substr($v['prefix'], 0, $len - 1);
                $country_id = $definfo->get_country($v['prefix']);
                $city_region_id = $definfo->get_region($v['prefix']);
                $price = $v['rate'];

                if ($len != $pre_len || $subdef != $pre_subdef ||
                    $country_id != $pre_country_id || $city_region_id != $pre_city_region_id ||
                    $price != $pre_price
                ) {
                    if (count($m) < 10)
                        foreach ($m as $mm) {
                            $defs3[] = $mm;
                        }
                    else {
                        $mm = $m[0];
                        $mm['prefix'] = substr($mm['prefix'], 0, strlen($mm['prefix']) - 1);
                        $defs3[] = $mm;
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
                $pre_price = $price;
            }
            if (count($m) < 10)
                foreach ($m as $mm) {
                    $defs3[] = $mm;
                }
            else {
                $mm = $m[0];
                $mm['prefix'] = substr($mm['prefix'], 0, strlen($mm['prefix']) - 1);
                $defs3[] = $mm;
                $nnn = $nnn + 1;
            }
        }
        $defs = $defs3;
        return $defs;
    }

    public function savePrices(PricelistFile $file, $data)
    {
        $transaction = Yii::$app->dbPg->beginTransaction();
        try {

            $new_rows = array();
            foreach ($data as $row) {
                $new_rows[] = $row;
                if (count($new_rows) >= 10000) {
                    $this->insertPrices($file, $new_rows);
                    $new_rows = array();
                }
            }
            if (count($new_rows) >= 0) {
                $this->insertPrices($file, $new_rows);
            }

            $file->rows = count($data);
            $file->parsed = true;
            $file->save();

            Yii::$app->dbPg->createCommand("select new_destinations(" . (int)$file->id . ")")->execute();
            //Event::go('update_voip_destination', $file->id);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function insertPrices(PricelistFile $file, $new_rows)
    {
        $q = "INSERT INTO voip.raw_price (rawfile_id, ndef, deleting, price, mob) VALUES ";
        $is_first = true;
        foreach ($new_rows as $row) {
            if ($is_first == false) $q .= ","; else $is_first = false;

            $mob = false ? 'TRUE' : 'NULL';

            if (!isset($row['deleting']))
                $row['deleting'] = 0;

            $deleting = isset($row['deleting']) && $row['deleting'] ? 'TRUE' : 'FALSE';

            $q .= "('" . pg_escape_string($file->id) . "','" . pg_escape_string($row['prefix']) . "'," . $deleting . ",'" . pg_escape_string($row['rate']) . "'," . $mob . ")";
        }

        Yii::$app->dbPg->createCommand($q)->execute();
    }

}
