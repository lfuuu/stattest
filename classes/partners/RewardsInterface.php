<?php

namespace app\classes\partners;

interface RewardsInterface
{

    /**
     * @return array
     */
    public function getAvailableRewards();

    /**
     * @param int $serviceId
     * @return mixed
     */
    public function getService($serviceId);

    /**
     * @param int $serviceId
     * @return bool
     */
    public function isExcludeService($serviceId);

}