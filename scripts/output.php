<?php


//error_log(print_r($data, true));

$data = json_decode($data);
$saveto = "docs/".$data->documentation."-".$data->path.".html";



//if(file_exists($saveto)) unlink($saveto);

if(!file_exists($saveto)){


	$url = 'http://docs.devdocs.io/'.$data->documentation.'/'.$data->path.'.html';

	$ch = curl_init ($url);


	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);


	$raw=curl_exec($ch);

	curl_close ($ch);


	$style = file_get_contents('scripts/style.css');

	$html = '<div id="ttWrapper">';
	
	$html .= $raw.'<style>'.$style.'</style>';
	$html .= '</div>';

	$fp = fopen($saveto,'x');
	fwrite($fp, $html);
	fclose($fp);

//  unlink($saveto);
}

exec('qlmanage -p '.$saveto.' -x');
echo $url;

?>
