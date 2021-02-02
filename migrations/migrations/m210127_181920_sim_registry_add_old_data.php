<?php

use app\classes\Migration;
use app\modules\sim\models\Registry;

/**
 * Class m210127_181920_sim_registry_add_old_data
 */
class m210127_181920_sim_registry_add_old_data extends Migration
{
    public $tableName;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = Registry::tableName();

        $sql = <<<SQL
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 5, '0', '4', '0', '4', '425019613998945', '425019613998949', '260060149991950', '260060149991954', 'ICCID: 8970137621000000000-8970137621000000004
IMSI: 250377400000000-250377400000004', null, '2020-02-25 12:00:00', '2020-02-25 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 5, '5', '9', '5', '9', '425019613998483', '425019613998487', '260060149991499', '260060149991503', 'ICCID: 8970137621000000005-8970137621000000009
IMSI: 250377400000005-250377400000009', null, '2020-02-25 12:00:00', '2020-02-25 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 3000, '10', '3009', '10', '3009', '425019613788000', '425019613790999', '260060149950000', '260060149952999', 'ICCID: 8970137621000000010-8970137621000003009
IMSI: 250377400000010-250377400003009', null, '2020-04-23 12:00:00', '2020-04-23 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 3000, '3010', '6009', '3010', '6009', '425019613955000', '425019613957999', '260060149935000', '260060149937999', 'ICCID: 8970137621000003010-8970137621000006009
IMSI: 250377400003010-250377400006009', null, '2020-05-12 12:00:00', '2020-05-12 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 2000, '6010', '8009', '6010', '8009', '425019613096000', '425019613097999', '260060149968000', '260060149969999', 'ICCID: 8970137621000006010-8970137621000008009
IMSI: 250377400006010-250377400008009', null, '2020-09-30 12:00:00', '2020-09-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 8000, '8010', '16009', '8010', '16009', '425019613098000', '425019613105999', '260060149982000', '260060149989999', 'ICCID: 8970137621000008010-8970137621000016009
IMSI: 250377400008010-250377400016009', null, '2020-09-30 12:00:00', '2020-09-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 45, 1000, '0', '999', '0', '999', '425019613185000', '425019613185999', '260060143265000', '260060143265999', 'ICCID: 8970137321000000000-8970137321000000999
IMSI: 250374500000000-250374500000999', null, '2020-10-22 12:00:00', '2020-10-22 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 47, 1000, '0', '999', '0', '999', '425019613186000', '425019613186999', '260060143266000', '260060143266999', 'ICCID: 8970137351000000000-8970137351000000999
IMSI: 250374700000000-250374700000999', null, '2020-10-22 12:00:00', '2020-10-22 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 24, 1000, '0', '999', '0', '999', '425019613187000', '425019613187999', '260060143267000', '260060143267999', 'ICCID: 8970137501000000000-8970137501000000999
IMSI: 250372000000000-250372000000999', null, '2020-10-22 12:00:00', '2020-10-22 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 29, 1000, '0', '999', '0', '999', '425019613189000', '425019613189999', '260060143269000', '260060143269999', 'ICCID: 8970137341000000000-8970137341000000999
IMSI: 250373400000000-250373400000999', null, '2020-10-22 12:00:00', '2020-10-22 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 65, 1000, '0', '999', '0', '999', '425019613188000', '425019613188999', '260060143268000', '260060143268999', 'ICCID: 8970137631000000000-8970137631000000999
IMSI: 250377500000000-250377500000999', null, '2020-10-22 12:00:00', '2020-10-22 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 64, 10000, '16010', '26009', '16010', '26009', '425019613190000', '425019613199999', '260060143270000', '260060143279999', 'ICCID: 8970137621000016010-8970137621000026009
IMSI: 250377400016010-250377400026009', null, '2020-10-30 12:00:00', '2020-10-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 62, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137601000000000-8970137601000000003
IMSI: 250377200000000-250377200000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 9, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137101000000000-8970137101000000003
IMSI: 250373100000000-250373100000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 10, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137181000000000-8970137181000000003
IMSI: 250375000000000-250375000000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 42, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137271000000000-8970137271000000003
IMSI: 250371400000000-250371400000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 14, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137091000000000-8970137091000000003
IMSI: 250371100000000-250371100000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 40, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137121000000000-8970137121000000003
IMSI: 250373000000000-250373000000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 25, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137081000000000-8970137081000000003
IMSI: 250373200000000-250373200000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 37, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137111000000000-8970137111000000003
IMSI: 250372200000000-250372200000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 13, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137021000000000-8970137021000000003
IMSI: 250373700000000-250373700000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 57, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137451000000000-8970137451000000003
IMSI: 250376700000000-250376700000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 54, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137421000000000-8970137421000000003
IMSI: 250376400000000-250376400000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 8, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137211000000000-8970137211000000003
IMSI: 250372100000000-250372100000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 26, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137041000000000-8970137041000000003
IMSI: 250375700000000-250375700000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 4, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137581000000000-8970137581000000003
IMSI: 250373800000000-250373800000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 11, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137191000000000-8970137191000000003
IMSI: 250375100000000-250375100000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 50, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137381000000000-8970137381000000003
IMSI: 250376000000000-250376000000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 56, 4, '0', '3', '0', '3', null, null, null, null, 'ICCID: 8970137441000000000-8970137441000000003
IMSI: 250376600000000-250376600000003', null, '2020-11-18 12:00:00', '2020-11-18 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 62, 500, '4', '503', '4', '503', '425019613106000', '425019613106499', '260060143280000', '260060143280499', 'ICCID: 8970137601000000004-8970137601000000503
IMSI: 250377200000004-250377200000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 9, 500, '4', '503', '4', '503', '425019613106500', '425019613106999', '260060143280500', '260060143280999', 'ICCID: 8970137101000000004-8970137101000000503
IMSI: 250373100000004-250373100000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 10, 500, '4', '503', '4', '503', '425019613107000', '425019613107499', '260060143281000', '260060143281499', 'ICCID: 8970137181000000004-8970137181000000503
IMSI: 250375000000004-250375000000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 42, 500, '4', '503', '4', '503', '425019613107500', '425019613107999', '260060143281500', '260060143281999', 'ICCID: 8970137271000000004-8970137271000000503
IMSI: 250371400000004-250371400000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 14, 500, '4', '503', '4', '503', '425019613108000', '425019613108499', '260060143282000', '260060143282499', 'ICCID: 8970137091000000004-8970137091000000503
IMSI: 250371100000004-250371100000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 40, 500, '4', '503', '4', '503', '425019613108500', '425019613108999', '260060143282500', '260060143282999', 'ICCID: 8970137121000000004-8970137121000000503
IMSI: 250373000000004-250373000000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 25, 500, '4', '503', '4', '503', '425019613109000', '425019613109499', '260060143283000', '260060143283499', 'ICCID: 8970137081000000004-8970137081000000503
IMSI: 250373200000004-250373200000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 37, 500, '4', '503', '4', '503', '425019613109500', '425019613109999', '260060143283500', '260060143283999', 'ICCID: 8970137111000000004-8970137111000000503
IMSI: 250372200000004-250372200000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 13, 500, '4', '503', '4', '503', '425019613110000', '425019613110499', '260060143284000', '260060143284499', 'ICCID: 8970137021000000004-8970137021000000503
IMSI: 250373700000004-250373700000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 57, 500, '4', '503', '4', '503', '425019613180000', '425019613180499', '260060143260000', '260060143260499', 'ICCID: 8970137451000000004-8970137451000000503
IMSI: 250376700000004-250376700000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 54, 500, '4', '503', '4', '503', '425019613180500', '425019613180999', '260060143260500', '260060143260999', 'ICCID: 8970137421000000004-8970137421000000503
IMSI: 250376400000004-250376400000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 8, 500, '4', '503', '4', '503', '425019613181000', '425019613181499', '260060143261000', '260060143261499', 'ICCID: 8970137211000000004-8970137211000000503
IMSI: 250372100000004-250372100000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 26, 500, '4', '503', '4', '503', '425019613181500', '425019613181999', '260060143261500', '260060143261999', 'ICCID: 8970137041000000004-8970137041000000503
IMSI: 250375700000004-250375700000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 4, 500, '4', '503', '4', '503', '425019613182000', '425019613182499', '260060143262000', '260060143262499', 'ICCID: 8970137581000000004-8970137581000000503
IMSI: 250373800000004-250373800000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 11, 500, '4', '503', '4', '503', '425019613182500', '425019613182999', '260060143262500', '260060143262999', 'ICCID: 8970137191000000004-8970137191000000503
IMSI: 250375100000004-250375100000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 50, 500, '4', '503', '4', '503', '425019613183000', '425019613183999', '260060143263000', '260060143263999', 'ICCID: 8970137381000000004-8970137381000000503
IMSI: 250376000000004-250376000000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
INSERT INTO {$this->tableName} (state, region_sim_settings_id, count, iccid_from, iccid_to, imsi_from, imsi_to, imsi_s1_from, imsi_s1_to, imsi_s2_from, imsi_s2_to, log, errors, created_at, updated_at, started_at, completed_at, created_by) VALUES (50, 56, 500, '4', '503', '4', '503', '425019613184000', '425019613184999', '260060143264000', '260060143264999', 'ICCID: 8970137441000000004-8970137441000000503
IMSI: 250376600000004-250376600000503', null, '2020-11-30 12:00:00', '2020-11-30 13:00:00', null, null, 74);
SQL;

        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        
    }
}
