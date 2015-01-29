<?php
namespace app\commands;

use Yii;
use yii\console\Exception;
use yii\db\Connection;
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
        if ($this->confirm("Recreate database \"" . $this->db->dsn . "\"?")) {

            $this->recreateDatabase();

            if (!$this->applyMigrations()) {
                echo "\nMigration failed. The rest of the migrations are canceled.\n";

                return self::EXIT_CODE_ERROR;
            }

            if (!$this->applyFixtures()) {
                echo "\nMigration failed. The rest of the fixtures are canceled.\n";

                return self::EXIT_CODE_ERROR;
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

}