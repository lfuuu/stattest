<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents adding new client page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class NewClient extends BasePage
{
    public $route = 'index.php?module=clients&action=new';

    public function createClient($data = array())
    {
        if (!empty($data))
        {
            foreach ($data as $k => $v)
            {
                $key = 'input[name="'. $k .'"]';
                $this->actor->fillField($key, $v);
            }
            $this->actor->click('Изменить');
        }
        
    }
}
