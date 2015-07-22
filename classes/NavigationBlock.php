<?php
namespace app\classes;

use Yii;
use yii\helpers\Url;


class NavigationBlock
{
    public $id;
    public $title;
    public $rights;
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

    public function addItem($title, $url, $rights = null)
    {
        if (is_array($url)) {
            $url = Url::toRoute($url);
        }

        if ($rights) {
            foreach ($rights as $right) {
                if (Yii::$app->user->can($right)) {
                    $this->items[] = ['title' => $title, 'url' => $url];
                    break;
                }
            }
        } else {
            $this->items[] = ['title' => $title, 'url' => $url];
        }

        return $this;
    }

    public function setRights(array $rights)
    {
        $this->rights = $rights;
        return $this;
    }

    public function addStatModuleItems($moduleName)
    {
        $statModule = StatModule::getHeadOrModule($moduleName);

        list($title, $items) = $statModule->GetPanel(null);

        if (!$title || !$items) {
            return null;
        }

        foreach ($items as $item) {
            $url =
                substr($item[1], 0, 1) == '/'
                    ? $item[1]
                    : '?' . $item[1];
            $this->addItem($item[0], $url);
        }

        return $this;
    }
}