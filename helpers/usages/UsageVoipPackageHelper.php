<?php

namespace app\helpers\usages;

use app\models\UsageVoipPackage;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\Url;
use app\models\usages\UsageInterface;

class UsageVoipPackageHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageVoipPackage  */
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
        return 'Телефония пакет';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->_usage->tariff->name, '', ''];
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
        return Url::toRoute(['/usage/voip/edit-package', 'id' => $this->_usage->id]);
    }

    /**
     * @return null
     */
    public function getTransferedFrom()
    {
        return null;
    }

}