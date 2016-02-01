<?php

class m160129_110555_append_EN_lang extends \app\classes\Migration
{
    public function up()
    {
        $this->insert('language', [
            'code' => 'en-EN',
            'name' => 'English',
        ]);
    }

    public function down()
    {
        $this->delete('language', ['code' => 'en-EN']);
    }
}