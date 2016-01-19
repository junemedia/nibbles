<?php

include("../../includes/paths.php");

$sPageTitle = "Process Leads Help";

if ($sItem == 'useCurrentTable') {
	
	$sHelpContent = "<b>Use Current Table:</b><BR><BR> By default leads are processed and sent from history table.  If Use Current Table checkbox is checked, 
					current table will be used to get the data.";
} if($sItem == 'testMode') {
	
	$sHelpContent = "<b>Test Mode:</b><BR><BR> If Test Mode checkbox is checked, leads will not be marked as sent and lead emails will only be sent to Test Email Recipient(s).";
	
} if ($sItem == 'sendFormPostLeads') {
	
	$sHelpContent = "<b>Send Form Post Leads:</b><BR><BR> Leads are sent using Send Form Post Leads button if offer leads are delivered through form post, (e.g. KDN_INV offer).
					 Form post leads should be sent at last after sending regular leads and advisable to run on a separate machine.";
	
} else if ($sItem == 'setLeadsBack') {
	
	$sHelpContent = "<b>Set Leads Back:</b><BR><BR> Once you send the leads out, leads are marked as sent and not processed again. 
				If you want to sent the leads once again which are already sent, click Set Leads Back link at the top left of the screen.
				It will open a window to allow you to mark the leads as 'Not Sent'. 
				Select offercode or offer group and date range on which leads were sent which you want to set back.";
	
} else if ($sItem == 'realTimePost') {

	$sHelpContent = "<b>Real Time Post / Email:</b><BR><BR> If you want to send real time leads which may or may not have been sent real time, click the Real Time Post / Email link at the top left of the screen.
				It will open a window to allow you to send/resend real time leads. 
				This is applicable to only Real Time Form POST, Real Time Form GET, and Real Time Email offers.
				Select offerCode and date range on which leads were added which you want to send.
				<BR>If Rerun Leads is not checked, only unprocessed(not sent) real time leads will be sent.
				If its checked, all the leads of the selected offer within the selected date range will be resent.
				<BR>Click Post Today's Leads button to send today's leads, otherwise click Post History Leads for any other date range";
	
} else if ($sItem == 'realTimeDaysBack') {
	
	$sHelpContent = "<b>Send Lead Counts To Fred:</b><BR><BR> Lead counts for each offer except daily batch form post offers ( e.g. KDN_INV)
						will be calculated and sent to Fred, Stuart and Phil. 
						<BR><BR>Real time lead counts are calculated different way as leads are sent on same date as of leads added hence calculated leads sent yesterday. 
						If it's monday, real time lead counts should calculate leads sent on Fri, Sat and Sun i.e. Calculate real time leads of last 3 days.
						1 will be selected by default except monday. 3 will be selected by default on monday. 
						<BR>If there was any holiday yesterday, 2 should be selected for last two days counts of real time offers. ";
	
} else {
	// display all help
}

include("../../includes/adminAddHeader.php");
	
?>
<table width=80% cellpadding=0 cellspacing=0 border=0 align=center>
<tr><td>
<?php echo $sHelpContent;?>
</td></tr>
<tr><td align=center><BR><BR><input type=button name=sClose value=Close onClick='self.close();'></td></tr>
</table>
</body>
</html>