<?php

namespace app\classes\actions\message;

use app\classes\Singleton;
use app\classes\Assert;

class SendActionFactory extends Singleton
{

    public function get($actionCode)
    {
        foreach ($this->getActions() as $action) {
            if ($action->code == $actionCode) {
                return $action;
            }
        }
        Assert::isUnreachable('Event not found: ' . $actionCode);
    }

    public function getSendEmail()
    {
        return new SendEmailAction;
    }

    public function getSendSms()
    {
        return new SendSmsAction;
    }

    public function getSendEmailSms()
    {
        return new SendEmailSmsAction;
    }

    public function getActions()
    {
        return [
            $this->getSendEmail(),
            $this->getSendSms(),
            $this->getSendEmailSms(),
        ];
    }

}