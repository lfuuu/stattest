<?php

class m150720_142319_removePassFromClientsTrigger extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
DROP TRIGGER `to_postgres_clients_after_upd_tr`;
CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_clients_after_upd_tr` AFTER UPDATE ON `clients` FOR EACH ROW BEGIN
                if NEW.voip_credit_limit <> OLD.voip_credit_limit
                    or
                   NEW.voip_credit_limit_day <> OLD.voip_credit_limit_day
                    or
                   NEW.voip_disabled <> OLD.voip_disabled
                    or
                   NEW.balance <> OLD.balance
                    or
                   NEW.credit <> OLD.credit
                    or
                   ifnull(NEW.last_account_date, '2000-01-01') <> ifnull(OLD.last_account_date,'2000-01-01')
                    or
                   ifnull(NEW.last_payed_voip_month, '2000-01-01') <> ifnull(OLD.last_payed_voip_month,'2000-01-01')
              then
                 call z_sync_postgres('clients', NEW.id);
              end if;

                if NEW.client <> OLD.client
                    or
                   NEW.status <> OLD.status
                    or
                   NEW.balance <> OLD.balance
              then
                 call z_sync_auth('clients', NEW.id);
              end if;


                if NEW.client <> OLD.client
                    or
                   NEW.currency <> OLD.currency
                    or
                   NEW.price_type <> OLD.price_type
              then
                 call z_sync_1c('clientCard', NEW.id);
              end if;

            if OLD.admin_contact_id <> NEW.admin_contact_id THEN
                 call add_event('admin_changed', NEW.id);
            end if;

            END;
        ");
    }

    public function down()
    {
        echo "m150720_142319_removePassFromClientsTrigger cannot be reverted.\n";

        return false;
    }
}