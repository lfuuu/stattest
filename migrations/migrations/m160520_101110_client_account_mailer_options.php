<?php

use yii\db\Query;
use app\models\ClientAccountOptions;
use app\models\ClientAccount;
use app\models\Country;

class m160520_101110_client_account_mailer_options extends \app\classes\Migration
{
    public function up()
    {
        $query =
            (new Query)
                ->select([
                    'clients.id', 'countries.lang'
                ])
                ->from([
                    'clients' => ClientAccount::tableName(),
                ])
                ->leftJoin(
                    ['countries' => Country::tableName()],
                    'countries.code = clients.country_id'
                )
                ->leftJoin(
                    ['options' => ClientAccountOptions::tableName()],
                    'options.client_account_id = clients.id AND options.option="' . ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE . '"'
                )
                ->where(['is', 'options.value', null]);

        $insertData = [];

        foreach ($query->each() as $client) {
            $insertData[] = [
                $client['id'], ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE, $client['lang'],
            ];
        }

        if (count($insertData)) {
            $chunks = array_chunk($insertData, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert(
                    ClientAccountOptions::tableName(),
                    [
                        'client_account_id',
                        'option',
                        'value',
                    ],
                    $chunk
                );
            }
        }
    }

    public function down()
    {
    }
}