<?php

namespace app\helpers\usages;

use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\Url;
use app\models\usages\UsageInterface;
use app\models\UsageEmails;
use app\models\ClientAccount;

class UsageEmailHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageEmails */
    private $_usage;

    /**
     * @param UsageInterface $usage
     */
    public function __construct(UsageInterface $usage)
    {
        $this->_usage = $usage;
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
     * @return string
     */
    public function getValue()
    {
        return $this->_usage->local_part . '@' . $this->_usage->domain;
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->getValue(), '', ''];
    }

    /**
     * @return array
     */
    public function getExtendsData()
    {
        return [];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getEditLink()
    {
        return Url::toRoute(['/pop_services.php', 'table' => UsageEmails::tableName(), 'id' => $this->_usage->id]);
    }

    /**
     * @return UsageInterface
     */
    public function getTransferedFrom()
    {
        return UsageEmails::findOne($this->_usage->prev_usage_id);
    }

    /**
     * @param ClientAccount $clientAccount
     */
    public function getAvailableUsagesToTransfer(ClientAccount $clientAccount)
    {

    }

}