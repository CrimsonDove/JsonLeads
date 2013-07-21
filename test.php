<h3>CPAlead text survey generator test</h3>
<p>numbers in brackets indicate points the survey is worth (100:1 ratio)</p>
<?php

	include_once "api.php";
	$api = new cpaSurveyAPI("55524", "NDcwNjE4");

	$data = $api->getSurveyArray();
	
	$surveycount = $data['cpainfo']['surveycount'];
	if($surveycount > 0)
	{
		for($i=0; $i < $surveycount; $i++)
		{
			echo(($i+1).'. ['.$data['surveys'][$i]['points'].'] <a href="redirect/'.$data['surveys'][$i]['url'].'">'.$data['surveys'][$i]['title'].'<a/><br/>');
		}
	}
	else
	{
		echo('No surveys!');
	}
?>