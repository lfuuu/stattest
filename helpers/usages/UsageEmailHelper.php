<?php

namespace app\helpers\usages;

use yii\base\Object;
use yii\helpers\Url;
use app\models\usages\UsageInterface;
use app\models\UsageEmails;
use app\models\ClientAccount;

class UsageEmailHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    private $usage;

    /**
     * @param UsageInterface $usage
     */
    public function __construct(UsageInterface $usage)
    {
        $this->usage = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'E-mail';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->usage->local_part . '@' . $this->usage->domain, '', ''];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute(['/pop_services.php', 'table' => UsageEmails::tableName(), 'id' => $this->usage->id]);
    }

    /**
     * @return UsageInterface
     */
    public function getTransferedFrom()
    {
        return UsageEmails::findOne($this->usage->prev_usage_id);
    }

    /**
     * @param ClientAccount $clientAccount
     */
    public function getAvailableUsagesToTransfer(ClientAccount $clientAccount)
    {

    }

}