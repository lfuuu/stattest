<?php
namespace app\classes\grid\account;

use app\classes\Assert;
use Yii;


abstract class AccountGrid implements AccountGridInterface
{
    protected function getDefaultFolder()
    {
        return $this->getFolders()[0];
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