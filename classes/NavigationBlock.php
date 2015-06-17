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

    public function addItem($title, $url, $rights = [])
    {
        if (!empty($rights)) {
            if (!is_array($rights)) {
                $rights = [$rights];
            }

            $grant = false;

            foreach ($rights as $right) {
                $grant = Yii::$app->user->can($right);
                if ($grant) {
                    break;
                }
            }

            if (!$grant) {
                return $this;
            }
        }

        if (is_array($url)) {
            $url = Url::toRoute($url);
        }
        $this->items[] = ['title' => $title, 'url' => $url];
        return $this;
    }

    public function setRights(array $rights)
    {
        $this->rights = $rights;
        return $this;
    }
}