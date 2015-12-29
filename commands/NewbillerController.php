<?php

namespace app\commands;

use Yii;
use \DateTime;
use yii\console\Controller;

class NewbillerController extends Controller
{

    public $from;
    public $to;
    public $hour;

    public function init()
    {
        parent::init();

        $this->from = new DateTime();
        $this->to = clone($this->from);
        $this->hour = intval($this->from->format('H'));
    }

    public function actionExec()
    {
        Yii::info("Start...");
        $log = new AccountLogComposite([
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
            'hour' => $this->hour,
        ]);

        try {
            $log->build();
        } catch (\Exception $e) {
            Yii::error('Error..');
            Yii::error($e);
            return 1;
        }


        Yii::info("End");
    }

}
