<?php

class LogBill extends ActiveRecord\Model
{
    static $table = "log_newbills";

    public static function log($billNo, $comment, $isUserAutoLK = false)
    {
        global $user;

        if (!$isUserAutoLK)
        {
            $userId = $user ? $user->Get('id') : 0;
        } else {
            $options = array();
            $options['select'] = 'id';
            $options['conditions'] = array('user = ?', 'AutoLK');
            $db_user = User::first($options);
            $userId = $db_user->id;
        }

        $now = new ActiveRecord\DateTime();
        $now = $now->format("db");

        $logRecord = new LogBill();
        $logRecord->bill_no = $billNo;
        $logRecord->ts = $now;
        $logRecord->user_id = $userId;
        $logRecord->comment = $comment;
        $logRecord->save();
    }

    public static function getLog($billNo)
    {
        $options = array();
        $options['select'] = 'log_newbills.*, user_users.user';
        $options['conditions'] = array('bill_no = ?', $billNo);
        $options['joins'] = array(
            'LEFT JOIN user_users ON user_users.id = log_newbills.user_id',
        );
        $options['order'] = 'ts DESC';
        
        $result = LogBill::find('all', $options);
        $ret = array();
        foreach ($result as $record)
        {
            $ret[] = $record->to_array();
        }
        return $ret;
    }
}
