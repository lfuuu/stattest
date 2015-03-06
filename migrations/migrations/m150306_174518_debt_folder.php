<?php

class m150306_174518_debt_folder extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("update grid_settings set `sql`='SELECT 
             cl.status, cl.id, cl.client, cl.company, cl.manager, cl.support, cl.telemarketing, s.name AS sale_channel, cl.created, cl.currency, 
                DATE(cls.ts) date_zayavka
           FROM clients cl
         LEFT JOIN client_statuses cls ON cl.id = cls.id_client
          AND
             ( cls.id IS NULL AND
                    cls.id = (SELECT MIN(id) FROM client_statuses WHERE id_client=cl.id)
                )
                        LEFT JOIN sale_channels s ON (s.id = cl.sale_channel)
inner join client_grid_statuses cs on (cs.client_id = cl.id and cs.grid_status_id = 9 and is_blocked=1)
          WHERE
               cl.contract_type_id=2' where id=11");

    }

    public function down()
    {
        echo "m150306_174518_debt_folder cannot be reverted.\n";

        return false;
    }
}
