<!DOCTYPE html>
<html lang="cs-cz">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <title>ARES api</title>
    <script src="js/jquery-3.1.1.js"></script>
    <script src="js/main.js"></script>
    <script src="js/AresTable.js"></script>
    <link rel="stylesheet" href="css/main.css" type="text/css" />
</head>
<body>
    <h1>ARES api</h1>
    <fieldset>
        <legend>hledání podle IČO</legend>
        <input type="text" id="ico" size="10" value="" />
        <button id="search-by-ico">hledej</button>
    </fieldset>
    <fieldset>
        <legend>hledání podle názvu firmy</legend>
        <input type="text" id="name" size="30" value="" />
        <button id="search-by-name">hledej</button>
    </fieldset>
    <hr class="cleaner" />
    <div id="test"></div>
    <div id="content"></div>
</body>
</html>