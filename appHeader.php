<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:82
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: appHeader.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:05
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

session_start();
if(isset($_SESSION['section']) && !empty($_SESSION['section'])) {
	// en session
	$login = $_SESSION['login'];
	$mdp = $_SESSION['mdp'];
	$app_section = $_SESSION['section'];
} else {
	// pas en session ou expir�e
	header('Location: index.php');
}
//$app_section = $_SESSION['sct'];
//$app_section = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
//$app_id = substr($_SERVER['REQUEST_URI'],5,3);
//$res = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
//echo $res[0]." - ".$res[1]." - ".$res[2];
//$app_section = strtoupper($res[1]);
//$app_id = $res[2];
//echo "<br>URI: ".$_SERVER['REQUEST_URI'];
//echo "<br>SCT: ".$_SESSION['section'];
$subs = explode($app_section,$_SERVER['REQUEST_URI'])[1];
//echo "<br>sub: ".$subs;
$app_id = explode("/",$subs)[1];
//echo "<br>APPID: ".$app_id;
//echo "<br>Home: ".$_SESSION['home'];

/* inclure les param�tres de connexion */
require("Config_".$app_section.".php");

/* connexion */
$connexionDB=null;
if($app_id=='admin') {
	$connexionDB = connexionAdmin($serveur,$login,$mdp);
} else if($app_id=='doc') {
	$connexionDB = connexionDoc($serveur,$login,$mdp);
} else if($app_id=='comp') {
	$connexionDB = connexionComp($serveur,$login,$mdp);
}
if($connexionDB==null) {
	//echo $_SESSION['login']."/".$_SESSION['mdp']."/".$_SESSION['section'];
	session_destroy();
	//unset($_COOKIE[$app_section.'login']);
    //unset($_COOKIE[$app_section.'mdp']);
    //setcookie($app_section.'login', '', time()-3600);
    //setcookie($app_section.'mdp', '',  time()-3600);
	header('Location: index.php');

} else {
	mysqli_set_charset($connexionDB, "latin1");
}
?>
