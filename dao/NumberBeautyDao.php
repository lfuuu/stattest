<?php
namespace app\dao;

use Yii;
use app\classes\Singleton;
use app\models\DidGroup;

/**
 * @method static NumberBeautyDao me($args = null)
 */
class NumberBeautyDao extends Singleton
{
    const DEFAULT_POSTFIX_LENGTH = 7;

    public static $beautyLvlPlatinum = [
        /**
         * 7 цифр подряд
         * @example XXXXXXX 222-22-22
         */
        '/^(\d)\1{6}$/',

        /**
         * 6 одинаковых цифр подряд
         * @example Z-XXXXXX 288-88-88
         * @example YYYYYY-X 7-777-770
         */
        '/^(\d)\1{5}\d$/',
        '/^\d(\d)\1{5}$/',

        /**
         * 6 цифр не подряд + 1 в середине
         * @example XXX-Y-XXX 666-2-666
         */
        '/^(\d)\1{2}\d\1{3}$/',

        /**
         * 6 цифр не подряд
         * @example XYX-XX-XX 939-99-99
         * @example XX-Y-XXXX 44-3-4444
         * @example XXXX-Y-XX 2222-3-22
         * @example XXXXX-Y-X 55555-6-5
         */
        '/^(\d)\d\1{5}$/',
        '/^(\d)\1\d\1{3}$/',
        //'/^(\d)\1\1\d\1\1\1$/',
        '/^(\d)\1{3}\d\1{2}$/',
        '/^(\d)\1{4}\d\1$/',

        /**
         * 5 цифр подряд + 2 одинаковые цифры
         * @example XX-YYYYY 225-55-55
         * @example XXXXX-YY 333-33-44
         * @example X-YYYYY-X 7-00000-7
         */
        '/^(\d)\1(\d)\2{4}$/',
        '/^(\d)\1{4}(\d)\2$/',
        '/^(\d)(\d)\2{4}\1$/',

        /**
         * 3+4, 4+3 одинаковые цифры подряд
         * @example XXX-YYYY 444-22-22
         * @example XXXX-YYY 22-22-444
         */
        '/^(\d)\1{2}(\d)\2{3}$/',
        '/^(\d)\1{3}(\d)\2{2}$/',

        /**
         * 3 одинаковых в середине сподряд + 4 одинаковых
         * @example XX-YYY-XX 22-555-22
         */
        '/^(\d)\1(\d)\2{2}\1{2}$/',

        /**
         * 5 одинаковых цифр + 2 одинаковые в середине номера
         * @example XXXX-YY-X 222-23-32
         * @example X-YY-XXXX 2-33-2222
         * @example XX-YY-XXX 22-33-222
         * @example XXX-YY-XX 222-33-22
         */
        '/^(\d)\1{3}(\d)\2\1$/',
        '/^(\d)(\d)\2\1{4}$/',
        '/^(\d)\1(\d)\2\1{3}$/',
        '/^(\d)\1{2}(\d)\2\1{2}$/',

        /**
         * 5 цифр подряд + 2 разные цифры
         * @example XY-ZZZZZ 245-55-55
         * @example XXXXX-YZ 555-55-32
         * @example YXXXXXZ 3-55555-2
         */
        '/^\d\d(\d)\1{4}$/',
        '/^(\d)\1{4}\d\d$/',
        '/^\d(\d)\1{4}\d$/',

        /**
         * 3 одинаковые пары цифр
         * @example XYX-YX-YX 242-42-42
         * @example XYZ-YZ-YZ 246-46-46
         * @example XY-XY-XYZ 24-24-246
         */
        '/^(\d)(\d)\1\2\1\2\1$/',
        '/^\d(\d)(\d)\1\2\1\2$/',
        '/^(\d)(\d)\1\2\1\2\d$/',

        /**
         * 2 группы по 3 одинаковых цифры
         * @example X-YYY-XXX 2-555-222
         * @example X-YYY-ZZZ 2-555-666
         * @example XXX-YYY-X 222-111-2
         * @example XXX-YYY-Z 555-666-7
         * @example XXX-Y-ZZZ 444-1-777
         */
        '/^\d(\d)\1{2}(\d)\2{2}$/',
        '/^(\d)\1{2}(\d)\2{2}\d$/',
        '/^(\d)\1{2}\d(\d)\2{2}$/',

        /**
         * 5 одинаковых цифр (из них 4 подряд) + 2 одинаковые
         * @example XXXX-YXY 4444-242
         * @example XXXX-YYX 4444-224
         * @example XYY-XXXX 422-4444
         * @example YXY-XXXX 242-4444
         */
        '/^(\d)\1{3}(\d)\1\2$/',
        '/^(\d)\1{3}(\d)\2\1$/',
        '/^(\d)(\d)\2\1{4}$/',
        '/^(\d)(\d)\1\2{4}$/',

        /**
         * 4 одинаковые цифры подряд в начале или конце номера
         * @example NUM-ZZ-ZZ 245-88-88
         * @example ZZ-ZZ-NUM 88-88-245
         */
        '/^\d\d\d(\d)\1{3}$/',
        '/^(\d)\1{3}\d\d\d$/',

        /**
         * 4 одинаковые цифры в середине номера + 3 одинаковых
         * @example XYYYYXX 244-44-22
         * @example XXYYYYX 22-444-42
         */
        '/^(\d)(\d)\2{3}\1{2}$/',
        '/^(\d)\1(\d)\2{3}\1$/',

        /**
         * 3 одинаковые цифры подряд + 2 пары цифр
         * @example XXX-YZ-YZ 222-45-45
         * @example XXX-YX-YX 222-42-42
         * @example XY-ZZZ-XY 71-999-71
         * @example XY-XY-ZZZ 45-45-222
         */
        '/^(\d)\1{2}(\d)(\d)\2\3$/',
        '/^(\d)\1{2}(\d)\1\2\1$/',
        '/^(\d)(\d)(\d)\3{2}\1\2$/',
        '/^(\d)(\d)\1\2(\d)\3{2}$/',

        /**
         * 2 группы по 3 цифры, зеркальные относительно центральной цифры номера
         * @example XXY-Z-XXY 556-7-556
         * @example XYY-Z-XYY 566-7-566
         * @example XYX-Z-XYX 565-7-565
         * @example XYZ-A-XYZ 576-9-576
         */
        '/^(\d)\1(\d)\d\1{2}\2$/',
        '/^(\d)(\d)\2\d\1\2{2}$/',
        '/^(\d)(\d)\1\d\1\2\1$/',
        '/^(\d)(\d)(\d)\d\1\2\3$/',

        /**
         * 2 одинаковые пары подряд в конце номера
         * @example ABX-XY-XY 247-75-75
         * @example ABY-XY-XY 375-25-25
         * @example NUM-XY-XY 245-16-16
         */
        '/^\d\d\d(\d)(\d)\1\2$/',
    ];

    public static $beautyLvlGold = [
        /**
         * 5 одинаквых цифр в номере
         * @example XX-ZY-XXX 22-40-222
         * @example XXX-ZY-XX 222-40-22
         */
        '/^(\d)\1\d\d\1{3}$/',
        '/^(\d)\1{2}\d\d\1{2}$/',

        /**
         * 3 одинаковых + 4 одинаковых (из них 3 подряд)
         * @example XXY-XY-YY 225-25-55
         */
        '/^(\d)\1(\d)\1\2{3}$/',

        /**
         * 2 группы по 3 цифры
         * @example XYZ-XYZ-A 283-283-0
         * @example XYY-XYY-X 288-288-2
         * @example XYX-XYX-Z 282-282-9
         * @example XYY-XYY-Z 288-288-9
         */
        '/^(\d)(\d)(\d)\1\2\3\d$/',
        '/^(\d)(\d)\2\1\2{2}\1$/',
        '/^(\d)(\d)\1{2}\2\1\d$/',
        '/^(\d)(\d)\2\1\2{2}\d$/',

        /**
         * 2 группы по 3 цифры
         * @example A-XYZ-XYZ 7-235-235
         * @example A-XXY-XXY 7-225-225
         * @example XXY-XXY-A 228-228-5
         * @example A-XYY-XYY 7-455-455
         * @example A-XYX-XYX 7-454-454
         */
        '/^\d(\d)(\d)(\d)\1\2\3$/',
        '/^\d(\d)\1(\d)\1{2}\2$/',
        '/^(\d)\1(\d)\1{2}\2\d$/',
        '/^\d(\d)(\d)\2\1\2{2}$/',
        '/^\d(\d)(\d)\1{2}\2\1$/',

        /**
         * 2 одинаковые пары подряд в конце номера + 2 одинаковые в начале
         * @example AXX-XY-XY 233-34-34
         * @example AYY-XY-XY 233-43-43
         */
        '/^\d(\d)\1{2}(\d)\1\2$/',
        '/^\d(\d)\1(\d)\1\2\1$/',

        /**
         * 3+4 одинаковые цифры в номере из них 3 и 2 одинаковых подряд
         * @example XYY-XX-YY 655-66-55
         * @example XXY-XX-YY 665-66-55
         * @example XYX-YY-XX 656-55-66
         * @example XYX-XX-YY 656-66-55
         */
        '/^(\d)(\d)\2\1{2}\2{2}$/',
        '/^(\d)\1(\d)\1{2}\2{2}$/',
        '/^(\d)(\d)\1\2{2}\1{2}$/',
        '/^(\d)(\d)\1{3}\2{2}$/',

        /**
         * 3 пары одинаковых цифр
         * @example XYY-ZZ-BB 244-66-77
         * @example XYY-ZZ-XX 233-55-22
         * @example XYY-ZZ-YY 455-66-55
         * @example XXA-YY-ZZ 663-55-77
         */
        /**
         * 2 пары одинаковых цифр в начале и конце номера + 3 одинаковых в середине
         * @example XX-YYY-ZZ 22-555-33
         */
        /**
         * 4 одинаковых цифры в середине + 2 одинаковых в конце
         * @example A-XXXX-YY 2-5555-33
         */
        '/^\d(\d)\1(\d)\2(\d)\3$/',
        '/^(\d)\1\d(\d)\2(\d)\3$/',

        /**
         * 3 одинаковые цифры в середине номера + 2 пары зеркальный цифр
         * @example XY-ZZZ-YX 24-555-42
         */
        '/^(\d)(\d)(\d)\3{2}\2\1$/',

        /**
         * 2 одинаковые пары + пара одинаковых цифр в середине
         * @example XYZ-BB-YZ 245-33-45
         */
        '/^\d(\d)(\d)(\d)\3\1\2$/',

        /**
         * 2 одинаковые пары в начале и конце номера, зеркальные относительно центральной
         * @example XY-ZAZ-XY 25-303-25
         */
        '/^(\d)(\d)(\d)\d\3\1\2$/',

        /**
         * 2 одинаковые + 3 одинаковые цифры в номере
         * @example A-XXX-YYB 7-999-552
         * @example AXX-YYY-B 799-555-2
         * @example AB-XX-YYY 79-22-555
         * @example AB-XXX-YY 79-222-55
         */
        '/^\d(\d)\1{2}(\d)\2\d$/',
        '/^\d(\d)\1(\d)\2{2}\d$/',
        '/^\d\d(\d)\1(\d)\2{2}$/',
        '/^\d\d(\d)\1{2}(\d)\2$/',

        /**
         * 3 подряд + 3 не подряд одинаковых цифр
         * @example XXX-YY-ZX 222-44-02
         * @example XXX-YY-ZY 222-44-04
         */
        '/^(\d)\1{2}(\d)\2\d\1$/',
        '/^(\d)\1{2}(\d)\2\d\2$/',

        /**
         * 5 одинаковых цифр (из них 3 подряд)
         * @example XYY-Z-YYY 277-5-777
         * @example XXX-YX-ZX 222-42-52
         */
        '/^\d(\d)\1\d\1{3}$/',
        '/^(\d)\1{2}\d\1\d\1$/',

        /**
         * 3 одинаковые цифры в начале + 2 одинаковые в парах
         * @example XXX-AZ-BZ 222-31-51
         */
        '/^(\d)\1{2}\d(\d)\d\2$/',

        /**
         * 4 одинаковые цифры в номере
         * @example AB-XXXX-C 246-66-67
         * @example A-XXXX-BC 244-44-71
         * @example AXX-BC-XX 244-58-44
         * @example XYZZ-A-ZZ 245-57-55
         * @example XY-ZZZ-AZ 245-55-75
         */
        '/^\d\d(\d)\1{3}\d$/',
        '/^\d(\d)\1{3}\d\d$/',
        '/^\d(\d)\1\d\d\1{2}$/',
        '/^\d\d(\d)\1\d\1{2}$/',
        '/^\d\d(\d)\1{2}\d\1$/',

        /**
         * 2 зеркальные пары в номере
         * @example NUM-XY-YX 792-34-43
         */
        '/^\d\d\d(\d)(\d)\2\1$/',
    ];

    public static $beautyLvlSilver = [
        /**
         * 4 одинаковые цифры не подряд
         * @example XYZ-A-ZZZ 245-3-555
         */
        '/^\d\d(\d)\d\1{3}$/',

        /**
         * 2 одинаковые пары в начале и конце номера, 3,5 и 6 цифры одинаковые
         * @example XYZ-AZ-YZ 245-35-45
         */
        '/^\d(\d)(\d)\d\2\1\2$/',

        /**
         * 3 одинаковые цифры в номере подряд
         * @example NUM-Y-XXX 275-8-000
         * @example AB-XXX-CD 28-000-34
         * @example NUM-XXX-Y 245-333-9
         * @example Y-XXX-NUM 2-333-845
         */
        '/^\d\d\d\d(\d)\1{2}$/',
        '/^\d\d(\d)\1{2}\d\d$/',
        '/^\d\d\d(\d)\1{2}\d$/',
        '/^\d(\d)\1{2}\d\d\d$/',

        /**
         * 2 одинаковые пары подряд в начале номера
         * @example XY-XY-NUM 64-64-875
         */
        '/^(\d)(\d)\1\2\d\d\d$/',

        /**
         * 2 одинаковые пары в начале и конце номера
         * @example XY-NUM-XY 24-351-24
         */
        '/^(\d)(\d)\d\d\d\1\2$/',

        /**
         * @example XYZ-AB-YZ 243-75-43
         */
        '/^\d(\d)(\d)\d\d\1\2$/',

        /**
         * 2 пары одинаковых цифр в номере
         * @example XX-YY-NUM 22-33-875
         * @example XXX-A-YYB 222-6-778
         * @example XYY-A-ZZB 266-8-449
         * @example AXX-BC-YY 266-39-55
         * @example XX-NUM-YY 33-521-88
         */
        '/^(\d)\1(\d)\2\d\d\d$/',
        '/^(\d)\1{2}\d(\d)\2\d$/',
        '/^\d(\d)\1\d(\d)\2\d$/',
        '/^\d(\d)\1\d\d(\d)\2$/',
        '/^(\d)\1\d\d\d(\d)\2$/',

        '/^(\d)(\d)(\d)\d\3\2\1$/',
        '/^\d\d(\d)(\d)\2\1\d$/',
        '/^\d(\d)(\d)\1\2\d\d$/',

        /**
         * 2 пары одинаковых цифр подряд в конце номера
         * @example NUM-XX-YY 283-22-00
         */
        '/^\d\d\d(\d)\1(\d)\2$/',
        //'/^\d\d\d(\d)\1(\d)\2$/',
    ];

    public static $beautyLvlBronze = [
        /**
         * @example NUM-XX-ZX 231-44-54
         * @example NUM-XY-ZZ 231-54-77
         */
        //'/^\d\d(\d)(\d)\1\2\d$/',
        //'/^\d\d(\d)\1(\d)\2\d$/',
        //'/^\d(\d)(\d)\2\1\d\d$/',
        //'/^\d(\d)(\d)\1\2\d\d$/',
        '/^\d\d\d(\d)\1\d\1$/',
        '/^\d\d\d\d\d(\d)\1$/',
    ];

    /**
     * Определение красивости номера
     *
     * @param string $number
     * @param int $postfixLength
     * @return int
     */
    public static function getNumberBeautyLvl($number, $postfixLength = self::DEFAULT_POSTFIX_LENGTH)
    {
        $number = substr($number, -$postfixLength);

        /**
         * Платиновые номера
         */
        foreach (self::$beautyLvlPlatinum as $expression) {
            if (preg_match($expression, $number)) {
                return DidGroup::BEAUTY_LEVEL_PLATINUM;
            }
        }

        /**
         * Золотые номера
         */
        foreach (self::$beautyLvlGold as $expression) {
            if (preg_match($expression, $number)) {
                return DidGroup::BEAUTY_LEVEL_GOLD;
            }
        }

        /**
         * Серебрянные номера
         */
        foreach (self::$beautyLvlSilver as $expression) {
            if (preg_match($expression, $number)) {
                return DidGroup::BEAUTY_LEVEL_SILVER;
            }
        }

        /**
         * Бронзовые номера
         */
        foreach (self::$beautyLvlBronze as $expression) {
            if (preg_match($expression, $number)) {
                return DidGroup::BEAUTY_LEVEL_BRONZE;
            }
        }

        /**
         * Стандартные номера
         */
        return DidGroup::BEAUTY_LEVEL_STANDART;
    }

}