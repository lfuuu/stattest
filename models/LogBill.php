<?php

class LogBill extends ActiveRecord\Model
{
    static $table = "log_newbills";

    public function log($billNo, $comment)
    {
        global $user;

        $userId = $user ? $user->Get('id') : 0;

        $now = new ActiveRecord\DateTime();
        $now = $now->format("db");

        $logRecord = new LogBill();
        $logRecord->bill_no = $billNo;
        $logRecord->ts = $now;
        $logRecord->user_id = $userId;
        $logRecord->comment = $comment;
        $logRecord->save();
    }
}
