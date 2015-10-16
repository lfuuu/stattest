1. Install Codeception if it's not yet installed:

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

If you've never used Composer for global packages run `composer global status`. It should output:

```
Changed current directory to <directory>
```

Then add `<directory>/vendor/bin` to you `PATH` environment variable. Now we're able to use `codecept` from command
line globally.

2. Install faker extension by running the following from template root directory where `composer.json` is:

```
composer require --dev yiisoft/yii2-faker:*
```

3. Создать бау данных для тестов:

```
./migration stat-nispd-test/recreate-db
```

4. In order to be able to run acceptance tests you need to start a webserver. The simplest way is to use PHP built in
webserver. In the `web` directory execute the following:

```
env YII_ENV=test php -S 0.0.0.0:8080
```

5. Тесты запускаются следующими коммандами:

```
# запустить все доступные тесты
codecept run

# запустить юнит тесты
codecept run unit

# запустить функциональные тесты
codecept run func

# запустить приемочные тесты через php браузер
codecept run web

# запустить приемочные тесты через silenium
codecept run browser
```

codeception/web.suite.yml
```
class_name: _WebTester
modules:
    enabled:
        - REST
        - PhpBrowser
        - Asserts
    config:
        PhpBrowser:
            url: 'http://192.168.56.101:801/'
            host: '192.168.56.101'
```
