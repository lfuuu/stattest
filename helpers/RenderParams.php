<?php
namespace app\helpers;

use Yii;
use app\models\ClientAccount;
use app\models\Region;
use app\models\Country;

class RenderParams
{

    /**
    *
    * @param string $tpl
    * @param int $clientAccountId
    * @param int $contactId
    **/
    public static function tplFilter($tpl, $clientAccountId, $contactId)
    {
        assert(!empty($tpl));
        foreach(Yii::$app->params['mail_map_names'] as $replaceFrom => $call) {
            $replaceTo = static::$call($clientAccountId, $contactId);
            $tpl = str_replace($replaceFrom, $replaceTo, $tpl);
        }
        return $tpl;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getClientAccountId($clientAccountId, $contactId)
    {
        return $clientAccountId;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getContactId($clientAccountId, $contactId)
    {
        return $contactId;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getKey($clientAccountId, $contactId)
    {
        return 'key';
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getContractNum($clientAccountId, $contactId)
    {
	return ClientAccount::findOne($clientAccountId)->contract_id;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getBalance($clientAccountId, $contactId)
    {
	return ClientAccount::findOne($clientAccountId)->getRealtimeBalance();
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getLnk($clientAccountId, $contactId)
    {
	$region_id = ClientAccount::findOne($clientAccountId)->region;
	$country_id = Region::findOne($region_id)->country_id;
	$lkPrefix = Yii::t('settings', 'lk_domain', [], Country::findOne(['code' => $country_id])->lang);
	return $lkPrefix . 'core/auth/activate?token=<token>';
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private static function getPassword($clientAccountId, $contactId)
    {
	return 'anyPass';
    }

}
