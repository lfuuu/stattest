<?php

namespace app\classes\media;

use Yii;
use yii\db\ActiveRecord;

abstract class MediaManager
{
    public $mimesTypes = [
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  => 'application/pdf',
        'gif'  => 'image/gif',
        'tif'  => 'image/tiff',
        'tiff' => 'image/tiff',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'txt'  => 'text/plain',
        'zip'  => 'application/zip',
        'rar'  => 'application/rar',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
    ];

    public $downloadable = [
        'doc', 'docx', 'pdf', 'zip', 'rar', 'xls', 'xlsx', 'ppt', 'txt',
    ];

    /**
     * @return ActiveRecord
     */
    protected abstract function createFileModel($name, $comment);

    protected abstract function deleteFileModel(ActiveRecord $file);

    protected abstract function getFileModels();

    protected abstract function getFolder();

    public function addFiles($fileField = '', $names = '') {
        if (isset($_FILES[$fileField])) {
            $files = (array) $_FILES[$fileField];
            $names = get_param_raw($names, false);
            for ($i=0, $s=sizeof($files['name']); $i<$s; $i++) {
                if (!$files['size'][$i]) {
                    continue;
                }

                $this->addFile(
                    [
                        'tmp_name' => $files['tmp_name'][$i],
                        'name' => $files['name'][$i],
                    ],
                    '',
                    (isset($names[$i]) ? $names[$i] : '')
                );
            }
        }
    }

    public function addFile(array $file, $comment = '', $name = '')
    {
        if (!file_exists($file['tmp_name']) || !is_file($file['tmp_name']))
            return false;

        if (!$name) {
            $name = $file['name'];
        } else {
            if (!preg_match('/\.([^\.]{2,5})$/', $name) && preg_match('/\.([^\.]{2,5})$/', $file['name'], $m)) {
                $name .= $m[0];
            }
        }

        $model = $this->createFileModel($name, $comment);
        move_uploaded_file($file['tmp_name'], $this->getFilePath($model));
    }

    public function removeFile(ActiveRecord $fileModel)
    {
        $this->deleteFileModel($fileModel);

        $filePath = $this->getFilePath($fileModel);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    public function getFiles()
    {
        $files = $this->getFileModels();

        $result = [];
        foreach ($files as $file) {
            $result[] = $this->getFile($file);
        }

        return $result;
    }

    public function getFile($file, $with_content = 0)
    {
        $fileData = [
            'id' => $file->id,
            'ext' => $this->getMime($file)[0],
            'mimeType' => $this->getMime($file)[1],
            'size' => $this->getSize($file),
            'name' => $file->name,
            'comment' => $file->comment,
            'author' => $file->user_id,
            'created' => $file->ts,
        ];

        if ($with_content) {
            $fileData['content'] = file_get_contents($this->getFilePath($file));
        }

        return $fileData;
    }

    public function getContent($file)
    {
        $filePath = $this->getFilePath($file);

        if (file_exists($filePath)) {
            $fileData = $this->getFile($file);

            if (in_array($fileData['ext'], $this->downloadable)) {
                Yii::$app->response->sendContentAsFile(file_get_contents($filePath), $fileData['name']);
                Yii::$app->end();
            }
            else {
                Header('Content-Type: ' . $fileData['mimeType']);
                echo file_get_contents($filePath);
                Yii::$app->end();
            }
        }

        throw new \yii\web\HttpException(404, 'Файл не найден');
    }

    protected function getSize($file)
    {
        $filePath = $this->getFilePath($file);

        if (file_exists($filePath)) {
            return filesize($filePath);
        }
    }

    protected function getMime($file)
    {
        $name = strtolower($file->name);

        $mime = 'text/plain';
        $info = pathinfo($name);

        if (!isset($info['extension']) || !$info['extension'])
            return ['txt', $mime];

        $ext = $info['extension'];

        return [$ext, isset($this->mimesTypes[$ext]) ? $this->mimesTypes[$ext] : $mime];
    }

    protected function getFilePath(ActiveRecord $fileModel)
    {
        return implode('/', [Yii::$app->params['STORE_PATH'], static::getFolder(), $fileModel->id]);
    }
}