<?php

namespace app\classes\transfer;

use app\models\ClientAccount;

/**
 * Класс переноса услуг типа "Виртуальная АТС"
 * @package app\classes\transfer
 */
class VirtpbxServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        $targetService = parent::process();

        LogTarifTransfer::process($this, $targetService->id);

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     */
    public function fallback()
    {
        LogTarifTransfer::fallback($this);

        parent::fallback();
    }

}