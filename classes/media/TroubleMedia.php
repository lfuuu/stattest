<?php

namespace app\classes\media;

use app\models\media\TroubleFiles;

class TroubleMedia extends MediaManager
{

    private
        $folder = 'troubles',
        $link_field = 'trouble_id';

    public function __construct($trouble_id = 0)
    {
        $this->model = new TroubleFiles;
        $this->record_id = $trouble_id;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function getLinkField()
    {
        return $this->link_field;
    }

}