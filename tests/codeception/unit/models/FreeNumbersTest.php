<?php

namespace tests\codeception\unit\models;

use app\models\City;
use app\models\filter\FreeNumberFilter;
use app\modules\nnp\models\NdcType;
use yii\codeception\TestCase;

class FreeNumbersTest extends TestCase
{
    // Фикстуры использовать нельзя, ибо unload() удаляет все данные из таблицы и веб-тесты падают
//    protected function setUp()
//    {
//        parent::setUp();
//        (new NumberFixture)->load();
//    }
//
//    protected function tearDown()
//    {
//        (new NumberFixture)->unload();
//        parent::tearDown();
//    }

    public function testAllFreeNumbers()
    {
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(200);
        $this->assertEquals(123, count($numbers->result()));
    }

    public function testRegularFreeNumbers()
    {
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNdcType(NdcType::ID_GEOGRAPHIC)
            ->setLimit(100);
        $this->assertEquals(20, count($numbers->result()));
    }

    public function test7800FreeNumbers()
    {
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNdcType(NdcType::ID_FREEPHONE)
            ->setLimit(200);
        $this->assertEquals(103, count($numbers->result()));
    }

    public function testFreeNumbersByRegions()
    {
        // Москва
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(200)
            ->setRegions([99]);
        $this->assertEquals(123, count($numbers->result()));
    }

    public function testFreeNumbersByCost()
    {
        // Between
        $numbers = (new FreeNumberFilter)
            ->setMinCost(0)
            ->setMaxCost(1999)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(100);
        $this->assertEquals(17, count($numbers->result()));

        // Between
        $numbers = (new FreeNumberFilter)
            ->setMinCost(999)
            ->setMaxCost(1999)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(100);
        $this->assertEquals(1, count($numbers->result()));

        // Greater than minCost
        $numbers = (new FreeNumberFilter)
            ->setMinCost(999)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(100);
        $this->assertEquals(1, count($numbers->result()));

        // Less than maxCost
        $numbers = (new FreeNumberFilter)
            ->setMaxCost(999)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(100);
        $this->assertEquals(16, count($numbers->result()));
    }

    public function testFreeNumbersByBeautyLvl()
    {
        // Стандартные номера
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setBeautyLvl(0)
            ->setLimit(200);
        $this->assertEquals(119, count($numbers->result()));

        // Бронзовые номера
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setBeautyLvl(4)
            ->setLimit(100);
        $this->assertEquals(1, count($numbers->result()));

        // Несколько
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setBeautyLvl([0, 4])
            ->setLimit(200);
        $this->assertEquals(120, count($numbers->result()));

        // Negative
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setBeautyLvl([10])
            ->setLimit(100);
        $this->assertEquals(0, count($numbers->result()));
    }

    public function testFreeNumbersByMask()
    {
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('XYZABYZ')
            ->setLimit(100);
        $this->assertEquals(1, count($numbers->result()));

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('XYY')
            ->setLimit(100);
        $this->assertEquals(3, count($numbers->result()));

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('000')
            ->setLimit(100);
        $this->assertEquals(12, count($numbers->result()));

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('13000')
            ->setLimit(100);
        $this->assertEquals(9, count($numbers->result()));

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('*002')
            ->setLimit(100);
        $this->assertEquals(3, count($numbers->result()));

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('*XXA')
            ->setLimit(100);
        $this->assertEquals(28, count($numbers->result()));

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setNumberMask('*XY00XY')
            ->setLimit(100);
        $this->assertEquals(1, count($numbers->result()));
    }

    public function testFreeNumbersOffset()
    {
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setLimit(21);
        $firstPage = $numbers->result();

        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setOffset(20)
            ->setLimit(20);
        $secondPage = $numbers->result();

        $this->assertEquals(21, count($firstPage));
        $this->assertLessThanOrEqual(20, count($secondPage));
        $this->assertNotEquals(reset($firstPage)->number, reset($secondPage)->number); // offset работает
    }

    public function testFreeNumbersSimilar()
    {
        $numbers = (new FreeNumberFilter)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
            ->setSimilar(2130003)
            ->setLimit(100);
        $result = $numbers->result();

        $firstNumber = array_shift($result);
        $secondNumber = array_shift($result);
        $thirdNumber = array_shift($result);

        $this->assertGreaterThanOrEqual($firstNumber->levenshtein, $secondNumber->levenshtein);
        $this->assertGreaterThanOrEqual($secondNumber->levenshtein, $thirdNumber->levenshtein);
    }

}