<?php
include_once 'definfo.php';
include_once 'prices_parser.php';

include_once 'network.php';
include_once 'operators.php';
include_once 'pricelists.php';

class m_voipnew extends IModule
{
    private $_inheritances = array();

    public function __construct()
    {
        $this->_addInheritance(new m_voipnew_operators);
        $this->_addInheritance(new m_voipnew_network);
        $this->_addInheritance(new m_voipnew_pricelists);
    }

    public function __call($method, array $arguments = array())
    {
        foreach ($this->_inheritances as $inheritance) {
            $inheritance->invoke($method, $arguments);
        }
    }

    protected function _addInheritance(Inheritance $inheritance)
    {
        $this->_inheritances[get_class($inheritance)] = $inheritance;
        $inheritance->module = $this;
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

        $query = "  select o.name as operator, p.type as type, p.id as pricelist_id, p.name as pricelist,f.id,f.date,f.format,f.filename,f.active,f.startdate, f.rows
                    from voip.raw_file f
                    left join voip.pricelist p on p.id=f.pricelist_id
                    left join voip.operator o on o.id=p.operator_id
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

        if ($file['type'] == 'network_prices') {
            $networkGroups = $pg_db->AllRecords('select id, name from voip.network_type', 'id');
            foreach ($defs as $k => $def) {
                if (isset($networkGroups[$def['defcode']])) {
                    $defs[$k]['destination'] = $networkGroups[$def['defcode']]['name'];
                }
            }
        }

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

        $query = "  select o.name as operator, p.name as pricelist,f.id,f.date,f.format,f.filename,f.active,f.startdate, f.rows
                    from voip.raw_file f
                    left join voip.pricelist p on p.id=f.pricelist_id
                    left join voip.operator o on o.id=p.operator_id
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
        global $pg_db;

        $id = get_param_protected('id', 0);

        $pricelist_id = $pg_db->GetValue("select pricelist_id from voip.raw_file where id=" . $id);

        $query = "delete from voip.raw_file where id=" . $id;
        $pg_db->Query($query);
        if ($pg_db->mError == '') {
            header("location: index.php?module=voipnew&action=raw_files&pricelist={$pricelist_id}");
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
            exit;
        }

    }

    public function voipnew_change_raw_file_start_date()
    {
        global $pg_db;
        $id = get_param_protected('id', 0);
        $startDate = get_param_protected('startdate', 0);

        $req = $pg_db->QueryUpdate('voip.raw_file', 'id', array('id' => $id, 'startdate' => $startDate));
        if (!$req) {
            trigger_error('Ошибка: Не удалось изменить дату начала действия');
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
            if (isset($row['destination']) && strpos($row['destination'], '(mob)') !== false)
                $mob = "TRUE";
            else
                $mob = 'NULL';

            if (!isset($row['deleting']))
                $row['deleting'] = 0;

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
        $rawfile_id = $pg_db->QueryInsert('voip.raw_file', $raw_file);
        if ($rawfile_id > 0 && $raw_file['rows'] > 0) {
            $new_rows = array();
            foreach ($defs as $row) {
                $row['rawfile_id'] = $rawfile_id;
                $row['pricelist_id'] = $raw_file['pricelist_id'];
                $new_rows[] = $row;
                if (count($new_rows) >= 10000) {
                    if (!$this->insert_raw_prices($new_rows)) {
                        echo $pg_db->mError;
                        return 0;
                    }
                    $new_rows = array();
                }
            }
            if (count($new_rows) >= 0) {
                if (!$this->insert_raw_prices($new_rows)) {
                    echo $pg_db->mError;
                    return 0;
                }
            }
            $pg_db->Query("select new_destinations({$rawfile_id})");
            echo $pg_db->mError;
            $pg_db->Commit();

        } else {
            echo $pg_db->mError;
            return 0;
        }
        return $rawfile_id;
    }

    public function voipnew_upload()
    {
        global $pg_db;
        set_time_limit(0);
        if (isset($_POST['step']) && $_POST['step'] == 'upfile') {
            if (!$_FILES['upfile']) {
                trigger_error('Пожалуйста, загрузите файл для обработки');
                return;
            } elseif ($_FILES['upfile']['error']) {
                trigger_error('При загрузке файла произошла ошибка. Пожалуйста, попробуйте еще раз' . $_FILES['upfile']['error']);
                return;
            }

            $f = & $_FILES['upfile'];
            if (in_array($_POST['ftype'], array('xls_beeline', 'xls_beeline_change', 'xls_arktel', 'xls_arktel_change'))
                &&
                $f['type'] <> 'application/vnd.ms-excel'
            ) {
                trigger_error('Формат файла указан не правильно');
                return;
            }

            $raw_file = array('date' => date('Y-m-d H:i:s'),
                'format' => $_POST['ftype'],
                'filename' => $f['name'],
                'full' => 0,
                'active' => 0);


            if ($_POST['ftype'] == 'xls_beeline_full1') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_beeline_full1($f['tmp_name']);

            } elseif ($_POST['ftype'] == 'xls_beeline_full2') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_beeline_full2($f['tmp_name']);

            } elseif ($_POST['ftype'] == 'xls_beeline_changes') {
                $defs = prices_parser::read_beeline_changes($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_mtt_full') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_mtt_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_arktel_changes') {
                $raw_file['full'] = 0;
                $defs = prices_parser::read_arktel_changes($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_mcn_prime_full') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_mcn_prime_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_mcn_prime_changes') {
                $raw_file['full'] = 0;
                $defs = prices_parser::read_mcn_prime_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_orange_full') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_orange_full($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'xls_networks') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_networks($f['tmp_name']);
            } elseif ($_POST['ftype'] == 'csv_mgts_networks') {
                $raw_file['full'] = 1;
                $defs = prices_parser::read_mgts_networks($f['tmp_name']);
            }

            if ($defs === false) {
                trigger_error('Ошибка чтения файла');
                return;
            }
            
            if ($_POST['ftype'] == 'csv_mgts_networks') {
                $defs = VoipUtils::reducePrefixes($defs, 'defcode', array('price'));
            }

            if ($raw_file['full'] == 1 && $_POST['ftype'] != 'csv_mgts_networks') {
                usort($defs, function($a, $b){
                    return strcmp($a["defcode"], $b["defcode"]);
                });

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
                if (isset($defs[0]['startdate']))
                    $raw_file['startdate'] = $defs[0]['startdate'];
                else
                    $raw_file['startdate'] = date('Y-m-d');
            }

            $pricelistIds = $_POST['pricelist_ids'];
            $rawFilesIds = array();
            $pg_db->Begin();

            foreach($pricelistIds as $pricelistId) {
                $raw_file['pricelist_id'] = (int)$pricelistId;

                $rawFileId = $this->save_price_file($raw_file, $defs);
                if ($rawFileId <= 0) {
                    $pg_db->Rollback();
                    die('error');
                }
                $rawFilesIds[] = $rawFileId;
            }

            $pg_db->Commit();

            header(
                count($rawFilesIds) == 1
                    ? 'location: index.php?module=voipnew&action=view_raw_file&id=' . (int)$rawFilesIds[0]
                    : 'location: index.php?' . http_build_query(array('module'=>'voipnew','action'=>'mass_activate','ids'=>$rawFilesIds))
            );
            exit;
        }

        trigger_error('bad parameters');
    }

    public function voipnew_mass_activate()
    {
        global $db, $pg_db, $design;

        if (isset($_POST['activate'])) {
            foreach ($_POST['ids'] as $id) {
                $pg_db->QueryUpdate('voip.raw_file', 'id', array('id' => $id, 'active' => 1));
            }
            header('location: index.php?module=voipnew&action=client_pricelists');
            exit;
        }
        foreach ($_GET['ids'] as $id)
            $ids[] = (int)$id;

        $query = "  select  p.*, f.id as rawfile_id, o.short_name as operator
                    from voip.raw_file f
                    left join voip.pricelist p on p.id=f.pricelist_id
                    left join voip.operator o on o.id=p.operator_id and o.region=p.region
                    where f.id in (" . implode(',', $ids) . ")
                    order by p.region desc, p.operator_id, p.name";

        $design->assign('list', $pg_db->AllRecords($query));
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));

        $design->AddMain('voipnew/mass_activate.html');
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
        $f_mob = get_param_protected('f_mob', '0');

        $query = "select o.id, o.name from voip.pricelist o";
        $design->assign('pricelists', $pg_db->AllRecords($query));

        if ($pricelist_id != '') {
            $filter = 'WHERE 1=1';
            if ($f_dest_group >= 0) $filter .= ' and g.dest=' . intval($f_dest_group);
            if ($f_country_id > 0) $filter .= ' and g.country=' . intval($f_country_id);
            if ($f_region_id > 0) $filter .= ' and g.region=' . intval($f_region_id);
            if ($f_mob == 't') $filter .= " and d.mob=true ";
            if ($f_mob == 'f') $filter .= " and d.mob=false ";

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
                        $resgroup['defcode'] = $resgroup['defcode'];
                        $resgroup['defcode2'] = $defs;
                    }
                    $res[] = $resgroup;
                }

            }

            function defs_cmp($a, $b)
            {
                return strcmp(iconv('utf-8', 'windows-1251', $a["destination"]) . $a["defcode"], iconv('utf-8', 'windows-1251', $b["destination"]) . $b["defcode"]);
            }

            usort($res, "defs_cmp");

        } else {
            $res = array();
        }

        $pricelists = $pg_db->AllRecords("select o.id, o.name from voip.pricelist o");
        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $regions = $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name");

        if (!isset($_REQUEST['export'])) {
            $design->assign('defs', $res);
            $design->assign('pricelists', $pricelists);
            $design->assign('pricelist_id', $pricelist_id);
            $design->assign('f_date', $f_date);
            $design->assign('f_short', $f_short);
            $design->assign('f_country_id', $f_country_id);
            $design->assign('f_region_id', $f_region_id);
            $design->assign('f_dest_group', $f_dest_group);
            $design->assign('f_mob', $f_mob);
            $design->assign('countries', $countries);
            $design->assign('regions', $regions);

            if ($f_print != '') {
                $design->display('voipnew/defs_print.html');
                exit;
            } else {
                $design->AddMain('voipnew/defs.html');
            }
        } else {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="price.csv"');

            ob_start();

            echo '"Префикс";"Цена";"Направление";"Направление";"Fix / Mоb";' . "\n";
            foreach ($res as $r) {
                echo '"' . $r['defcode'] . (isset($r['defcode2']) ? ' (' . $r['defcode2'] . ')' : '') . '";';
                echo '"' . str_replace('.', ',', $r['price']) . '";';
                echo '"' . $r['dgroup'] . '";';
                echo '"' . $r['destination'] . '";';
                echo '"' . ($r['mob']=='t'?'mob':'fix') . '";';
                echo "\n";
            }

            echo iconv('utf-8', 'windows-1251', ob_get_clean());
            exit;
        }


    }

    /*
    public function voipnew_operator_networks()
    {
        global $db, $pg_db, $design;

        $res = $pg_db->AllRecords(" select p.*, o.short_name as operator, c.code as currency from voip.pricelist p
                                    left join public.currency c on c.id=p.currency_id
                                    left join voip.operator o on o.id=p.operator_id and o.region=p.region
                                    where p.type = 'network'
                                    order by p.region desc, p.operator_id, p.name");

        $design->assign('pricelists', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->AddMain('voipnew/operator_networks.html');
    }
    */

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

        $servers = $pg_db->AllRecords('select id, name from billing.instance_settings where active = true order by id desc');
        $design->assign('servers', $servers);

        $tasks = $pg_db->AllRecords("select region_id, max(id) as id, max(task) as task, max(status) as status, max(created) as created from billing.tasks where task in ('recalc_current_month','recalc_last_month') group by region_id", 'region_id');
        $design->assign('tasks', $tasks);

        $design->AddMain('voipnew/calls_recalc.html');
    }

    public function voipnew_catalogs()
    {
        global $pg_db, $design;

        $instances = array();

        $query = "  select i.id, r.id as region_id, r.name as region_name, i.city_prefix, i.city_geo_id, cg.city_name as city_name
                    from billing.instance_settings i
                    left join geo.geo cg on cg.id=i.city_geo_id
                    left join geo.region r on r.id::varchar = ANY(i.region_id)
                    order by i.id desc, r.name asc ";
        foreach ($pg_db->AllRecords($query) as $r) {
            if (!isset($instances[$r['id']])) {
                $r['city_prefix'] = str_replace('{', '', $r['city_prefix']);
                $r['city_prefix'] = str_replace('}', '', $r['city_prefix']);
                $r['city_prefix'] = str_replace(',', ', ', $r['city_prefix']);
                $instances[$r['id']] = $r;
                $instances[$r['id']]['regions'] = array();

            }
            $instances[$r['id']]['regions'][$r['region_id']] = $r['region_name'];
        }
        $design->assign('instances', $instances);

        $design->AddMain('voipnew/catalogs.html');
    }


    public function voipnew_catalog_prefix()
    {
        global $db, $pg_db, $design;

        $f_prefix = get_param_protected('f_prefix', '');
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_city_geo_id = get_param_protected('f_city_geo_id', '0');
        $f_mob = get_param_protected('f_mob', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');


        $report = array();
        if (isset($_GET['make'])) {
            $where = '';
            if ($f_prefix != '')
                $where .= " and p.prefix like '" . intval($f_prefix) . "%' ";
            if ($f_city_geo_id != '0')
                $where .= " and p.geo_id='{$f_city_geo_id}'";
            if ($f_dest_group != '-1')
                $where .= " and g.dest='{$f_dest_group}'";
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}'";
            if ($f_region_id != '0')
                $where .= " and p.region='{$f_region_id}'";
            if ($f_mob == 't')
                $where .= " and p.mob=true ";
            if ($f_mob == 'f')
                $where .= " and p.mob=false ";

            $report = $pg_db->AllRecords("
                                        select p.prefix, p.mob, g.name as destination, o.name as operator
                                        from geo.prefix p
                                        left join geo.geo g on g.id=p.geo_id
                                        left join geo.operator o on o.id=p.operator_id
                                        where true {$where}
                                        order by p.prefix
                                         ");

        }

        $design->assign('report', $report);
        $design->assign('f_prefix', $f_prefix);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_city_geo_id', $f_city_geo_id);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));
        $design->assign('cities', $pg_db->AllRecords("SELECT i.city_geo_id as id, g.city_name as name FROM billing.instance_settings i left join geo.geo g on i.city_geo_id=g.id ORDER BY g.city_name"));
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));

        $design->AddMain('voipnew/catalog_prefix.html');
    }


    public function voipnew_mass_upload_mcn_price()
    {
        global $db, $pg_db, $design;

        $res = $pg_db->AllRecords(" select p.*, o.short_name as operator, c.code as currency from voip.pricelist p
                                    left join public.currency c on c.id=p.currency_id
                                    left join voip.operator o on o.id=p.operator_id and o.region=p.region
                                    where p.operator_id = 999 and p.type = 'client'
                                    order by p.region desc, p.operator_id, p.name");

        $design->assign('pricelists', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));

        $design->AddMain('voipnew/mass_upload_mcn_price.html');
    }
}
