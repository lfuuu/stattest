<?php

namespace app\helpers\usages;

use app\models\UsageTrunk;
use yii\base\InvalidParamException;
use yii\base\Object;
use app\models\usages\UsageInterface;
use yii\helpers\Url;

class UsageVoipTrunkHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageTrunk */
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
        return 'Телефония транки';
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
        return [$this->_usage->description ?: 'Описание отсутствует', '', ''];
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
        return Url::toRoute(['/usage/trunk/edit', 'id' => $this->_usage->id]);
    }

    /**
     * @return null
     */
    public function getTransferedFrom()
    {
        return null;
    }

    /**
     * Получение полей для связи с лицевым счетом
     * Поле в услуге => Поле в лицевом счете
     *
     * @return array
     */
    public function getFieldsForClientAccountLink()
    {
        // Поле в услуге, Поле в лицевом счете
        return ['client_account_id', 'id'];
    }

}