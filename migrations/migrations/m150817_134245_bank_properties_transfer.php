<?php

class m150817_134245_bank_properties_transfer extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent_person`
                CHANGE COLUMN `passport_issued` `passport_issued` VARCHAR (1024) NULL DEFAULT '' AFTER `passport_number`;

            INSERT INTO client_contragent_person (`contragent_id`, `passport_issued`)
                (
                    SELECT cg.id, GROUP_CONCAT(DISTINCT c.bank_properties SEPARATOR ' / ') FROM clients c
                    INNER JOIN client_contract cr ON cr.id = c.contract_id
                    INNER JOIN client_contragent cg ON cg.id = cr.contragent_id
                    WHERE cg.legal_type = 'person' AND c.bank_properties NOT IN ('', '-') AND cg.id NOT IN(
                        SELECT DISTINCT contragent_id FROM client_contragent_person
                    )
                    GROUP BY cg.id
                )
            ;

            UPDATE clients SET bank_properties = ''
                WHERE contract_id IN (
                    SELECT cr.id
                        FROM client_contract cr
                        INNER JOIN client_contragent cg ON cg.id = cr.contragent_id
                        WHERE cg.id IN(
                            SELECT DISTINCT contragent_id FROM client_contragent_person
                        )
                );
        ");
    }

    public function down()
    {
        echo "m150817_134245_bank_properties_transfer cannot be reverted.\n";

        return false;
    }
}