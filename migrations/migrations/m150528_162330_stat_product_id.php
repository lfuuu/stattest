<?php

class m150528_162330_stat_product_id extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `product_state`
            MODIFY COLUMN `client_id`  int(11) NOT NULL DEFAULT 0 FIRST ,
            ADD COLUMN `stat_product_id`  int NOT NULL DEFAULT 0 AFTER `product` ,
            DROP INDEX `client_id` ,
            ADD UNIQUE INDEX `client_id` (`client_id`, `product`, `stat_product_id`) USING BTREE ;
        ");

        $this->execute("
            update product_state p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.stat_product_id = u.id
            where p.product = 'vpbx' and p.stat_product_id = 0 and u.actual_from <= cast(now() AS date) and u.actual_to >= cast(now() AS date)
        ");

        $this->execute("
            update product_state p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.stat_product_id = u.id
            where p.product = 'vpbx' and p.stat_product_id = 0
         ");

        $this->execute("
            update virtpbx_stat p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.usage_id = u.id
            where p.usage_id = 0 and u.actual_from <= cast(now() AS date) and u.actual_to >= cast(now() AS date)
        ");


        $this->execute("
            update virtpbx_stat p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.usage_id = u.id
            where p.usage_id = 0
        ");

        $this->execute("
            CREATE TABLE `actual_virtpbx` (
              `usage_id` int(11) NOT NULL DEFAULT '0',
              `client_id` int(11) NOT NULL DEFAULT '0',
              `tarif_id` int(11) DEFAULT NULL,
              PRIMARY KEY (`usage_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            INSERT INTO `actual_virtpbx`(usage_id, client_id, tarif_id)
                SELECT
                    u.id as usage_id,
                    c.id as client_id,
                    IFNULL((SELECT id_tarif AS id_tarif FROM log_tarif WHERE service='usage_virtpbx' AND id_service=u.id AND date_activation<NOW() ORDER BY date_activation DESC, id DESC LIMIT 1),0) AS tarif_id
                FROM
                    usage_virtpbx u, clients c
                WHERE
                        actual_from <= DATE_FORMAT(now(), '%Y-%m-%d')
                    AND actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')
                    AND u.client = c.client
                    AND (
                               c.status IN ('work','negotiations','connecting','testing','debt','blocked','suspended')
                            OR c.client = 'id9130'
                            )
                ORDER BY u.id
        ");

        $this->execute("
            CREATE TABLE `actual_number` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `client_id` int(11) NOT NULL,
              `number` char(16) NOT NULL,
              `region` int(11) NOT NULL DEFAULT '99',
              `call_count` int(11) NOT NULL,
              `number_type` enum('vnumber','nonumber','number') NOT NULL DEFAULT 'number',
              `direction` enum('localmob','local','full','blocked','russia') NOT NULL DEFAULT 'full',
              `line7800_id` int(11) NOT NULL DEFAULT '0',
              `is_blocked` tinyint(4) NOT NULL DEFAULT '0',
              `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `number` (`number`) USING BTREE
            ) ENGINE=MyISAM AUTO_INCREMENT=4772 DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            INSERT INTO actual_number (`client_id`, `number`, `region`,`call_count`,`number_type`,`direction`,`line7800_id`,`is_blocked`,`is_disabled` )
				SELECT
                    client_id,
                    e164 AS number,
                    region,
                    no_of_lines AS call_count,
                    IF(is_virtual, 'vnumber', IF(LENGTH(e164) > 5,'number','nonumber')) AS number_type,
                    allowed_direction AS direction,
                    line7800_id,
                    is_blocked,
                    voip_disabled AS is_disabled

                FROM (
                        SELECT
                            c.id AS client_id,
                            TRIM(e164) AS e164,
                            u.no_of_lines,
                            u.region,
                            IFNULL((SELECT an.id FROM usage_voip u7800, actual_number an WHERE u7800.id = u.line7800_id and an.number = u7800.e164), 0) AS line7800_id,
                            IFNULL((SELECT block FROM log_block WHERE id= (SELECT MAX(id) FROM log_block WHERE service='usage_voip' AND id_service=u.id)), 0) AS is_blocked,
                            IFNULL((
                                SELECT
                                    is_virtual
                                FROM
                                    log_tarif lt, tarifs_voip tv
                                WHERE
                                        service = 'usage_voip'
                                    AND id_service = u.id
                                    AND id_tarif = tv.id
                                ORDER BY lt.date_activation DESC, lt.id DESC
                                LIMIT 1), 0) AS is_virtual,
                            allowed_direction,
                            c.voip_disabled
                        FROM
                            usage_voip u, clients c
                        WHERE
                            (actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') and actual_to >= DATE_FORMAT(now(), '%Y-%m-%d'))
                            and u.client = c.client
                            and ((c.status in ('negotiations','work','connecting','testing')) or c.id = 9130)
                            and LENGTH(e164) > 3
                        ORDER BY u.id
                    )a
            ");

    }

    public function down()
    {
        echo "m150528_162330_stat_product_id cannot be reverted.\n";

        return false;
    }
}