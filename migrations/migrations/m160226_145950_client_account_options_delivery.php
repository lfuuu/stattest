<?php

use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;
use app\models\UsageExtra;

class m160226_145950_client_account_options_delivery extends \app\classes\Migration
{
    public function up()
    {
        $paymentDeliveryAccounts = Yii::$app->db->createCommand('
            SELECT DISTINCT(c.`client`)
            FROM
                `client_account_options` cao LEFT JOIN `clients` c ON c.`id` = cao.`client_account_id`
            WHERE
                cao.`option` = "mail_delivery_variant"
                AND cao.`value` = "payment";
        ')->queryColumn('client');

        foreach ($paymentDeliveryAccounts as $clientAccountName) {
            $model = new UsageExtra;
            $model->client = $clientAccountName;
            $model->code = 'uspd';
            $model->tarif_id = 383; // Доставка комплекта бухгалтерских документов почтой РФ
            $model->actual_from = (new DateTime('2016-03-01'))->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $model->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
            $model->insert(true);
        }

        $paymentUndefinedDeliveryAccounts = Yii::$app->db->createCommand('
            SELECT
                c.`id`
            FROM
                `clients` c
                    LEFT JOIN
                        `client_account_options` cao on cao.`client_account_id` = c.`id`
            WHERE
                cao.`client_account_id` IS NULL
                OR (
                    cao.`option` = "mail_delivery_variant"
                    AND cao.`value` NOT IN ("payment", "undefined")
                )
            GROUP BY c.`id`
        ')->queryColumn('id');

        $insert = [];

        foreach ($paymentUndefinedDeliveryAccounts as $clientAccountId) {
            $insert[] = [
                $clientAccountId,
                'mail_delivery_variant',
                'undefined'
            ];
        }

        if (count($insert)) {
            $chunks = array_chunk($insert, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert('client_account_options', ['client_account_id', 'option', 'value'], $chunk);
            }

            $this->update('clients', ['mail_print' => 'no'], ['in', 'id', $paymentUndefinedDeliveryAccounts]);
        }
    }

    public function down()
    {
    }
}