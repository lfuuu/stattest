<?php

namespace app\classes;

use Yii;
use app\models\ClientFile;

class FileManager
{
    private $accountId = null;

    public static function create($accountId)
    {
        return new self($accountId);
    }

    private function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    public function addFile($comment = "", $name = "")
    {
        global $db,$user;

        if (!isset($_FILES['file']) || !$_FILES['file']['tmp_name']) 
            return false;

        if (!$name) {
            $name = basename($_FILES['file']['name']);
        } else {
            if (!preg_match('/\.([^.]{2,5})$/',$name) && preg_match('/\.([^.]{2,5})$/',$_FILES['file']['name'],$m)) {
                $name.= $m[0];
            }
        }

        $file = new ClientFile;
        $file->client_id = $this->accountId;
        $file->ts = (new \DateTime())->format(\DateTime::ATOM);

        $file->name = $name;
        $file->comment = $comment;
        $file->user_id = Yii::$app->user->getId();

        $file->save();

        move_uploaded_file($_FILES['file']['tmp_name'], Yii::$app->params['STORE_PATH'].'files/'.$file->id);
    }

    public function addFileFromParam($name, $content, $comment = "", $userId = null)
    {
        if (!$name)
            throw new Exception("Не задано имя файла");

        if (!$userId)
        {
            $userId = Yii::$app->user->getId();
        }


        $file = new ClientFile;
        $file->client_id = $this->accountId;
        $file->ts = (new \DateTime())->format(\DateTime::ATOM);

        $file->name = $name;
        $file->comment = $comment;
        $file->user_id = $userId;

        $file->save();

        if(file_put_contents(Yii::$app->params['STORE_PATH'].'files/'.$file->id, $content) !== false)
        {
            return $file;
        } else {
            return false;
        }
    }

    public function removeFile($id)
    {
        $f = ClientFile::findOne(["client_id" => $this->accountId, "id" => $id]);
        if ($f)
        {
            $f->delete();

            $filePath = Yii::$app->params['STORE_PATH'].'files/'.$f->id;
            if (file_exists($filePath))
            {
                @unlink($filePath);
            }
        }
    }

    public function getContent(ClientFile $file)
    {
        $filePath = Yii::$app->params['STORE_PATH'].'files/'.$file->id;
        if (file_exists($filePath))
        {
            return file_get_contents($filePath);
        }
    }

    public function getMime(ClientFile $file)
    {
        $name = strtolower($file->name);

        $mime = "text/plain";
        $info = pathinfo($name);


        if (!isset($info["extension"]) || !$info["extension"])
            return $mime;

        $ext = $info["extension"];

        $mimes = [
            "doc"  => "application/msword",
            "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "pdf"  => "application/pdf",
            "gif"  => "image/gif",
            "tif"  => "image/tiff",
            "tiff" => "image/tiff",
            "jpeg" => "image/jpeg",
            "jpg"  => "image/jpeg",
            "jpe"  => "image/jpeg",
            "htm"  => "text/html",
            "html" => "text/html",
            "txt"  => "text/plain",
            "zip"  => "application/zip",
            "rar"  => "application/rar",
            "xls"  => "application/vnd.ms-excel",
            "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "ppt"  => "application/vnd.ms-powerpoint"
        ];

        if (isset($mimes[$ext]))
            return $mimes[$ext];

        return $mime;
    }
}
