<?php
function http_check_url($url, $timeout = 10){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	if (strpos($url, 'https://') === 0) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // On ne vérifie que l'existence de la page
	}
	if (!curl_exec($ch)) {
		return FALSE;
	}
	$ret = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return in_array($ret, array(200, 301, 302));
}
?>