<?php
/*
include("POP3-1.0/class.POP3.php3");

$pop3 = new POP3();

if (! $pop3->connect("mail.attbi.com", 995)) {
	echo "Server: 'mail.attbi.com' Ooops $pop3->ERROR <BR>\n";
}else {
	echo "connected";
}
$pop3->quit();*/

$mbox = imap_open ("{mail.earthlink.net:110/pop3}INBOX", "myfree3400", "cubicle");
//$mbox = imap_open ("{pop.mail.yahoo.com:110/pop3}INBOX", "myfree3400", "cubicle");
//$mbox = imap_open ("{pop.ameritech.yahoo.com:110/pop3}INBOX", "myfree3400", "cubicle");

//$mbox = imap_open ("{mail.attbi.com:993/pop3/ssl}INBOX", "myfree3400@myfree.com", "cubicle1");
//$mbox = imap_open ("{mail.myfree.com:110/pop3/}INBOX", "seed@mail.myfree.com", "january");
$errors = imap_errors();
while(list($key,$val) = each($errors)) {
	echo "<br>$val";
}


$check = imap_check ($mbox);

print "Earthlink Messages: " . $check->Nmsgs   ;
imap_close($mbox);
?>