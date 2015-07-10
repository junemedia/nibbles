<?

//
// ----------------------------- EMAILS --------------------------------
//

//globales
$headers='';
$subject='';
$recipient='';
$body='';
$separateur='';

function CreateEmail($destinataires,$sujet,$contenu) {
GLOBAL $headers,$subject,$recipient,$body,$separateur,$SERVER_NAME;

  $headers='';
  $subject='';
  $recipient='';
  $body='';
  $separateur='12EDAIN34';
  $From="leon.subctr. <leonz@junemedia.com>";


  $subject=$sujet;
  $headers.="MIME-Version: 1.0\r\nFrom: $From\n";
  $tableau=array();
  $tableau=explode(';',$destinataires);
  for ($i=0;$i<count($tableau);$i++) {
  if (substr($tableau[$i],0,3)=='CC:') {
    $adresses=substr($tableau[$i],3); 
    $headers2="cc: $adresses\n";
  } else if (substr($tableau[$i],0,4)=='BCC:') {
    $adresses=substr($tableau[$i],4);
    $headers2.="Bcc: $adresses\n";
  } else {
    if (substr($tableau[$i],0,3)=='To:') $adresses=substr($tableau[$i],3);
     else $adresses=$tableau[$i];
    $recipient="$adresses";
    }
  }
  $headers.="Reply-To: $From\r\nX-Mailer: PHP/" . phpversion()."\r\nX-Priority: 3\r\n";
  $headers.="Content-Type: multipart/mixed; boundary=\"$separateur\"\r\n";
  $headers.="Content-Transfer-Encoding: 7bit\r\n";
  $headers.=$headers2;
  $body.="This part of the E-mail should never be seen. If\nyou are reading this, consider upgrading your e-mail\nclient to a MIME-compatible client.\r\n";
  $body.="\r\n--$separateur\r\n";
  $body.="Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
  $body.="Content-Transfer-Encoding: 7bit\r\n";
  $body.="\r\n$contenu\r\n";
}

function AddAttachment( $filename ) {
  GLOBAL $headers,$subject,$recipient,$body,$separateur;
  if (strpos($filename,'.')) $locext=substr($filename,strpos($filename,'.'));
   else $locext='';
  $locext=strtolower($locext);
  switch ($locext) {
    case '.jpg' :
    case '.jpe' :
    case '.jpeg' : $locmimetype='image/jpeg'; $locencode='base64'; break;
    case '.txt' : $locmimetype='text/plain'; $locencode="7bit"; break;
    case '.ai' :
    case '.eps' :
    case '.ps' : $locmimetype='application/postscript'; $locencode="7bit"; break;
    case '.rtf' : $locmimetype='application/rtf'; $locencode="7bit"; break;
    case '.wav' : $locmimetype='audio/x-wav'; $locencode="base64"; break;
    case '.gif' : $locmimetype='image/gif'; $locencode="base64"; break;
    case '.tiff' :
    case '.tif' : $locmimetype='image/tiff'; $locencode="base64"; break;
    case '.html' : $locmimetype='text/html'; $locencode="7bit"; break;
    case '.mpeg' :
    case '.mpg' :
    case '.mpe' : $locmimetype='video/mpeg'; $locencode="base64"; break;
    case '.mov' : $locmimetype='video/quicktime'; $locencode="base64"; break;
    case '.avi' : $locmimetype='video/x-msvideo'; $locencode="base64"; break;
    case '.doc' : $locmimetype='application/msword'; $locencode="base64"; break;
    case '.pdf' : $locmimetype='application/pdf'; $locencode="base64"; break;
    default : $locmimetype='text/plain'; $locencode="7bit"; break;
  }
  $locname=basename($filename);
  $fd = fopen ($filename, "r");
  $dataread = fread ($fd, filesize ($filename));
  fclose ($fd);
  if ($locencode=='base64') $locdata=base64_encode($dataread);
   else $locdata=$dataread;
  $body.="\r\n--$separateur\r\n";
  $body.="Content-Type: $locmimetype; name=$locname\r\n";
  $body.="Content-Transfer-Encoding: $locencode\r\n";
  $body.="Content-Description: $locname\r\n";
  $body.="Content-Disposition: attachment\r\n";
  $body.="\r\n$locdata\r\n";
}


function CloseAndSend() {
    GLOBAL $headers,$subject,$recipient,$body,$separateur;
    $body.="\r\n--$separateur--\r\n";
    mail("$recipient", "$subject", "$body","$headers");
    /*:
    echo "recipient ".htmlspecialchars($recipient)."<HR>";
    echo "subject ".htmlspecialchars($subject)."<HR>";
    echo "body ".htmlspecialchars($body)."<HR>";
    echo "headers ".htmlspecialchars($headers)."<HR>";
    */
}
?>