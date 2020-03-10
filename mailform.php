<?php
if(isset($_POST["nombre"]) && isset($_POST["sender"]) && isset($_POST["message"]) ){
$to = "federico@fotovoltaica.com.ar";
$subject = "Consulta desde STL Energias Renovables";
$contenido .= "Nombre: ".$_POST["nombre"]."\n";
$contenido .= "Email: ".$_POST["sender"]."\n\n";
$contenido .= "Comentario: ".$_POST["message"]."\n\n";
$header = "From: info@fotovoltaica.com.ar\nReply-To:".$_POST["sender"]."\n";
$header .= "Mime-Version: 1.0\n";
$header .= "Content-Type: text/plain";

$captcha=$_POST['g-recaptcha-response'];
$ip = getenv('HTTP_CLIENT_IP')?:getenv('HTTP_X_FORWARDED_FOR')?:getenv('HTTP_X_FORWARDED')?:getenv('HTTP_FORWARDED_FOR')?:getenv('HTTP_FORWARDED')?:getenv('REMOTE_ADDR');
$url="https://www.google.com/recaptcha/api/siteverify?secret=6LdETIsUAAAAACSG03MwIUl9yT5VwdX8dqtmJ5C_&response=".$captcha."&remoteip=".$ip;
$data=file_get_contents($url);
$data = json_decode($data);

if(!$data->success)
    die("Get out of here bot!</body></html>");
//if($data->success) {
    if(mail($to, $subject, $contenido ,$header))
      {
        echo "Mail Enviado.";
        //header('Location: index.html');
      }
    else {
        die("Get out of here bot!</body></html>");
          }
  //    }
}
?>
