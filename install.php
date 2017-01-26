<?php 

$responseData = array();
$responseData['install'] = false;

if( isset($_POST['action']) && isset($_POST['host']) && 
    isset($_POST['database']) && isset($_POST['user']) &&
    isset($_POST['password']))
{
    $responseData['install'] = true;
    foreach($_POST as $k => $v)
    {
        $requestData[$k] = trim($v);
    }
}

if($responseData['install'])
{
    if($requestData['host'] == '')
    {
        $responseData['message'] = 'host: Název je příliš krátký.';
        $responseData['install'] = false;
    }
    elseif($requestData['database'] == '')
    {
        $responseData['message'] = 'database: Název je příliš krátký.';
        $responseData['install'] = false;
    }
    elseif($requestData['user'] == '')
    {
        $responseData['message'] = 'user: Název je příliš krátký.';
        $responseData['install'] = false;
    }
    if(!$responseData['install'])
    {
        jsonResponse($responseData);
    }
}

if($responseData['install'])
{
    // test pripojeni
    try {
        $connection = @new PDO( 'mysql:host='.$requestData['host'],
                        $requestData['user'],
                        $requestData['password'],
                        array(  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
    }
    catch(PDOException $ex){
        $responseData['message'] = 'Nelze se připojit k databázi, neplatné přihlašovací údaje či host';
        $responseData['install'] = false;
        jsonResponse($responseData);
    }
    // kontrola databaze
    $database = $connection->query('SHOW DATABASES');
    foreach($database as $db)
    {
        if($db['Database'] == $requestData['database'])
        {
            $responseData['message'] = 'Databáze již existuje.';
            $responseData['install'] = false;
            jsonResponse($responseData);
        }
    }
    $connection->exec('CREATE DATABASE `'.$requestData['database'].'` CHARACTER SET utf8 COLLATE utf8_czech_ci;');
    $connection->exec('USE '.$requestData['database']);
    
    $createTableSQL =   "CREATE TABLE `ares` (
                            `ares_id` int(11) NOT NULL,
                            `ico` varchar(12) COLLATE utf8_czech_ci NOT NULL,
                            `dic` varchar(12) COLLATE utf8_czech_ci DEFAULT NULL,
                            `firma` varchar(255) COLLATE utf8_czech_ci NOT NULL,
                            `ulice` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
                            `cp1` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
                            `cp2` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
                            `mesto` varchar(128) COLLATE utf8_czech_ci NOT NULL,
                            `psc` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
                            `last_update` date NOT NULL
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;";    

    $connection->exec($createTableSQL);
    $connection->exec('ALTER TABLE `ares` ADD PRIMARY KEY (`ares_id`);');
    $connection->exec('ALTER TABLE `ares` MODIFY `ares_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;');

    $configFile = 'config/config.php';
    $configSearch = array(  '<--localhost-->', 
                            '<--database-->',
                            '<--user-->',
                            '<--password-->');
    $configReplace = array( $requestData['host'],
                            $requestData['database'],
                            $requestData['user'],
                            $requestData['password']);
    
    file_put_contents($configFile,str_replace($configSearch, $configReplace, file_get_contents($configFile)));
    
    $responseData['message'] = 'Instalace proběhla úspěšně, pokračujte na <a href="index.php">ARES api</a>';
    jsonResponse($responseData);
}

function jsonResponse($data)
{
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit();
}

?>
<!DOCTYPE html>
<html lang="cs-cz">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <title>ARES api - install</title>
    <script src="js/jquery-3.1.1.js"></script>
    <script type="text/javascript">
$(document).ready(function() {
    
    function showMessage(msg) {
        msg = (typeof msg !== "undefined" && msg != null)? msg : '';
        return (msg != '')? "<h3>"+msg+"</h3>" : '';
    }

    $("#install-button").click(function() {
        $("#message").html('');
        if($("#mysql-host").val().length < 1) {
            $("#message").html(showMessage("host: Název je příliš krátký."));
            return;
        }
        if($("#mysql-database").val().length < 1) {
            $("#message").html(showMessage("database: Název je příliš krátký."));
            return;
        }
        if($("#mysql-user").val().length < 1) {
            $("#message").html(showMessage("user: Název je příliš krátký."));
            return;
        }
        $(this).prop("disabled",true);
        $.ajax({
            method: "POST",
            url: 'install.php',
            dataType: "json",
            data: { action: "install", 
                    host: $("#mysql-host").val(), 
                    database: $("#mysql-database").val(), 
                    user: $("#mysql-user").val(), 
                    password: $("#mysql-password").val() }
        })
        .done(function( ajax ) {
            if(ajax.install == false) {
                $("#install-button").prop("disabled",false);
            }
            $("#message").html(showMessage(ajax.message));
        });
    });

});
    </script>
    <style>
body {
    font-family: Segoe UI, verdana, arial, helvetica, sans-serif;
    font-size: 14px;
}    
a {
    color: blue;
    text-decoration: none;
}
a:hover {
    color: red;
}
fieldset {
    float: left;
}
table {
    border-collapse: collapse;
    width: 100%;
}
td, th {
    text-align: left;
    padding: 3px;
}
    </style>
</head>
<body>
    <h1>ARES api - instalace</h1>
    <div id="message"></div>
    <fieldset>
        <legend>přihlašovaví údaje k MySQL databázi</legend>
        <table>
            <tr>
                <td><label for="mysql-host">host</label></td>
                <td><input type="text" id="mysql-host" size="20" value="localhost" /></td>
            </tr>
            <tr>
                <td><label for="mysql-database">databáze</label></td>
                <td><input type="text" id="mysql-database" size="20" value="" /></td>
            </tr>
            <tr>
                <td><label for="mysql-user">user</label></td>
                <td><input type="text" id="mysql-user" size="20" value="root" /></td>
            </tr>
            <tr>
                <td><label for="mysql-password">password</label></td>
                <td><input type="text" id="mysql-password" size="20" value="" /></td>
            </tr>
            <tr>
                <td></td>
                <td><button id="install-button">install</button></td>
            </tr>
        </table>
    </fieldset>
</body>
</html>
