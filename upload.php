
<?php

//Para enviar mails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Endroid\QrCode\QrCode;

// Para xerar pdfs
$path = (getenv('MPDF_ROOT')) ? getenv('MPDF_ROOT') : __DIR__;
require $path . '/vendor/autoload.php';

//Variables do formulario
$modulo    = $_POST["modulo"]; 
$modalidade= $_POST["modalidade"];
$dia= $_POST["dia"];
$mes=$_POST["mes"];
$aula	   = $_POST["aula"];
$mailProfe = $_POST["mailProfe"];
$mailSenha = $_POST["mailSenha"];
$nomeprofe = $_POST["nomeprofe"];

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
echo "dia: " . $dia  . "<br>";
echo "mes: " . $mes  . "<br>";
echo "nomeprofe: " . $nomeprofe  . "<br>";
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
$datos 		= [];
$datos 		= leerCSV($carpetaTempCSV.$filename);
$htmlAsist	= ' 
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>

<table style="width:100%"> ';// <tr>  <th>Nome</th>   <th>DNI</th>   <th>Quenda</th>   <th>Mesa</th>    <th>Sinatura</th>  </tr>';


foreach ($datos as $row) {
  // echo $row[0] . '<br>';
  $data   = $row[8];
  $alumno = $row[0] . " " . $row[1] . " " . $row[2];
  $dni    = $row[3];
  $horario = $row[9];
  $turno  = $row[6];
  $mesa   = $row[7];
  $mail   = $row[4];
  $xustif = (strcmp($row[5], 'Si') == 0) ; 

  echo  $alumno .' - ';
  
  xerarTicketPDF($modulo, $data, $alumno, $dni, $aula, $horario, $turno, $mesa, $carpetaTempPDF);
  
  envio($mail,$mailProfe, $mailSenha, $dni,$modulo, $alumno, $carpetaTempPDF);
  
  //Xeracion xustificante
  if (  (strcmp($xustif, '1') == 0)){
	    echo $xustif .'<br>';
	    xerarXustificantePDF($modulo, $data, $alumno, $dni, $aula, $horario, $turno, $mesa, $carpetaTempPDF,$dia, $mes, $modalidade, $nomeprofe );
  }
  
  //Listado asistentes
  $htmlAsist=$htmlAsist.'<tr>  <td>'.$alumno.'</td>   <td>'.$dni.'</td>   <td>'.$turno.'</td>   <td>'.$mesa.'</td>    <td width="30%">  </td>  </tr>';

}
//Fin bucle
$htmlAsist=$htmlAsist.'</table>';
xerarListadoPDF($modulo, $data, $alumno, $dni, $aula, $horario, $turno, $mesa, $carpetaTempPDF,$htmlAsist, $modalidade, $dia, $mes);


echo $htmlAsist;

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

  //xerar codigo qr	
  $qrCode = new QrCode($dni);
  $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
//  $qrCode->setBackgroundColor(['r' => 96, 'g' => 96, 'b' => 95, 'a' => 0]);

  $qrCode->writeFile($directorio.$dni.'.png');
  //header('Content-Type: '.$qrCode->getContentType());

  $html = "";
  $mpdf = new \Mpdf\Mpdf([]);
  $mpdf->SetHTMLHeader("<img src='./img/logo.png' >");


  $html = '<br> <br> <br> <h2 style="text-align:center">' . $modulo . '</h2>

<div>
   Os datos para este exame son:

    <div style="float: right; width: 25%;background-color:#f4f4f2">

          
           <!-- <p   style="float:right;font-size:120%; text-align:center ; font-weight: bold;"> Quenda '.$turno.'<br>'.$horario.' </p> -->
		   <!-- <p   style="float:right;font-size:80%;text-align:center;  "> '.$horario.' </p>-->
                   <p   style="float:right;font-size:70%;text-align:center;  "> <img src="'.$directorio.$dni.'.png"> </p>
                     
	   

    </div>
    <div style="float: right; width: 25%;background-color:#f4f4f2">

          
           <p   style="float:right;font-size:190%; text-align:center ; font-weight: bold;"> Quenda '.$turno.' </p>
		   <p   style="float:right;font-size:180%;text-align:center;  "> '.$horario.' </p>
           <!--        <p   style="float:right;font-size:70%;text-align:center;  "> <img src="'.$directorio.$dni.'.png"> </p> -->
                     
	   

    </div>

    <div style="float: left; width: 50%;">
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


//Xeracion de PDF's cos tickets para cada alumno, gardanse polo DNI
function xerarXustificantePDF($modulo, $data, $nome, $dni, $aula, $horario, $turno, $mesa, $carpetaTempPDF, $dia, $mes, $modalidade, $nomeprofe )
{
	echo 'xusti <br>';
  $html = "";
  $mpdf = new \Mpdf\Mpdf([]);
  $mpdf->SetHTMLHeader("<img src='./img/logo.png' >");
  $mpdf->SetHTMLFooter('Páxina: {PAGENO}/{nbpg} -  Quenda:'.$turno.'- Mesa: '.$mesa);

  $htmlES = '<br> <br> <br> <h2 align="center"> CERTIFICADO DE ASISTENCIA A EXAMEN  </h2>

  <br>
  <br>
<div>
   <p  style="line-height: 200%"> D./Dona '.$nomeprofe.' profesor/a responsable del módulo '.$modulo.' que se imparte en el IES San Clemente en el 2º curso del Ciclo Superior  de Desarrollo de Aplicaciones Web en la modalidad de '.$modalidade.' en el curso 2020-2021.</p>

   <p  style="line-height: 200%"> <b> CERTIFICA QUE: </b> </p>

<p  style="line-height: 200%">
   El/la alumno/a '.$nome.' matriculado/a en dicha asignatura con DNI '.$dni.' ha asistido en la tarde de hoy a la realización del examen presencial correspondiente al primer parcial liberador de materia que se ha realizado, en el turno '.$turno.', en la franja horaria de '.$horario.' en el aula nº '.$aula.' del citado centro.
</p>

   <p  style="line-height: 200%"> Para que así conste, a petición del interesado y a los efectos oportunos, firmo la presente en  </p> 
  <br>
  <p align="center">   <img   src="./img/selo.png"></p>
  <br>
   <p  style="line-height: 200%" align="center"> Santiago de Compostela, a '.$dia.' de '.$mes.' de 2020. </p> ';


 $htmlGL = '<br> <br> <br> <h2 align="center"> CERTIFICADO DE ASISTENCIA AO EXAME  </h2>

 <br>
 <br>
<div>
  <p  style="line-height: 200%"> D. '.$nomeprofe.' profesor/a responsable do módulo '.$modulo.' que se imparte no IES San Clemente no 2º curso do Ciclo Superior  de Desenvolvemento de aplicacións web na modalidade de '.$modalidade.' no curso 2020-2021.</p>

  <p  style="line-height: 200%"> <b> CERTIFICA QUE: </b> </p>

<p  style="line-height: 200%">
  O/a alumno/a '.$nome.' matriculado/a en dito módulo con DNI '.$dni.' asistiu na tarde de hoxe á realización do exame presencial correspondente ao primeiro parcial liberador de materia que se realizou, na quenda '.$turno.', na franxa horaria de '.$horario.' na aula nº '.$aula.' do citado centro.
</p>

  <p  style="line-height: 200%"> Para que así conste, a petición do interesado e aos  efectos oportunos, asino a presente en   </p> 
 <br>
 <p align="center">   <img   src="./img/selo.png"></p>
 <br>
  <p  style="line-height: 200%" align="center"> Santiago de Compostela, a '.$dia.' de '.$mes.' de 2020. </p>

';

  $mpdf->WriteHTML($htmlGL);
  $mpdf->AddPage();
  $mpdf->WriteHTML($htmlES);
   
  $mpdf->Output($carpetaTempPDF.'/xusti_' . $dni . '.pdf', 'F');
}

function xerarListadoPDF($modulo, $data, $nome, $dni, $aula, $horario, $turno, $mesa, $carpetaTempPDF, $htmlListado, $modalidade, $dia, $mes,$nomeprofe )
{
	echo 'xusti <br>';
  $html = "";
  $mpdf = new \Mpdf\Mpdf([]);
  $mpdf->SetHTMLHeader("<img src='./img/logo.png' > <br>");
  $mpdf->SetHTMLFooter('Páxina: {PAGENO}/{nbpg} ');


 $htmlGL = '<br> <br> <br> <h3 align="center"> LISTADO PRESENTADOS AO PRIMEIRO EXAME PARCIAL PRESENCIAL DE '.$modulo.'</h3>';
 $htmlGL = $htmlGL.$htmlListado;
 


 $htmlGL = $htmlGL .'<br>
<div>

  <p  style="line-height: 200%"> D. '.$nomeprofe.' responsable do módulo '.$modulo.' que se imparte no IES San Clemente no 2º curso do Ciclo Superior  de Desenvolvemento de aplicacións web na modalidade de '.$modalidade.' no curso 2020-2021.</p>
  <p  style="line-height: 200%"> O resumo desta proba son ______ participantes, presentados ____  </p>

 <br>
 <p align="center">   <img   src="./img/selo.png"></p>
 <br>
  <p  style="line-height: 200%" align="center"> Santiago de Compostela, a '.$dia.' de '.$mes.' de 2020. </p>

';

  $mpdf->WriteHTML($htmlGL);

   
  $mpdf->Output($carpetaTempPDF.'/listado.pdf', 'F');
}



 
?>
