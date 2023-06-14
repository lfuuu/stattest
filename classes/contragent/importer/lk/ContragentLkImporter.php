<?php

namespace app\classes\contragent\importer\lk;

class ContragentLkImporter
{
    public function run($contragentId = null)
    {
        /** @var CoreLkContragent $obj */
        foreach (DataLoader::getObjectsForSync($contragentId) as $obj) {
            $obj
                ->getTransformatorByType()
                ->update();
        }
    }
}

