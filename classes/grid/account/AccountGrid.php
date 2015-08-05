<?php
namespace app\classes\grid\account;

use app\classes\Assert;
use Yii;


abstract class AccountGrid implements AccountGridInterface
{
    public function getFolder($folderId)
    {
        if ($folderId === null) {
            return $this->getFolders()[0];
        }

        foreach ($this->getFolders() as $folder) {
            if ($folderId == $folder->getId()) {
                return $folder;
            }
        }

        Assert::isUnreachable('Acount grid folder not found');
    }
}