<?php

use app\models\ClientContragent;
use app\models\ClientAccount;

class m160404_133022_fix_clients_partners_link extends \app\classes\Migration
{
    public function up()
    {
        $clientsOfParthers = new \yii\db\Query;

        $clientsOfParthers
            ->select([
                'cc.id', 'cc.partner_contract_id', 'c.contract_id'
            ]);

        $clientsOfParthers
            ->from([
                'cc' => ClientContragent::tableName()
            ])
            ->leftJoin([
                'c' => ClientAccount::tableName(),
            ], 'c.id = cc.partner_contract_id');

        $clientsOfParthers
            ->where('cc.partner_contract_id')
            ->andWhere('c.id != c.contract_id');

        foreach ($clientsOfParthers->all() as $record) {
            $this->update(
                ClientContragent::tableName(),
                [
                    'partner_contract_id' => $record['contract_id'],
                ],
                [
                    'id' => $record['id'],
                ]
            );
        }
    }

    public function down()
    {
    }
}