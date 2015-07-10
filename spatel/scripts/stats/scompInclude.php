<?php

$savedirpath = '';
$jk=new readattachment(); // Creating instance of class####
$sMessageFromAttachment = $jk->getdata($scompMailServer,$scompUsername,$scompPasswd,$savedirpath); // calling member function


###################### Class read attachment ###############
class readattachment {
	
	function getdecodevalue($message,$coding) {
		echo 'insdie decode: '."$coding ";
		if ($coding == 0) { 
			$message = imap_8bit($message); 
		} elseif ($coding == 1) { 
			$message = imap_8bit($message); 
		} elseif ($coding == 2) { 
			$message = imap_binary($message); 
		} elseif ($coding == 3) { 
			$message=imap_base64($message); 
		} elseif ($coding == 4) { 
			$message = imap_qprint($message); 
		} elseif ($coding == 5) { 
			$message = imap_base64($message); 
		}
		return $message;
	}

	function getdata($host,$login,$password,$savedirpath) {
		$mbox = imap_open('{mail.amperemedia.com:110/pop3/novalidate-cert}INBOX', 'scomp', 'scomp200') or die("can't connect: " . imap_last_error());
		$message = array();
		$message["attachment"]["type"][0] = "text";
		$message["attachment"]["type"][1] = "multipart";
		$message["attachment"]["type"][2] = "message";
		$message["attachment"]["type"][3] = "application";
		$message["attachment"]["type"][4] = "audio";
		$message["attachment"]["type"][5] = "image";
		$message["attachment"]["type"][6] = "video";
		$message["attachment"]["type"][7] = "other";

		for ($jk = 1; $jk <= imap_num_msg($mbox); $jk++) {
			$data = '';
			$structure = imap_fetchstructure($mbox, $jk , FT_UID);    
			$parts = $structure->parts;
			print_r($parts);
			$fpos=2;
			for($i = 0; $i < count($parts); $i++) {
				$message["pid"][$i] = ($i);
				$part = $parts[$i];
				if($part->disposition == "ATTACHMENT" || $part->disposition == "INLINE") {
					$message["type"][$i] = $message["attachment"]["type"][$part->type] . "/" . strtolower($part->subtype);
					$message["subtype"][$i] = strtolower($part->subtype);
					$ext=$part->subtype;
					$params = $part->dparameters;

				  	$data = imap_fetchbody($mbox,$jk,$fpos);
				  	$data = str_replace("*mf ", "\n*mf ",$data);
					//$data .= $this->getdecodevalue($mege,$part->type);
					$fpos+=1;

					$header = imap_header($mbox, $jk);
					$from = $header->from;
					foreach ($from as $id => $object) {
					   $fromname = $object->personal;
					   $fromaddress = $object->mailbox . "@" . $object->host;
					}
				}
			}
			if ($data != '') {
				if ($fromaddress == '') { $fromaddress = 'scomp@amperemedia.com'; }
				mail('scomp@amperemedia.com', 'attachment', $data, "From: $fromaddress\r\n");
				imap_delete($mbox,$jk);
			}
		}
		imap_expunge($mbox);
		imap_close($mbox);
	}
}



?>