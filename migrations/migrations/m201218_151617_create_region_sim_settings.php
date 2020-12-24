<?php

use app\classes\Migration;
use app\modules\sim\models\RegionSettings;

/**
 * Class m201218_151617_create_region_sim_settings
 */
class m201218_151617_create_region_sim_settings extends Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = RegionSettings::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),

            'region_name' => $this->string(36)->notNull(),
            'region_code' => $this->string(3)->notNull(),

            'iccid_prefix' => $this->integer()->notNull(),
            'iccid_region_code' => $this->smallInteger()->notNull(),
            'iccid_vendor_code' => $this->smallInteger()->notNull(),
            'iccid_range_length' => $this->smallInteger()->notNull(),

            'iccid_last_used' => $this->integer()->notNull()->defaultValue(0),

            'imsi_prefix' => $this->integer()->notNull(),
            'imsi_region_code' => $this->smallInteger(),
            'imsi_range_length' => $this->smallInteger()->notNull(),

            'region_id' => $this->integer(11),
            'parent_id' => $this->integer(11),

        ], $this->tableOptions);

        $tableName = $this->tableName;
        $sql = <<<SQL
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (1, 'Arkhangelsk', 'AR', 8970137, 54, 1, 9, 0, 25037, 19, 8, 35, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (2, 'Belgorod', 'BE', 8970137, 53, 1, 9, 0, 25037, 53, 8, 49, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (3, 'Birobidzhan', 'BJ', 8970137, 22, 1, 9, 0, 25037, 41, 8, 138, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (4, 'Bryansk', 'BR', 8970137, 58, 1, 9, 0, 25037, 38, 8, 85, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (5, 'Veliky Novgorod', 'VN', 8970137, 55, 1, 9, 0, 25037, 55, 8, 131, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (6, 'Vladimir', 'VL', 8970137, 15, 1, 9, 0, 25037, 26, 8, 42, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (7, 'Vologda', 'VA', 8970137, 17, 1, 9, 0, 25037, 29, 8, 32, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (8, 'Voronezh', 'VO', 8970137, 21, 1, 9, 0, 25037, 21, 8, 86, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (9, 'Izhevsk', 'IZ', 8970137, 10, 1, 9, 0, 25037, 31, 8, 74, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (10, 'Kaliningrad', 'KG', 8970137, 18, 1, 9, 0, 25037, 50, 8, 72, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (11, 'Kaluga', 'KA', 8970137, 19, 1, 9, 0, 25037, 51, 8, 47, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (12, 'Kamchatka', 'KM', 8970137, 23, 1, 9, 0, 25037, 43, 8, 141, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (13, 'Kemerovo', 'KE', 8970137, 2, 1, 9, 0, 25037, 37, 8, 52, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (14, 'Kirov', 'KI', 8970137, 9, 1, 9, 0, 25037, 11, 8, 69, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (15, 'Kostroma', 'KO', 8970137, 20, 1, 9, 0, 25037, 10, 8, 46, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (16, 'Krasnodar', 'KR', 8970137, 57, 1, 9, 0, 25037, 54, 8, 97, 64);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (17, 'Kursk', 'KU', 8970137, 52, 1, 9, 0, 25037, 52, 8, 58, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (18, 'Lipetsk', 'LI', 8970137, 80, 1, 9, 0, 25037, 80, 8, 59, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (19, 'Magadan', 'MG', 8970137, 24, 1, 9, 0, 25037, 40, 8, 140, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (20, 'Maykop', 'AD', 8970137, 97, 1, 9, 0, 25037, 97, 8, 106, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (21, 'Murmansk', 'MU', 8970137, 51, 1, 9, 0, 25037, 13, 8, 71, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (22, 'Naryan-Mar', 'NM', 8970137, 28, 1, 9, 0, 25037, 17, 8, 134, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (23, 'Nizhniy Novgorod', 'NN', 8970137, 14, 1, 9, 0, 25037, 39, 8, 88, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (24, 'Novosibirsk', 'NS', 8970137, 50, 1, 9, 0, 25037, 20, 8, 94, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (25, 'Omsk', 'OM', 8970137, 8, 1, 9, 0, 25037, 32, 8, 53, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (26, 'Oryol', 'OR', 8970137, 4, 1, 9, 0, 25037, 57, 8, 45, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (27, 'Petrozavodsk', 'PT', 8970137, 5, 1, 9, 0, 25037, 12, 8, 33, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (28, 'Pskov', 'PS', 8970137, 7, 1, 9, 0, 25037, 59, 8, 34, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (29, 'Rostov-na-Donu', 'RO', 8970137, 34, 1, 9, 0, 25037, 34, 8, 87, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (30, 'Ryazan', 'RY', 8970137, 1, 1, 9, 0, 25037, 58, 8, 77, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (31, 'Sakhalin', 'SA', 8970137, 25, 1, 9, 0, 25037, 42, 8, 142, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (32, 'Sankt-Peterburg', 'SP', 8970137, 6, 1, 9, 0, 25037, 33, 8, 98, 64);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (33, 'Smolensk', 'SM', 8970137, 16, 1, 9, 0, 25037, 36, 8, 44, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (34, 'Syktyvkar', 'SY', 8970137, 56, 1, 9, 0, 25037, 56, 8, 29, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (35, 'Tambov', 'TA', 8970137, 3, 1, 9, 0, 25037, 25, 8, 43, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (36, 'Tver', 'TV', 8970137, 13, 1, 9, 0, 25037, 24, 8, 57, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (37, 'Tomsk', 'TO', 8970137, 11, 1, 9, 0, 25037, 22, 8, 54, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (38, 'Tula', 'TL', 8970137, 59, 1, 9, 0, 25037, 23, 8, 79, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (39, 'Tura', 'TR', 8970137, 29, 1, 9, 0, 25037, null, 8, null, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (40, 'Chelyabinsk', 'CH', 8970137, 12, 1, 9, 0, 25037, 30, 8, 90, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (41, 'Chukotka', 'CK', 8970137, 26, 1, 9, 0, 25037, null, 8, 137, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (42, 'Barnaul', 'BA', 8970137, 27, 1, 9, 0, 25037, 14, 8, 50, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (43, 'Buryatia', 'BU', 8970137, 30, 1, 9, 0, 25037, 15, 8, 31, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (44, 'Chuvashia', 'CV', 8970137, 31, 1, 9, 0, 25037, 16, 8, 62, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (45, 'Ekaterinburg', 'EK', 8970137, 32, 1, 9, 0, 25037, 45, 8, 95, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (46, 'Irkutsk', 'IR', 8970137, 33, 1, 9, 0, 25037, 46, 8, 51, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (47, 'Kazan', 'KZ', 8970137, 35, 1, 9, 0, 25037, 47, 8, 93, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (48, 'Khakassia', 'KH', 8970137, 36, 1, 9, 0, 25037, 69, 8, 115, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (49, 'KhMAO', 'KT', 8970137, 37, 1, 9, 0, 25037, 49, 8, 68, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (50, 'Krasnoyarsk', 'KK', 8970137, 38, 1, 9, 0, 25037, 60, 8, 55, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (51, 'Kurgan', 'KN', 8970137, 39, 1, 9, 0, 25037, 61, 8, 70, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (52, 'Mari El', 'ME', 8970137, 40, 1, 9, 0, 25037, 62, 8, 113, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (53, 'Mordovia', 'MR', 8970137, 41, 1, 9, 0, 25037, 63, 8, 41, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (54, 'Orenburg', 'OB', 8970137, 42, 1, 9, 0, 25037, 64, 8, 64, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (55, 'Penza', 'PE', 8970137, 43, 1, 9, 0, 25037, 65, 8, 65, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (56, 'Perm', 'PR', 8970137, 44, 1, 9, 0, 25037, 66, 8, 92, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (57, 'Saratov', 'ST', 8970137, 45, 1, 9, 0, 25037, 67, 8, 66, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (58, 'Tyumen', 'TN', 8970137, 46, 1, 9, 0, 25037, 68, 8, 78, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (59, 'Tyva', 'TY', 8970137, 47, 1, 9, 0, 25037, null, 8, 117, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (60, 'Ulyanovsk', 'UL', 8970137, 48, 1, 9, 0, 25037, 70, 8, 63, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (61, 'Vladivostok', 'VS', 8970137, 49, 1, 9, 0, 25037, 71, 8, 89, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (62, 'Volgograd', 'VG', 8970137, 60, 1, 9, 0, 25037, 72, 8, 91, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (63, 'YaNAO', 'YN', 8970137, 61, 1, 9, 0, 25037, 73, 8, 67, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (64, 'Moskva', 'MO', 8970137, 62, 1, 9, 0, 25037, 74, 8, 99, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (65, 'Samara', 'SR', 8970137, 63, 1, 9, 0, 25037, 75, 8, 96, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (66, 'Norilsk', 'NO', 8970137, 64, 1, 9, 0, 25037, 76, 8, null, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (67, 'Gorniy Altay', 'GA', 8970137, 65, 1, 9, 0, 25037, 77, 8, 116, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (68, 'Osetiya', 'OS', 8970137, 68, 1, 9, 0, 25037, 78, 8, 40, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (69, 'Yaroslavl', 'YO', 8970137, 66, 1, 9, 0, 25037, 79, 8, 56, null);
INSERT INTO nispd.{$tableName} (id, region_name, region_code, iccid_prefix, iccid_region_code, iccid_vendor_code, iccid_range_length, iccid_last_used, imsi_prefix, imsi_region_code, imsi_range_length, region_id, parent_id) VALUES (70, 'Ivanovo', 'IV', 8970137, 67, 1, 9, 0, 25037, 81, 8, 48, null);
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->tableName = RegionSettings::tableName();

        $this->dropTable($this->tableName);
    }
}
