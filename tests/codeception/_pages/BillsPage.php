<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents adding new client page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class BillsPage extends BasePage
{
    public $route = '?module=newaccounts';

    public function createRegularBill($date = null)
    {
        $this->actor->click('Создать счёт');
        if (!is_null($date) && strtotime($date) !== false)
        {
            $this->actor->click('редактировать');
            $this->actor->fillField('input[name="bill_date"]', $date);
            $this->actor->click('Изменить');
        }
        $this->actor->click('Ежемесячное');
        
    }
}
