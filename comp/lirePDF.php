<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:96
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: lirePDF.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:66
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

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


$result=mysqli_query($connexionDB,"SELECT datasheet FROM composant WHERE IDComposant=$IDComp");
$pdfdata=mysqli_fetch_array($result);
if(!empty($pdfdata['datasheet'])) {
	header('Content-type: application/pdf');
	echo $pdfdata['datasheet'];
} else {
	echo "<font color=red>Aucune Datasheet disponible pour ce composant</font>";
}

?>
