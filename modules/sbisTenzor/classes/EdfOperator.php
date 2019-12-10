<?php

namespace app\modules\sbisTenzor\classes;

/**
 * Операторы электронного документооборота
 * Electronic document flow operator
 *
 * https://www.nalog.ru/rn77/taxation/submission_statements/el_count/#t2
 * https://www.nalog.ru/rn77/taxation/submission_statements/operations/
 */
class EdfOperator
{
    const CODE_2CN = '2CN';
    const CODE_2IG = '2IG';
    const CODE_2BE = '2BE';
    const CODE_2BN = '2BN';
    const CODE_2AK = '2AK';
    const CODE_2LB = '2LB';
    const CODE_2AE = '2AE';
    const CODE_2JH = '2JH';
    const CODE_2AL = '2AL';
    const CODE_2CI = '2CI';
    const CODE_2LD = '2LD';
    const CODE_2IM = '2IM';
    const CODE_2KX = '2KX';
    const CODE_2AD = '2AD';
    const CODE_2BK = '2BK';
    const CODE_2BA = '2BA';
    const CODE_2BM = '2BM';
    const CODE_2HX = '2HX';
    const CODE_2LH = '2LH';
    const CODE_2LG = '2LG';
    const CODE_2LJ = '2LJ';
    const CODE_2IJ = '2IJ';
    const CODE_2BV = '2BV';
    const CODE_2JD = '2JD';
    const CODE_2AB = '2AB';
    const CODE_2IA = '2IA';
    const CODE_2BD = '2BD';
    const CODE_2LF = '2LF';
    const CODE_2KL = '2KL';
    const CODE_2JM = '2JM';
    const CODE_2KY = '2KY';
    const CODE_2PS = '2PS';
    const CODE_2KO = '2KO';
    const CODE_2GT = '2GT';
    const CODE_2LO = '2LO';
    const CODE_2RT = '2RT';
    const CODE_2AO = '2AO';
    const CODE_2LP = '2LP';
    const CODE_2LQ = '2LQ';
    const CODE_2JB = '2JB';
    const CODE_2VO = '2VO';
    const CODE_2KV = '2KV';
    const CODE_2HU = '2HU';
    const CODE_2BF = '2BF';
    const CODE_2LT = '2LT';
    const CODE_2AH = '2AH';
    const CODE_2GP = '2GP';

    protected static $operators = [
        self::CODE_2CN => [
            'name' => 'Litoria Doc',
            'company' => 'УЦ ГИС',
            'url' => 'http://ca.gisca.ru/',
        ],
        self::CODE_2IG => [
            'name' => 'Synerdocs',
            'company' => 'Директум',
            'url' => 'https://www.synerdocs.ru/',
        ],
        self::CODE_2BE => [
            'name' => 'СБИС',
            'company' => 'Тензор',
            'url' => 'https://sbis.ru/',
        ],
        self::CODE_2AK => [
            'name' => 'ТаксНет ЭДО',
            'company' => 'ТаксНет',
            'url' => 'http://www.taxnet.ru/',
        ],
        self::CODE_2AE => [
            'name' => 'Астрал ЭДО',
            'company' => 'Калуга Астрал',
            'url' => 'http://astralnalog.ru/',
        ],
        self::CODE_2AL => [
            'name' => 'Такском ЭДО',
            'company' => 'Такском',
            'url' => 'http://taxcom.ru/',
        ],
        self::CODE_2LD => [
            'name' => 'Evolution',
            'company' => 'Э-КОМ',
            'url' => 'https://exite.ru/',
        ],
        self::CODE_2BK => [
            'name' => 'СФЕРА Курьер',
            'company' => 'КОРУС Консалтинг',
            'url' => 'http://www.esphere.ru/',
        ],
        self::CODE_2BM => [
            'name' => 'Диадок',
            'company' => 'Контур',
            'url' => 'https://kontur.ru/',
        ],
        self::CODE_2IJ => [
            'name' => 'EDI',
            'company' => ' Эдисофт',
            'url' => 'http://ediweb.ru/',
        ],
        self::CODE_2AB => [
            'name' => 'Комита',
            'company' => 'Удостоверяющий центр',
            'url' => 'http://nwudc.ru/',
        ],
        self::CODE_2BD => [
            'name' => 'Комита Бел',
            'company' => 'УЦ Белинфоналог',
            'url' => 'http://www.belinfonalog.ru/',
        ],
        self::CODE_2AO => [
            'name' => 'Аском ЭДО',
            'company' => 'УЦ Аском',
            'url' => 'http://www.ackom.net/',
        ],
        self::CODE_2PS => [
            'name' => 'ЭДО.Поток',
            'company' => 'ПС СТ',
            'url' => 'https://ofd.ru/',
        ],
        self::CODE_2GP => [
            'name' => 'КДС ЭДО',
            'company' => 'КДС',
            'url' => 'http://www.kds-trust.ru/',
        ],
        self::CODE_2BN => [
            'name' => 'Линк-ЭДО',
            'company' => 'Линк-Сервис',
            'url' => 'http://www.link-service.ru/',
        ],
        self::CODE_2LB => [
            'name' => '2LB',
            'company' => '2LB',
            'url' => '',
        ],
        self::CODE_2JH => [
            'name' => '2JH',
            'company' => '2JH',
            'url' => '',
        ],
        self::CODE_2CI => [
            'name' => '2CI',
            'company' => '2CI',
            'url' => '',
        ],
        self::CODE_2IM => [
            'name' => '2IM',
            'company' => '2IM',
            'url' => '',
        ],
        self::CODE_2KX => [
            'name' => '2KX',
            'company' => '2KX',
            'url' => '',
        ],
        self::CODE_2AD => [
            'name' => '2AD',
            'company' => '2AD',
            'url' => '',
        ],
        self::CODE_2BA => [
            'name' => '2BA',
            'company' => '2BA',
            'url' => '',
        ],
        self::CODE_2HX => [
            'name' => '2HX',
            'company' => '2HX',
            'url' => '',
        ],
        self::CODE_2LH => [
            'name' => '2LH',
            'company' => '2LH',
            'url' => '',
        ],
        self::CODE_2LG => [
            'name' => '2LG',
            'company' => '2LG',
            'url' => '',
        ],
        self::CODE_2LJ => [
            'name' => '2LJ',
            'company' => '2LJ',
            'url' => '',
        ],
        self::CODE_2BV => [
            'name' => '2BV',
            'company' => '2BV',
            'url' => '',
        ],
        self::CODE_2JD => [
            'name' => '2JD',
            'company' => '2JD',
            'url' => '',
        ],
        self::CODE_2IA => [
            'name' => '2IA',
            'company' => '2IA',
            'url' => '',
        ],
        self::CODE_2LF => [
            'name' => '2LF',
            'company' => '2LF',
            'url' => '',
        ],
        self::CODE_2KL => [
            'name' => '2KL',
            'company' => '2KL',
            'url' => '',
        ],
        self::CODE_2JM => [
            'name' => '2JM',
            'company' => '2JM',
            'url' => '',
        ],
        self::CODE_2KY => [
            'name' => '2KY',
            'company' => '2KY',
            'url' => '',
        ],
        self::CODE_2KO => [
            'name' => '2KO',
            'company' => '2KO',
            'url' => '',
        ],
        self::CODE_2GT => [
            'name' => '2GT',
            'company' => '2GT',
            'url' => '',
        ],
        self::CODE_2LO => [
            'name' => '2LO',
            'company' => '2LO',
            'url' => '',
        ],
        self::CODE_2RT => [
            'name' => '2RT',
            'company' => '2RT',
            'url' => '',
        ],
        self::CODE_2LP => [
            'name' => '2LP',
            'company' => '2LP',
            'url' => '',
        ],
        self::CODE_2LQ => [
            'name' => '2LQ',
            'company' => '2LQ',
            'url' => '',
        ],
        self::CODE_2JB => [
            'name' => '2JB',
            'company' => '2JB',
            'url' => '',
        ],
        self::CODE_2VO => [
            'name' => '2VO',
            'company' => '2VO',
            'url' => '',
        ],
        self::CODE_2KV => [
            'name' => '2KV',
            'company' => '2KV',
            'url' => '',
        ],
        self::CODE_2HU => [
            'name' => '2HU',
            'company' => '2HU',
            'url' => '',
        ],
        self::CODE_2BF => [
            'name' => '2BF',
            'company' => '2BF',
            'url' => '',
        ],
        self::CODE_2LT => [
            'name' => '2LT',
            'company' => '2LT',
            'url' => '',
        ],
        self::CODE_2AH => [
            'name' => '2AH',
            'company' => '2AH',
            'url' => '',
        ],
    ];

    protected static $withAutoRoaming = [
        self::CODE_2BE,
        // авто-роуминг пока не работает у СБИС'а как надо
//        self::CODE_2AL,
//        self::CODE_2GP,
//        self::CODE_2PS,
    ];

    /** @var string */
    public $code;

    /**
     * EdfOperator constructor.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Автоматический роуминг со СБИС
     *
     * @param string $code
     * @return bool
     */
    public static function isAutoRoamingByCode($code)
    {
        return in_array($code, self::$withAutoRoaming);
    }

    /**
     * Получить значение поля оператора
     *
     * @param string $code
     * @param string $property
     * @return string
     */
    protected static function getProperty($code, $property)
    {
        if (!empty(self::$operators[$code][$property])) {
            return self::$operators[$code][$property];
        }

        return '';
    }

    /**
     * Имя системы
     *
     * @param string $code
     * @return string
     */
    public static function getNameByCode($code)
    {
        return self::getProperty($code, 'name') ? : '<неизвестна>';
    }

    /**
     * Ссылка
     *
     * @param string $code
     * @return string
     */
    public static function getUrlByCode($code)
    {
        return self::getProperty($code, 'url');
    }

    /**
     * Внешняя ли система?
     *
     * @return bool
     */
    public function isExternal()
    {
        return $this->code != self::CODE_2BE;
    }

    /**
     * Внешняя ли система?
     *
     * @return bool
     */
    public function isAutoRoaming()
    {
        return self::isAutoRoamingByCode($this->code);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::getNameByCode($this->code);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlByCode($this->code);
    }

}