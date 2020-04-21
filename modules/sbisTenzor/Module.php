<?php

namespace app\modules\sbisTenzor;

use app\classes\Navigation;
use app\classes\NavigationBlock;
use app\modules\sbisTenzor\models\SBISOrganization;
use Yii;
use yii\base\InvalidConfigException;

/**
 * sbisTenzor Модуль взаимодействия с системой СБИС компании Тензор
 */
class Module extends \yii\base\Module
{
    protected $organizations = [];
    public $isEnabled;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\sbisTenzor\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\sbisTenzor\commands';
        }

        // подключить конфиги
        $fileParams = require __DIR__ . '/config/params.php';
        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            $fileParams = require $localConfigFileName;
        }
        Yii::configure($this, $fileParams);

        $params = [];
        foreach ($this->getOrganizations() as $id => $sbisOrganization) {
            $extraParams = !empty($this->params[$sbisOrganization->organization_id]) ? $this->params[$sbisOrganization->organization_id] : [];
            Yii::configure($sbisOrganization, $extraParams);
            $params[$id] = $sbisOrganization;
        }

        $this->params = $params;
    }

    /**
     * @param Navigation $navigation
     */
    public function getNavigation(Navigation $navigation)
    {
        $navigation->addBlock(
            NavigationBlock::create()
                ->setId('sbisTenzor')
                ->setTitle('СБИС')
                ->addItem('Общий список пакетов документов', ['/sbisTenzor/document'])
                ->addItem('Список сгенерированных черновиков', ['/sbisTenzor/draft'])
                ->addItem('Список групп обмена', ['/sbisTenzor/group'])
                ->addItem('Список форм документов', ['/sbisTenzor/form'])
                ->addItem('Статусы интеграции клиентов', ['/sbisTenzor/contractor/'])
                ->addItem('Роуминг', ['/sbisTenzor/contractor/roaming'])
        );
    }

    /**
     * @return array|SBISOrganization[]
     */
    protected function getOrganizations()
    {
        if (!$this->organizations) {
            $this->organizations = SBISOrganization::find()
                ->where(['is_active' => true])
                ->indexBy('id')
                ->all();
        }

        return $this->organizations;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function getParams()
    {
        if (!$this->isEnabled) {
            throw new InvalidConfigException('Функционал взаимодействия со СБИС отключён');
        }

        return $this->params;
    }
}
