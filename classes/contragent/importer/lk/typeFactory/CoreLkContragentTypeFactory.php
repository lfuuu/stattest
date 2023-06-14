<?php

namespace app\classes\contragent\importer\lk\typeFactory;


use app\classes\contragent\importer\lk\CoreLkContragent;

class CoreLkContragentTypeFactory
{
    public static function getTransformer(CoreLkContragent $coreContragent)
    {
        /** @var CoreLkContragentTypeDefault $transformer */
        foreach ([
                     CoreLkContragentTypePerson::class,
                     CoreLkContragentTypeIp::class,
                     CoreLkContragentTypeLegal::class,
                 ] as $transformer) {
            if ($transformer::$orgType == $coreContragent->getOrgType()) {
                return (new $transformer($coreContragent));
            }
        }

        return (new CoreLkContragentTypeDefault($coreContragent));
    }
}

