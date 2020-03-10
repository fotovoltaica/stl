<?php 	
/*
 * Copyright (C) 2006-2009 David Giardini
 * 12/209: Agregados para zoom con libreria flot por Ruben Pennino
 * Todos los derechos reservados - All rigths reserved
 */
session_start();
$DEBUG=0;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<!--[if lt IE 7.]>
<script defer type="text/javascript" src="/js/fixpng.js"></script>
<![endif]-->
<title>Trending</title>
<link rel="stylesheet" type="text/css" href="/css/my_style.css">
<script language="javascript">
function dept_onchange(SubmitSelect) 
{
   SubmitSelect.submit();
}
function SetHoraTxtBox(a,b,c,d)
{
	var item1=document.getElementById('hini');
	var item2=document.getElementById('hfin');
	var item3=document.getElementById('mini');
	var item4=document.getElementById('mfin');
	item1.value=a;
	item2.value=b;
	item3.value=c;
	item4.value=d;
}
function ValidarHora()
{
	var item1=document.getElementById('hini');
	var item2=document.getElementById('hfin');
	var item3=document.getElementById('mini');
	var item4=document.getElementById('mfin');
	var val1=item1.value;	var val2=item2.value;
	var val3=item3.value;	var val4=item4.value;
  // Horas
	if (!IsNumeric(val1))		item1.value=0;
	if (!IsNumeric(val2))		item2.value=23;
	// Minutos
	if (!IsNumeric(val3))		item3.value=0;
	if (!IsNumeric(val4))		item4.value=59;
  // Horas
	if(val1 < 0)	item1.value=0;
	if(val1 > 23)	item1.value=23;
	if(val2 < 0)	item2.value=0;
	if(val2 > 23)	item2.value=23;
	// Minutos
	if(val3 < 0)	item3.value=0;
	if(val3 > 59)	item3.value=59;
	if(val4 < 0)	item4.value=0;
	if(val4 > 59)	item4.value=59;
}
function IsNumeric(strString)
{
   var strValidChars = "0123456789",strChar,blnResult= true,i;
   if (strString.length == 0) return false;
   for (i = 0; i < strString.length && blnResult == true; i++)
   {
      strChar = strString.charAt(i);
      if (strValidChars.indexOf(strChar) == -1)
         blnResult = false;
   }
   return blnResult;
}

</script>
<script language="javascript" type="text/javascript" src="js/excanvas.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.navigate.js"></script> 
<style>
    #placeholder .button,     #placeholder0 .button,    #placeholder1 .button,    #placeholder2 .button,    #placeholder3 .button {
        position: absolute;
        cursor: pointer;
    }
    #placeholder div.button,#placeholder0 div.button,#placeholder1 div.button,#placeholder2 div.button,#placeholder3 div.button {
        font-size: smaller;
        color: #999;
        background-color: #eee;
        padding: 2px;
    }
	#tooltip {
		position: absolute;
		padding: 4px;
		opacity: 0;
		-moz-border-radius: 4px;
		-webkit-border-radius: 4px;
	}

</style>

</head>
<body>
<div style='background-color:Lavender;border:solid 1px DarkSlateGray;margin-bottom:2px'><h1 style='text-align:center;color:#22A;font-size:1.8em;font-style:italic;margin:10px 0px;text-shadow: 0.05em 0.05em 0.1em gray'>Consulta de Hist&oacute;ricos</h1></div>
<?php
include("../header.php");
require_once ('../Calendar/cal_class.php');
$esta_pagina="d_trend.php";
$USAR_PHPLOT=0;
if($USAR_PHPLOT)
{
    include_once('./phplot.php');  
}
$MAX_REGS_EXPORTAR=10000;
/* Pasa tiempo de formato latino a USA y devuelve time_t */
function parsedate($value)
{
    // If it looks like a UK date dd/mm/yy, reformat to US date mm/dd/yy so strtotime can parse it.
    $reformatted = preg_replace("/^\s*([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $value);
    return strtotime($reformatted);
}



$refer= $_SERVER['HTTP_REFERER'];

$IMG_W=1200;
$IMG_H=450;



/* Acceso a la base de datos */

$USAR_LUPA=0;
$user="usuario";
$password="";
$database="webtrend";
$stat=mysql_connect("localhost",$user, $password);
if($stat)
	@mysql_select_db($database) or die("<b>Cannot connect to database $database </b><br></br>");
else
	die("<b>Dying. Cannot connect to database $database </b><br></br>");


/* Veo si viene un time_span, sino coloco por defecto */
$time_span=$_POST['span_combo'];
if(strlen($time_span)< 1)
	$time_span=3; // 6 horas
$combo_span_cambio=0;
if (!isset($_SESSION['span_combo'])) 
   $_SESSION['span_combo'] = $time_span; // Guardo por primera vez el span del combo
else
{
	if($_SESSION['span_combo']!=$time_span) // Cambio desde el combo
	{
		$_SESSION['span_combo'] = $time_span; // Guardo de vuelta
		$combo_span_cambio=1;
	}
			
}


/* Para seleccionar un tag  arbitrario de la BD */
$tag_selector_get=0;
if(isset($_GET['tag_selector']))
{
	// Veo que tipo de seleccion de tags me piden. Un numero= todos, d discretos, a analogicos. 
	$tipo_tags=$_GET['tag_selector'];
	$tag_selector_get=1;
}
/* Pocesamiento POST-GET  */
$title_get=$_GET['title'];
if (strlen($title_get)<1)
{
	$title_get="(Sin titulo)";
}
$tagnames=array();
$n_graphs=0;
$MAX_GRAPHS=6;
if($tag_selector_get) 
{
	// Busco los posibles tags seleccionados de los combos
	for($i=0;$i<$MAX_GRAPHS;$i++)
	{
		$combo_ver="multi_selector_$i";
		$aux=$_POST["$combo_ver"];
		$tagnames_raw[$i]=$aux;
		if (strlen($aux) > 0 && ($aux!="---"))
		{
			$tagnames[$n_graphs]=$aux;
			$n_graphs++;
		}
	}
}
else
{
	for($j=0;$j<$MAX_GRAPHS;$j++)
	{
		$tag_ver="tag$j";
		$aux=$_GET["$tag_ver"];
		if (strlen($aux) > 0)
		{
			$tagnames[$n_graphs++]=$aux;
		}
	}

}

if(isset($_GET['agrupar']))
	$agrupar=$_GET['agrupar'];
elseif(isset($_POST['agrupar']))
	$agrupar=$_POST['agrupar'];
	

// **************************************************************
// Formulario con  los botones de ajuste y zoom
echo "<table style='background-color:#e5e5e5;border:solid DarkSlateGray 1px;color:#330;margin-bottom:3px;font-style:italic;padding:0px 2px;font-size:12px'>";
echo "<tr><td>";
	$accion="$esta_pagina?".$_SERVER['QUERY_STRING'];
	// Veo si venía de un query por fecha la consulta anterior por default
	if(isset($_POST['query_por_fecha_x'])) 
	{
		$dini=$_POST['finicio'];
		$dfin=$_POST['ffin'];
	}
	else
	{
		$dini=date("d/m/Y");
		$dfin=date("d/m/Y");
		$hfinaux=date("H"); // Hora actuales
		$mfinaux=date("i"); // Minutos actuales
	}
	echo "<FORM NAME='myForm' ACTION='$accion' METHOD='POST' style='margin:0px;padding:0px'>";
	echo "<script language='javascript' type='text/javascript' src='../Calendar/calendar.core.js'></script>";
	/* Agrego los calendarios */
	echo "<div style='display:inline;margin:0px 5px 0px 0px;position:relative;top:-10px'>";
	echo "Fecha";
	$calendario=new Calendar("it");
	$calendario->grandezza_carattere="Small";
	$calendario->sfondo_settimana="Darkkhaki";
	$calendario->CreateCalendar($dini,"finicio",date("m/d/Y"),"12/31/2006");
    echo " Hora <input id='hini' style='font-size:xx-small;height:22px;width:22px' type=text maxlength='2' size=2 name='hini' onchange='return ValidarHora();' value=00>";
    echo " Min <input id='mini' style='font-size:xx-small;height:22px;width:22px' type=text maxlength='2' size=2 name='mini' onchange='return ValidarHora();' value=00>";
    echo "</div>";
	echo "<div style='padding-left:4px;display:inline;margin:0px;position:relative;top:-10px'>";
	echo "Fecha Fin";
	$calendario=new Calendar("it");
	$calendario->grandezza_carattere="Small";
	$calendario->sfondo_settimana="Darkkhaki";
	$calendario->CreateCalendar($dfin,"ffin",date("m/d/Y"),"12/31/2006");
    echo " Hora <input id='hfin' style='font-size:xx-small;height:22px;width:22px' type=text maxlength='2' size=2 name='hfin' onchange='return ValidarHora();' value=$hfinaux>";
    echo " Min. <input id='mfin' style='font-size:xx-small;height:22px;width:22px' type=text maxlength='2' size=2 name='mfin' onchange='return ValidarHora();' value=$mfinaux>";
    echo "<input type='image' src='/images/search.png' alt='Consultar' name='query_por_fecha' value='1' title='Consultar por fechas utilizando el calendario' class='btn-sgp' style='width:37px;height:33px;position:relative;top:14px;margin-left:15px;'>\n";
    echo "</div>";
echo "</td></tr>";
echo "</table>";

	// Armo el combo para elegir el rango de fechas
echo "<table style='background-color:#ddd;border:solid DarkSlateGray  1px;color:#330;font-style:italic;padding:2px;font-size:12px;'>";
echo "<tr><td>";
	if($USAR_LUPA)
        echo "<input type=image src='/images/adjust.png' name='query' value='1' title='Ajustar' onclick='Submit()' style='width:27px;height:32px;'></input>\n";
	echo "Espacio de Tiempo: <select name='span_combo' languaje=javascript onchange='return document.myForm.submit()'>\n";
	// Pregunto cada vez para ver si debe estar seleccionado
	for($pp=0;$pp<9;$pp++)
	{
		if($time_span==$pp)
			$selected[$pp]="selected";
		else
			$selected[$pp]="";
	}
	echo "<option value='0' $selected[0]>1 min</option>\n";
	echo "<option value='1' $selected[1]>5 min</option>\n";
	echo "<option value='2' $selected[2]>10 min</option>\n";
	echo "<option value='3' $selected[3]>1 hora</option>\n";
	echo "<option value='4' $selected[4]>6 horas</option>\n";
	echo "<option value='5' $selected[5]>12 horas</option>\n";
	echo "<option value='6' $selected[6]>1 dia</option>\n";
	echo "<option value='7' $selected[7]>10 dia</option>\n";
	echo "<option value='8' $selected[8]>1 mes</option>\n";
	echo "</select>";
	echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";

echo "</td><td>";
        echo "<input type='image' src='/images/zoom+.png' name='zoom_in' value='1' title='Zoom in' class='btn-sgp'  style='width:37px;height:33px;'>\n";
echo "</td><td>";
	echo "<input type='image' src='/images/zoom-.png' name='zoom_out' value='1' title='Zoom out' class='btn-sgp' style='width:37px;height:33px;padding-right:15px;'>\n";
echo "</td><td>";
        echo "<input type='image' src='/images/previous.png' name='shift_rigth' value='1' title='Retroceder en el tiempo' class='btn-sgp'  style='width:37px;height:33px;'>\n";
echo "</td><td>";
        echo "<input type='image' src='/images/next.png'  name='shift_left' value='1' title='Avanzar en el tiempo' class='btn-sgp'  style='width:37px;height:33px;padding-right:15px;'>\n";
echo "</td><td>";
        echo "<input type='image' src='/images/save.png'  name='exportar' value='1' title='Exportar datos' class='btn-sgp' style='width:37px;height:33px;padding-right:15px;'>\n";
echo "</td>";
echo "<td>";
        echo "<input type='image' src='/images/graph.png' name='exportar_grafica' value='1' title='Exportar valores de la gráfica' class='btn-sgp'   style='width:37px;height:33px;padding-right:9px;'>\n";
echo "</td>";

echo "</tr></table>";
if($tag_selector_get) // Armo el combo del selector de tags
{
	if($tipo_tags=="a") // analogicos
		$twhere=" AND id_tag_types <> 1";
	else if($tipo_tags=="d") // discretos
		$twhere=" AND id_tag_types = 1 ";
	else
		$twhere="";
	$query ="select tagname from t_tags WHERE 1=1 $twhere AND active=1 order by tagname asc";
	$rs=mysql_query($query);
	$num=mysql_numrows($rs);
	$i=0;
	$all_tags[0]="---";
	while($i < $num  )
	{
		$actual_tag=mysql_result($rs,$i,"tagname");
		$all_tags[$i+1]=$actual_tag;
		$i++;	
	}
	echo "<table style='background-color:#ddd;border:solid DarkSlateGray 1px;color:#330;font-style:italic;padding:2px;font-size:12px;margin-top:3px;'><tr>";
	for($i=0;$i < $MAX_GRAPHS; $i ++)
	{
		echo "<td>\n";
		echo "<select name='multi_selector_$i' style='font-size:10;'>\n";
		foreach($all_tags as $aux)
		{
			if($tagnames_raw[$i]==$aux) // Este estaba seleccionado
				$selected="selected";
			else
				$selected="";
			echo "<option value='$aux' $selected >$aux</option>\n";
		}
		echo "</select>\n";
		echo "</td>";
	}
	echo "<td>";
	if($agrupar)
		$ckecked_agrupar="checked";
	else
		$ckecked_agrupar="";
        echo "Agrupar <input type='checkbox' $ckecked_agrupar name='agrupar' value='1' title='Agrupar Tags'></input>\n";
	echo "</td>";
	echo "<td>";
        echo "<input type='image' src='/images/search.png'  name='seleccion_multiple' value='1' class='btn-sgp' title='Seleccion Multiple'>\n";
	echo "</td>";
	echo "</tr></table>";
}
// Para volver
if(strpos(strtolower($refer),'trend')<=0) // No viene del propio trending
{
	// Guardo la pagina de referencia 	
	echo "<input type='hidden' name='refer' value='$refer'></input>";
	$volver_a=$refer;
	 // Como no viene del propio trending reseteo las variables de sesion
   $_SESSION['start_time'] = -1;
   $_SESSION['end_time'] = -1;
	
}
else	// Viene del trending
{
	// re-obtengo la pagina de referencia y la vuelvo a guardar
	$volver_a=$_POST['refer'];
	echo "<input type='hidden' name='refer' value='$volver_a'></input>";
}
echo "</FORM>";
//Linea division
//echo "<div style='border-bottom:1px solid rgb(18, 18, 18);box-shadow: 0px 1px rgb(42, 42, 42);margin: 20px 0px;'></div>";
//Constantes
$POINTS=500;
$ZOOM_FACTOR=2;
$SHIFT_FACTOR=4;

if (!isset($_SESSION['start_time'])) 
   $_SESSION['start_time'] = -1;
if (!isset($_SESSION['end_time'])) 
   $_SESSION['end_time'] = -1;

/* Pocesamiento POST-GET Especificar titulos graficas */
if($tag_selector_get) // Si uso el selector de tags, los titulos son los taganmes
{
	$i=0;
	foreach($tagnames as $aux)
		$titles[$i++]=$aux;
}
else
{
	$titles[0]=$_GET['title_1'];
	$titles[1]=$_GET['title_2'];
	$titles[2]=$_GET['title_3'];
	$titles[3]=$_GET['title_4'];
	$titles[4]=$_GET['title_5'];
}
// Si hago click en el combo restauro el zoom. Agrego el cambio en el combo
if($_POST['query_x'] || $combo_span_cambio)
{
	$_SESSION['start_time']=-1;
	$_SESSION['end_time']=-1;
}

if($DEBUG)
	$ahora=time(); // registro del inicio

// recupero los valores de inicio y fin de consulta
$end_time=$_SESSION['end_time'];
$start_time=$_SESSION['start_time'];
$shift_left=$_POST['shift_left_x'];
$shift_rigth=$_POST['shift_rigth_x'];
$zoom_in=$_POST['zoom_in_x'];
$zoom_out=$_POST['zoom_out_x'];
$exportar=$_POST['exportar_x'];
$exportar_grafica=$_POST['exportar_grafica_x'];
$query_por_fecha=$_POST['query_por_fecha_x'];


// Paso todo a segundos 
switch($time_span)
{
	case 0: $span=60;		break;	// 1 minuto
	case 1: $span=5*60;		break;	// 5 minutos
	case 2: $span=10*60;		break;	// 10 minutos
	case 3: $span=3600; 		break;	// 1 hora
	case 4: $span=6*3600;		break;	// 6 horas
	case 5: $span=12*3600;		break;	// 12 horas
	case 6: $span=86400;		break;	// 1 dia
	case 7: $span=10*86400;		break;	 // 10 dias
	case 8: $span=30*86400; 	break;	 // 1 mes
}


// Inicializacion, debo ajustar los tiempos al span
if(($end_time==-1) || ($start_time==-1))
{
	$end_time=time(NULL);	
	$start_time=time(NULL)-$span;
}

$now=time(NULL);
$mid=($end_time+$start_time)/2; // Punto medio del zoom
$half_interv=($end_time - $start_time)/2;

if($zoom_out)
{
	$tmp_end_time=$mid + $half_interv * $ZOOM_FACTOR;
	$tmp_start_time=$mid - $half_interv  * $ZOOM_FACTOR;
	// Caso de que se fue a futuro
	if($tmp_end_time > $now)
	{
		$end_time=$now;
		$start_time=$now-2*$half_interv*$ZOOM_FACTOR ;
	}
	else
	{
		$end_time=$tmp_end_time;
		$start_time=$tmp_start_time;		
	}
}
if($zoom_in)
{
	$tmp_end_time=$mid + $half_interv / $ZOOM_FACTOR;
	$tmp_start_time=$mid - $half_interv  / $ZOOM_FACTOR;
	// Caso de que se fue a futuro
	if($tmp_end_time > $now)
	{
		$end_time=$now;
		$start_time=$now-2*$half_interv / $ZOOM_FACTOR ;
	}
	else
	{
		$end_time=$tmp_end_time;
		$start_time=$tmp_start_time;		
	}
}

if($shift_left)
{
	$tmp_end_time=$end_time+$half_interv*2/$SHIFT_FACTOR;
	$tmp_start_time=$start_time+$half_interv*2/$SHIFT_FACTOR;
	// Si s e va a futuro ...
	if($tmp_end_time > $now)
	{
		$end_time=$now;
		$start_time=$now-$half_interv*2;
	}
	else // Si no ....
	{
		$end_time=$tmp_end_time;
		$start_time=$tmp_start_time;
	}
}

if($shift_rigth)
{
	$end_time=$end_time-$half_interv*2/$SHIFT_FACTOR;
	$start_time=$start_time-$half_interv*2/$SHIFT_FACTOR;
}

if(isset($query_por_fecha)) // Los tiempos son por fecha, usando el calendario
{
	//echo "QUERY POR FECHA";
	$finicio=$_POST['finicio'];
	$ffin=$_POST['ffin'];
	$hini=$_POST['hini'];
	$hfin=$_POST['hfin'];
	$mini=$_POST['mini'];
	$mfin=$_POST['mfin'];
	// Seteo los valores en los textboxes
	echo "<script>SetHoraTxtBox($hini,$hfin,$mini,$mfin);</script>";
	$start_time=parsedate($finicio) +  3600 *$hini + 60 *$mini;
	$end_time=parsedate($ffin) +  3600 *$hfin + 60 *$mfin+59;
}

// Almaceno las variables de sesion
$_SESSION['start_time']=$start_time;
$_SESSION['end_time']=$end_time;

// Armo escala de tiempo
$delta_time=($end_time-$start_time)/$POINTS;
if($delta_time <= 0)
	die("Escala de tiempos erronea");

// Relleno el array de data con los valores de tiempo espaciados y cero por defecto
for($i=0;$i<$POINTS;$i++)
{
	$tiempos[$i]=($start_time + $i*$delta_time);
	$tiempos[$i]=strftime("%d/%m/%Y\n%H:%M:%S",$tiempos[$i]);
}

//echo $start_time."   ".$end_time; die();
if(($end_time-$start_time) <=0 )
	die("Error:Intervalo de fechas erroneo.");
$formatted_start_time=date("Y-m-d H:i:s",$start_time);
$formatted_end_time=date("Y-m-d H:i:s",$end_time);


// Borro imagenes anteriores
system("rm -f *.png > /dev/null");
system ("del *.png");


$colors[0]='red';
$colors[1]='blue';
$colors[2]='green';
$colors[3]='magenta';
$colors[4]='yellow'; 
$colors[5]='cyan'; 
$colors[6]='orange'; 
$colors[7]='violet'; 

// Veo si hay que leer de tablas multiples o de una sola t_data
$query ="SELECT count(*) as C FROM information_schema.tables WHERE table_schema ='webtrend'  AND table_name ='t_data_1'";
$rs=mysql_query($query);
$res=mysql_result($rs,0,"C");
$usar_tablas_multiples=0;
if($res==1)
{
    $usar_tablas_multiples=1;
}
else
{
    $usar_tablas_multiples=0;
}

if($DEBUG)
{
	$diff=time()-$ahora;
	echo "<br> Tablas multiples: $diff </br>";
	$ahora=time();
}
// Para usar la t_data descomentar la siguiente linea
// $usar_tablas_multiples=0;

if(isset($exportar))
{
	Exportar($tagnames);
	echo "<br><a href='$volver_a'><img alt='Inicio' src='/images/volver.png'  class='btn-sgp'  style='width:100;height:44;border:none'  title='Volver a pagina de inicio'></a>";
	die();
}
$valores=array();
for($pp=0; $pp < $n_graphs ;$pp++)
{
	$tagname=$tagnames[$pp];
	// Obtengo el id tag para el tagname
	$query ="select id_tag,driver from t_tags where tagname='$tagname'";
	$rs=mysql_query($query);
	$id_post=mysql_result($rs,0,"id_tag");
	$driver=mysql_result($rs,0,"driver");
	if(!$id_post)
	{
		$titles[$pp]=$tagnames[$pp] .":(No en BD) ";
		continue; // Este tag está mal. 
	}
	// Obtengo el titulo y la escala para el tag seleccionado
	$query ="select tagname,lowscale,highscale from t_tags where id_tag=$id_post";
	$rs=mysql_query($query);
	$title[$pp]=mysql_result($rs,0,"tagname");
	$lowscale[$pp]=mysql_result($rs,0,"lowscale");
	$highscale[$pp]=mysql_result($rs,0,"highscale");
	if($DEBUG)
	{
		$diff=time()-$ahora;
		echo "<br> Datos tag: $diff </br>";
		$ahora=time();
	}


	if(!$usar_tablas_multiples)
	{
	    $sql_where="((time>= '$formatted_start_time') and (time <= '$formatted_end_time')  and (id_tag=$id_post) and (valid=1))";
	    $query = "select id_tag,Value,Time from t_data where $sql_where order by time asc ";
	}
	else
	{
	    $sql_where="((time>= '$formatted_start_time') and (time <= '$formatted_end_time')  and (id_tag=$id_post))";
	    $query="select id_tag,value,time from t_data_$driver where $sql_where order by time asc ";
	}
	$rs=mysql_query($query);
	$num=mysql_numrows($rs);
	// echo "<p>" .$query ." ->"  .$num ." registros</p>";
	if($num < 1)
	{
		//die("No data in the given interval:$query");
		continue;
	}
	// Aca vamos....
	for($i=0;$i<$POINTS;$i++)
		$repeticiones[$pp][$i]=1;
	$i=0;$j=0;

	// Este truco lo agregue para tomar mediciones intercaladas
	// en tiempos muy elevados. El valor de N en ($num/$POINTS)/N
	// representa cuantos valores se promediarï¿½ en cada "actual_place"
	$incremento=(int)(($num/$POINTS)/1);
	if($incremento < 1)
		$incremento=1;
//	echo "<p></p>incremento:$incremento <p></p>";
	$actual_place=0;
	while($i < $num)
	{
		mysql_data_seek($rs,$i);
		$row=mysql_fetch_row($rs);
		$tag_id=$row[0];
		$value=$row[1];
		$time=$row[2];
		$time=chunk_split($time,10,"\n");
		//	$utime=$row[3];
		$utime=strtotime($time); // Paso a unix timestamp
		// Busco el lugar donde debe ir este sample
		$actual_place=(int)(($utime-$start_time)/$delta_time);
		if( ($actual_place < 0) || ($actual_place >= $POINTS))
		{
			$i++;
			//echo "<p>se fue" .$utime ."</p>";
			continue;	
		}
	//	echo "<br>Actual Place:".$actual_place." Val:".$value."</br>";
	 	//$data[$i]=@ array($time,$value);
		if(0) // max/min
		{
			if((abs($value) > abs($maxmin[$pp][$actual_place])) || !$value)
				$maxmin[$pp][$actual_place]=$value;
			if(isset($maxmin[$pp][$actual_place]))
				$valores[$pp][$actual_place]=$maxmin[$pp][$actual_place];
		}
		if(0)// Promedio los valores concurrentes
		{
			$valores[$pp][$actual_place]= ($valores[$pp][$actual_place]*($repeticiones[$pp][$actual_place]-1)+$value)/$repeticiones[$pp][$actual_place];
			$repeticiones[$pp][$actual_place]++;
		}
		
		if(1) // Sampleado
		{
			$valores[$pp][$actual_place]=$value;
		}		
		// Relleno hasta el final
		for($j=$actual_place;$j<$POINTS;$j++)
		{
			$valores[$pp][$j]=$valores[$pp][$actual_place];
		}
		
		$i+=$incremento;	
	}
	$i=0;
	$MAX[$pp]=-9999999999999;
	$MIN[$pp]= 9999999999999;
	if($DEBUG)
	{
		$diff=time()-$ahora;
		echo "<br> Levantar los datos: $diff </br>";
		$ahora=time();
	}
	// El valor inicial veo si es necesario cargarlo; ahora o hago al final
	// Aca tengo los datos, me falta obtener el valor inicial
	// DAG 09/11/11: Primero verifico si hace falta. Si el slot 0 se ocupa con la data no es necesario.
	
	// Si existe un valor previo, relleno el array con dicho valor
	if(!isset($valores[$pp][0])) // Falta el valor inicial
	{
	
		$last_time=mysql_result($rs,0,"time");
		if($DEBUG)
		{
			
			$diff=time()-$ahora;
			echo "<br> Falta valor inicial. Preparacion  query y tiempo inicial: $diff </br>";
			$ahora=time();
		}
		
		if(!$usar_tablas_multiples)
		{
			$sql_where="time < \"$last_time\" and id_tag=$id_post and valid=1";
			$query_init_val="select Value from t_data where $sql_where order by time desc limit 1";
		}
		else
		{    
			$sql_where="time < \"$last_time\" and id_tag=$id_post";
			$query_init_val="select Value from t_data_$driver where $sql_where order by time desc limit 1";
		}
		
		// echo $query_init_val;
		$rs2=mysql_query($query_init_val);
		$ExisteValorPrevio=mysql_numrows($rs2);
		if ($ExisteValorPrevio)
			$ValorInicial=mysql_result($rs2,0,"Value");
		else
			$ValorInicial=0;
			
		for($i=0;$i<$POINTS;$i++)
		{
			if(!isset($valores[$pp][$i]))
				$valores[$pp][$i]=$ValorInicial;
			else
				break;
		}
		if($DEBUG)
		{
			$diff=time()-$ahora;
			echo "<br> Valor Inicial: $diff </br>";
			$ahora=time();
		}
	}
	else
	{
		if($DEBUG)
		{
			$diff=time()-$ahora;
			echo "<br> Valor Inicial en la consulta original: $diff </br>";
			$ahora=time();
		}
	}
	$i=0;
	// Busqueda de maximos y minimos
	while($i < $POINTS)
	{
		if(isset($valores[$pp][$i]))
		{
			if($valores[$pp][$i]>$MAX[$pp])
			{
				 $MAX[$pp]=$valores[$pp][$i];
				 $TMAX[$pp]=$i;
			}
			if($valores[$pp][$i] < $MIN[$pp])
			{
				$MIN[$pp]=$valores[$pp][$i];
				$TMIN[$pp]=$i;
			}
		}
		$i++;	
	}
	if($DEBUG)
	{
		$diff=time()-$ahora;
		echo "<br> Maximmos y minimos: $diff </br>";
		$ahora=time();
	}
	
}

/******************** ARMADO DE LA GRAFICA ****************************/
$i=0;
while($i < $POINTS)
{
	if($agrupar || $exportar_grafica)
	{
		// Los pongo en orden inverso para que los muestre correctamente
		// Fuerza bruta hasta que se me ocurra algo mejor
		switch($n_graphs)
		{
			case 1:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i]); break;
			case 2:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i]);break; 
			case 3:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i],$valores[2][$i]); break ;
			case 4:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i],$valores[2][$i],$valores[3][$i]);break; 
			case 5:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i],$valores[2][$i],$valores[3][$i],$valores[4][$i]);break; 
			case 6:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i],$valores[2][$i],$valores[3][$i],$valores[4][$i],$valores[5][$i]); break;
			case 7:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i],$valores[2][$i],$valores[3][$i],$valores[4][$i],$valores[5][$i],$valores[6][$i]); break;
			case 8:$a_data[$i]=@ array($tiempos[$i],$valores[0][$i],$valores[1][$i],$valores[2][$i],$valores[3][$i],$valores[4][$i],$valores[5][$i],$valores[6][$i],$valores[7][$i]); break;
		}
	}
	else // Armo todo por separado
	{
		for($pp=0;$pp < $n_graphs;$pp++)
		{
			$a_data[$pp][$i]=@ array($tiempos[$i], $valores[$pp][$i]);
		}
	}
	$i++;	
}

if(isset($exportar_grafica))
{
	system("rm -f *.tag.csv data.* *.zip > /dev/null");
	system("del *.tag.csv *.data.zip *.query");
	$fd=fopen("tags.csv",'wb');
	$str='"Timestamp"';
	foreach($tagnames as $nombre)
		$str= $str .',"' .$nombre .'"';

		$str=$str ."\015\012"; 
	fwrite($fd,$str);
	foreach($a_data as $key => $aux)
	{
//		print_r($aux);
		$str="";
		foreach($aux as $reg) // recorro toda la linea
		{
			if(strlen($str) > 0)
				$str= $str .',"' .$reg .'"';
			else // Es el timestamp
			{
				$str= '"' .$reg .'"';
				$str[11]=" ";
			}
		}
		$str=$str ."\015\012"; 
	//	echo "$str <br>";
		fwrite($fd,$str);
	}
/*	for($pp=0;$pp < $n_graphs ;$pp++)
	{
		$fd=fopen($tags_exportar[$pp].".tag".".csv",'w');
		$titulo="Tagname:" .$tags_exportar[$pp] ."\015\012Descripcion:" .$desc_e ."\015\012";
		$titulo=$titulo ."Timestamp,Valores\015\012";
		fwrite($fd,$titulo);
		foreach(
		for($j=0;$j< $num; $j++)
		{
			
			$str=mysql_result($rs,$j,'Timestamp') ."," .mysql_result($rs,$j,'Valor') ."," .mysql_result($rs,$j,'Valid') ."\015\012"  ;
			fwrite($fd,$str);
		}
	}
*/
	fclose($fd);
	//system("zip data.zip *.tag.csv > /dev/null");
	$zip_file=time(NULL).".data.zip";
	system("zip -q $zip_file tags.csv");
	system("rm -f *.tag.dat > /dev/null");
	system("del *.tag.dat null");
	echo "<a href='$zip_file'>Descargar archivo:$zip_file</a>";
	echo "<br><a href='$volver_a'><img alt='Inicio' src='/images/volver.png'  class='btn-sgp'  style='width:100;height:44;border:none' title='Volver a pagina de inicio'></a>";
	die();
}

if($agrupar)
{
	$hscale_final=-9999999999999;
	$lscale_final= 9999999999999;

	for($i=0;$i < $n_graphs;$i++)
	{
		if(!isset($highscale[$i]))
		{
			echo "<br><h3>Warning: Tag $tagnames[$i] no está en BD</h3>";
			continue;
		}
		if($highscale[$i] > $hscale_final)
			$hscale_final=$highscale[$i];
		if($lowscale[$i] < $lscale_final)
			$lscale_final=$lowscale[$i];
	}
	// Subtitile es en realidad el titulo general. Lo armo concatenando los titulos. Los espacios ponerlos al llamar al trending
	for ($i=0;$i < $n_graphs;$i++)
		$subtitle=$subtitle ." ".$titles[$i] ;
    if($USAR_PHPLOT)
    {
	//$subtitle=$titles[0] .$titles[1] .$titles[2] .$titles[3] .$titles[4];
	$graph=&new PHPlot($IMG_W,$IMG_H);
	$graph->SetPrintImage(0);
	//$graph->SetPlotType("lines");
	$graph->SetPlotType('squared'); // Igual que "lines" pero en lugar de interpolar con rampas dibuja solo rectas
	$graph->SetDataColors($colors); 
	$graph->SetPlotBorderType("full");
	$bgcolor=array(230,230,230);
	$graph->SetBackgroundColor($bgcolor);
	$graph->SetPrecisionY(2);
	$graph->SetNumHorizTicks(10);
	$graph->SetXGridLabelType("title");
	$graph->SetDrawXGrid(true);
	$graph->SetFileFormat("png");
	$filename=time(NULL)."multi".".png";
	$graph->SetOutputFile($filename);
	$graph->SetIsInline(1);
	$graph->SetXTitle("Time");
	$graph->SetYTitle("Values");
	$graph->SetDrawXDataLabels(0); // ya estan 
	$graph->SetLegend($title);
	$graph->SetTitle($subtitle);
	$graph->SetDataValues($a_data);
	if($hscale_final > $lscale_final)
		$graph->SetPlotAreaWorld(NULL,$lscale_final,NULL,$hscale_final);
	$graph->DrawGraph();
	// Agregado para ver posición de máximos y mínimos
	$__color=ImageColorAllocate ($graph->img, 0, 0,0);
	$xmin=$graph->x_left_margin; 	
	$xmax=$graph->image_width - $graph->x_right_margin;
	$draw_width=$xmax-$xmin;
	$ymin=$graph->y_top_margin;
	$ymax=$graph->image_height - $graph->y_bot_margin;
	$draw_h=$ymax-$ymin;	 	
	for ($pp=0;$pp < $n_graphs;$pp++)
	{
		$x0_min=$xmin + $draw_width * $TMIN[$pp]/$POINTS;
		$y0_min=$ymin + $draw_h * (1- $MIN[$pp] / ($graph->plot_max_y -$graph->plot_min_y )) 
				 +  $draw_h * $graph->plot_min_y  / ($graph->plot_max_y -$graph->plot_min_y ) ;
		$x0_max=$xmin + $draw_width * $TMAX[$pp]/$POINTS;
		$y0_max=$ymin + $draw_h * (1- $MAX[$pp] / ($graph->plot_max_y -$graph->plot_min_y )) 
				+  $draw_h * $graph->plot_min_y  / ($graph->plot_max_y -$graph->plot_min_y ) ;;
		// Indicador de minimo 
	        ImageLine($graph->img,$x0_min-5,$y0_min+5,$x0_min,$y0_min,$__color);
	        ImageLine($graph->img,$x0_min,$y0_min,$x0_min+5,$y0_min+5,$__color);
	        ImageLine($graph->img,$x0_min-5,$y0_min+5,$x0_min+5,$y0_min+5,$__color);
		// Indicador de maximo 
	        ImageLine($graph->img,$x0_max-5,$y0_max-5,$x0_max,$y0_max,$__color);
	        ImageLine($graph->img,$x0_max,$y0_max,$x0_max+5,$y0_max-5,$__color);
	        ImageLine($graph->img,$x0_max-5,$y0_max-5,$x0_max+5,$y0_max-5,$__color);
	}
	$graph->PrintImage();
	echo "<IMG src='$filename' width=$IMG_W heht=$IMG_H>";
    
}
else
{
    echo '
    <div id="placeholder" style="width:'.$IMG_W.'px;height:'.$IMG_H.'px;border-color:DarkSlateGray"></div> 

    <script type="text/javascript"> 
    (function($){
      $(function () {
        // generate data set from a parametric function with a fractal
        // look
        var data=[],series=[];';
        $maxy=0;
        $miny=0;
        $minx=parsedate($tiempos[0]." - 3 hours").'000';
        $maxx=parsedate($tiempos[$POINTS-1]." - 3 hours").'000';
        $jj=0;
        for( $jj=0 ; $jj < $n_graphs ; $jj++ ){
            echo '        
            var d1=[];
            var d1 = [';
            $kk=0;
	        for($kk=0;$kk<$POINTS ; $kk++){
	            echo '['.parsedate($tiempos[$kk]." - 3 hours").'000,'.$valores[$jj][$kk].']';
                if( $valores[$jj][$kk] > $maxy ) $maxy=$valores[$jj][$kk]; 
                if( $valores[$jj][$kk] < $miny ) $miny=$valores[$jj][$kk]; 
	            if($kk != ($POINTS-1)) echo ',';
	        }
            echo '];
            data.push({"label": "'.$titles[$jj].'", "data": d1});';
        }
        echo '
        series={"data": data, "minx": '.$minx.', "miny": '.$miny.', "maxx": '.$maxx.', "maxy": '.$maxy.'};

	if(series.miny > 0){
		series.miny=0;
	}

                
        var placeholder = $("#placeholder"); 
        var tooltip = $("<div id=" +  "\042" + "tooltip" + "\042" + "/>").appendTo($("body"));

        var options = {
                colors: ["#ff0000", "#00ff00", "#0000ff", "#4da74d", "#9440ed"],
                series: { lines: { show: true, lineWidth:"1"}, shadowSize: 0 },
                grid: { hoverable: true, clickable: true, color: "#000000", backgroundColor:"rgb(230,230,230)", borderColor:"DarkSlateGray", tickColor:"rgb(210,210,210)"},
                legend: {show: true, position: "nw"},
                xaxis: {
                    mode: "time",
                    timeformat: "%d/%m %H:%M",
                    monthNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
                    min: series.minx,
                    max: series.maxx,
                    zoomRange: [1, series.maxx-series.minx],
                    panRange: [series.minx, series.maxx]
                },
            yaxis: { min:series.miny*1.1, max:series.maxy*1.1, zoomRange: [1, series.maxy*1.1-series.miny*1.1], panRange: [series.miny*1.1, series.maxy*1.1] },
            zoom: {
                interactive: true
            },
            pan: {
                interactive: true
            }
        };
     
        var plot = $.plot(placeholder, series.data, options);
     
     
        // add zoom out button
	    var divstring="<div class=" + "\042" + "button" + "\042" + " style=" + "\042" + "right:28px;top:27px" + "\042" + ">zoom out</div>";
        $(divstring).appendTo(placeholder).click(function (e) {
            e.preventDefault();
            plot.zoomOut();
        });
     
        // and add panning buttons
        
        // little helper for taking the repetitive work out of placing
        // panning arrows
        function addArrow(dir, right, top, offset) {
	    var divstring2= "<img class=" + "\042" + "button" + "\042" + " src=" + "\042" + "images/arrow-" + dir + ".gif" + "\042" +  " style=" + "\042" + "right:" + right + "px;top:" + top + "px" + "\042" + ">";
	    $(divstring2).appendTo(placeholder).click(function (e) {
                e.preventDefault();
                plot.pan(offset);
            });
        }
     
        addArrow("left", 55, 60, { left: -100 });
        addArrow("right", 25, 60, { left: 100 });
        addArrow("up", 40, 45, { top: -100 });
        addArrow("down", 40, 75, { top: 100 });


        // agregar tooltips
        var point = null;
        placeholder.bind("plothover", function(event, pos, item) {
            // si el cursor estÃ¡ sobre uno de los puntos
            if (item) {
                // comprueba que se trate de un punto diferente al que se le generÃ³
                // tooltip la Ãºltima vez
                if (point === null || point[0] != item.datapoint[0] || point[1] != item.datapoint[1]) {
                    // guarda el punto para evitar generar el mismo tooltip dos
                    // veces consecutivas
                    point = item.datapoint;
                    // Flot permite conocer informaciÃ³n sobre el punto para el cual
                    // estamos generando el tooltip:
                    // - item.series contiene informaciÃ³n sobre la serie a la q pertenece
                    // el punto. la etiqueta y el color son dos buenos ejemplos.
                    // - item.pageX e item.pageY son las coordenadas el punto respecto
                    // al documento (coordenadas globales).
                    // - pos.pageX y pos.pageY son las coordenadas globales del cursor.
//                    tooltip.html(item.label + ": " + parseInt(point[1].toFixed(2)))
//                           .css("background-color", item.color);

            var horapunto = new Date(point[0] + 10800000);
		    var puntouno = 0;
		    if(series.maxy>100) {
			puntouno=point[1].toFixed(0);
		    }else{
			puntouno=point[1].toFixed(2);
		    }
                    tooltip.html(puntouno + "<br/>" + horapunto.toLocaleString() ).css("background-color","white");

                    // centra el tooltip sobre el punto
                    var x = item.pageX - (tooltip.width() / 2),
                        y = item.pageY - tooltip.height() - 18;
                    // animaciÃ³n para el tooltip
                    if (tooltip.css("opacity") < 0.2) { 
                        tooltip.stop().css({top: y, left: x}).animate({ opacity: 1}, 400);
                    } else {
                        tooltip.stop().animate({ opacity: 1, top: y, left: x}, 600);
                    }
                }
            } else {
                // si el cursor no estÃ¡ sobre uno de los puntos escondemos el tooltip
                tooltip.stop().animate({opacity: 0}, 400);
                point = null;
            }
        });
    });
 })(jQuery);
 </script> 
 ';
  } // $USAR_PHPLOT
}
else // No agrupadas
{
	for ($pp=0;$pp <$n_graphs;$pp++)
	{

	    if($USAR_PHPLOT)
    	   {
		if(strpos($titles[$pp],"(No en BD)")) // Tag en error
		{
			echo "<br><h3>Warning: Tag $tagnames[$pp] no está en BD</h3>";
			continue;
		}
		$graph=&new PHPlot($IMG_W,$IMG_H);
		$graph->SetPrintImage(0);
		//$graph->SetPlotType("lines");
		$graph->SetPlotType('squared'); // Igual que "lines" pero en lugar de interpolar con rampas dibuja solo rectas
//			$graph->SetDataColors(array('red', 'blue','green','magenta','yellow')); 
		$graph->SetDataColors(array($colors[$pp])); 
		$graph->SetPlotBorderType("full");
		$bgcolor=array(230,230,230);
		$graph->SetBackgroundColor($bgcolor);
		$graph->SetPrecisionY(2);
		$graph->SetNumHorizTicks(10);
		$graph->SetXGridLabelType("title");
		$graph->SetDrawXGrid(true);
		$graph->SetFileFormat("png");
		$filename=time(NULL).$pp.".png";
		$graph->SetOutputFile($filename);
		$graph->SetIsInline(1);
		$graph->SetXTitle("Time");
		$graph->SetYTitle("Values");
		$graph->SetDrawXDataLabels(0); // ya estan 
		$graph->SetLegend($title[$pp]);
		$graph->SetTitle($titles[$pp]);
		$graph->SetDataValues($a_data[$pp]);
		if($highscale[$pp] > $lowscale[$pp])
			$graph->SetPlotAreaWorld(NULL,$lowscale[$pp],NULL,$highscale[$pp]);
		$graph->DrawGraph();
		// Agregado para ver posición de máximos y mínimos
		$__color=ImageColorAllocate ($graph->img, 0, 0,0);
		$xmin=$graph->x_left_margin; 	
		$xmax=$graph->image_width - $graph->x_right_margin;
		$draw_width=$xmax-$xmin;
		$ymin=$graph->y_top_margin;
		$ymax=$graph->image_height - $graph->y_bot_margin;
		$draw_h=$ymax-$ymin;	 	

		$x0_min=$xmin + $draw_width * $TMIN[$pp]/$POINTS;
		$y0_min=$ymin + $draw_h * (1- $MIN[$pp] / ($graph->plot_max_y -$graph->plot_min_y ))  +  
				$draw_h * $graph->plot_min_y  / ($graph->plot_max_y -$graph->plot_min_y ) ;
		$x0_max=$xmin + $draw_width * $TMAX[$pp]/$POINTS;
		$y0_max=$ymin + $draw_h * (1- $MAX[$pp] / ($graph->plot_max_y -$graph->plot_min_y )) + 
				 $draw_h * $graph->plot_min_y  / ($graph->plot_max_y -$graph->plot_min_y ) ;
		// Indicador de minimo 
	        ImageLine($graph->img,$x0_min-5,$y0_min+5,$x0_min,$y0_min,$__color);
	        ImageLine($graph->img,$x0_min,$y0_min,$x0_min+5,$y0_min+5,$__color);
	        ImageLine($graph->img,$x0_min-5,$y0_min+5,$x0_min+5,$y0_min+5,$__color);
		// Indicador de maximo 
	        ImageLine($graph->img,$x0_max-5,$y0_max-5,$x0_max,$y0_max,$__color);
	        ImageLine($graph->img,$x0_max,$y0_max,$x0_max+5,$y0_max-5,$__color);
	        ImageLine($graph->img,$x0_max-5,$y0_max-5,$x0_max+5,$y0_max-5,$__color);
		$graph->PrintImage();
		echo "<IMG src='$filename' width=$IMG_W height=$IMG_H>";
	} // if ($USAR_PHPLOT)
	else
	{
	    echo '
	    <div id="placeholder'.$pp.'" style="width:'.$IMG_W.'px;height:'.$IMG_H.'px;color:yellow"></div> 
	    <script type="text/javascript"> 
	   (function($){
          $(function () {
           // generate data set from a parametric function with a fractal
        // look
        var d1 = [';
        $kk=0;
	    for($kk=0;$kk<$POINTS ; $kk++){
	        echo '['.parsedate($tiempos[$kk]." - 3 hours").'000,'.$a_data[$pp][$kk][1].']';
	        if($kk != ($POINTS-1)) echo ',';
	    } 
        echo '];
        var data'.$pp.' = [];
        data'.$pp.'.push({"label":"'.$titles[$pp].'" , "data":d1});

        var n='.$POINTS.'-1;
        var minx =d1[0][0];
        var maxx =d1[n][0];
        var miny=0, maxy=0,j=0;
        for(j=0;j<n;j++){
            if(d1[j][1] > maxy) maxy=d1[j][1];
            if(d1[j][1] < miny) miny=d1[j][1];
        } 
	
	if(miny > 0){
		miny=0;
	}
        
        var placeholder = $("#placeholder'.$pp.'"); 
        var tooltip = $("<div id=" +  "\042" + "tooltip" + "\042" + "/>").appendTo($("body"));


        var options = {
                series: { lines: { show: true, lineWidth:"1"}, color:"rgb(255,0,0)", shadowSize: 0 },
                grid: { hoverable: true, clickable: true, color: "#000000", backgroundColor:"rgb(230,230,230)", borderColor:"DarkSlateGray", tickColor:"rgb(210,210,210)"},
                legend: {show: true, position: "nw"},
                xaxis: {
                    mode: "time",
                    timeformat: "%d/%m %H:%M",
                    monthNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
                    min: minx,
                    max: maxx,
                    zoomRange: [1, maxx-minx],
                    panRange: [minx, maxx]
                },
            yaxis: { min:miny*1.1, max:maxy*1.1, zoomRange: [1, maxy*1.1]-miny*1.1, panRange: [miny*1.1, maxy*1.1] },
            zoom: {
                interactive: true
            },
            pan: {
                interactive: true
            }
        };
     
        var plot = $.plot(placeholder, data'.$pp.', options);
     
     
        // add zoom out button
	    var divstring="<div class=" + "\042" + "button" + "\042" + " style=" + "\042" + "right:28px;top:27px" + "\042" + ">zoom out</div>";
        $(divstring).appendTo(placeholder).click(function (e) {
            e.preventDefault();
            plot.zoomOut();
        });
     
        // and add panning buttons
        
        // little helper for taking the repetitive work out of placing
        // panning arrows
        function addArrow(dir, right, top, offset) {
	    var divstring2= "<img class=" + "\042" + "button" + "\042" + " src=" + "\042" + "images/arrow-" + dir + ".gif" + "\042" +  " style=" + "\042" + "right:" + right + "px;top:" + top + "px" + "\042" + ">";
	    $(divstring2).appendTo(placeholder).click(function (e) {
                e.preventDefault();
                plot.pan(offset);
            });
        }
     
        addArrow("left", 55, 60, { left: -100 });
        addArrow("right", 25, 60, { left: 100 });
        addArrow("up", 40, 45, { top: -100 });
        addArrow("down", 40, 75, { top: 100 });


        // agregar tooltips
        var point = null;
        placeholder.bind("plothover", function(event, pos, item) {
            // si el cursor estÃ¡ sobre uno de los puntos
            if (item) {
                // comprueba que se trate de un punto diferente al que se le generÃ³
                // tooltip la Ãºltima vez
                if (point === null || point[0] != item.datapoint[0] || point[1] != item.datapoint[1]) {
                    // guarda el punto para evitar generar el mismo tooltip dos
                    // veces consecutivas
                    point = item.datapoint;
                    // Flot permite conocer informaciÃ³n sobre el punto para el cual
                    // estamos generando el tooltip:
                    // - item.series contiene informaciÃ³n sobre la serie a la q pertenece
                    // el punto. la etiqueta y el color son dos buenos ejemplos.
                    // - item.pageX e item.pageY son las coordenadas el punto respecto
                    // al documento (coordenadas globales).
                    // - pos.pageX y pos.pageY son las coordenadas globales del cursor.
//                    tooltip.html(item.label + ": " + parseInt(point[1].toFixed(2)))
//                           .css("background-color", item.color);
                    var horapunto = new Date(point[0] + 10800000);
		    var puntouno = 0;
		    if(maxy>100) {
			puntouno=point[1].toFixed(0);
		    }else{
			puntouno=point[1].toFixed(2);
		    }
                    tooltip.html(puntouno + "<br/>" + horapunto.toLocaleString() ).css("background-color","white");

                    // centra el tooltip sobre el punto
                    var x = item.pageX - (tooltip.width() / 2),
                        y = item.pageY - tooltip.height() - 18;
                    // animaciÃ³n para el tooltip
                    if (tooltip.css("opacity") < 0.2) { 
                        tooltip.stop().css({top: y, left: x}).animate({ opacity: 1}, 400);
                    } else {
                        tooltip.stop().animate({ opacity: 1, top: y, left: x}, 600);
                    }
                }
            } else {
                // si el cursor no estÃ¡ sobre uno de los puntos escondemos el tooltip
                tooltip.stop().animate({opacity: 0}, 400);
                point = null;
            }
        });
      });
    })(jQuery);
    </script> 
   ';
    } // if(!$USAR_PHPLOT)

   }// for()
}

if($DEBUG)
{
	$diff=time()-$ahora;
	echo "<br> GRAFICAS: $diff </br>";
	$ahora=time();
}

echo "<br><a href='$volver_a'><img alt='Inicio' src='/images/volver.png'  class='btn-sgp'  style='width:100;height:44;border:none' title='Volver a pagina de inicio'></a>";
mysql_close();
	
/*************************************************************************************/

function Exportar($tags_exportar)
{
	global $n_graphs;
	global $tag_exportar;
	global $start_time;
	global $end_time;
	global $usar_tablas_multiples;
	global $MAX_REGS_EXPORTAR;
	system("rm -f *.tag.csv data.* *.zip > /dev/null");
	system("del *.tag.csv *.data.zip *.query");
	
	for($pp=0;$pp < $n_graphs ;$pp++)
	{
		if(strlen($tags_exportar[$pp])<1)
			continue;
		$query="SELECT id_tag,description,driver FROM t_tags where tagname='$tags_exportar[$pp]'";
		$rs=mysql_query($query);
		$id_tag_e=mysql_result($rs,0,"id_tag");
		$driver=mysql_result($rs,0,"driver");
		$desc_e=mysql_result($rs,0,"description");
		$formatted_start_time=date("Y-m-d H:i:s",$start_time);
		$formatted_end_time=date("Y-m-d H:i:s",$end_time);
		if(!$usar_tablas_multiples)
		{
			$sql_where="((time>= '$formatted_start_time') and (time <= '$formatted_end_time')  and (id_tag=$id_tag_e))";
			$query="select value as Valor,Time as Timestamp,Valid from t_data where $sql_where";	
		}
		else
		{
			$sql_where="((time>= '$formatted_start_time') and (time <= '$formatted_end_time')  and (id_tag=$id_tag_e))";
			$query="select value as Valor,Time as Timestamp FROM t_data_$driver where $sql_where";	
		}
		$rs=mysql_query($query);
		$num=mysql_numrows($rs);
		if($num > $MAX_REGS_EXPORTAR)
		{
			$skip=(int)($num/$MAX_REGS_EXPORTAR);
		}
		else
			$skip=0;
		$reporte="Tagname:" .$tags_exportar[$pp] ."\015\012Descripcion:" .$desc_e ."\015\012";
		$reporte=$reporte ."Timestamp,Valor,Valido\015\012";
		$tiempo_ctrl=time(NULL);
		for($j=0;$j< $num; )
		{
			if(!$skip)
				$j++;
			else
			{
				mysql_data_seek($rs,$j);
				$j+=$skip;
			}
			
			if(!$usar_tablas_multiples)
			{
				$row=mysql_fetch_row($rs);
				$reporte=$reporte .$row[1] ."," .$row[0] ."," .$row[3] ."\015\012";
				//$str=mysql_result($rs,$j,'Timestamp') ."," .mysql_result($rs,$j,'Valor') 
				//    ."," .mysql_result($rs,$j,'Valid') ."\015\012";
			}
			else
			{
				$row=mysql_fetch_row($rs);
				$reporte=$reporte .$row[1] ."," .$row[0] .",1 \015\012";
//				$str=mysql_result($rs,$j,'Timestamp') ."," .mysql_result($rs,$j,'Valor') 
//			    .",1" ."\015\012";
			}
		}
		$fd=fopen($tags_exportar[$pp].".tag".".csv",'w');
		fwrite($fd,$reporte); 
		fclose($fd);
	}
	//system("zip data.zip *.tag.csv > /dev/null");
	$zip_file=time(NULL).".data.zip";
	system("zip -q $zip_file *.tag.csv");
	system("rm -f *.tag.dat > /dev/null");
	system("del *.tag.dat null");
	echo "<a href='$zip_file'>Descargar archivo:$zip_file</a>";
}

?>
</body>
</html>
