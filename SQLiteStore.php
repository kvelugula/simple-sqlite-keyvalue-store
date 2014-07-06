<?php

/**
 * Simple Key-Value SQLite Store for SQLite3 with PHP
 *
 * @author Krishna
 */
class SQLiteStore {

    private $_tableName;
    private $_db;

    const KEY_NAME = 'storeKey';
    const DATA_NAME = 'storeValue';

    public function __construct($tableName = 'default', 
            $dbFilePath = 'default-db.sqlite') {
        $tableName = SQLite3::escapeString($tableName);
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
            throw new Exception('Invalid table name specified');
        } else {
            $this->_tableName = $tableName;
        }

        // TODO: Validate DB file path
        $this->_db = new SQLite3($dbFilePath);

        $newTableSql = sprintf(
                "CREATE TABLE IF NOT EXISTS %s (%s TEXT PRIMARY KEY, %s TEXT)",
                $this->_tableName, self::KEY_NAME, self::DATA_NAME
            );
        if (FALSE === $this->_db->exec($newTableSql)) {
            throw new Exception('Table creation failed');
        }
    }

    public function get($key) {
        $getSql = sprintf(
                "SELECT %s FROM %s WHERE %s = ? LIMIT 1", 
                self::DATA_NAME, $this->_tableName, self::KEY_NAME
            );
        $stmt = $this->_db->prepare($getSql);
        $stmt->bindValue(1, $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        if (FALSE === $result) {
            throw new Exception('Key search failed');
        } else {
            $dataRecord = $result->fetchArray();
            if (FALSE === $dataRecord ){
                // Zero Records Found
                return FALSE;
            } else {
                return $dataRecord[self::DATA_NAME];
            }
        }
    }
    public function set($key, $value) {
        $setSql = sprintf(
                "REPLACE INTO %s (%s, %s) VALUES (?, ?)", 
                $this->_tableName, self::KEY_NAME, self::DATA_NAME
            );
        $stmt = $this->_db->prepare($setSql);
        $stmt->bindValue(1, $key, SQLITE3_TEXT);
        $stmt->bindValue(2, $value, SQLITE3_TEXT);
        if (FALSE === $stmt->execute()) {
            throw new Exception('Key-Value insert failed');
        } else {
            return TRUE;
        }
    }
}
