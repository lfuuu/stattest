<?php

namespace app\modules\sim\classes;

use app\classes\media\MimeType;
use app\modules\sim\models\ImsiToken;
use PHPQRCode\QRencode;

class TokenQrCodeMedia extends MimeType
{
    public function generateImage(ImsiToken $imsiToken)
    {
        $token = $imsiToken->token;
        $tmpFile = tempnam('/tmp', 'token-qrcode-');

        $enc = QRencode::factory('H', 4, 2);
        $enc->encodePNG($token, $tmpFile);

        return [
            'mime_type' => $this->mimesTypes['png'],
            'image' => base64_encode(file_get_contents($tmpFile))
        ];
    }
}