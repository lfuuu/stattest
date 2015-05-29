<?php

class m150528_162330_stat_product_id extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `product_state`
            MODIFY COLUMN `client_id`  int(11) NOT NULL DEFAULT 0 FIRST ,
            ADD COLUMN `stat_product_id`  int NOT NULL DEFAULT 0 AFTER `product` ,
            DROP INDEX `client_id` ,
            ADD UNIQUE INDEX `client_id` (`client_id`, `product`, `stat_product_id`) USING BTREE ;
        ");

        $this->execute("
            update product_state p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.stat_product_id = u.id
            where p.product = 'vpbx' and p.stat_product_id = 0 and u.actual_from <= cast(now() AS date) and u.actual_to >= cast(now() AS date)
        ");

        $this->execute("
            update product_state p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.stat_product_id = u.id
            where p.product = 'vpbx' and p.stat_product_id = 0
         ");

        $this->execute("
            update virtpbx_stat p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.usage_id = u.id
            where p.usage_id = 0 and u.actual_from <= cast(now() AS date) and u.actual_to >= cast(now() AS date)
        ");


        $this->execute("
            update virtpbx_stat p
            inner join clients c on c.id = p.client_id
            inner join usage_virtpbx u on c.client=u.client
            set p.usage_id = u.id
            where p.usage_id = 0
        ");
    }

    public function down()
    {
        echo "m150528_162330_stat_product_id cannot be reverted.\n";

        return false;
    }
}