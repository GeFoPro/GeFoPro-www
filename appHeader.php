<?php
session_start(); 
$login = $_SESSION['login'];
$mdp = $_SESSION['mdp'];
//$app_section = $_SESSION['sct'];
//$app_section = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
//$app_id = substr($_SERVER['REQUEST_URI'],5,3);
$res = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
//echo $res[0]." - ".$res[1]." - ".$res[2];
$app_section = strtoupper($res[1]);
$app_id = $res[2];

if(!isset($login)) {
	header('Location: /'.$app_section.'/index.php');
}

/* inclure les paramètres de connexion */
require("Config_".$app_section.".php");

/* connexion */
if($app_id=='admin') {
	$connexionDB = connexionAdmin($serveur,$login,$mdp);
} else if($app_id=='doc') {
	$connexionDB = connexionDoc($serveur,$login,$mdp);
} else if($app_id=='comp') {
	$connexionDB = connexionComp($serveur,$login,$mdp);
}
if($connexionDB==null) {
	session_destroy();
	//unset($_COOKIE[$app_section.'login']);
    //unset($_COOKIE[$app_section.'mdp']);
    //setcookie($app_section.'login', '', time()-3600);
    //setcookie($app_section.'mdp', '',  time()-3600);
	header('Location: /'.$app_section.'/index.php');
}
?>