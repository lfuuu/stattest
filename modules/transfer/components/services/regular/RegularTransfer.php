<?php

namespace app\modules\transfer\components\services\regular;

use app\models\ClientAccount;
use app\modules\transfer\components\services\Processor;
use app\modules\transfer\forms\services\RegularForm;

/**
 * @property-read RegularForm $form
 * @property-read array $services
 */
class RegularTransfer extends Processor
{

    /**
     * @param ClientAccount $clientAccount
     * @return RegularForm
     */
    public function getForm(ClientAccount $clientAccount)
    {
        return new RegularForm([
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
            parent::SERVICE_TRUNK => TrunkServiceTransfer::class,
            parent::SERVICE_WELLTIME_SAAS => WelltimeServiceTransfer::class,
            parent::SERVICE_EXTRA => ExtraServiceTransfer::class,
            parent::SERVICE_EMAIL => EmailServiceTransfer::class,
            parent::SERVICE_INTERNET => IpPortsServiceTransfer::class,

            parent::SERVICE_PACKAGE => PackageServiceTransfer::class,
            parent::SERVICE_INTERNET_ROUTES => IpRoutesServiceTransfer::class,
            parent::SERVICE_INTERNET_DEVICES => TechCpeServiceTransfer::class,
        ];
    }

}
