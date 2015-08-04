<?php

namespace app\classes\media;

use app\models\media\ClientFiles;

class ClientMedia extends MediaManager
{

    private
        $folder = '',
        $link_field = 'contract_id';

    public function __construct($contract_id = 0)
    {
        $this->model = new ClientFiles;
        $this->record_id = $contract_id;
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
