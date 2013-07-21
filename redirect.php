<?php
//(?<=catu=)([.\w=:/-]+)$
//^([.\w=:/-]+)(?=cat)


//$togo	=	$_GET['u'];
//$ref		=	$_GET['r'];
$base = $_GET['r'];

$ref;
if(preg_match('/^([.\w=:\/-]+)(?=cat)/',$base, $ref));
{
	$ref = 'http://'.$ref [0];	
}

$togo;
if(preg_match('/(?<=catu=)([.\w=:\/-]+)$/',$base, $togo));
{
	$togo = 'http://'.$togo [0];	
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$togo);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_REFERER, $ref);
$out = curl_exec($ch);
echo('<h3>Redirecting</h3>');
//echo($togo.'<br/>');
//echo($ref.'<br/>');
echo($out);

?>