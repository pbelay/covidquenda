
<?php
//Para enviar mails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Para xerar pdfs
$path = (getenv('MPDF_ROOT')) ? getenv('MPDF_ROOT') : __DIR__;
require $path . '/vendor/autoload.php';


//Variables do formulario
$modulo  = $_POST["modulo"];
$aula	 = $_POST["aula"];
$mailProfe = $_POST["mailProfe"];
$mailSenha = $_POST["mailSenha"];

//Variables globais
$mailSMTP = 'smtp.edu.xunta.gal';
$portSMTP = '587';
$carpetaTemp =  rand(0, 9999999);
$carpetaTempPDF = $carpetaTemp.'/pdf/';
$carpetaTempCSV = $carpetaTemp.'/csv/';

//Crear carpetas temporais
mkdir($carpetaTemp);
mkdir($carpetaTempPDF);
mkdir($carpetaTempCSV);



echo "modulo: " . $modulo . "<br>";
echo "aula: " . $aula . "<br>";
echo "mailProfe: " . $mailProfe . "<br>";
echo "mailSenha: " . $mailSenha . "<br>";
echo "carpetaTemp: " . $carpetaTemp  . "<br>";
echo "<br>";

echo "<h1>Procesado do ficheiro CSV </h1>";

//datos do arquivo
$filename = $_FILES['userfile']['name'];
$tipo_archivo = $_FILES['userfile']['type'];
$tamano_archivo = $_FILES['userfile']['size'];
	
//COMPROBAR FICHEIRO CSV
if (!((strpos($tipo_archivo, "csv")) && ($tamano_archivo < 100000))) {
   	echo "O arquivo non é de tipo CSV ";
}else{
   	if (move_uploaded_file($_FILES['userfile']['tmp_name'],  $carpetaTempCSV.$filename)){
      		echo "O arquivo subiuse correctamente <br>";
   	}else{
      		echo " Existiu un problema subindo o arquivo.";
   	}
}



//..................
//PRINCIPAL
//Lista cos datos do CSV
$datos = [];
$datos = leerCSV($carpetaTempCSV.$filename);



foreach ($datos as $row) {
  // echo $row[0] . '<br>';
  $data   = $row[8];
  $alumno = $row[0] . " " . $row[1] . " " . $row[2];
  $dni    = $row[3];
  $horario = $row[9];
  $turno  = $row[6];
  $mesa   = $row[7];
  $mail   = $row[4];
  echo  $alumno ;
  xerarTicketPDF($modulo, $data, $alumno, $dni, $aula, $horario, $turno, $mesa, $carpetaTempPDF);
  envio($mail,$mailProfe, $mailSenha, $dni,$modulo, $alumno, $carpetaTempPDF);
}


//FUNCIÓNS
//Lectura CSV
function leerCSV($filename)
{
  $datos = [];
  if (($h = fopen("{$filename}", "r")) !== FALSE) {

    while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
      $datos[] = $data;
    }
    fclose($h);
  }
  return $datos;
}


//Xeracion de PDF's cos tickets para cada alumno, gardanse polo DNI
function xerarTicketPDF($modulo, $data, $nome, $dni, $aula, $horario, $turno, $mesa, $directorio)
{
  $html = "";
  $mpdf = new \Mpdf\Mpdf([]);
  $mpdf->SetHTMLHeader("<img src='./img/logo.png' >");


  $html = '<br> <br> <br> <h2>' . $modulo . '</h2>

<div>
   Os datos para este exame son:

    <div style="float: right; width: 28%;">
           <img  style="float:right;" src="./img/quenda' . $turno . '.png" >
    </div>

    <div style="float: left; width: 54%;">
             <ul>* Data:' . $data . ' </ul>
             <ul>* Horario: ' . $horario . ' </ul>
             <ul>* Aula: ' . $aula . '</ul>
             <ul>* Quenda: ' . $turno . ' </ul>
             <ul>* Mesa: ' . $mesa . '</ul>
             <ul>* Alumno/a: ' . $nome . ' </ul>
    </div>

    <div style="clear: both; margin: 0pt; padding: 0pt; "><p> <b> IMPORTANTE:</b>  Para poder entrar no Centro/Aula, será imprescindible a  presentación desta notificación, acompañada do DNI do alumno/a.</p></div>

 </div>';

  $mpdf->WriteHTML($html);
  //$mpdf->Output('./pdf/' . $dni . '.pdf', 'F');
  $mpdf->Output( $directorio . $dni . '.pdf', 'F');

  
 
}



function envio($correo,$mailProfe, $mailSenha, $dni,$modulo, $alumno, $carpetaTempPDF )
{

  //Create a new PHPMailer instance
  $mail = new PHPMailer();
  //Tell PHPMailer to use SMTP
  $mail->isSMTP();
  //Enable SMTP debugging
  // SMTP::DEBUG_OFF = off (for production use)
  // SMTP::DEBUG_CLIENT = client messages
  // SMTP::DEBUG_SERVER = client and server messages
  $mail->SMTPDebug = SMTP::DEBUG_OFF;
  //Set the hostname of the mail server
  $mail->Host = 'smtp.edu.xunta.gal';
  $mail->Port = 587;
  $mail->SMTPAuth = true;
  $mail->Username =  $mailProfe;
  $mail->Password =  $mailSenha;
  //Set who the message is to be sent from
  $mail->setFrom($mailProfe, '');
  //Set an alternative reply-to address
  $mail->addReplyTo($mailProfe, '');
  //Set who the message is to be sent to
  $mail->addAddress($correo, '');
  //Set the subject line
  $mail->Subject = 'Quenda do exame: '.$modulo;
  //Read an HTML message body from an external file, convert referenced images to embedded,
  //convert HTML into a basic plain-text alternative body
  //$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
  //Replace the plain text body with one created manually
  $mail->AltBody = 'This is a plain-text message body';
  //Attach an image file
  $mail->addAttachment($carpetaTempPDF.$dni.'.pdf');
  $mail->Body = '<b>Estimado/a '.$alumno. ', <br> no documento adxunto podes descargar o xustificante de acceso para o exame. </b>';



  //send the message, check for errors
  if (!$mail->send()) {
    echo ' Mailer Error: ' . $mail->ErrorInfo. '<br>';
  } else {
    echo ' Message sent!'. '<br>';
  }
}





 
?>
