<?php
namespace app\classes\grid\account;

use app\classes\Assert;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus as BPS;


abstract class AccountGrid implements AccountGridInterface
{
    // Инджект связанных отчетов
    const BUSINESS_MAPPING_INJECT = 0;
    // Реджект связанных отчетов
    const BUSINESS_MAPPING_REJECT = 1;

    // Карта замен отчетов, используемая в цикле при обработке запрашиваемых статусов
    public static $BUSINESS_CYCLE_MAPPING = [
        BusinessProcess::TELECOM_MAINTENANCE => [
            // Инджект связанного отчета
            [
                BPS::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES => '\telecom\maintenance\OrderServiceFolder',
                BPS::TELEKOM_MAINTENANCE_WORK => '\telecom\maintenance\WorkFolder',
                BPS::TELEKOM_MAINTENANCE_TRASH => '\telecom\maintenance\TrashFolder',
            ],
            // Реджект связанного отчета
            [
                BPS::TELEKOM_MAINTENANCE_EXCEPTION_FROM_BOOK_OF_PROD,
            ],

        ],
    ];
    // Карта добавочных отчетов, внедряемых независимо от основного цикла карты замен
    public static $BUSINESS_EXTRA_MAPPING = [
        BusinessProcess::TELECOM_MAINTENANCE => [
            '\telecom\maintenance\DisconnectedDebtFolder',
            '\telecom\maintenance\AutoBlockCreditFolder',
            '\telecom\maintenance\AutoBlockDayLimitFolder',
            '\telecom\maintenance\BlockBillPayOverdueFolder',
            '\telecom\maintenance\AutoBlock800Folder',
            '\telecom\maintenance\AutoBlockFolder',
            '\telecom\maintenance\AutoBlockInternetFolder',
        ],
        BusinessProcess::TELECOM_MAINTENANCE_B2C => [
            '\telecom\maintenance\b2c\DisconnectedDebtFolder',
            '\telecom\maintenance\b2c\AutoBlockCreditFolder',
            '\telecom\maintenance\b2c\AutoBlockDayLimitFolder',
            '\telecom\maintenance\b2c\BlockBillPayOverdueFolder',
            '\telecom\maintenance\b2c\AutoBlock800Folder',
            '\telecom\maintenance\b2c\AutoBlockFolder',
            '\telecom\maintenance\b2c\AutoBlockInternetFolder',
        ],
        BusinessProcess::OTT_MAINTENANCE => [
            '\ott\maintenance\DisconnectedDebtFolder',
            '\ott\maintenance\AutoBlockCreditFolder',
            '\ott\maintenance\AutoBlockDayLimitFolder',
            '\ott\maintenance\AutoBlock800Folder',
        ],

        BusinessProcess::OPERATOR_CLIENTS => [
            '\operator\clients\AutoBlockedFolder',
        ]


    ];

    protected function getDefaultFolder()
    {
        return $this->getFolders()[0];
    }

    /**
     * @return string
     */
    public function getBusinessTitle()
    {
        /** @var Business $businessTitle */
        $businessTitle = Business::findOne($this->getBusiness());

        return $businessTitle->name;
    }

    /**
     * @return string
     */
    public function getBusinessProcessTitle()
    {
        /** @var BusinessProcess $businessProcessTitle */
        $businessProcessTitle = BusinessProcess::findOne($this->getBusinessProcessId());

        return $businessProcessTitle->name;
    }

    public function getFolder($folderId)
    {
        //Get Default ...
        if ($folderId === null) {
            return $this->getDefaultFolder();
        }

        foreach ($this->getFolders() as $folder) {
            if ($folderId == $folder->getId()) {
                return $folder;
            }
        }

        Assert::isUnreachable('Acount grid folder not found');
    }

    /**
     * @param int $businessProcessId
     * @return bool
     */
    public function isCurrentReport($businessProcessId)
    {
        return $this->getBusinessProcessId() === $businessProcessId;
    }
}