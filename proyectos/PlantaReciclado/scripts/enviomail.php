
<?php
/* Acceso a la base de datos */
    $host="localhost";
    $name = "arduino";
    $passwd = "ArduinO2015";
    $dbname = "subtilis";

require 'class.email-query-results-as-csv-file.php';
$emailCSV = new EmailQueryResultsAsCsv('$host','$dbname','$name','$passwd');
$emailCSV->setQuery("SELECT Amps, t_P1, fechahora FROM planta_reciclado ORDER BY fechahora DESC LIMIT 100");
$emailCSV->sendEmail("sender@website.com","f.estavillo@ieee.org",
    "MySQL Query Results as CSV Attachment");
	
?>