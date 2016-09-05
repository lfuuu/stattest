<?php
namespace app\models;

use Yii;
use ReflectionClass;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;
use app\exceptions\FormValidationException;
use app\classes\validators\ArrayValidator;
use app\classes\DynamicModel;

class Person extends ActiveRecord
{

    public $canDelete = true;

    private $langCode = Language::LANGUAGE_DEFAULT;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'person';
    }

    public function rules()
    {
        return [
            ['id', 'integer'],
            ['signature_file_name', 'string'],
        ];
    }

    /**
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'name_nominative' => 'ФИО',
            'name_genitive' => 'Фио (род. п.)',
            'post_nominative' => 'Должность',
            'post_genitive' => 'Должность (род. п.)',
        ];
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch(\Exception $e) {
            $i18n = $this->getI18N($this->langCode);
            if (array_key_exists($name, (array)$i18n)) {
                return $i18n[$name];
            }

            $i18n = $this->getI18N();
            if (array_key_exists($name, (array)$i18n)) {
                return $i18n[$name];
            }

            if (!$this->getPrimaryKey()) {
                return '';
            }
            else {
                throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
            }
        }
    }

    /**
     * @param string $langCode
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function getI18N($langCode = Language::LANGUAGE_DEFAULT)
    {
        return
            $this->hasMany(PersonI18N::className(), ['person_id' => 'id'])
                ->andWhere(['lang_code' => $langCode])
                ->indexBy('field')
                ->all();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name_nominative;
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLanguage($langCode = Language::LANGUAGE_DEFAULT)
    {
        $this->langCode = $langCode;
        return $this;
    }

    /**
     * @param bool|true $runValidation
     * @param array $attributesName
     * @return bool
     * @throws \Exception
     */
    public function save($runValidation = true, $attributesName = [])
    {
        parent::save($runValidation, $attributesName);

        $personI18N = DynamicModel::validateData(
            Yii::$app->request->post((new ReflectionClass($this))->getShortName()),
            [
                [['name_nominative', 'name_genitive', 'post_nominative', 'post_genitive'], ArrayValidator::className()],
            ]
        );

        if ($personI18N->hasErrors()) {
            throw new FormValidationException($personI18N);
        }

        foreach ($personI18N->attributes as $attribute => $i18nData) {
            foreach ($i18nData as $lang => $value) {
                $transaction = PersonI18N::getDb()->beginTransaction();
                try {
                    PersonI18N::deleteAll([
                        'person_id' => $this->id,
                        'lang_code' => $lang,
                        'field' => $attribute,
                    ]);

                    $i18n = new PersonI18N;
                    $i18n->person_id = $this->id;
                    $i18n->lang_code = $lang;
                    $i18n->field = $attribute;
                    $i18n->value = $value;
                    $i18n->save();

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }

        return true;
    }

    /**
     * @return []
     */
    public function getOldModeInfo()
    {
        return [
            'name' => $this->name_nominative,
            'name_' => $this->name_genitive,
            'position' => $this->post_nominative,
            'position_' => $this->post_genitive,
            'sign' => [
                'src' => str_replace('/images/', '', \Yii::$app->params['SIGNATURE_DIR']) . $this->signature_file_name,
            ]
        ];
    }

}