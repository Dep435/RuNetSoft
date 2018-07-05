<?php

spl_autoload_register(function ($class_name) {
     include_once($class_name . ".php");
});

$server = "127.0.0.1";
$user = "root";
$pwd = "";
$db = "runetsoft";

$link = mysql_connect($server, $user, $pwd) or die("Could not connect: " . mysql_error());
mysql_query("SET NAMES cp1251");
mysql_select_db($db) or die("Could not select database");

echo '<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <body>
    <form action="" method="GET">
      <br><textarea name="tire" cols="100" rows="20"></textarea>
      <br><input type="submit">
    </form>';

if($_GET['tire'] != "") {
    $text = explode(chr(13) . chr(10), $_GET['tire']);
    for($i = 0; $i < count($text); $i++) {
        if(trim($text[$i]) != "") {
            $tire = new WorkTire($text[$i]);
            $tire->writeTire($tire->name, $tire->properties, "tire", "properties");
            unset($tire);
        }
    }
}

echo '</body>
</head>';

?>
