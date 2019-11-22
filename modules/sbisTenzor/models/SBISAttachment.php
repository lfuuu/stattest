<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use app\modules\sbisTenzor\helpers\SBISUtils;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Вложение из пакета документов в системе СБИС
 *
 * @property integer $id
 * @property integer $sbis_document_id
 * @property integer $number
 * @property string $external_id
 * @property string $extension
 * @property string $file_name
 * @property string $stored_path
 * @property string $hash
 * @property string $signature_stored_path
 * @property integer $is_sign_needed
 * @property integer $is_signed
 * @property string $link
 * @property string $url_online
 * @property string $url_html
 * @property string $url_pdf
 * @property string $created_at
 * @property string $updated_at
 * @property string $signed_at
 *
 * @property-read SBISDocument $document
 */
class SBISAttachment extends ActiveRecord
{
    const STORE_PATH = 'files/external/sbis';
    const SUB_DIR_SIGNATURES = 'signatures';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sbis_document_id', 'external_id', 'number', 'is_sign_needed', 'is_signed'], 'required'],
            [['sbis_document_id'], 'integer'],
            [['created_at', 'updated_at', 'signed_at', 'number'], 'safe'],
            [['external_id'], 'string', 'max' => 36],
            [['extension'], 'string', 'max' => 10],
            [['file_name', 'hash', 'url_online'], 'string', 'max' => 255],
            [['stored_path', 'signature_stored_path', 'link'], 'string', 'max' => 512],
            [['url_html', 'url_pdf'], 'string', 'max' => 2048],
            [['sbis_document_id', 'number'], 'unique', 'targetAttribute' => ['sbis_document_id', 'number'], 'message' => 'The combination of Sbis Document ID and Number has already been taken.'],
            [['sbis_document_id'], 'exist', 'skipOnError' => true, 'targetClass' => SbisDocument::class, 'targetAttribute' => ['sbis_document_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sbis_document_id' => 'Пакет документов',
            'external_id' => 'External ID',
            'number' => 'Порядковый номер в пакете',
            'extension' => 'Extension',
            'file_name' => 'Имя файла',
            'stored_path' => 'Полный путь в файлу',
            'hash' => 'Хэш',
            'signature_stored_path' => 'Signature stored path',
            'is_sign_needed' => 'Is sign needed',
            'is_signed' => 'Is signed',
            'link' => 'Link',
            'url_online' => 'Url online',
            'url_html' => 'Url Html',
            'url_pdf' => 'Url Pdf',
            'created_at' => 'Создан',
            'updated_at' => 'Последнее обновление',
            'signed_at' => 'Signed at',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(SBISDocument::class, ['id' => 'sbis_document_id']);
    }

    /**
     * Получить полный путь для сохранения подписи к вложению
     *
     * @return string
     */
    public function getSignatureFileName()
    {
        if (!$this->is_sign_needed) {
            return '';
        }

        $fileName = $this->stored_path;
        $signatureFileName = $this->signature_stored_path;
        if ((!$signatureFileName || !file_exists($signatureFileName)) && file_exists($fileName)) {
            $dirPath =
                dirname($fileName) .
                DIRECTORY_SEPARATOR .
                self::SUB_DIR_SIGNATURES;

            SBISUtils::checkDirectory($dirPath);

            $signatureFileName =
                $dirPath .
                    DIRECTORY_SEPARATOR .
                    basename($fileName) . '.sgn';
        }

        return $signatureFileName;
    }

    /**
     * Получить путь к хранилищу
     *
     * @return string
     */
    public static function getBasePath()
    {
        return Yii::$app->params['STORE_PATH'] . self::STORE_PATH;
    }

    /**
     * Получить путь для сохранения вложения
     *
     * @param string $dirSuffix
     * @return string
     * @throws \Exception
     */
    public function getPath($dirSuffix = '')
    {
        $organizationFrom = $this->document->sbisOrganization->organization->firma;
        $clientTo = $this->document->clientAccount->client;
        $date = $this->document->date ? (new \DateTime($this->document->date)) : (new \DateTime());
        $dateStr = $date->format('Y-m');

        $day = $this->document->date ? (new \DateTime($this->document->date)) : (new \DateTime());
        $dayStr = $day->format('Ymd');
        $folderName = $dayStr. '-' . $this->document->external_id;
        if ($dirSuffix) {
            $folderName .= '-' . $dirSuffix;
        }

        $dirData = [$organizationFrom, $clientTo, $dateStr, $folderName];

        $path = self::getBasePath();
        foreach ($dirData as $p) {
            $path .= DIRECTORY_SEPARATOR . $p;
            SBISUtils::checkDirectory($path);
        }

        return $path . DIRECTORY_SEPARATOR;
    }

    /**
     * Получить полный путь для сохранения вложения
     *
     * @param string $fileName
     * @param string $dirSuffix
     * @return string
     * @throws \Exception
     */
    public function getStoredPath($fileName = '', $dirSuffix = '')
    {
        $info = pathinfo($this->file_name);
        $fileName = $fileName ? : sprintf('%02d.%s', $this->number, $info['extension']);

        return $this->getPath($dirSuffix) . $fileName;
    }

    /**
     * Подписать вложение
     *
     * @param string $command Команда для подписи
     * @return bool
     */
    public function sign($command)
    {
        if ($this->is_signed) {
            return true;
        }
        $signatureFileName = $this->getSignatureFileName();
        if (!$signatureFileName) {
            Yii::error(sprintf("SBIS signature file not created, attachment #%s", $this->id), SBISDocument::LOG_CATEGORY);
            return false;
        }

        $command = strtr(
            $command,
            [
                '{thumbprint}' => $this->document->sbisOrganization->thumbprint,
                '{file}' => $this->stored_path,
                '{signatureFile}' => $signatureFileName,
            ]
        );

        // execute and log command/result
        Yii::info(sprintf('SBIS sign (attachment #%s) command: %s', $this->id, $command), SBISDocument::LOG_CATEGORY);
        $output = shell_exec($command);
        Yii::info(sprintf('SBIS sign (attachment #%s) output: %s', $this->id, $output), SBISDocument::LOG_CATEGORY);

        preg_match('/ErrorCode: ([^\]]*)\]/', $output, $matches);
        $success = (count($matches) == 2) && hexdec($matches[1]) === 0;
        if (!$success) {
            Yii::error(sprintf('SBIS sign (attachment #%s) output: %s', $this->id, $output), SBISDocument::LOG_CATEGORY);
            return false;
        }

        $this->is_signed = true;
        $this->signature_stored_path = $signatureFileName;

        return true;
    }
}
