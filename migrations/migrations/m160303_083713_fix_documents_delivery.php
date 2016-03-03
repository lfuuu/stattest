<?php

class m160303_083713_fix_documents_delivery extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            DELETE FROM `client_account_options`
            WHERE
             `option` = "mail_delivery_variant"
             AND `value` = "undefined"
             AND `client_account_id` IN (
                 SELECT client_account_id FROM (
                    SELECT DISTINCT(c1.`client_account_id`)
                    FROM
                        `client_account_options` c1
                            LEFT JOIN `client_account_options` c2 ON c2.`client_account_id` = c1.`client_account_id` AND c1.`option` = c2.`option`
                    WHERE
                        c1.`option` = "mail_delivery_variant" AND c1.`value` = "payment"
                        AND c2.`option` = "mail_delivery_variant" AND c2.`value` = "undefined"
                 ) AS t_x
            )
        ');

        $this->delete('client_account_options', [
            'option' => 'mail_delivery_variant',
            'value' => 'black_list',
        ]);
    }

    public function down()
    {
    }
}