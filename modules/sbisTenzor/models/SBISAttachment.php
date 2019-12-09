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
 * @property string $stored_path_modified
 * @property string $hash
 * @property string $hash_stored_path
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
    const SUB_DIR_MODIFIED = 'modified';
    const SUB_DIR_HAHES = 'hashes';

    protected $hashHex;

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
            [['stored_path', 'stored_path_modified', 'hash_stored_path', 'signature_stored_path', 'link'], 'string', 'max' => 512],
            [['url_html', 'url_pdf'], 'string', 'max' => 2048],
            [['sbis_document_id'], 'exist', 'skipOnError' => true, 'targetClass' => SbisDocument::class, 'targetAttribute' => ['sbis_document_id' => 'id']],
            [
                ['sbis_document_id', 'number'], 'unique', 'targetAttribute' => ['sbis_document_id', 'number'],
                'message' => 'Вложение для пакета документов с этим порядковым номером уже существует: {values}'
            ],
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
            'stored_path' => 'Полный путь к файлу',
            'stored_path_modified' => 'Полный путь к модифицированному файлу',
            'hash' => 'Хэш',
            'hash_stored_path' => 'Hash file stored path',
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
     * Получить актуальный путь к файлу
     *
     * @return string
     */
    public function getActualStoredPath()
    {
        return $this->stored_path_modified ? : $this->stored_path;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(SBISDocument::class, ['id' => 'sbis_document_id']);
    }

    /**
     * Получить полный путь для сохранения изменённого вложения
     *
     * @param $fileName
     * @return string
     */
    protected function getModifiedFileName($fileName)
    {
        $originalFileName = $this->stored_path;
        $dirPath =
            dirname($originalFileName) .
            DIRECTORY_SEPARATOR .
            self::SUB_DIR_MODIFIED;

        SBISUtils::checkDirectory($dirPath);

        $modifiedFileName =
            $dirPath .
            DIRECTORY_SEPARATOR .
            $fileName;
        return $modifiedFileName;
    }

    /**
     * Сохранить изменённое вложение
     *
     * @param string $fileName
     * @param string $content
     */
    public function saveModifiedFile($fileName, $content)
    {
        $modifiedFileName = $this->getModifiedFileName($fileName);
        if (!file_put_contents($modifiedFileName, $content)) {
            $this->document->addErrorText(
                sprintf("SBIS modified file: can't save file, attachment #%s, path: %s", $this->id, $modifiedFileName)
            );
        }

        $this->file_name = $fileName;
        $this->stored_path_modified = $modifiedFileName;

        if ($this->is_sign_needed) {
            $this->is_signed = 0;
            $this->signature_stored_path = '';
            $this->hash_stored_path = '';
        }
    }

    /**
     * Получить полный путь для сохранения хэш-файла от вложению
     *
     * @return string
     */
    public function getHashFileName()
    {
        if (!$this->is_sign_needed) {
            return '';
        }

        $fileName = $this->getActualStoredPath();
        $hashFileName = $this->hash_stored_path;
        if ((!$hashFileName || !file_exists($hashFileName)) && file_exists($fileName)) {
            $dirPath =
                dirname($fileName) .
                DIRECTORY_SEPARATOR .
                self::SUB_DIR_HAHES;

            SBISUtils::checkDirectory($dirPath);

            $hashFileName =
                $dirPath .
                    DIRECTORY_SEPARATOR .
                    basename($fileName) . '.hsh';
        }

        return $hashFileName;
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

        $fileName = $this->getActualStoredPath();
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
    public function getFullStoredPath($fileName = '', $dirSuffix = '')
    {
        $info = pathinfo($this->file_name);
        $fileName = $fileName ? : sprintf('%02d.%s', $this->number, $info['extension']);

        return $this->getPath($dirSuffix) . $fileName;
    }

    /**
     * Подписать вложение
     *
     * @param string $signCommand Команда для подписи
     * @param string $hashCommand
     * @return bool
     */
    public function sign($signCommand, $hashCommand)
    {
        if ($this->is_signed) {
            return true;
        }

        $signatureFileName = $this->getSignatureFileName();
        if (!$signatureFileName) {
            $this->document->addErrorText(
                sprintf("SBIS signature file not created, attachment #%s", $this->id)
            );

            return false;
        }

        // at first creating hash
        $this->createHash($hashCommand);
        if ($this->hash && !$this->isHashesEqual()) {
            $this->document->addErrorText(
                sprintf("SBIS hash value '%s' is not equal to SBIS one, attachment #%s: %s", $this->getGeneratedHash(), $this->id, $this->hash)
            );

            return false;
        }

        $fileName = $this->getActualStoredPath();
        $signCommand = strtr(
            $signCommand,
            [
                '{thumbprint}' => $this->document->sbisOrganization->thumbprint,
                '{algorithm}' => $this->document->sbisOrganization->algorithm,
                '{file}' => $fileName,
                '{signatureFile}' => $signatureFileName,
            ]
        );

        // execute and log command/result
        Yii::info(sprintf('SBIS sign (attachment #%s) command: %s', $this->id, $signCommand), SBISDocument::LOG_CATEGORY);
        $output = shell_exec($signCommand);
        Yii::info(sprintf('SBIS sign (attachment #%s) output: %s', $this->id, $output), SBISDocument::LOG_CATEGORY);

        preg_match('/ErrorCode: ([^\]]*)\]/', $output, $matches);
        $success = (count($matches) == 2) && hexdec($matches[1]) === 0;
        if (!$success) {
            $this->document->addErrorText(
                sprintf('SBIS sign error (attachment #%s) output: %s', $this->id, $output)
            );

            return false;
        }

        $this->is_signed = true;
        $this->signature_stored_path = $signatureFileName;

        return true;
    }

    /**
     * Создать хэш от вложения
     *
     * @param string $hashCommand Команда создания хэшированного файла
     * @return bool
     */
    protected function createHash($hashCommand)
    {
        $hashFileName = $this->getHashFileName();
        if (!$hashFileName) {
            $this->document->addErrorText(
                sprintf("SBIS hash file not created, attachment #%s", $this->id)
            );

            return false;
        }

        $fileName = $this->getActualStoredPath();
        $hashCommand = strtr(
            $hashCommand,
            [
                '{algorithm}' => $this->document->sbisOrganization->algorithm,
                '{hashDir}' => dirname($hashFileName),
                '{file}' => $fileName,
            ]
        );

        // execute and log command/result
        Yii::info(sprintf('SBIS create hash (attachment #%s) command: %s', $this->id, $hashCommand), SBISDocument::LOG_CATEGORY);
        $output = shell_exec($hashCommand);
        Yii::info(sprintf('SBIS create hash (attachment #%s) output: %s', $this->id, $output), SBISDocument::LOG_CATEGORY);

        preg_match('/ErrorCode: ([^\]]*)\]/', $output, $matches);
        $success = (count($matches) == 2) && hexdec($matches[1]) === 0;
        if (!$success) {
            $this->document->addErrorText(
                sprintf('SBIS create hash error (attachment #%s) output: %s', $this->id, $output)
            );

            return false;
        }

        if (!file_exists($hashFileName)) {
            $this->document->addErrorText(
                sprintf('SBIS hash file not found (attachment #%s): %s', $this->id, $hashFileName)
            );

            return false;
        }

        $this->hash_stored_path = $hashFileName;

        return true;
    }

    /**
     * Получение хэша от вложения в base64
     *
     * @return string
     */
    public function getGeneratedHash()
    {
        if (is_null($this->hashHex)) {
            $this->hashHex = $this->hash_stored_path ?
                base64_encode(file_get_contents($this->hash_stored_path)) :
                '';
        }

        return $this->hashHex;
    }

    /**
     * Проверка, совпадают ли хэши
     *
     * @return bool
     */
    public function isHashesEqual()
    {
        return $this->hash === $this->getGeneratedHash();
    }
}
