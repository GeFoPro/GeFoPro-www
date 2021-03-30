<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:59
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: impressionJournal.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:36
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

if(hasAdminRigth()) {
	if(isset($_GET['nom'])) {
		$nom = $_GET['nom'];
		$prenom = $_GET['prenom'];
		$IDEleve = $_GET['idEleve'];
	} else if(isset($_POST['nom'])) {
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$IDEleve = $_POST['IDEleve'];
	}

} else {
	// tentative de recherche par userid
	$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where Userid='".$_SESSION['user_login']."'";
    	//echo $requete;
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_assoc($resultat);
	$nom = $ligne['Nom'];
	//echo "Nom: ".$nom;
	$prenom = $ligne['Prenom'];
	$IDEleve = $ligne['IDGDN'];
	$classe = $ligne['Classe'];
}

$IDTheme=0;
if(isset($_POST['IDTheme'])) {
	$IDTheme = $_POST['IDTheme'];
} else if(isset($_GET['IDTheme'])) {
	$IDTheme = $_GET['IDTheme'];
}


$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}
include("entete.php");
?>

<div id="page">
<?
include($app_section."/userInfo.php");
/* en-t