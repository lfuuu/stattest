<?php

namespace app\models\light_models\uu;

use Yii;
use yii\base\Component;
use app\models\Person;

class InvoicePersonLight extends Component implements InvoiceLightInterface
{

    public
        $name_nominative,
        $name_genitive,
        $post_nominative,
        $post_genitive;

    /**
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        parent::__construct();

        $this->name_nominative = $person->name_nominative;
        $this->name_genitive = $person->name_genitive;
        $this->post_nominative = $person->post_nominative;
        $this->post_genitive = $person->post_genitive;
    }

    /**
     * @return string
     */
    public static function getKey()
    {
        return 'person';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Данные об ответственном лице';
    }

    /**
     * @return array
     */
    public static function attributeLabels()
    {
        return [
            'name_nominative' => 'ФИО',
            'name_genitive' => 'ФИО (род. п.)',
            'post_nominative' => 'Должность',
            'post_genitive' => 'Должность (род. п.)',
        ];
    }

}