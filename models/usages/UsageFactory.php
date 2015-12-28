<?php
namespace app\models\usages;

use app\classes\Assert;

use app\models\UsageEmails;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageTechCpe;
use app\models\UsageTrunk;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\models\UsageWelltime;

use app\forms\usage\UsageVoipEditForm;
use app\forms\usage\UsageVirtpbxForm;
use app\forms\usage\UsageExtraForm;
use app\forms\usage\UsageEmailsForm;
use app\forms\usage\UsageTechCpeForm;
use app\forms\usage\UsageSmsForm;
use app\forms\usage\UsageIpPortsForm;
use app\forms\usage\UsageWelltimeForm;
use app\forms\usage\UsageVoipAddPackageForm;
use app\forms\usage\UsageTrunkEditForm;

abstract class UsageFactory
{

    const USAGE_VOIP = 'usage_voip';
    const USAGE_VIRTBPX = 'usage_virtpbx';
    const USAGE_EXTRA = 'usage_extra';
    const USAGE_EMAIL = 'usage_email';
    const USAGE_TECH_CPE = 'usage_tech_cpe';
    const USAGE_SMS = 'usage_sms';
    const USAGE_IP_PORTS = 'usage_ip_ports';
    const USAGE_WELLTIME = 'usage_welltime';
    const USAGE_VOIP_PACKAGE = 'usage_voip_package';
    const USAGE_TRUNK = 'usage_trunk';

    public static $usage = [
        self::USAGE_VOIP => UsageVoip::class,
        self::USAGE_VIRTBPX => UsageVirtpbx::class,
        self::USAGE_EXTRA => UsageExtra::class,
        self::USAGE_EMAIL => UsageEmails::class,
        self::USAGE_TECH_CPE => UsageTechCpe::class,
        self::USAGE_SMS => UsageSms::class,
        self::USAGE_IP_PORTS => UsageIpPorts::class,
        self::USAGE_WELLTIME => UsageWelltime::class,
        self::USAGE_VOIP_PACKAGE => UsageVoipPackage::class,
        self::USAGE_TRUNK => UsageTrunk::class,
    ];

    public static $usageForms = [
        self::USAGE_VOIP => UsageVoipEditForm::class,
        self::USAGE_VIRTBPX => UsageVirtpbxForm::class,
        self::USAGE_EXTRA => UsageExtraForm::class,
        self::USAGE_EMAIL => UsageEmailsForm::class,
        self::USAGE_TECH_CPE => UsageTechCpeForm::class,
        self::USAGE_SMS => UsageSmsForm::class,
        self::USAGE_IP_PORTS => UsageIpPortsForm::class,
        self::USAGE_WELLTIME => UsageWelltimeForm::class,
        self::USAGE_VOIP_PACKAGE => UsageVoipAddPackageForm::class,
        self::USAGE_TRUNK => UsageTrunkEditForm::class,
    ];

    public static function getUsage($usage)
    {
        if (array_key_exists($usage, self::$usage)) {
            return new self::$usage[$usage];
        }

        Assert::isUnreachable('Usage "' . $usage . '" not found');
    }

    public static function getUsageForm($usage)
    {
        if (array_key_exists($usage, self::$usageForms)) {
            return new self::$usageForms[$usage];
        }

        Assert::isUnreachable('Usage form "' . $usage . '" not found');
    }

}