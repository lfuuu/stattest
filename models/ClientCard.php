<?php
class ClientCard extends ActiveRecord\Model
{
    static $table_name = 'clients';
    static $after_create = array('init_client_nick');

    static $belongs_to = array(
        array("super", "class_name" => "ClientSuper", "foreign_key" => "super_id"),
        array("contragent", "class_name" => "ClientContragent", "foreign_key" => "contragent_id")
        );

    public function getClient()
    {
        if (strrpos($this->client, '/') === false)
            $client_nick = $this->client;
        else
            $client_nick = substr($this->client, 0, -2);

        if (!$client_nick)
            throw new Exception('Incorrect client nick');

        return ClientCard::first(array('conditions' => array('client = ?', $client_nick)));
    }

    public function init_client_nick()
    {
        if (!$this->client) {
            $this->client = 'id' . $this->id;
            $this->save();
        }
    }

    public function markSync($syncFlag)
    {
        if ($syncFlag) {
            ClientCard::query("call z_sync_1c('clientCard',?", array($this->id));
        } else {
            ClientCard::query("delete from z_sync_1c where tname='clientCard' and tid=?", array($this->id));
        }
    }

    public function isMainCard()
    {
        return (strrpos($this->client, '/') === false);
    }
}
