<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents adding new client page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class NewClientPage extends BasePage
{
    public $route = '/client/create';

    public function createClient($data = array())
    {
        if (!empty($data))
        {
            foreach ($data as $section => $sectionData)
            {
                foreach($sectionData as $k => $v)
                {
                    if ($k == "comment")
                    {
                        $key = 'textarea[name="'. $section . '[' . $k .']"]';
                        $this->actor->fillField($key, $v);
                    }elseif (in_array($k, [
                        "country_id", "opf_id", "tax_regime", 
                        "business_id", "business_process_id", "business_process_status_id", 
                        "manager", "account_manager", "organization_id", "state", "nal",
                        "region", "timezone_name", "currency", "price_type", "form_type",
                        "sale_channel_id", "partner_contract_id"]))
                    {
                        $key = 'select[name="'. $section . '[' . $k .']"]';
                        $this->actor->selectOption($key, $v);
                    } elseif (in_array($k, ["mail_print", "is_with_consignee", "stamp", "is_upd_without_sign", "is_agent"]))
                    {
                        if ($v)
                        {
                            $key = 'input[name="'. $section . '[' . $k .']"][type=checkbox]';
                            $this->actor->checkOption($key);
                        }
                    } else {
                        $key = 'input[name="'. $section . '[' . $k .']"]';
                        $this->actor->fillField($key, $v);
                    }
                }
            }
            $this->actor->click('Сохранить');
        }
        
    }
}
