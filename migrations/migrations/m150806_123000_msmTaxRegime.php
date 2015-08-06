<?php

class m150806_123000_msmTaxRegime extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE client_contragent SET tax_regime = '1';

            UPDATE client_contragent SET tax_regime = '2' WHERE id IN (
                SELECT id FROM (
                    SELECT cg.id FROM client_contract cr
                    INNER JOIN client_contragent cg ON cr.contragent_id = cg.id
                    WHERE cr.organization_id = 11
                ) z
            );
        ");
    }

    public function down()
    {
        echo "m150806_123000_msmTaxRegime cannot be reverted.\n";

        return false;
    }
}