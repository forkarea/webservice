<?php

require('connect_mysql.php');

$mysqlConnector = new MysqlConnector();
$mysqlConnector->createDatabase();
$mysqlConnector->useDatabase();
$mysqlConnector->loadDatabaseSchema();
$mysqlConnector->deleteDatabaseConnection();
