<?php
namespace app\forms\templates\uu;

use Yii;
use app\classes\Form;
use app\classes\Smarty;
use app\models\Language;
use yii\web\UploadedFile;

class InvoiceForm extends Form
{

    const STORE_PATH = 'files/invoice_content';
    const TEMPLATE_EXTENSION = 'html';
    const UNIVERSAL_INVOICE_KEY = 'en-EN-universal';

    private $_langCode = Language::LANGUAGE_DEFAULT;

    /**
     * @param string $langCode
     */
    public function __construct($langCode = Language::LANGUAGE_DEFAULT)
    {
        parent::__construct();

        $this->_langCode = $langCode;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->_langCode;
    }

    /**
     * Получить имя файла
     *
     * @return string
     */
    public function getFileName()
    {
        return self::getPath() . $this->_langCode . '.' . self::TEMPLATE_EXTENSION;
    }

    /**
     * @return bool
     */
    public function fileExists()
    {
        return file_exists($this->getFileName());
    }

    /**
     * @return bool|string
     */
    public function getFile()
    {
        if ($this->fileExists()) {
            return file_get_contents(self::getPath() . $this->_langCode . '.' . self::TEMPLATE_EXTENSION);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        // Сохранение универсального шаблона
        $this->_storeFile(self::UNIVERSAL_INVOICE_KEY);

        // Сохранение языковых шаблонов
        foreach (Language::getList() as $languageCode => $languageTitle) {
            $this->_storeFile($languageCode);
        }
    }

    /**
     * @return string
     */
    public static function getPath()
    {
        return Yii::$app->params['STORE_PATH'] . self::STORE_PATH . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $key
     */
    private function _storeFile($key)
    {
        /** @var UploadedFile $file */
        $file = UploadedFile::getInstance($this, 'filename[' . $key . ']');

        if (!is_null($file) && $file->getExtension() !== self::TEMPLATE_EXTENSION) {
            Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' должен быть с расширением ' . self::TEMPLATE_EXTENSION . '<br />');
            return;
        }

        if ($file && $file->saveAs(self::getPath() . $key . '.' . $file->extension, $deleteTempFile = true)) {
            $content = preg_replace_callback(
                '#\{[^\}]+\}#',
                function ($matches) {
                    return preg_replace('#&[^;]+;#', '', strip_tags($matches[0]));
                },
                (new self($key))->getFile()
            );

            try {
                $smarty = Smarty::init();
                $smarty->fetch('string:' . $content);
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' не может быть преобразован<br />' . $e->getMessage());
            }
        }
    }

}