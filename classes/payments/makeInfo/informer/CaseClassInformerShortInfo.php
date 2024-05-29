<?php

namespace app\classes\payments\makeInfo\informer;

class CaseClassInformerShortInfo
{
    public string $type = '';
    public string $comment = '';

    public function __construct(string $type, string $comment)
    {
        $this->type = $type;
        $this->comment = $comment;
    }
}