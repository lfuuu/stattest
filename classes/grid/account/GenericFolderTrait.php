<?php

namespace app\classes\grid\account;

use app\classes\grid\account\operator\operators\GenericFolder;
use app\models\BusinessProcessStatus;

trait GenericFolderTrait
{
    /**
     * @param $statuses
     * @return AccountGridFolder[] $folders
     */
    public function getGenericFolders($statuses)
    {
        $folders = [];

        foreach ($statuses as $status) {
            /** @var BusinessProcessStatus $status */
            $folder = GenericFolder::create($this);
            $folder->initialize($status, $this->getColumns());
            $folders[] = $folder;
        }

        return $folders;
    }

    /**
     * @return AccountGridFolder[]
     */
    public function getFolders()
    {
        $statuses = BusinessProcessStatus::find()
            ->where(['business_process_id' => $this->getBusinessProcessId()])
            ->all();

        return $this->getGenericFolders($statuses);
    }
}