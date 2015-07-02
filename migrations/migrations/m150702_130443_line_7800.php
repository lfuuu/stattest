<?php

class m150702_130443_line_7800 extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `actual_number`
            DROP COLUMN `line7800_id`,
            ADD COLUMN `number7800`  char(13) NOT NULL DEFAULT '' AFTER `is_disabled`");

        $this->execute("
                update actual_number an,    ( SELECT
                                a.*,
                                IF (number_type = 'nonumber', IFNULL((SELECT e164 FROM usage_voip u WHERE line7800_id = usage_id AND CAST(NOW() AS  DATE) BETWEEN actual_from AND actual_to),'') , '') AS number7800
                            FROM (
                                SELECT
                                    e164 AS number,
                                    usage_id,
                                    IF(is_virtual, 'vnumber', IF(LENGTH(e164) > 5,'number','nonumber')) AS number_type

                                FROM (
                                        SELECT
                                            c.id AS client_id,
                                            TRIM(e164) AS e164,
                                            u.no_of_lines,
                                            u.region,
                                            u.id as usage_id,
                                            #IFNULL((SELECT an.id FROM usage_voip u7800, actual_number an WHERE u7800.id = u.line7800_id and an.number = u7800.e164), 0) AS line7800_id,
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
                                            and LENGTH(e164) > 3 and LENGTH(e164) <= 5 
                                        ORDER BY u.id
                                    )a
                                WHERE e164 NOT LIKE '7800%'
                                )a

                having number7800 != '')a
                set an.number7800 = a.number7800 where an.number = a.number
                ");
    }

    public function down()
    {
        echo "m150702_130443_line_7800 cannot be reverted.\n";

        return false;
    }
}
