<?php
//include_once('/home/sites/www_popularliving_com/html/includes/paths.php');
//pixel class
//so, I'm going to need one big class

class Pixel {
	var $sourceCode;
	var $pageId;
	var $subSourceCode;
	var $alwaysDisplay;
	var $pixelHtml = '';
	var $_loaded = false;
	var $partnerId;		//the id pf the pixel's partner
	var $id;			//the id of this pixel
	var $campaignId;
	var $displayOption;
	var $type;
	var $serial;
	
	//var $

	//public function initWithId($id){
	function initWithId($id){
		//init the pixel from the pixel's id
		$initSQL = 'SELECT * FROM pixels WHERE id = '.$id;
		$res = dbQuery($initSQL);
		$data = dbFetchObject($res);
		
		$this->init($data->id, 
					$data->partnerId,
					$data->campaignId, 
					$data->displayOption, 
					$data->type, 
					$data->pixelHtml, 
					$data->sourceCode, 
					$data->subSourceCode, 
					$data->pageId, 
					$data->alwaysDisplay,
					$data->serial);
	}
	
	function init($id, $partnerId, $campaignId, $displayOption, $type, $pixelHtml, $sourceCode = '', $subSourceCode = '', $pageId = '0', $alwaysDisplay = '0', $serial = '0'){
		//init the pixel using parameters.
		$this->id = $id;
		$this->partnerId = $partnerId;
		$this->campaignId = $campaignId;
		$this->displayOption = $displayOption;
		$this->type = $type;
		$this->pixelHtml = $pixelHtml;
		$this->sourceCode = $sourceCode;
		$this->subSourceCode = $subSourceCode;
		$this->pageId = $pageId;
		$this->alwaysDisplay = $alwaysDisplay;
		$this->serial = $serial;
		
		$this->_loaded = true;

	}
	
	function incrementDisplays(){
		if($this->_loaded != true){
			return false;
		}
		
		$sCheckQuery = "SELECT id FROM otPixelTracking
						WHERE  sourceCode = '$this->sourceCode'
						AND    subSourceCode = '$this->subSourceCode'
						AND    openDate = CURRENT_DATE";
			$rCheckResult = dbQuery($sCheckQuery);
			echo dbError();
			if ( dbNumRows($rCheckResult) == 0 ) {
				$sPixelQuery = "INSERT INTO otPixelTracking(sourceCode, subSourceCode, openDate, opens)
							   VALUES('$this->sourceCode', '$this->subSourceCode',CURRENT_DATE, 1)";
			} else {
				$sPixelQuery = "UPDATE otPixelTracking
							   SET    opens = opens + 1
							   WHERE  sourceCode = '$this->sourceCode'
							   AND    subSourceCode = '$this->subSourceCode'
							   AND    openDate = CURRENT_DATE";
			}
			$rPixelResult = dbQuery($sPixelQuery);
			echo dbError();
		return true;
	}

	
	//html method for instances
	function html(){
		if($this->_loaded == false){
			return '';
		}
		//else, return the formatted pixel HTML.
		$out = $this->pixelHtml;
		
		if(strstr($out, '[serial]')){
			//replace this tag with the serial
			$out = str_replace('[serial]', $this->serial, $out);
			
			//then, update the serial number for this pixel
			$serialSQL = "UPDATE pixels SET serial = serial + 1 WHERE id = $this->id LIMIT 1";
			$res = dbQuery($serialSQL);
			
		}
		
		if(strstr($out, '[6_DIGIT_RAND_NUM]')){
			$out = str_replace('[6_DIGIT_RAND_NUM]', rand(100000, 999999), $out);
		}
		
		return $out;
	}
}

class PixelFactory {
	
	var $list = NULL;
	
	//factory methods
	function pixelList($sourceCode = ''){
		//this method should return the full list of pixels for the flow, indexed by page
		if($sourceCode == ''){
			return false;
		}
		
		//get the partner and campaign off the link for this source code
		$linkSQL = "SELECT partnerId, campaignId FROM links WHERE sourceCode = '$sourceCode'";
		//echo "$linkSQL <br>";
		$res = dbQuery($linkSQL);
		echo dbError();
		$partnerId = 0;
		$campaignId = 0;
		while($data = dbFetchObject($res)){
			$partnerId = $data->partnerId;
			$campaignId = $data->campaignId;
			//echo "$partnerId is partner id, and $campaignId is campaign id";
		}
		//look for pixels associated with that partner
		
		$pixelSQL = "SELECT * FROM pixels WHERE sourceCode = '$sourceCode' UNION ";
		$pixelSQL .= "SELECT * FROM pixels WHERE partnerId = $partnerId AND displayOption = 'global' UNION ";
		$pixelSQL .= "SELECT * FROM pixels WHERE partnerId = $partnerId AND displayOption = 'campaign' and campaignId = $campaignId UNION ";
		$pixelSQL .= "SELECT * FROM pixels WHERE partnerId = $partnerId AND displayOption = 'sourceCode' and sourceCode = '$sourceCode'";
		//echo $pixelSQL."\n";
		$res = dbQuery($pixelSQL);
		echo dbError();
		$pixels = array();
		$pix = null;
		while($data = dbFetchObject($res)){
			unset($pix);
			$pix = new Pixel();
			$pix->init($data->id,
					$data->partnerId,
					$data->campaignId, 
					$data->displayOption, 
					$data->type, 
					$data->pixelHtml, 
					$data->sourceCode, 
					$data->subSourceCode, 
					$data->pageId, 
					$data->alwaysDisplay,
					$data->serial);
					
			//array_push($pixels, clone $pix);
			array_push($pixels, $pix);
			
			//print_r($pix);
			//will there be issues with this? should I make a clone method?
		}
		
		//someday: if any of the pixels are 'lastPage', then find out how many pages are in this flow
		
		$this->list = $pixels;
		
		return $pixels;
	}
	
		
	function isLastPage ($templateType, $currentPosition, $noOfFlow) {
		if(($currentPosition == ($noOfFlow - 1)) || ($currentPosition >= $noOfFlow)){
			return true;
		}
		return false;
	}
	
	function isLandingPage($templateType, $currentPosition, $noOfFlow){
		if($currentPosition == 0){
			return true;
		}
		return false;
	}
	
	function isEmailCapPage($templateType, $currentPosition, $noOfFlow){
		if(($templateType == 'EP') || ($templateType == 'FRP')){
			return true;
		}
		return false;
	}
	
	function isRegPage($templateType, $currentPosition, $noOfFlow){
		if(($templateType == 'RP') || ($templateType == 'FRP')){
			return true;
		}
		return false;
	}
	
	function pixelListByPage($templateType, $currentPosition, $noOfFlow, $sourceCode = ''){
		//returns an array list of the pixels for the current page.
		if($this->list == NULL){
			if($sourceCode == ''){
				echo __file__.':'.__line__.": Call to PixelFactory::pixelList() without an sourceCode.\n";
				return array();
			} else {
				$list = $this->pixelList($sourceCode);
			}
		} else {
			$list = $this->list;
		}
		$out = array();
				
		if(PixelFactory::isLastPage($templateType, $currentPosition, $noOfFlow)){
			//then get all of the last page pixels
			foreach($list as $pixel){
				if($pixel->type == 'lastPage'){
					//$pixelStr .= $pixel->html()."\n";
					array_push($out, $pixel);
				}
			}
		}
		
		
		if(PixelFactory::isEmailCapPage($templateType, $currentPosition, $noOfFlow)){
			//then get all of the last page pixels
			foreach($list as $pixel){
				if($pixel->type == 'emailCap'){
					//$pixelStr .= $pixel->html()."\n";
					array_push($out, $pixel);
				}
			}
		}
		
		
		if(PixelFactory::isLandingPage($templateType, $currentPosition, $noOfFlow)){
			//then get all of the last page pixels
			foreach($list as $pixel){
				if($pixel->type == 'landingPage'){
					//$pixelStr .= $pixel->html()."\n";
					array_push($out, $pixel);
				}
			}
		}
		
		
		if(PixelFactory::isRegPage($templateType, $currentPosition, $noOfFlow)){
			//then get all of the last page pixels
			foreach($list as $pixel){
				if($pixel->type == 'regPage'){
					//$pixelStr .= $pixel->html()."\n";
					array_push($out, $pixel);
				}
			}
		}
		
		return $out;
	}
	
}
?>