<?php

namespace app\modules\transfer\components\services\universal;

use app\models\ClientAccount;
use app\modules\transfer\components\services\Processor;
use app\modules\transfer\forms\services\UniversalForm;

/**
 * @property-read UniversalForm $form
 * @property-read array $services
 */
class UniversalTransfer extends Processor
{

    /**
     * @param ClientAccount $clientAccount
     * @return UniversalForm
     */
    public function getForm(ClientAccount $clientAccount)
    {
        return new UniversalForm([
            'processor' => $this,
            'clientAccount' => $clientAccount,
        ]);
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return [
            parent::SERVICE_VOIP => VoipServiceTransfer::class,
            parent::SERVICE_VPBX => VirtpbxServiceTransfer::class,
            parent::SERVICE_CALL_CHAT => CallChatServiceTransfer::class,
//            parent::SERVICE_TRUNK => TrunkServiceTransfer::class,
            parent::SERVICE_WELLTIME_SAAS => WelltimeServiceTransfer::class,
            parent::SERVICE_EXTRA => ExtraServiceTransfer::class,

            parent::SERVICE_PACKAGE => PackageServiceTransfer::class,
        ];
    }

}