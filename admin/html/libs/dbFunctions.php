<?php

// customized db functions 


	function dbConnect( $dbHost, $dbUser, $dbPass )
	{
		$rLink = @mysql_connect ($dbHost, $dbUser ,$dbPass);
		if ($rLink)
			return ($rLink );	 		
		else
			return 0 ;
	}

	
  	function dbSelect($dbName)
  	{
  		return (mysql_select_db($dbName) );
  	}
  	
    
 	function dbInsertId()
 	{
		return (mysql_insert_id () ) ;

  	}
  	
  	
  	function dbNumRows($rResult)
  	{  		
  		if ($rResult) {
  			return (mysql_num_rows($rResult));	
  		}
  		
  	}
  	

	function dbAffectedRows()
	{
		return ( mysql_affected_rows () ) ;
	}  

	
	function dbDataSeek ($rResult, $iOffset)
	{
		if ($rResult) {
			return (mysql_data_seek ($rResult, $iOffset) ) ;
		}
	}
	
	
	function dbFetchObject($rResult)
	{
		if ($rResult) {
			return ( mysql_fetch_object($rResult) );
		}
	}
	
	
	function dbFetchArray($rResult)
	{
		if ($rResult) {
			return ( mysql_fetch_array($rResult) );
		}
	}
	
	
	function dbFetchRow($rResult)
	{
		if ($rResult) {
			return ( mysql_fetch_row($rResult) );
		}
	}
	
	
    function dbErrno()
    {
		return (mysql_errno () ) ;
	}
	
	
	function dbError()
	{
		return (mysql_error ( ) ) ;
	}
	
	
  	function dbFreeResult($rResult)
  	{
  		if ($rResult) {
		  	return ( mysql_free_result($rResult) ) ;
  		}
	}	
	
	
	
	function dbNumFields($rResult)
	{
		if ($rResult) {
			return (mysql_num_fields($rResult));
		}
	}
	
	function dbFetchField($rResult)
	{
		if ($rResult) {
			return (mysql_fetch_field($rResult));
		}
	}
	
	
	function dbFieldName($rResult,$iFieldNum)
	{
		if ($rResult) {
			return (mysql_field_name($rResult,$iFieldNum));
		}
	}
	

	function dbQuery($sQuery) {
		
    	//ascii chars 10, 13 and 32 to 126 are only allowed withing the query.
    	/*for ($i=0; $i < strlen($sQuery); $i++) {
    		
    		$sAsciiStr = ord(substr($sQuery,$i));    		
    		if ( ($sAsciiStr < 32 || $sAsciiStr > 126) && $sAsciiStr != 10 && $sAsciiStr != 13 && $sAsciiStr != 9) {
    			echo "char not valid".ord(substr($str,$i,1));
    			return 0;
    		}
    		
    	}*/
    	
		$rResult = mysql_query($sQuery);

		if (!$rResult) {
			
			return 0;
		} else {
			return($rResult);
		}
	}


//function to perform the query receives the query string and
//returns the resultset and number of records found else exits
/*
	function cst_query_select($query_string, &$totalRecordFound)
	{
		$rResult = cst_query($query_string);

		if(!$rResult)
		{

			$error =  "Database Error: <BR>";
			$error_desc = cst_errno()." : ".cst_error() ;
			echo $error."<br>".$error_desc ;			
		}
		else
		{
			$totalRecordFound = cst_num_rows($rResult) ;

			return($rResult);
		}
	}


	
	// function to fetch result in multidimensional array

function cst_fetchData($queryString)
{
	if($queryString)
	{
		$rResult = cst_query($queryString);
		if($rResult)
		{
			$i = 0;
			while($title = cst_fetch_field($rResult))
			{
				$fieldName[$i] = $title->name;
				$i++;
			}
			$j = 0;
			while($row = cst_fetch_object($rResult))
			{
				for($i = 0; $i< count($fieldName); $i++)
				{
					$key =  $fieldName[$i];
					$rResultRow[$key][$j] = $row->$key;
				}
				$j++;
			}
			cst_free_result($rResult);
			return $rResultRow;
		}
		else
		{
			//echo $queryString.mysql_error();
			return(0);
		}
	}
	else
		return(0);

}

*/
?>