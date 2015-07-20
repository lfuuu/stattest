<?php

class m150720_103940_addRights extends \app\classes\Migration
{
    public function up()
    {
        return $this->execute("
UPDATE `nispd`.`user_rights` SET `values`='read,read_filter,read_all,new,edit,restatus,edit_tele,sale_channels,file,inn_double,all4net,history_edit,client_type_change,changeback_contract_state', `values_desc`='просмотр вообще,просмотр с фильтрами,просмотр всех,создание,редактирование,изменение статуса,редактирование для телемаркетинга,редактирование каналов продаж,доступ к файлам,заведение совпадающих ИНН,синхронизация с all4net,редактирование истории,Изменение тип договора,Изменение статуса проверки документов на \"не проверено\"' WHERE  `resource`='clients';
UPDATE `nispd`.`user_grant_groups` SET `access`='read,read_filter,read_all,new,edit,restatus,sale_channels,file,inn_double,all4net,client_type_change,changeback_contract_state' WHERE  `name`='admin' AND `resource`='clients';
        ");
    }

    public function down()
    {
        echo "m150720_103940_addRights cannot be reverted.\n";

        return false;
    }
}