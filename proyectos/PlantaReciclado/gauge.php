<?php
	/* Acceso a la base de datos */
    $host="localhost";
    $name = "arduino";
    $passwd = "ArduinO2015";
    $dbname = "subtilis";

	$stat = mysql_connect($host, $name, $passwd);
	if(!$stat)
	{
    die('No pudo conectarse: ' . mysql_error());
	}
	//echo 'Conectado satisfactoriamente';

	mysql_select_db($dbname, $stat);
	
	$q1="select Amps, t_P1, fechahora from planta_reciclado ORDER BY fechahora DESC LIMIT ";	
	
	$q2="8640";
	$q=$q1 . $q2;
	$rs=mysql_query($q); 
	$num=mysql_numrows($rs);
	//$num=10000;
	$nreg=0;
		while($num--)
			{
				$valor[$nreg]=mysql_result($rs,$num,"Amps");
				$t_P1[$nreg]=mysql_result($rs,$num,"t_P1");
				$fechahora[$nreg]=mysql_result($rs,$num,"fechahora");
				//echo $fechahora[$nreg];
				//echo '<br>';
				$nreg++;
			}
	mysql_close($stat);
	
    
?>

<?php
/**
 * Charts 4 PHP
 *
 * @author Shani <support@chartphp.com> - http://www.chartphp.com
 * @version 1.2.3
 * @license: see license.txt included in package
 */
 
include("../../lib/inc/chartphp_dist.php");

$p = new chartphp();

$p->data = array(array(266));
$p->intervals = array(200,300,400,600);
$p->chart_type = "meter";

// Common Options
$p->title = "Meter Gauge Chart";

$out = $p->render('c1');
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="../../lib/js/jquery.min.js"></script>
        <script src="../../lib/js/chartphp.js"></script>
        <link rel="stylesheet" href="../../lib/js/chartphp.css">
    </head>
    <body>
        <div style="width:40%; min-width:450px;">
            <?php echo $out; ?>
        </div>
    </body>
</html> 
