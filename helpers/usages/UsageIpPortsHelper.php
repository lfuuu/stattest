<?php

namespace app\helpers\usages;

use yii\base\Object;
use yii\helpers\Url;
use app\models\usages\UsageInterface;
use app\models\UsageIpPorts;

class UsageIpPortsHelper extends Object implements UsageHelperInterface
{

    private $usage;

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
        return 'Интернет';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->usage->address, '', ''];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getEditLink()
    {
        return Url::toRoute(['/pop_services.php', 'table' => UsageIpPorts::tableName(), 'id' => $this->usage->id]);
    }

}