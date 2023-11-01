<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use Yii;

/**
 * Методы криптографии
 *
 * Class CryptoController
 */
class CryptoController extends ApiInternalController
{
    const STORE_PATH = 'files/crypto/messages';
//    const CRYPTO_PRO_SIGN_COMMAND = 'ssh root@cryptopro-prod /opt/cprocsp/bin/amd64/cryptcp -signf -dir {fileDir} -der -strict -cert -detached -thumbprint {thumbprint} {pin} {file}';
    const CRYPTO_PRO_SIGN_COMMAND = '/home/httpd/stat.mcn.ru/stat/modules/sbisTenzor/script/sign.sh {thumbprint} {file} {file}.sgn';

    /**
     * Получить путь к хранилищу
     *
     * @return string
     */
    protected static function getBasePath()
    {
        return Yii::$app->params['STORE_PATH'] . self::STORE_PATH;
    }

    /**
     * Check directory and create it
     *
     * @param $dirPath
     */
    protected static function checkDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0775, true);
        }
    }

    protected function getPath()
    {
        $dirData = [date('Y-m'), date('d')];

        $path = self::getBasePath();
        foreach ($dirData as $p) {
            $path .= DIRECTORY_SEPARATOR . $p;
            self::checkDirectory($path);
        }

        return $path;
    }

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Post(tags = {"Методы криптографии"}, path = "/internal/crypto/crypto-pro-sign-message", summary = "Подписать строку", operationId = "Подписать строку",
     *     @SWG\Parameter(name = "message", type = "string", description = "Message to sign", in = "query", default = "", required=true),
     *     @SWG\Parameter(name = "thumbprint", type = "string", description = "Certificate's thumbprint", in = "query", default = "", required=true),
     *     @SWG\Parameter(name = "private_key_password", type = "string", description = "Password for private key", in = "query", default = ""),
     *
     *   @SWG\Response(response=200, description="Результат подписи",
     *     @SWG\Schema(type="object", required={"success","signature"},
     *       @SWG\Property(property="success", type="boolean", description="Подписание успешно"),
     *       @SWG\Property(property="signature", type="string", description="Подписанная строка, в base64")
     *     )
     *   ),
     *
     *     @SWG\Response(response = "default", description = "Ошибки",
     *         @SWG\Schema(ref = "#/definitions/error_result")
     *     )
     * )
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCryptoProSignMessage()
    {
        $message = $this->requestData['message'] ?? '';

        $thumbprint = $this->requestData['thumbprint'] ?? '';
        if ($privateKeyPassword = $this->requestData['private_key_password'] ?? '') {
            $privateKeyPassword = sprintf('-pin %s', $privateKeyPassword);
        }

        if (!$message || !$thumbprint) {
            throw new BadRequestHttpException;
        }

        $success = false;
        $signature = '';

        $dirPath = $this->getPath();
        $fileName = $dirPath . DIRECTORY_SEPARATOR . date('Y-m-d_H:i:s_') . md5($message);
        $signatureFileName = $fileName . '.sgn';

        file_put_contents($fileName, $message);

        $command = strtr(
            self::CRYPTO_PRO_SIGN_COMMAND,
            [
                '{thumbprint}' => ($thumbprint == '4fe6047a964d397c9a7c445e643dc20a0aa69be7' ? 'c9f65edeb5805b059631e8ea1deb20a4cbd09d11' : $thumbprint),
                '{fileDir}' => $dirPath,
                '{pin}' => $privateKeyPassword,
                '{file}' => $fileName,
                '{message}' => $message,
                '{message_base64}' => base64_encode($message),
            ]
        );

        $output = shell_exec($command);

        preg_match('/ErrorCode: ([^\]]*)\]/', $output, $matches);
        $resOk = (count($matches) == 2) && hexdec($matches[1]) === 0;

        if ($resOk && file_exists($signatureFileName)) {
            $success = true;
            $signature = base64_encode(file_get_contents($signatureFileName));
        }

        return [
            'success' => $success,
            'signature' => $signature,
        ];
    }
}
