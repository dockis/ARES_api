<?php

namespace Database;

use \PDO;

class MySQL
{
    private $connection;

    private $settings = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    );

    /** Vytváří připojení na databázi.
     * @param $host // host - localhost apd.
     * @param $db // název databáze
     * @param $user // uživatelskéjméno
     * @param $password // uživatelské heslo
     * @return PDO // instance PDO
     */
    public function __construct($host, $db, $user, $password)
    {
        if(!isset($this->connection))
        {
            $this->connection = new PDO("mysql:host=".$host.";dbname=".$db, $user, $password, $this->settings);
        }
        return $this;
    }

    /** Předává dotaz a parametry instanci PDO
     * @param $sql // MySql dotaz - string
     * @param array $param // předávané parametry - array
     * @return mixed // vrací výsledek dotazu
     */
    public function query($sql, $param = array())
    {
        $q = $this->connection->prepare($sql);
        $q->execute($param);
        return $q->rowCount();
    }

    public function queryOne($sql, $param = array())
    {
        $q = $this->connection->prepare($sql);
        $q->execute($param);
        return $q->fetch();
    }

    public function queryAll($sql, $param = array())
    {
        $q = $this->connection->prepare($sql);
        $q->execute($param);
        return $q->fetchAll();
    }

    public function getLastId()
    {
        return $this->connection->lastInsertId();
    }
}