<?php
namespace app\tests\codeception\fixtures;

use app\models\voip\Destination;

class DestinationFixture extends ActiveFixture
{
    public $modelClass = Destination::class;
}