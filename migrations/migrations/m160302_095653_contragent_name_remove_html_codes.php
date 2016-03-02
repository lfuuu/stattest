<?php

use yii\db\Expression;

class m160302_095653_contragent_name_remove_html_codes extends \app\classes\Migration
{
    public function up()
    {
        $this->update(
            'client_contragent',
            [
                'name' => new Expression('REPLACE(name_full, "&#8220;", "“")'),
                'name_full' => new Expression('REPLACE(name_full, "&#8220;", "“")'),
            ],
            ['like', 'name_full', '&#8220;']
        );

        $this->update(
            'client_contragent',
            [
                'name' => new Expression('REPLACE(name_full, "&#8221;", "”")'),
                'name_full' => new Expression('REPLACE(name_full, "&#8221;", "”")')
            ],
            ['like', 'name_full', '&#8221;']
        );
    }

    public function down()
    {
    }
}