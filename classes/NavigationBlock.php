<?php
namespace app\classes;

use Yii;
use yii\helpers\Url;


class NavigationBlock
{
    public $id;
    public $title;
    public $items = [];

    /**
     * @return NavigationBlock
     */
    public static function create()
    {
        return new static;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function addItem($title, $url)
    {
        if (is_array($url)) {
            $url = Url::toRoute($url);
        }
        $this->items[] = ['title' => $title, 'url' => $url];
        return $this;
    }
}