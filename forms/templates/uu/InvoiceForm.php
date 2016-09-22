<?php
namespace app\forms\templates\uu;

use app\classes\Smarty;
use app\models\light_models\uu\InvoiceLight;
use Yii;
use app\classes\Form;
use app\models\Language;
use yii\web\UploadedFile;

class InvoiceForm extends Form
{

    const STORE_PATH = 'files/invoice_content';
    const TEMPLATE_EXTENSION = 'html';
    const UNIVERSAL_INVOICE_KEY = 'en-EN-universal';

    private $langCode = Language::LANGUAGE_DEFAULT;

    /**
     * @param string $langCode
     */
    public function __construct($langCode = Language::LANGUAGE_DEFAULT)
    {
        parent::__construct();

        $this->langCode = $langCode;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->langCode;
    }

    /**
     * @return bool
     */
    public function fileExists()
    {
        return file_exists(self::getPath() . $this->langCode . '.' . self::TEMPLATE_EXTENSION);
    }

    /**
     * @return bool|string
     */
    public function getFile()
    {
        if ($this->fileExists()) {
            return file_get_contents(self::getPath() . $this->langCode . '.' . self::TEMPLATE_EXTENSION);
        }
        return false;
    }

     /**
     * @inheritdoc
     */
    public function save()
    {
        // Сохранение универсального шаблона
        $this->storeFile(self::UNIVERSAL_INVOICE_KEY);

        // Сохранение языковых шаблонов
        foreach (Language::getList() as $languageCode => $languageTitle) {
            $this->storeFile($languageCode);
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
    private function storeFile($key)
    {
        /** @var UploadedFile $file */
        $file = UploadedFile::getInstance($this, 'filename[' . $key . ']');

        if (!is_null($file) && $file->getExtension() !== self::TEMPLATE_EXTENSION) {
            Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' должен быть в формате HTML<br />');
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
            } catch (\SmartyException $e) {
                Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' не может быть преобразован<br />' . $e->getMessage());
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('error', 'Шаблон ' . $key . ' не может быть преобразован<br />' . $e->getMessage());
            }
        }
    }

}