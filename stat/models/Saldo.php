<?php
class Saldo extends ActiveRecord\Model
{
    static $table_name = 'newsaldo';

    public function getLastSaldo($clientId)
    {
        return self::first(array(
                    'select' => 'ts as date, saldo',
                    'conditions' => array('client_id = ?', $clientId),
                    'order' => 'ts desc, id desc',
                    'limit' => 1
                    )
            );
    }
}

