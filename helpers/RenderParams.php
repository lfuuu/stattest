<?php
namespace app\helpers;

use Yii;
use app\models\ClientAccount;
use app\models\Region;
use app\models\Country;

class RenderParams
{

    public static function tplFilter($tpl, $clientAccountId, $contactId)
    {
        assert(!empty($tpl));
        foreach(Yii::$app->params['mail_map_names'] as $replaceFrom => $call) {
            $replaceTo = static::$call($clientAccountId, $contactId);
            $tpl = str_replace($replaceFrom, $replaceTo, $tpl);
        }
        return $tpl;
    }

    private static function getClientAccountId($clientAccountId, $contactId)
    {
        return $clientAccountId;
    }

    private static function getContactId($clientAccountId, $contactId)
    {
        return $contactId;
    }

    private static function getKey($clientAccountId, $contactId)
    {
        return 'key';
    }

    private static function getContractNum($clientAccountId, $contactId)
    {
	return ClientAccount::findOne(['id' => $clientAccountId])->contract_id;
    }

    private static function getBalance($clientAccountId, $contactId)
    {
	return ClientAccount::findOne(['id' => $clientAccountId])->getRealtimeBalance();
    }

    private static function getLnk($clientAccountId, $contactId)
    {
	$region_id = ClientAccount::findOne(['id' => $clientAccountId])->region;
	$country_id = Region::findOne(['id' => $region_id])->country_id;
	if (Country::findOne(['code' => $country_id])->lang == 'ru-RU' ){
	    $lkPrefix = 'https://lk.mcn.ru/';
	} else {
	    $lkPrefix = 'https://lk.mcntele.com/';
	}
	return $lkPrefix . 'core/auth/activate?token=<token>';
    }

    private static function getPassword($clientAccountId, $contactId)
    {
	return 'anyPass';
    }

}
