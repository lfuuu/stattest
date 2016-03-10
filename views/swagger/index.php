<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>API Документация</title>

        <link rel="icon" type="image/png" href="/swagger-data/images/favicon-32x32.png" sizes="32x32" />
        <link rel="icon" type="image/png" href="/swagger-data/images/favicon-16x16.png" sizes="16x16" />

        <link href="/css/site.css" media="screen" rel="stylesheet" type="text/css" />
        <link href="/swagger-data/css/typography.css" media="screen" rel="stylesheet" type="text/css" />
        <link href="/swagger-data/css/reset.css" media="screen" rel="stylesheet" type="text/css" />
        <link href="/swagger-data/css/screen.css" media="screen" rel="stylesheet" type="text/css" />
        <link href="/swagger-data/css/reset.css" media="print" rel="stylesheet" type="text/css" />
        <link href="/swagger-data/css/print.css" media="print" rel="stylesheet" type="text/css" />

        <script src="/swagger-data/lib/jquery-1.8.0.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/jquery.slideto.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/jquery.wiggle.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/jquery.ba-bbq.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/handlebars-2.0.0.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/js-yaml.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/lodash.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/backbone-min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/highlight.7.3.pack.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/jsoneditor.min.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/marked.js" type="text/javascript"></script>
        <script src="/swagger-data/lib/swagger-oauth.js" type="text/javascript"></script>
        <script src="/swagger-data/swagger-ui.js" type="text/javascript"></script>

        <script src="/swagger-data/lang/translator.js" type="text/javascript"></script>
        <script src="/swagger-data/lang/ru.js" type="text/javascript"></script>

        <script type="text/javascript">
        $(function () {
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
                    if (typeof initOAuth == 'function') {
                        initOAuth({
                            clientId: "your-client-id",
                            clientSecret: "your-client-secret-if-required",
                            realm: "your-realms",
                            appName: "your-app-name",
                            scopeSeparator: ",",
                            additionalQueryStringParams: {}
                        });
                    }

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
        });
        </script>
    </head>

    <body class="swagger-section">
        <div id="header">
            <div class="swagger-ui-wrap">
                <a class="logo" style="margin: 0;" href="/"></a>
                <form id="api_selector">
                    <div class="input">
                        <label>JSON документация</label><br />
                        <input placeholder="https://<?= $host . $documentationPath; ?>" id="input_baseUrl" name="baseUrl" type="text" />
                    </div>
                    <div class="input">
                        <label>API Key</label><br />
                        <input placeholder="api_key" id="input_apiKey" name="apiKey" type="text" value="<?= $apiKey; ?>" />
                    </div>
                    <div class="input">
                        <br /><a id="explore" href="#" data-sw-translate="">Explore</a>
                    </div>
                </form>
            </div>
        </div>

        <div id="message-bar" class="swagger-ui-wrap" data-sw-translate="">&nbsp;</div>
        <div id="swagger-ui-container" class="swagger-ui-wrap"></div>
    </body>
</html>
