<?php

namespace app\classes\media;

use Yii;
use yii\db\ActiveRecord;

abstract class MediaManager
{

    public $mimesTypes = [
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf' => 'application/pdf',
        'gif' => 'image/gif',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'png' => 'image/png',
        'htm' => 'text/html',
        'html' => 'text/html',
        'txt' => 'text/plain',
        'zip' => 'application/zip',
        'rar' => 'application/rar',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
    ];

    public $downloadable = [
        'doc',
        'docx',
        'pdf',
        'zip',
        'rar',
        'xls',
        'xlsx',
        'ppt',
        'txt',
        'html',
    ];

    /**
     * @return ActiveRecord
     */
    protected abstract function createFileModel($name, $comment);

    /**
     * @param ActiveRecord $fileModel
     * @return mixed
     */
    protected abstract function deleteFileModel(ActiveRecord $fileModel);

    /**
     * @return ActiveRecord[]
     */
    protected abstract function getFileModels();

    /**
     * @return string
     */
    protected abstract function getFolder();

    /**
     * @param string $fileField
     * @param string $names
     */
    public function addFiles($fileField = '', $names = '')
    {
        if (isset($_FILES[$fileField])) {
            $files = (array)$_FILES[$fileField];
            $names = Yii::$app->request->post('names', []);
            for ($i = 0, $s = count($files['name']); $i < $s; $i++) {
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

    /**
     * @param array $file - as $_FILES format
     * @param string $comment
     * @param string $name
     * @return string
     */
    public function addFile(array $file, $comment = '', $name = '')
    {
        if (!file_exists($file['tmp_name']) || !is_file($file['tmp_name'])) {
            return false;
        }

        if (!$name) {
            $name = $file['name'];
        } else {
            if (!preg_match('/\.([^\.]{2,5})$/', $name) && preg_match('/\.([^\.]{2,5})$/', $file['name'], $m)) {
                $name .= $m[0];
            }
        }

        $model = $this->createFileModel($name, $comment);
        move_uploaded_file($file['tmp_name'], $this->getFilePath($model));

        return $name;
    }

    /**
     * @param ActiveRecord $fileModel
     */
    public function removeFile(ActiveRecord $fileModel)
    {
        $this->deleteFileModel($fileModel);

        $filePath = $this->getFilePath($fileModel);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        $files = $this->getFileModels();

        $result = [];
        foreach ($files as $file) {
            $result[] = $this->getFile($file);
        }

        return $result;
    }

    /**
     * @param ActiveRecord $fileModel
     * @param int $withContent
     * @return array
     */
    public function getFile(ActiveRecord $fileModel, $withContent = 0)
    {
        $fileData = [
            'id' => $fileModel->id,
            'ext' => $this->getMime($fileModel)[0],
            'mimeType' => $this->getMime($fileModel)[1],
            'size' => $this->getSize($fileModel),
            'name' => $fileModel->name,
            'comment' => $fileModel->comment,
            'author' => $fileModel->user_id,
            'created' => $fileModel->ts,
        ];

        if ($withContent) {
            $fileData['content'] = file_get_contents($this->getFilePath($fileModel));
        }

        return $fileData;
    }

    /**
     * @param ActiveRecord $fileModel
     * @param boolean $isDownload
     * @throws \yii\base\ExitException
     * @throws \yii\web\HttpException
     */
    public function getContent(ActiveRecord $fileModel, $isDownload = false)
    {
        $filePath = $this->getFilePath($fileModel);

        if (file_exists($filePath)) {
            $fileData = $this->getFile($fileModel);

            if ($isDownload && in_array($fileData['ext'], $this->downloadable, true)) {
                Yii::$app->response->sendContentAsFile(file_get_contents($filePath), $fileData['name']);
                Yii::$app->end();
            } else {
                Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
                Header('Content-Type:' . $fileData['mimeType']);
                echo file_get_contents($filePath);
                Yii::$app->end();
            }
        }

        throw new \yii\web\HttpException(404, 'Файл не найден');
    }

    /**
     * @param ActiveRecord $fileModel
     * @return int|boolean
     */
    protected function getSize(ActiveRecord $fileModel)
    {
        $filePath = $this->getFilePath($fileModel);

        if (file_exists($filePath)) {
            return filesize($filePath);
        }

        return false;
    }

    /**
     * @param ActiveRecord|\stdClass $file
     * @return array
     */
    protected function getMime($fileModel)
    {
        $name = strtolower($fileModel->name);

        $mime = 'text/plain';
        $info = pathinfo($name);

        if (!isset($info['extension']) || !$info['extension']) {
            return ['txt', $mime];
        }

        $ext = $info['extension'];

        return [$ext, isset($this->mimesTypes[$ext]) ? $this->mimesTypes[$ext] : $mime];
    }

    /**
     * @param ActiveRecord $fileModel
     * @return string
     */
    protected function getFilePath(ActiveRecord $fileModel)
    {
        return implode('/', [Yii::$app->params['STORE_PATH'], static::getFolder(), $fileModel->id]);
    }
}
