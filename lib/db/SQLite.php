<?php

require_once(__DIR__ . '/AbstractDb.php');

class SQLite extends AbstractDb
{
    public function __construct($db, $hostname = null, $username = null, $password = null, $prefix = '')
    {
        try {
            $this->db = new \PDO('sqlite:' . $db);
            $this->prefix = '';
        } catch (\PDOException $e) {
            _log("SQLite error: " . $e->getMessage());
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    protected function query(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->execute($params);

        $result = array();
        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Loop through results here $data[]
            $result[] = $data;
        }

        $stmt->closeCursor();
        return $result;
    }

    protected function execute(string $sql, array $params): bool
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $result = $stmt->execute($params);
        if ($result) {
            $stmt->closeCursor();
        }
        return $result;
    }
}
