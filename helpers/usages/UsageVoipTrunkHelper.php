<?php

namespace app\helpers\usages;

use yii\base\Object;
use app\models\usages\UsageInterface;
use yii\helpers\Url;

class UsageVoipTrunkHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

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
        return 'Телефония транки';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->usage->description ?: 'Описание отсутствует', '', ''];
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
        return Url::toRoute(['/usage/trunk/edit', 'id' => $this->usage->id]);
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