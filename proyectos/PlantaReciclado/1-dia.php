<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

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
	<script type="text/javascript">

	$(function() {

		var d1 = [];
		<?php 
			
			$arreglo="d1 = [";
			$n=0;
			for ($i=0;$i<$nreg ; $i++){ 
			//$arreglo=$arreglo."[ $fechahora[i],$valor[i]] ";
			$n++;
			//$d2=$fechahora[$i];
			$d2=strtotime($fechahora[$i]) * 1000;
			//$d2=$i;
			$d3=$valor[$i];
			//$d3=$t_P1[$i];
		?> 
			d1[<?php echo $n?>] = [ <?php echo $d2?>, <?php echo $d3?> ];
			
			//d1 = [[ <?php echo $d2?>, <?php echo $d3?> ]];
			
		<?php } ?>

		//var d2 = [[1 , 3], [4, 8], [8, 5], [9, 13]];
		var d2 = [];
				<?php 
					
					$arreglo="d2 = [";
					$n=0;
					for ($i=0;$i<$nreg ; $i++){ 
					//$arreglo=$arreglo."[ $fechahora[i],$valor[i]] ";
					$n++;
					//$d2=$fechahora[$i];
					//$d2=$i;
					$d2=strtotime($fechahora[$i]) * 1000;
					$d3=$t_P1[$i];
					//$d3=$t_P1[$i];
				?> 
					d2[<?php echo $n?>] = [ <?php echo $d2?>, <?php echo $d3?> ];
					
					//d1 = [[ <?php echo $d2?>, <?php echo $d3?> ]];
					
				<?php } ?>

		$.plot("#placeholder", [ 
									{ data: d1, label:"Corriente (A)"},
									{ data: d2, label:"Temperatura (C)", yaxis: 2}
								], {
									xaxis: { 
										mode: "time",
										timeformat: "%Y/%m/%d %H:%M:%S"
									},
									yaxes: { min: 0 },
									legend: { position: "ne" }
									});

		$.plot("#placeholder1", [ 
									{ data: d1, label:"Corriente (A)"},
									//{ data: d2, label:"Temperatura (C)", yaxis: 2}
								], {
									xaxis: { 
										mode: "time",
										timeformat: "%Y/%m/%d %H:%M:%S"
									},
									yaxes: { min: 0 },
									legend: { position: "ne" }
									});
			
		$.plot("#placeholder2", [ 
									//{ data: d1, label:"Corriente (A)"},
									{ data: d2, label:"Temperatura (C)", yaxis: 2}
								], {
									xaxis: { 
										mode: "time",
										timeformat: "%Y/%m/%d %H:%M:%S"
									},
									yaxes: { min: 0 },
									legend: { position: "ne" }
									});
			
			
						
	
		/*
		$.plot("#placeholder", [
							{ data: [d1], label: "Corriente(A)" },
							{ data: [d2], label: "Temperatura (C)", yaxis: 2 }
						], {
							xaxes: [ { mode: "time" } ],
							yaxes: [ { min: 0 }, ],
							legend: { position: "sw" }
						});
	
					
	
		function doPlot(position) {
						$.plot("#placeholder", [
							{ data: [ d1 ], label: "Corriente(A)" },
							{ data: [ d2 ], label: "Temperatura (C)", yaxis: 2 }
						], {
							xaxes: [ { mode: "time" } ],
							yaxes: [ { min: 0 }, {
								// align if we are to the right
								alignTicksWithAxis: position == "right" ? 1 : null,
								position: position,
								tickFormatter: euroFormatter
							} ],
							legend: { position: "sw" }
						});
					}
	
		doPlot("right");

		$("button").click(function () {
			doPlot($(this).text());
		});
	*/
	//	$.plot("#placeholder", [ d1 ]);
	//	$.plot("#placeholder2", [ d2 ]);
	
		// Add the Flot version string to the footer

		$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
	});

	</script>
</head>
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
</center>
		
 	
  
 
  
 
		
		
	</div>

	<!-- <img src="icons/stl.png" border=0 width="48px" height="48px"/> -->

	<div id="content">

		<div class="demo-container">
			<h4><center>Corriente generada vs. Temperatura</center></h4>
				<div id="placeholder" class="demo-placeholder"></div>
		</div>
	<br>
	
		<div class="demo-container">
			<h4><center>Corriente generada (A)</center></h4>
				<div id="placeholder1" class="demo-placeholder"></div>
		</div>
	<br>
		<div class="demo-container">
			<h4><center>Temperatura (C)</center></h4>
				<div id="placeholder2" class="demo-placeholder"></div>
		</div>

<!--
	<?php 
			echo "El eje de las ordenadas corresponde al periodo de tiempo comprendido desde: ";
			echo $d2=$fechahora[0];
			echo "  hasta: ";
			echo $d2=$fechahora[$nreg-1];
	?>
-->	
		
	<br>
		<p><center>Los ejes se escalan automaticamente</center></p>

	</div>
	<hr>
	<div id="footer">
<!--	Copyright &copy; 2007 - 2013 IOLA and Ole Laursen-->
			Copyright &copy; 2015 - STL energ&iacuteas renovables
	</div>

</body>
</html>
