<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html> 

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<!--<img src="/var/www/subtilis/html/PlantaReciclado/icons/STL.png" alt="[ Powered by Apache ]">-->
	<title>STL - Planta Reciclado</title>
	<link href="flot/examples/examples.css" rel="stylesheet" type="text/css">
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="../../excanvas.min.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="flot/jquery.flot.time.js"></script>
	<script type="text/javascript"></script>

</head>

<style>
a:link    {color:DarkViolet; background-color:transparent; text-decoration:none}
a:visited {color:pink; background-color:transparent; text-decoration:none}
a:hover   {color:blue; background-color:transparent; text-decoration:underline}
a:active  {color:violet; background-color:transparent; text-decoration:underline}
</style>

<body> 
 	<div id="header">
	
	<center>
 <table>
  <tr>
    <th><img src="icons/stl.png" border=0 width="100px" height="100px"/></th>
        <th></th>
	<th><h2 style="color:black;"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Planta de Reciclado&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></h2></th>
	<th><img src="icons/MR.jpg" border=0 width="100px" height="100px"/></th>
  </tr>

</table> 
</center>
		<hr> 

		 <table>
  <tr>
    <th><a href="index.php"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inicio&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></a></th>
    <th><a href="10-min.php"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;10 minutos&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></a></th>
	<th><a href="1-hora.php"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 hora&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></a></th>
	<th><a href="1-dia.php"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 d&iacutea&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></</a></th>
	<th><a href="10-dias.php"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;10 d&iacuteas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></a></th>
	<th><a href="mostrar_tabla.php"><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mostrar tabla&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></a></th>
  </tr>
</table> 
		<br>
<?php 
  $host='localhost';
 //   $host='www.subtilis.com.ar';
 //  $name = 'fede'; //usuario
  // $passwd = 'fede'; //contraseña
   //$dbname = 'temporal'; //nombre de la DB
 $name = 'arduino'; //usuario
 $passwd = 'ArduinO2015'; //contraseña
 $dbname = 'subtilis'; //nombre de la DB

    // Connect to Database and select the database to use
    $dbi = mysql_connect($host, $name, $passwd) or die('No pudo conectarse a la DB. Error :' . mysql_error());
	
mysql_select_db($dbname, $dbi); 

$result = mysql_query("SELECT id,Amps,t_P1,fechahora FROM planta_reciclado ORDER BY fechahora DESC LIMIT 10000", $dbi); 
if ($row = mysql_fetch_array($result)){ 
   echo "<center><table style='width:100%' border = '2'> \n"; 
   echo "<tr><td align = 'center'>Sensor</td><td align = 'center'>Corriente</td><td align = 'center'>Temperatura</td><td align = 'center'>Fecha y Hora</td></tr> \n"; 
   do { 
      echo "<tr><td align = 'center'>".$row["id"]."</td><td align = 'center'>".$row["Amps"]."</td><td align = 'center'>".$row["t_P1"]."</td><td align = 'center'>".$row["fechahora"]."</td></tr> \n"; 
   } while ($row = mysql_fetch_array($result)); 
   echo "</table></center> \n"; 
} else { 
echo "¡ No se ha encontrado ningún registro !"; 
} 
?> 
  
</body> 
</html>