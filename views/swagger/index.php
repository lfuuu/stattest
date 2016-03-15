<?php

use app\classes\Html;
use app\assets\SwaggerUiAsset;

SwaggerUiAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <base href="/" />
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <link href="/css/site.css" media="screen" rel="stylesheet" type="text/css" />
        <script type="text/javascript">
        jQuery(document).ready(function() {
            var
                url = window.location.search.match(/url=([^&]+)/),
                apiKeyField = $('#input_apiKey');

            if (url && url.length > 1) {
                url = decodeURIComponent(url[1]);
            } else {
                url = '<?= $documentationPath; ?>';
            }

            apiKeyField
                .on('change', function() {
                    var key = $(this).val();

                    if(key && key.trim() != '') {
                        window.swaggerUi.api.clientAuthorizations.add('api_key', new SwaggerClient.ApiKeyAuthorization('Authorization', 'Bearer ' + key, 'header'));
                        log('Added key ' + key);
                    }
                });

            if(window.SwaggerTranslator) {
                window.SwaggerTranslator.translate();
            }

            window.swaggerUi = new SwaggerUi({
                url: url,
                dom_id: 'swagger-ui-container',
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function(swaggerApi, swaggerUi) {
                    $('li.operation').on('click', function(e) {
                        e.preventDefault();
                    });

                    if(window.SwaggerTranslator) {
                        window.SwaggerTranslator.translate();
                    }

                    apiKeyField.trigger('change');
                },
                onFailure: function(data) {
                    log('Unable to Load SwaggerUI');
                },
                docExpansion: 'none',
                jsonEditor: false,
                apisSorter: 'alpha',
                defaultModelRendering: 'schema',
                showRequestHeaders: false
            });

            window.swaggerUi.load();

            function log() {
                if ('console' in window) {
                    console.log.apply(console, arguments);
                }
            }
        })
        </script>
    </head>
    <body class="swagger-section">

    <?php $this->beginBody() ?>

        <div id="header" style="background-color: #F0F0F0;">
            <div class="swagger-ui-wrap">
                <a class="logo" style="margin: 0;" href="/"></a>
                <form id="api_selector">
                    <div class="input">
                        <label>JSON документация</label><br />
                        <input placeholder="<?= $documentationPath; ?>" id="input_baseUrl" name="baseUrl" type="text" />
                    </div>
                    <div class="input">
                        <label>API Key</label><br />
                        <input placeholder="api_key" id="input_apiKey" name="apiKey" type="text" value="<?= $apiKey; ?>" />
                    </div>
                    <div class="input">
                        <br /><a id="explore" href="#" data-sw-translate="" style="background-color: #000033;">Explore</a>
                    </div>
                </form>
            </div>
        </div>

        <div id="message-bar" class="swagger-ui-wrap" data-sw-translate="">&nbsp;</div>
        <div id="swagger-ui-container" class="swagger-ui-wrap"></div>

        <?php $this->endBody() ?>

    </body>
</html>
<?php $this->endPage() ?>

