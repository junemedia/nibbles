<?php

include('/home/sites/admin.popularliving.com/html/includes/paths.php');

$sOffersDownSources = "select apiRejectionLog.sourceCode as sourceCode, apiRejectionLog.offerCode as offerCode, count(apiRejectionLog.id) as count, offerCompanies.repDesignated from apiRejectionLog left join offers on offers.offerCode = apiRejectionLog.offerCode left join offerCompanies on offerCompanies.id = offers.companyId where datePosted = CURRENT_DATE and reason like '%Offer%is down%' group by sourceCode, offerCode;";

$res = dbQuery($sOffersDownSources);

$out = "<html><body><table border=1><tr><td>Source Code</td><td>Offer Code</td><td>Acct. Exec.</td><td>Count</td></tr>";
$sources = array();
if(dbNumRows($res) > 0){
	
	while($badApi = dbFetchObject($res)){

		$sUserQuery = "SELECT firstName, lastName FROM nbUsers where id in (".$badApi->repDesignated.")";
		$res = dbQuery($sUserQuery);
		$oUser = dbFetchObject($res);


		$out .= "<tr><td>".$badApi->sourceCode."</td><td>".$badApi->offerCode."</td><td>".$oUser->firstName.' '.$oUser->lastName."</td><td>".$badApi->count."</td></tr>\n";
		array_push($sources, $badApi->sourceCode);
	}

	$out .= "</table><br><br><table>";
	$sGetAcctSQL = "SELECT partnerCompanies.companyName as companyName, 
			partnerContacts.contact as contact, 
			partnerContacts.email as email, 
			partnerContacts.phoneNo as phoneNo, 
			links.sourceCode as sourceCode
                FROM partnerCompanies, partnerContacts, links
                WHERE links.partnerId = partnerCompanies.id
                AND partnerCompanies.id = partnerContacts.partnerId
                AND links.sourceCode in ('".join("','",$sources)."') ";

	$res = dbQuery($sGetAcctSQL);
	while($contact = dbFetchObject($res)){
	
		$out .= "<tr><td>Source Code:  ".$contact->sourceCode."<br></td></tr>";
        	$out .= "<tr><td>Partner: ".$contact->companyName."<br>".$contact->contact."<br>".$contact->email."<br>".$contact->phoneNo."<br></td>";
		$out .= "</td></tr>\n";

	}

	$out .= "</table></body></html>";	

        $sGetEmail = "SELECT * FROM emailRecipients WHERE purpose='Offer Down API Alert'";
        $rEmailResult = dbQuery($sGetEmail);
        //$sEmailTo = '';
	
	$oEmailRow = dbFetchObject($rEmailResult);
        $sEmailTo = $oEmailRow->emailRecipients;
        //echo $sEmailTo;

	$sHeader  = "MIME-Version: 1.0\r\n";
        $sHeader .= "Content-type: text/html; charset=iso-8859-1\r\n";
       	$sHeader .= "FROM: nibbles@amperemedia.com\r\n";
        //$sHeader .= "CC: $sEmailTo\r\n";

	foreach(explode(',',$sEmailTo) as $sEmail){
		if(($sEmail != '') && (strpos($sEmail,'@'))){
			mail($sEmail,'Client Posting API Leads While Offer Is Down',$out,$sHeader);
		}
	}
}
?>
