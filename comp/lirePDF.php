<?php
include("../appHeader.php");
$IDComp = "";
if(isset($_GET['IDComposant'])) {
	$IDComp = $_GET['IDComposant'];
}

if(!isset($login)) {
	if(isset($_GET['modeAltium'])) {
		$login = DBUser;
		$mdp = DBPwd;
		$_SESSION['user_nom'] = "ELT";
		$_SESSION['user_type'] = "Altium";
	} else {
		// utilisateur par défaut -> invité
		$login = DBUser;
		$mdp = DBPwd;
		$_SESSION['user_nom'] = "Invité";
		$_SESSION['user_type'] = "Anonyme";
	}
}


$result=mysql_query("SELECT datasheet FROM composant WHERE IDComposant=$IDComp");
$pdfdata=mysql_fetch_array($result);
if(!empty($pdfdata['datasheet'])) {
	header('Content-type: application/pdf');
	echo $pdfdata['datasheet'];
} else {
	echo "<font color=red>Aucune Datasheet disponible pour ce composant</font>";
}

?>

