<?php
namespace app\commands;

use Yii;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class MigrateController extends \yii\console\controllers\MigrateController
{
    public $templateFile = '@app/views/migration.php';
    public $migrationPath = '@app/migrations/migrations';
    public $fixturePath = '@app/migrations/fixtures';

    protected function createMigration($class)
    {
        if (substr($class, 0, 1) == 'f') {
            $file = $this->fixturePath . DIRECTORY_SEPARATOR . $class . '.php';
            $path = $this->fixturePath;
        } else {
            $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
            $path = $this->migrationPath;
        }

        require_once(Yii::getAlias($file));

        $migration = new $class(['db' => $this->db]);
        $migration->migrationPath = $path;

        return $migration;
    }

    /**
     * Recreates database
     */
    public function actionRecreateDb()
    {
        if (YII_ENV == "test" || $this->confirm("Recreate database \"" . $this->db->dsn . "\"?")) {

            $this->recreateDatabase();

            // Чтобы тесты проходили быстрее надо периодически (раз в несколько месяцев)
            //      codeception/bin/db migrate/recreate-db
            //      mysqldump --user=root --password --host=localhost nispd_test --opt --force --no-autocommit > ~/www/stat/migrations/migrations/data/m100000_000001_init/nispd.sql
            //      убрать из него m100000_000001_init, иначе будет дубль
            //      удалить все старые (более 1-2 месяцев) миграции

            // восстановить из дампа
            // первичную миграцию нельзя накатить обычным образом, иначе он потом будет накатывать все миграции повторно (и, очевидно, ошибка будет)
            $this->migrateUp('m100000_000001_init');

            if (!$this->applyMigrations()) {
                echo "\nMigration failed. The rest of the migrations are canceled.\n";

                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (!$this->applyFixtures()) {
                echo "\nMigration failed. The rest of the fixtures are canceled.\n";

                return ExitCode::UNSPECIFIED_ERROR;
            }

            echo "Database successfully recreated.\n";
        }
    }

    private function recreateDatabase()
    {
        $this->db->close();

        if (!preg_match_all('/;dbname=(\w+)/i', $this->db->dsn, $matches)) {
            throw new Exception("Bad database configuration {$this->db->dsn}");
        }

        $dbName = $matches[1][0];
        $dsnWithoutDb = str_replace($matches[0][0], '', $this->db->dsn);

        $connection =
            new \yii\db\Connection([
                'dsn' => $dsnWithoutDb,
                'username' => $this->db->username,
                'password' => $this->db->password,
                'charset' => $this->db->charset,
            ]);


        $this->dropActiveConnections($connection, $dbName);


        $connection->createCommand('
            DROP DATABASE IF EXISTS ' . $dbName . '
        ')->execute();

        $connection->createCommand('
            CREATE DATABASE ' . $dbName . '
        ')->execute();

        $connection->createCommand('
            ALTER DATABASE ' . $dbName . ' CHARACTER SET utf8 COLLATE utf8_general_ci;
        ')->execute();
    }

    protected function dropActiveConnections(Connection $connection, $dbName)
    {
    }

    private function applyMigrations()
    {
        foreach ($this->getNewMigrations() as $migration) {
            if (!$this->migrateUp($migration)) {
                return false;
            }
        }
        return true;
    }

    private function applyFixtures()
    {
        $fixturesMigrations = $this->getFixturesMigrations();
        foreach ($fixturesMigrations as $migration) {
            if (!$this->migrateUp($migration)) {
                return false;
            }
        }
        return true;
    }

    private function getFixturesMigrations()
    {
        $filesToSearch = ['f*.php'];

        $files = FileHelper::findFiles(Yii::getAlias($this->fixturePath), ['only' => $filesToSearch]);
        $foundFixtures = [];

        foreach ($files as $fixture) {
            $foundFixtures[] = basename($fixture, '.php');
        }

        return array_reverse($foundFixtures);
    }

    public function actionFlushSchema($db = 'db')
    {
        $connection = Yii::$app->get($db, false);
        if ($connection === null) {
            $this->stdout("Unknown component \"$db\".\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$connection instanceof \yii\db\Connection) {
            $this->stdout("\"$db\" component doesn't inherit \\yii\\db\\Connection.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
//        } elseif (!$this->confirm("Flush cache schema for \"$db\" connection?")) {
//            return ExitCode::OK;
        }

        try {
            $schema = $connection->getSchema();
            $schema->refresh();
            $this->stdout("Schema cache for component \"$db\", was flushed.\n\n", Console::FG_GREEN);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . "\n\n", Console::FG_RED);
        }
    }

}
