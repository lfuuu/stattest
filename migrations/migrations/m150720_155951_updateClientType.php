<?php

class m150720_155951_updateClientType extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            update clients set type = 'org' where id in (
            select id from (SELECT id,type,company_full,  status FROM `clients` where
            length(company_full) > 0
            and
             (
               company_full regexp '^[ ]*(OOO|ООО|ЗАО|ОАО|ГБУ|АО|ОАНО|АНО|ПАО|НО|ФГОУ|ТОО)'or 
            company_full regexp '^[ ]*(З|з)акрытое[ ]+акционерное[ ]+общество'
            or company_full REGEXP '^[ ]*(О|о)бщество[ ]+с[ ]+ограниченной[ ]+ответственностью'
            or company_full REGEXP '^[ ]*Акционерное[ ]+общество'
            or company_full REGEXP '^[ ]*(А|а)втономная[ ]+некоммерческая[ ]+организация'

            or company_full REGEXP '^[ ]*Производственный[ ]+кооператив'
            or company_full REGEXP 'Благотворительный[ ]+фонд'
            or company_full REGEXP 'ООО'
            or company_full REGEXP '(OOO|ООО|ЗАО|ОАО|ГБУ|АО|ОАНО|АНО|ПАО)'
            or company_full REGEXP 'омпани'
            or company_full REGEXP 'Федеральное '
            or company_full REGEXP 'Негосударственное'

            )

            and type != 'org'
            and type != 'office'
            and type != 'multi'
            )a
            )
            ");
        $this->execute("
            update clients set type = 'ip' where id in (
            select id from (
            SELECT id,type,company_full,  status FROM `clients` where
            length(company_full) > 0
            and
             (
            company_full REGEXP '^[ ]*(И|и)ндивидуальный[ ]+(П|п)редприниматель'
            or
            company_full REGEXP '^[ ]*ИП'
            or
            company_full REGEXP '^[ ]*ПБОЮЛ'
            )

            and type != 'ip'
            and type != 'office'
            and type != 'multi'
            )a)
            ");
        $this->execute("
            update clients set type ='priv' where id in (select id from (
            select id, company_full, type, bank_properties, status from clients WHERE

            bank_properties REGEXP '^[ ]*Паспорт'
            and type != 'priv'
            and type != 'office'
            and type != 'multi'

            )a)
            ");

    }

    public function down()
    {
        echo "m150720_155951_updateClientType cannot be reverted.\n";

        return false;
    }
}








