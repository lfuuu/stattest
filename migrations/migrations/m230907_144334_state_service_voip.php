<?php

/**
 * Class m230907_144334_state_service_voip
 */
class m230907_144334_state_service_voip extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->executeRaw(<<<SQL
    create table state_service_voip
    (
        usage_id       int                not null,
        client_id      int                not null,
        e164           bigint             not null,
        region         smallint default 0 not null,
        actual_from    date               not null,
        actual_to      date,
        activation_dt  datetime           not null,
        expire_dt      datetime,
        lines_amount   int,
        device_address longtext           null,
        primary key (usage_id),
        index region (region)
    );
SQL);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable('state_service_voip');
    }
}
