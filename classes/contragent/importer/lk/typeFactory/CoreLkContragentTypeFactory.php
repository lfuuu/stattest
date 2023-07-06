<?php

namespace app\classes\contragent\importer\lk\typeFactory;


use app\classes\contragent\importer\lk\CoreLkContragent;
use app\classes\contragent\importer\lk\typeFactory\eu\CoreLkContragentTypeLegalEu;
use app\classes\contragent\importer\lk\typeFactory\eu\CoreLkContragentTypePersonEu;

class CoreLkContragentTypeFactory
{
    public static function getTransformer(CoreLkContragent $coreContragent)
    {
        /** @var CoreLkContragentTypeDefault $transformer */
        foreach (self::getTypes() as $transformer) {
            if ($transformer::$orgType == $coreContragent->getOrgType()) {
                return (new $transformer($coreContragent));
            }
        }

        return (new CoreLkContragentTypeDefault($coreContragent));
    }

    private static function getTypes()
    {
        if (\Yii::$app->isRus()) {
            return [
                CoreLkContragentTypePerson::class,
                CoreLkContragentTypeIp::class,
                CoreLkContragentTypeLegal::class,
            ];
        }

        return [
            CoreLkContragentTypePersonEu::class,
            CoreLkContragentTypeLegalEu::class,
        ];
    }
}

