<?php
namespace app\models\tariffication\type;

class ServiceType
{
    /**
     * @return AbstractServiceType
     */
    public static function getById($serviceTypeId)
    {
        $className = 'app\models\tariffication\ServiceType' . $serviceTypeId;
        return new $className;
    }
}