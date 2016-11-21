<?php

use app\classes\uu\model\Resource;

class m161031_134405_uu_resource_unit_eng extends \app\classes\Migration
{
    public function up()
    {
        $tableName = Resource::tableName();

        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="Гб"', ['value' => 'Gb']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="шт."', ['value' => 'Unit']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="у.е."', ['value' => '¤']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="Мб."', ['value' => 'Mb']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="ГГц"', ['value' => 'Hz']);
    }

    public function down()
    {
        $tableName = Resource::tableName();

        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="Gb"', ['value' => 'Гб']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="Unit"', ['value' => 'шт.']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="¤"', ['value' => 'у.е.']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="Mb"', ['value' => 'Мб.']);
        $this->execute('UPDATE ' . $tableName . ' SET unit=:value WHERE unit="Hz"', ['value' => 'ГГц']);
    }
}