    <?php

class m150710_000004_editTriggerToPGClients extends \app\classes\Migration
{
    public function up()
    {
        $sql = <<<SQL
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
                   NEW.password <> OLD.password
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

            if OLD.password <> NEW.password THEN
                 call add_event('password_changed', NEW.id);
            end if;

            if OLD.admin_contact_id <> NEW.admin_contact_id THEN
                 call add_event('admin_changed', NEW.id);
            end if;

            END;


            ALTER DEFINER=`latyntsev`@`localhost` VIEW
              `clients_select` AS select `nispd`.`clients`.`id` AS `id`,
                `nispd`.`clients`.`client` AS `client`,
                md5(`nispd`.`clients`.`password`) AS `password`
              from `nispd`.`clients`
              where ((`nispd`.`clients`.`status` in ('work','connecting')) or (`nispd`.`clients`.`id` = 9130))  ;

          DROP PROCEDURE `create_super_client`;
          DROP TRIGGER `create_super_client`;

          ALTER ALGORITHM = UNDEFINED DEFINER=`latyntsev`@`localhost` VIEW `client_grid_statuses` AS select `nispd`.`clients`.`id` AS `client_id`,
          cc.`business_process_status_id` AS `grid_status_id`
	from `clients`
	inner join client_contract cc on cc.id = clients.contract_id ;


SQL;

        $this->execute($sql);
    }

    public function down()
    {
        echo "m150601_135758_editTriggerToPGClients cannot be reverted.\n";

        return false;
    }
}