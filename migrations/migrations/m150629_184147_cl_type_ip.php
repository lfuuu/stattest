<?php

class m150629_184147_cl_type_ip extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("alter table clients modify `type` enum('org','priv','office','multi','distr','operator', 'ip') NOT NULL DEFAULT 'org'");


    $clients = app\models\ClientAccount::find()->where(
        [
            'and', 
            [
                'or', 
                ["regexp", "company_full", "^[ ]*(И|и)ндивидуальный[ ]+(П|п)редприниматель"], 
                ["regexp", "company_full", "^[ ]*И\.?[ ]*П\.?"],
                ["type" => "priv"]
            ],

            [
                "firma" => 'mcn_telekom',
                "contract_type_id" => 2,
                "business_process_id" => 1,
                "business_process_status_id" => [8 ,9, 11, 19, 21]
            ],

            ['not', ["or", ['like', 'company_full', 'ООО'], ['like', 'company_full', 'ЗАО']]]
            ])->all();

        foreach($clients as $client)
        {
            echo "\nclient: ".$client->id.", as type=IP";
            $client->type = 'ip';
            $client->save();
        }
    }

    public function down()
    {
        echo "m150629_184147_cl_type_ip cannot be reverted.\n";

        return false;
    }
}
