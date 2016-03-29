<?php
namespace app\classes\grid\account;

use Yii;
use app\classes\Assert;
use app\models\Business;
use app\models\BusinessProcess;


abstract class AccountGrid implements AccountGridInterface
{

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
}