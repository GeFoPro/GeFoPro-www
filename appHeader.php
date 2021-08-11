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
	$app_instance = $_SESSION['instance'];
} else {
	// pas en session ou expirée
	header('Location: index.php');
}
if(isset($_SERVER['REDIRECT_URL'])) {
	$pageSelf = $_SERVER['REDIRECT_URL'];
}else {
	$pageSelf = $_SERVER['PHP_SELF'];
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
$pagesubs = explode("/",$pageSelf);
$nomPage = substr(end($pagesubs),0,-4);
//echo "<br>Home: ".$_SESSION['home'];

/* inclure les paramètres de connexion */
$config_file = "Config_".$app_section;
if(!empty($app_instance)) {
	$config_file .= "_";
	$config_file .= $app_instance;
}
require($config_file.".php");

/* connexion */
$connexionDB=null;
if($app_id=='admin') {
	$connexionDB = connexionAdmin(DBServer,$login,$mdp);
} else if($app_id=='doc') {
	$connexionDB = connexionDoc(DBServer,$login,$mdp);
} else if($app_id=='comp') {
	$connexionDB = connexionComp(DBServer,$login,$mdp);
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
	// maj traduction si nécessaire
	if(isset($_GET['actionTrad'])) {
		if($_GET['actionTrad']==='ModifierTous') {
			//$requeteTradREP = "UPDATE traduction set Texte='".$_GET['TexteTrad']."' where Libelle='".$_GET['LibelleTrad']."' and Langue='".$_GET['langTrad']."'";
			$requeteTradREP = "REPLACE into traduction (NomPage,Libelle,Langue,Texte) values ('*','".$_GET['LibelleTrad']."','".$_GET['langTrad']."','".utf8_decode(mysqli_real_escape_string($connexionDB,$_GET['TexteTrad']))."')";
			//echo $requeteTrad;
			mysqli_query($connexionDB,$requeteTradREP);
		} else if($_GET['actionTrad']==='Modifier') {
			//echo "->".utf8_decode($_GET['TexteTrad']);
			$requeteTradREP = "REPLACE into traduction (NomPage,Libelle,Langue,Texte) values ('".$nomPage."','".$_GET['LibelleTrad']."','".$_GET['langTrad']."','".utf8_decode(mysqli_real_escape_string($connexionDB,$_GET['TexteTrad']))."')";
			//echo $requeteTrad;
			mysqli_query($connexionDB,$requeteTradREP);
		}
	}
	// récupération des traductions
	$requeteTrad = "SELECT Libelle,Langue,Texte FROM traduction where NomPage = '".$nomPage."' OR NomPage='*'"; //  and Langue = '".$_SESSION['user_lang']."'";
	//echo $requeteTrad;
	$resultatTrad =  mysqli_query($connexionDB,$requeteTrad);
	while($resultatTrad!=null && $row =mysqli_fetch_assoc($resultatTrad)){
     $traductions[$row['Libelle']][$row['Langue']] = $row['Texte'];
	}
	//print_r($traductions);
}

function libelleTrad($libelle) {
	global $traductions;
	if(isset($traductions[$libelle][$_SESSION['user_lang']])) {
		$trad = $traductions[$libelle][$_SESSION['user_lang']];
	} else {
		if(isset($traductions[$libelle]['fr'])) {
			$trad = $traductions[$libelle]['fr'];
		} else {
			$trad = '???';
		}
	}
	return $trad;
}

function libelleTradUpd($libelle) {
	global $configTraducteur;
	$trad = libelleTrad($libelle);
	if($_SESSION['user_login']===$configTraducteur) {
		$returnedTxt =  "<div idTrad_".$libelle."='1' onclick='toggleTrad(\"idTrad_".$libelle."\")' style='display: inline-block;'>".$trad."</div>";
		$returnedTxt .= "<div idTrad_".$libelle."='1' style='display:none' onclick='toggleTrad(\"idTrad_".$libelle."\")'>";
		$returnedTxt .= "<input type='text' name='idTrad_".$libelle."' value=\"".$trad."\" onclick='limitEventTrad(event)' size='10' onChange='updateTraduction(\"".$libelle."\",\"".$pageSelf."\",\"".$_SERVER['QUERY_STRING']."\",this.value,\"".$_SESSION['user_lang']."\")' style='text-align: right'>";
		$returnedTxt .= "</div>";
	} else {
		$returnedTxt = $trad;
	}		
	return $returnedTxt;
}

function libelleTradUpdAll($libelle) {
	global $configTraducteur;
	$trad = libelleTrad($libelle);
	if($_SESSION['user_login']===$configTraducteur) {
		$returnedTxt =  "<div idTrad_".$libelle."='1' onclick='toggleTrad(\"idTrad_".$libelle."\")' style='display: inline-block;'>".$trad."</div>";
		$returnedTxt .= "<div idTrad_".$libelle."='1' style='display:none' onclick='toggleTrad(\"idTrad_".$libelle."\")'>";
		$returnedTxt .= "<input type='text' name='idTrad_".$libelle."' value=\"".$trad."\" onclick='limitEventTrad(event)' size='10' onChange='updateTraductionAll(\"".$libelle."\",\"".$pageSelf."\",\"".$_SERVER['QUERY_STRING']."\",this.value,\"".$_SESSION['user_lang']."\")' style='text-align: right'>";
		$returnedTxt .= "</div>";
	} else {
		$returnedTxt = $trad;
	}		
	return $returnedTxt;
}

function dispUpdField($id,$fieldname,$libelle,$size, $align) {
	$libelleTxt = $libelle;
	if(empty($libelle)) {
		$libelleTxt = "-";
	}
	$returnedTxt =  "<div idField_".$fieldname.$id."='1' onclick='toggleTrad(\"idField_".$fieldname.$id."\")' style='display: inline-block;'>".$libelleTxt."</div>";
	$returnedTxt .= "<div idField_".$fieldname.$id."='1' style='display:none' onclick='toggleTrad(\"idField_".$fieldname.$id."\")'>";
	$returnedTxt .= "<input type='text' name='idField_".$fieldname.$id."' value=\"".$libelle."\" onclick='limitEventTrad(event)' size='".$size."' onChange='updateField(".$id.",\"".$fieldname."\",this.value)' style='text-align: ".$align."'>";
	$returnedTxt .= "</div>";
	return $returnedTxt;
}
?>
