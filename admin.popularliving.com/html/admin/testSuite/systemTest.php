<?php

// Automated testing suite

$testsRun = 0;
$testsPass = 0;
$testsFail = 0;
$errors = "";
$scriptStartTime = getMicroTime();

// include sql access.

include_once("../../includes/paths.php");

// include librarys with classes & functions to be tested.

include("../../libs/validationFunctions.php");


// Start tests

    // Test validateName

        // Check if name is not sample bad word (shit).
        // Should fail if bad word passed.
        
        $testsRun += 1 ;
        
        if (validateName("shit") == false) {
        
               $testsPass += 1 ;
           
        } else {
        
        $testsFail += 1;
        $errors .= "validateName - sample bad word fails" . "\n";
        
        }
        
        
        // Check that name does not contain three vowels in a row.
        // Should fail if three vowels in a row.
    
        $testsRun += 1 ;
        
        if (validateName("jeeeen") == false) {
        
               $testsPass += 1 ;
           
        } else {
        
        	$testsFail += 1;
        	$errors .= "validateName - four vowels fails" . "\n";
        
        }
        
        // Check that name does not contain five constants in a row
    
        // Check that name is at least 1 char long.
    
        // Check that known good name (john) passes
    
        $testsRun += 1;
        
       	if(validateName("john") == true ){
        
             $testsPass += 1 ;
           
        } else {
        
        	$testsFail += 1;
        	$errors .= "validateName - known good name fails" . "\n";
        
        }
    
        // validate phone tests
        
        // check valid phone no
         $testsRun += 1;
        
        if (validatePhone("847", "205", "9320", '', 'IL') == true ){
        
               $testsPass += 1 ;
           
        } else {
        
      		 $testsFail += 1;
       		 $errors .= "validatePhone - known good phone no fails" . "\n";
        
        }
        
        // check sample banned phone no
        $testsRun += 1;
        
        if(validatePhone("630", "588", "1621", '', 'IL') == false ){
        
               $testsPass += 1 ;
           
        } else {
        
	        $testsFail += 1;
    	    $errors .= "validatePhone - Sample banned phone no. fails" . "\n";
        
        }
        
        // check for aol or rr in BDA
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
        	} else {
        		$testsFail += 1;
        		$errors .= "Check for aol or rr in BDA - Found $iNumRows aol or rr emails subscribed to BDA\n";
        	}
        } else {
        	$testsFail += 1;
        	$errors .= "Check for aol or rr in BDA - Test couldn't be finished.\n";
        }
        							
        // check for BDA only people in total
        
       
        
// End tests



$scriptEndTime = getMicroTime();
$scriptExecutionTime = $scriptEndTime - $scriptStartTime;

print "\n\n";
Print "Number of Tests Run: " . $testsRun . "\n\n";
Print "Number of Tests Passed: " . $testsPass . "\n\n";
Print "Number of Tests Failed: " . $testsFail . "\n\n";
Print "Execution Time: " . $scriptExecutionTime . "\n\n";
print "Errors: " . $errors . "\n\n";


// functions

function getMicroTime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

?>
