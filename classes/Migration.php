<?php
namespace app\classes;

use yii\console\Exception;

class Migration extends \yii\db\Migration
{
    public $migrationPath;

    public function executeRaw($sql)
    {
        $pdo = $this->db->getMasterPdo();
        try {
            $pdo->exec($sql);
        } catch (\Exception $e) {
            throw $this->db->getSchema()->convertException($e, mb_substr($sql, 0, 255));
        }
    }

    public function executeFile($fileName)
    {
        $sql = $this->readFile($fileName);
        $this->executeRaw($sql);
    }

    public function executeSqlFile($fileName)
    {
        if (!preg_match_all('/host=([\w\.]+);dbname=(\w+)/i', $this->db->dsn, $matches)) {
            throw new Exception("Bad database configuration {$this->db->dsn}");
        }

        $dbHost = $matches[1][0];
        $dbName = $matches[2][0];
        $dbUser = $this->db->username;
        $dbPass = $this->db->password;
        $fullFileName =  $this->getFullFileName($fileName);

        $command = "mysql -h $dbHost -u $dbUser";
        if ($dbPass) {
            $command .= " -p$dbPass";
        }
        $command .= " $dbName < $fullFileName";

        system($command, $result);
        if ($result !== 0) {
            throw new Exception("Error executing sql file");
        }
    }

    public function applyFixture($tableName)
    {
        $fixture = new Fixture();
        $fixture->tableName = $tableName;
        $fixture->dataFile = $this->getFullFileName($tableName . '.php');
        $fixture->load();
    }

    private function readFile($fileName)
    {
        $fullFileName =  $this->getFullFileName($fileName);

        if (!file_exists($fullFileName)) {
            throw new Exception('Can\'t read file. Not exists. ' . $fullFileName);
        }

        if (!is_readable($fullFileName)) {
            throw new Exception('Can\'t read file. Not readable. ' . $fullFileName);
        }

        return file_get_contents($fullFileName);
    }

    private function getFullFileName($fileName)
    {
        return $this->migrationPath . '/data/' . get_class($this) . '/' . $fileName;
    }
}