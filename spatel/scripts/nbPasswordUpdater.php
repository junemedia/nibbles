<?php

/************** Nibbles Password Updater ***************/

#include( "/home/scripts/includes/cssLogFunctions.php" );
#$iScriptId = cssLogStart( "nbPasswordUpdater.php" );
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$rTemp = dbQuery( "update nbUsers set currentPasswdLogins = '0', newPasswd=NULL WHERE userName='spatel' limit 1" );


$sNewPasswdSql = "SELECT id, passwd, newPasswd, userName, email
				FROM nbUsers
				WHERE newPasswd is not null";

$rNewPasswdResult = dbQuery( $sNewPasswdSql );
echo dbNumRows($rNewPasswdResult);
// if user has a newpasswd, put it into passwd and reset newPasswd to null
while( $oNewPasswdRow = dbFetchObject( $rNewPasswdResult )) {
	//echo "asdf";
	// uncomment for testing
	//if( $oNewPasswdRow->userName == 'matts' ) {
	
		$sUpdateNewPasswdSql = "UPDATE nbUsers
					SET passwd = '" . $oNewPasswdRow->newPasswd . "',
						newPasswd = NULL, 
						currentPasswdLogins = 0
					WHERE id = " . $oNewPasswdRow->id;
	
		dbQuery( $sUpdateNewPasswdSql );
		
		// shell out and update htpasswed file
		
		$cmd = "/usr/bin/htpasswd -b /home/global/.passwd " . $oNewPasswdRow->userName . " '" . $oNewPasswdRow->newPasswd . "'";
		system($cmd);
		echo "$oNewPasswdRow->userName\n";
		/**********  Send email  *************/
		$sHeaders = "From: it@amperemedia.com\n";
		$sHeaders .= "Reply-To: it@amperemedia.com\n";
		$sHeaders .= "X-Priority: 1\n";
		$sHeaders .= "X-MSMail-Priority: High\n";
		$sHeaders .= "X-Mailer: My PHP Mailer\n";

		$sPasswordEmailSubject = 'Your new Nibbles password';
		$sPasswordEmailBody = "Your new Nibbles password is " . $oNewPasswdRow->newPasswd . "\n";
		
		mail($oNewPasswdRow->email, $sPasswordEmailSubject, $sPasswordEmailBody, $sHeaders);

		/***************  End of sending email  **************/
		
		// start of track users' activity in nibbles 
		$sTrackingUser = 'root';
	
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '/home/scripts/nbPasswordUpdater.php', now(), 'Changed nibbles password: user: " . $oNewPasswdRow->userName . ", old password:  " . $oNewPasswdRow->passwd . ", new password: " . $oNewPasswdRow->newPasswd . "')"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
	// uncomment for testing
	//}

}

#cssLogFinish( $iScriptId );

?>
