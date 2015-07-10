<?php

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "systemTest.php" );

// Automated testing suite

$testsRun = 0;
$testsPass = 0;
$testsFail = 0;
$errors = "";
$scriptStartTime = getMicroTime();

// include sql access.

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

// include librarys with classes & functions to be tested.
include_once("/home/sites/admin.popularliving.com/html/libs/validationFunctions.php");



// Start tests

    // Test validateName

        // Check if name is not sample bad word (shit).
        // Should fail if bad word passed.
        
        $testsRun += 1 ;
        $testDesc = "\n\nvalidateName - sample bad word fails - ";
        
        if (validateName("shit") == false) {
        
               $testsPass += 1 ;
           		$testDesc .= "Passed";
               
        } else {
        
        $testsFail += 1;
        $testDesc .= "Failed";
        $errors .= "validateName - sample bad word fails" . "\n";
        
        }
        
        
        // Check that name does not contain three vowels in a row.
        // Should fail if three vowels in a row.
    
        $testsRun += 1 ;
        $testDesc .= "\n\nvalidateName - four vowels fails - ";
        if (validateName("jeeeen") == false) {
        
               $testsPass += 1 ;
               $testDesc .= "Passed";
           
        } else {
        
        	$testsFail += 1;
        	$testDesc .= "Failed";
        	$errors .= "validateName - four vowels fails" . "\n";
        
        }
        
        // Check that name does not contain five constants in a row
    
        // Check that name is at least 1 char long.
    
        // Check that known good name (john) passes
    
        $testsRun += 1;
        $testDesc .= "\n\nvalidateName - known good name fails - ";
       	if(validateName("john") == true ){
        
             $testsPass += 1 ;
           $testDesc .= "Passed";
        } else {
        
        	$testsFail += 1;
        	$testDesc .= "Failed";
        	$errors .= "validateName - known good name fails" . "\n";
        
        }
    
        // validate phone tests
        
        // check valid phone no
         $testsRun += 1;
        $testDesc .= "\n\nvalidatePhone - known good phone no fails - ";
        if (validatePhone("847", "205", "9320", '', 'IL') == true ){
        
               $testsPass += 1 ;
           		$testDesc .= "Passed";
        } else {
        
      		 $testsFail += 1;
      		 $testDesc .= "Failed";
       		 $errors .= "validatePhone - known good phone no fails" . "\n";
        
        }
        
        // check sample banned phone no
        $testsRun += 1;
        $testDesc .= "\n\nvalidatePhone - Sample banned phone no. fails - ";
        if(validatePhone("630", "588", "1621", '', 'IL') == false ){
        
               $testsPass += 1 ;
           		$testDesc .= "Passed";
        } else {
        
	        $testsFail += 1;
	        $testDesc .= "Failed";
    	    $errors .= "validatePhone - Sample banned phone no. fails" . "\n";
        
        }
        
        // check for aol or rr in BDA
        $testsRun += 1;
        $testDesc .= "\n\nCheck for aol or rr in BDA - ";
        $sTestSuiteQuery = "SELECT *
        					FROM    joinEmailActive
        					WHERE   joinListId = '215'
        					AND     (email LIKE '%@aol.com'	OR email LIKE '%@aol.net'
        							|| email LIKE '%@rr.com' OR email LIKE '%@rr.net')";
        $rTestSuiteResult = dbQuery($sTestSuiteQuery);
        if ($rTestSuiteResult) {
        	$iNumRows = dbNumRows($rTestSuiteResult);
        	if ( dbNumRows($rTestSuiteResult) == 0) {
        		$testsPass+= 1;
        		$testDesc .= "Passed";
        	} else {
        		$testsFail += 1;
        		$testDesc .= "Failed";
        		$errors .= "Check for aol or rr in BDA - Found $iNumRows aol or rr emails subscribed to BDA\n";
        	}
        } else {
        	$testsFail += 1;
        	$testDesc .= "Failed";
        	$errors .= "Check for aol or rr in BDA - Test couldn't be finished.\n";
        }
        				
          // check for mwfeedback table cleaning up
        $testsRun += 1;
        $testDesc .= "\n\nCheck for mwfeedback table backlog - ";
        $sTestSuiteQuery = "SELECT *
        					FROM    mwfeedback
        					WHERE   dateTimeAdded < date_add(dateTimeAdded, INTERVAL -1 DAY)";
        $rTestSuiteResult = dbQuery($sTestSuiteQuery);
        if ($rTestSuiteResult) {
        	$iNumRows = dbNumRows($rTestSuiteResult);
        	if ( dbNumRows($rTestSuiteResult) == 0) {
        		$testsPass+= 1;
        		$testDesc .= "Passed";
        	} else {
        		$testsFail += 1;
        		$testDesc .= "Failed";
        		$errors .= "Check for mwfeedback table backlog - Found $iNumRows records older than 1 24 hours\n";
        	}
        } else {
        	$testsFail += 1;
        	$testDesc .= "Failed";
        	$errors .= "Check for mwfeedback table backlog - Test couldn't be finished.\n";
        }
        
// End tests



$scriptEndTime = getMicroTime();
$scriptExecutionTime = $scriptEndTime - $scriptStartTime;

print "\n\n";
Print "Summary\n\n";
Print "Number of Tests Run: " . $testsRun . "\n\n";
Print "Number of Tests Passed: " . $testsPass . "\n\n";
Print "Number of Tests Failed: " . $testsFail . "\n\n";
Print "Execution Time: " . $scriptExecutionTime . "\n\n";
print "Errors: " . $errors . "\n\n";

print "Tests run:\n";
print $testDesc;


// functions

function getMicroTime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

cssLogFinish( $iScriptId );

?>
