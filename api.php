<?php
class cpaSurveyAPI
{
	public function __construct($pubid, $gateid)
	{
		if(isset($pubid) && isset($gateid))
		{
			$this->cpa_pubid 	= $pubid;
			$this->cpa_gateid	= $gateid;
			$this->cpa_subid	= 'bacon';
		}
		else
		{
			echo('CpaSurveyJson:: Invalid Class Setup');
		}
	}
	
	public function setSurveyPubid($pubid)
	{
		$this->cpa_pubid = $pubid;
	}
	public function setSurveyGateid($gateid)
	{
		$this->cpa_gateid = $gateid;
	}
	public function setSubid($subid)
	{
		$this->cpa_subid = $subid;
	}
	
	public function getSurveyArray()
	{
		return $this->getRawData();
	}
	public function getSurveyJson()
	{
		header('Content-type: application/json');
		return json_encode($this->getRawData());
	}
	
	//allows us to pull specific information from our javascript json
	private function getJSData($raw, $field)
	{	
		$jsResult;
		if(preg_match('/(?<="'.$field.'": ")([-\%\?\$\,\.\! 0-9a-zA-Z]+)/',$raw, $jsResult))
			return $jsResult[0];
		else
			return 'Field Does Not Exist';
	}
	
	private function getPath()
	{
		$selfurl = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

		//^([.\w:\/-]+/)(?=[.\w:\/-]+.php)
		$selfurl;
		if(preg_match_all('/^([.\w:\/-]+\/)(?=[.\w:\/-]+(.php|.html|.phtml|.xml|.json|.js|.rss))/',$selfurl, $newurl));
		{
			$newurl = $newurl[0][0];
		}
		return($newurl);
	}
	
	private function generateURL($json, $id)
	{
		//return 'redirect.php?r=http://'.$this->getJSData($json,'brandwwwdomain').'&u='.'http://'.$this->getJSData($json,'trackdomain').'/z/'.$id.'/'.$this->getJSData($json,'pub').'/'.$this->getJSData($json,'gateid').'/'.$this->getJSData($json,'subid').'/'.$this->getJSData($json,'gatehash').'/'.$this->getJSData($json,'cacheurl').'/';
		
		//return 'http://tbr-studio.com/cpajson/landing/redirect/'.$this->getJSData($json,'brandwwwdomain').'catu='.''.$this->getJSData($json,'trackdomain').'/z/'.$id.'/'.$this->getJSData($json,'pub').'/'.$this->getJSData($json,'gateid').'/'.$this->getJSData($json,'subid').'/'.$this->getJSData($json,'gatehash').'/'.$this->getJSData($json,'cacheurl').'/';
		return $this->getPath().$this->getJSData($json,'brandwwwdomain').'catu='.''.$this->getJSData($json,'trackdomain').'/z/'.$id.'/'.$this->getJSData($json,'pub').'/'.$this->getJSData($json,'gateid').'/'.$this->getJSData($json,'subid').'/'.$this->getJSData($json,'gatehash').'/'.$this->getJSData($json,'cacheurl').'/';
	}
	
	private function getRawData()
	{
		//some values needed by cpalead's servers
		$cururl			=	urlencode(base64_encode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
		$refurl			=	urlencode(getenv("HTTP_REFERER"));
		$subid			=	urlencode($_SERVER['REMOTE_ADDR']); //not really needed since we are not using postbacks, so i used the default IP set up

		//Where we fetch the surveys
		$surveyURL	=	'http://www.offeradvertising.biz/mygateway/mygateway_iframe_loader.php?pub='.$this->cpa_pubid.'&subid='.$this->cpa_subid.'&gateid='.$this->cpa_gateid .'&ref='.$refurl.'&cacheurl='.$cururl.'';

		//this holds the entire raw HTML from the survey
		$webdata	=	file_get_contents($surveyURL);
		
		//initialize vars for later data
		$javascriptJson;
		$surveyID;
		$surveyTitle;
		$surveyInfo;
		$surveyPoints;

		//gathers some data from the encoded javascript
		if(preg_match('/(?<=unescape\(\')([%0-9a-zA-Z]+)/',$webdata, $javascriptJson));
		{
			$javascriptJson = urldecode($javascriptJson[0]);	
		}

		//gathers survey ids
		if(preg_match_all('/(?<=clickSurvey\()([0-9]+)(?=, )/',$webdata, $surveyID));
		{
			$surveyID = $surveyID[0];
		}

		//gathers survey title
		if(preg_match_all('/(?<=<\/font><br \/>)([0-9a-zA-Z \!\.\,\-\$\!\?#&]+)(?=<\/div)/',$webdata, $surveyTitle));
		{
			$surveyTitle = $surveyTitle[0];
		}

		//gathers survey tooltips
		if(preg_match_all('/(?<=<\/font><br \/>)([0-9a-zA-Z \!\.\,\-\$\!\?#&]+)(?=<\/div)/',$webdata, $surveyInfo));
		{
			$surveyInfo = $surveyInfo[0];
		}

		//gets points for surveys
		if(preg_match_all('/(?<=pointsdisplay">)([0-9,]+)/',$webdata, $surveyPoints));
		{
			$surveyPoints = $surveyPoints[0];
		}
		
		//generate json
		$surveyCount = count($surveyID);
		$output;
		
		//creates basic data from the survey
		$output['cpainfo']['publisher']			= $this->getJSData($javascriptJson,'pub');
		$output['cpainfo']['subid']				= $this->getJSData($javascriptJson,'subid');
		$output['cpainfo']['gateid']				= $this->getJSData($javascriptJson,'gateid');
		$output['cpainfo']['gatehash']			= $this->getJSData($javascriptJson,'gatehash');
		$output['cpainfo']['cacheurl']			= $this->getJSData($javascriptJson,'cacheurl');
		$output['cpainfo']['surveycount']		= $surveyCount;
			
		//takes surveys and adds the data to the json array
		for($s=0;$s<$surveyCount;$s++)
		{
			//echo(generateURL($javascriptJson, $surveyID[$s]).'<br/>');
			$output['surveys'][$s]['points']		= (int)str_replace(',','',$surveyPoints[$s]);
			$output['surveys'][$s]['id']			= $surveyID[$s];
			$output['surveys'][$s]['title']			= htmlspecialchars_decode($surveyTitle[$s]);
			$output['surveys'][$s]['info']			= $surveyInfo[$s];
			$output['surveys'][$s]['url']			= $this->generateURL($javascriptJson, $surveyID[$s]);
		}
		
		//var_dump($surveyPoints);
		//var_dump($surveyTitle);
		var_dump($output);
		
		return $output;
		//return $webdata;
	}
}
?>