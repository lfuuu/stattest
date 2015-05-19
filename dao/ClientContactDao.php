<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ClientContact;

class ClientContactDao extends Singleton
{
    public function GetContacts($clientId, $type = null,$onlyActive = false,$onlyOfficial = false) {
        global $db;
        $wh = '';
        if ($type) $wh.= ' and client_contacts.type="'.addslashes($type).'"';
        if ($onlyActive) $wh.= ' and client_contacts.is_active=1';
        if ($onlyOfficial) $wh.= ' and client_contacts.is_official=1';

        return ClientContact::getDb()->createCommand(
            "select 
                client_contacts.*,
                user_users.user 
            from 
                client_contacts 
            LEFT JOIN user_users ON user_users.id=client_contacts.user_id 
            where 
                client_id='".$clientId."'
                ".$wh." 
            order by client_contacts.id")->queryAll();
    }

    public function GetContact($clientId, $onlyOfficial = true) {
        global $db;
        $V = $this->GetContacts($clientId, null,true,$onlyOfficial);
        $R = array('fax'=>array(),'phone'=>array(),'email'=>array());
        if ($V) {
            foreach ($V as $v) $R[$v['type']][] = $v;
        }
        return $R;
    }
}
