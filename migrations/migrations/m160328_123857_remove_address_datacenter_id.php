<?php

use \app\classes\Migration;

class m160328_123857_remove_address_datacenter_id extends Migration
{
    public function up()
    {
        $this->update("usage_voip", ['address' => ''], 'address_from_datacenter_id > 0');

        $this->executeRaw("
                UPDATE usage_voip uv1,
                  (SELECT uv.id, (IF (legal_type = 'person',
                                      IF(registration_address != '',
                                         registration_address,
                                         IF (address_jur != '',
                                             address_jur,
                                             '')),cg.address_jur)) AS newaddres
                  FROM `usage_voip` uv,
                        clients c,
                        client_contract cc,
                        client_contragent cg
                  LEFT JOIN client_contragent_person cgp ON (cg.id = cgp.contragent_id)
                  WHERE uv.client = c.client
                    AND c.contract_id = cc.id
                    AND cg.id = cc.contragent_id
                    AND uv.address = '') a
                SET uv1.address = a.newaddres
                WHERE uv1.id = a.id
        ");
    }

    public function down()
    {
        $this->executeRaw("
          UPDATE usage_voip uv,
                 datacenter d
          SET uv.address = d.address
          WHERE address_from_datacenter_id > 0
            AND d.id = uv.address_from_datacenter_id
        ");


        return true;
    }
}