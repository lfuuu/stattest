<?php

namespace app\helpers;


use app\classes\Singleton;

class Semaphore extends Singleton
{
    const ID_UU_CALCULATOR = 555;

    private $_storage = [];

    public function acquire($semId, $isWait = true)
    {
        $resource = sem_get($semId);

        if (!$resource) {
            \Yii::error('Error get semaphore by id:' . $semId);
            return true; // emulate acquire semaphore
        }

        $this->_storage[$semId] = $resource;

        return sem_acquire($resource, !$isWait);
    }

    public function release($semId)
    {
        if (!isset($this->_storage[$semId])) {
            return true;
        }

        $result = sem_release($this->_storage[$semId]);

        if ($result) {
            unset($this->_storage[$semId]);
        }

        return $result;
    }

    public function __destruct()
    {
        foreach ($this->_storage as $semResource) {
            $semResource && sem_release($semResource);
        }
    }
}