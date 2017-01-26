<?php
require_once '../config/config.php';

if(!isset($_POST['action']) || !isset($_POST['request_data']))
{
    exit();
}

$requestAction  = $_POST['action'];
$requestData    = $_POST['request_data'];

spl_autoload_register(function($className)
{
    $className = str_replace('\\', '/', $className);
    require_once "../class/$className.php";
});

$connection = new Database\MySQL(
        $config['connection']['mysql']['host'],
        $config['connection']['mysql']['db'],
        $config['connection']['mysql']['user'],
        $config['connection']['mysql']['password']);

$ares = new Api\Ares\XML($connection);

switch($requestAction)
{
    case "search-by-ico":
        $ares->getDataByIco($config['ares']['by_ico']['url'], $requestData);
        break;
    case "search-by-name":
        $ares->getDataByName($config['ares']['by_name']['url'], $requestData);
        break;
}

$ares->jsonResponse();