<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ActualNumber;

/**
 * @method static ActualNumberDao me($args = null)
 * @property
 */
class ActualNumberDao extends Singleton
{
    public function collectFromUsages()
    {
        return
            ActualNumber::getDb()->createCommand("
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
                            and ((c.status in ('work','connecting','testing')) or c.id = 9130) 
                            and LENGTH(e164) > 3
                        ORDER BY u.id
                    )a
                    ")->queryAll();
    }

    public function loadSaved()
    {
        return ActualNumber::getDb()->createCommand("
                SELECT 
                    client_id, 
                    number, 
                    region, 
                    call_count, 
                    number_type, 
                    direction, 
                    line7800_id,
                    is_blocked, 
                    is_disabled 
                FROM 
                    actual_number a
                    ORDER BY id")->queryAll();
    }
}
