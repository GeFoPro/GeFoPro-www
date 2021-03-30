<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:01
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: checkURL.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:88
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

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
