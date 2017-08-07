<?php

namespace app\modules\transfer;

use app\models\ClientAccount;
use app\modules\transfer\components\services\Processor;
use app\modules\transfer\components\services\regular\RegularTransfer;
use app\modules\transfer\components\services\universal\UniversalTransfer;
use yii\base\InvalidCallException;

class Module extends \yii\base\Module
{

    /** @var string */
    public $controllerNamespace = 'app\modules\transfer\controllers';

    /** @var array */
    private $_serviceProcessorVersions = [
        ClientAccount::VERSION_BILLER_USAGE => RegularTransfer::class,
        ClientAccount::VERSION_BILLER_UNIVERSAL => UniversalTransfer::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @param int $clientAccountVersion
     * @return Processor
     * @throws InvalidCallException
     */
    public function getServiceProcessor($clientAccountVersion)
    {
        if (!array_key_exists($clientAccountVersion, $this->_serviceProcessorVersions)) {
            throw new InvalidCallException('Unknown service transfer processor');
        }

        return new $this->_serviceProcessorVersions[$clientAccountVersion];
    }

}