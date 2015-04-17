<?php

class m150415_170322_history extends \app\classes\Migration
{
    public function safeUp()
    {
        $this->execute("
            CREATE TABLE `history_changes` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `model` varchar(50) NOT NULL,
              `model_id` int(11) NOT NULL,
              `user_id` int(11) NOT NULL,
              `created_at` datetime NOT NULL,
              `action` enum('insert','update','delete') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `data_json` text NOT NULL,
              `prev_data_json` text NOT NULL,
              PRIMARY KEY (`id`),
              KEY `history_changes__model_model_id` (`model`,`model_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"sum\":\"',substr(l.comment, 8) ,'\"}'), '{\"sum\":\"\"}' from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Сумма: _%';

            delete from log_newbills where comment like 'Сумма:%';

            insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"postreg\":\"',substr(l.comment, 17) ,'\"}'), '{\"postreg\":\"\"}' from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Почтовый реестр _%';

            delete from log_newbills where comment like 'Почтовый реестр%';

             insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"postreg\":\"\"}'), '{\"postreg\":\"\"}' from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Удаление из почтового реестра';

              delete from log_newbills where comment like 'Удаление из почтового реестра';

              insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"doc_date\":\"',substr(l.comment, 28) ,'\"}'), '{\"doc_date\":\"\"}'  from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Дата документ установлена: %';

              delete from log_newbills where comment like 'Дата документ установлена%';


             insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"doc_date\":\"\"}'), '{\"doc_date\":\"\"}'  from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Дата документа убрана';

              delete from log_newbills where comment like 'Дата документа убрана';

             insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"bill_no_ext_date\":\"',substr(l.comment, 32) ,'\"}'), '{\"bill_no_ext_date\":\"\"}'  from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Именена дата внешнего счета на %';

             delete from log_newbills where comment like 'Именена дата внешнего счета на%';

              insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"bill_no_ext\":\"',substr(l.comment, 25) ,'\"}'), '{\"bill_no_ext\":\"\"}'  from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Именен внешний номер на %';

              delete from log_newbills where comment like 'Именен внешний номер на%';


             insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"nal\":\"',substr(l.comment, 38) ,'\"}'), '{\"nal\":\"\"}'  from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Именен предпологаемый тип платежа на %';

              delete from log_newbills where comment like 'Именен предпологаемый тип платежа на%';


             insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"courier_id\":\"0\"}'), '{\"courier_id\":\"\"}'  from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                where l.comment like 'Назначен курьер ';

              delete from log_newbills where comment like 'Назначен курьер ';

              insert into history_changes(model,model_id,user_id,created_at,action,data_json,prev_data_json)
                select 'Bill', b.id, l.user_id, l.ts, 'update', concat('{\"courier_id\":\"',ifnull(c.id,0),'\"}'), '{\"courier_id\":\"\"}' from log_newbills l
                inner join newbills b on l.bill_no =b.bill_no
                                 left join courier c on c.`name`=substr(l.comment, 17)
                where l.comment like 'Назначен курьер %';

              delete from log_newbills where comment like 'Назначен курьер %';
        ");
    }

    public function down()
    {
        echo "m150415_170322_history cannot be reverted.\n";

        return false;
    }
}
