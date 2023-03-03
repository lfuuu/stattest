<?php

namespace app\classes\payments\recognition\processors;

/**
 * Always - yes, but it is not known why
 */
class DummyRecognitionProcessor extends RecognitionProcessor
{
    public static function detect($infoJson): bool
    {
        return true;
    }

    protected function yetWho(): int
    {
        return 0;
    }
}