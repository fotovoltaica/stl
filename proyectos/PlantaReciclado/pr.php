<?php
$id=$_GET["id"];
$Amps=$_GET["Amps"];
$t_P1=$_GET["t_P1"];
$t_P2=$_GET["t_P2"];
$t_P3=$_GET["t_P3"];

    $host='localhost';
    $name = 'arduino'; //usuario
    $passwd = 'ArduinO2015'; //contraseña
    $dbname = 'subtilis'; //nombre de la DB

    // Connect to Database and select the database to use
    $dbi = mysql_connect($host, $name, $passwd) or die('No pudo conectarse a la DB. Error :' . mysql_error());
    mysql_select_db($dbname,$dbi);

    $sql='INSERT INTO planta_reciclado (id,Amps,t_P1,t_P2,t_P3,fechahora) VALUE ('.$id.','.$Amps.','.$t_P1.','.$t_P2.','.$t_P3.', NOW() ) ';
    $result = mysql_query( $sql , $dbi )  or die ('Error' . mysql_error()) ;
    echo 'OK';

$sql = "SELECT id, Amps, t_P1, t_P2, t_P3, fechahora\n"
    . "FROM planta_reciclado\n"
    . "ORDER BY fechahora DESC\n"
    . "LIMIT 5";	
?>
