<?php

class m160204_112359_convert_saleChanel_to_PartnerContractId extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE client_contragent cgg, 
            (
                SELECT sc.name, sale_channel, dealer_id, cg.partner_contract_id, cg.id AS cg_id, c.id
                FROM clients c, `sale_channels_old` sc, client_contract  cc, client_contragent cg

                WHERE sc.`is_agent` = '1' 
                AND c.sale_channel = sc.id
                AND cc.id = c.contract_id
                AND cg.id = cc.contragent_id
            )a

            set cgg.partner_contract_id = a.dealer_id
            where cgg.id = a.cg_id
            ");

        $this->execute("update client_contragent set partner_contract_id = 34265 where partner_contract_id = 33643");
        $this->execute("update client_contragent set partner_contract_id = 33728 where partner_contract_id =  33597");
        $this->execute("update client_contragent set partner_contract_id = 34265 where partner_contract_id =  23091");

        $this->execute("
                        insert into client_contract_reward select null, a.id, b.type, 0, 10, 10, 'month', 24
                        from 
                                                    
                        (SELECT
                                        cc.id AS id
                                    FROM
                                        (SELECT
                                            DISTINCT partner_contract_id
                                         FROM client_contragent
                                         WHERE partner_contract_id > 0
                                    ) p,
                                    clients c, client_contragent cg, client_contract cc
                                    LEFT JOIN client_contract_reward cr ON (cr.contract_id = cc.id)
                                    WHERE
                                            p.partner_contract_id = c.id
                                        AND c.contract_id = cc.id
                                        AND cc.contragent_id = cg.id
                                        AND cr.id is null
                                    ORDER BY cg.name
                        )a, (select 'voip' as type union select 'virtpbx' as type) b
                        ");

    }

    public function down()
    {
        echo "m160204_112359_convert_saleChanel_to_PartnerContractId cannot be reverted.\n";

        return false;
    }
}

