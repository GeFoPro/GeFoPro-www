<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:01
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: checkLien.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:54
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

$subs = explode("/",$_SERVER['REQUEST_URI']);
$app_section = $subs[count($subs)-3];
//$app_section = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
require("Config_".$app_section.".php");
$connexionELT = connexionDoc("localhost",DBUserAdmin,DBPwdAdmin);

include("checkURL.php");

$message = 'Gestion des documents - liste des liens invalides<br><br>';
// tester les document
$resultDoc = mysqli_query($connexionELT,'SELECT * FROM document');
while ($doc = mysqli_fetch_array($resultDoc)) {
	echo $doc['Libelle'].":\n";
	// test du lien sur le document
	if(!empty($doc['LienLibelle'])) {
		echo "  ".$doc['LienLibelle'];
		if(strpos($doc['LienLibelle'], 'lireCours.php') !== false) {
			// lien interne
			echo "... lien interne ok\n";
			//mysql_query('update Document set StatusLienLibelle=1 where IDDocument='.$doc['IDDocument']);
		} else {
			if(http_check_url($doc['LienLibelle'],5)) {
				echo "... ok\n";
				mysqli_query($connexionELT,'update Document set StatusLienLibelle=1 where IDDocument='.$doc['IDDocument']);
			} else {
				echo "... Erreur de lien\n";
				$message .= $doc['Libelle'].': '.$doc['LienLibelle']."<br>";
				mysqli_query($connexionELT,'update Document set StatusLienLibelle=0 where IDDocument='.$doc['IDDocument']);
			}
		}
	}

	// test du lien sur l'auteur
	if(!empty($doc['LienAuteur'])) {
		echo  "  ".$doc['LienAuteur'];
		if(http_check_url($doc['LienAuteur'],5)) {
			echo "... ok\n";
			mysqli_query($connexionELT,'update Document set StatusLienAuteur=1 where IDDocument='.$doc['IDDocument']);
		} else {
			echo "... Erreur de lien\n";
			mysqli_query($connexionELT,'update Document set StatusLienAuteur=0 where IDDocument='.$doc['IDDocument']);
		}
	}
}

//if(date('N')==5) {
//	sendAdminMailEWS('Gestion des documents',$message);
//}

// tester les logiciels
$resultSoft = mysqli_query($connexionELT,'SELECT * FROM software');
while ($soft = mysqli_fetch_array($resultSoft)) {
	echo $soft['Nom'].":\n";
	// test du lien direct
	if(!empty($soft['LienDirect'])) {
		echo "  ".$soft['LienDirect'];
		if(http_check_url($soft['LienDirect'],5)) {
			echo "... ok\n";
			mysqli_query($connexionELT,'update Software set StatusLienDirect=1 where IDSoft='.$soft['IDSoft']);
		} else {
			echo "... Erreur de lien\n";
			mysqli_query($connexionELT,'update Software set StatusLienDirect=0 where IDSoft='.$soft['IDSoft']);
		}
	}

	// test du lien du site
	if(!empty($soft['LienSite'])) {
		echo "  ".$soft['LienSite'];
		if(http_check_url($soft['LienSite'],5)) {
			echo "... ok\n";
			mysqli_query($connexionELT,'update Software set StatusLienSite=1 where IDSoft='.$soft['IDSoft']);
		} else {
			echo "... Erreur de lien\n";
			mysqli_query($connexionELT,'update Software set StatusLienSite=0 where IDSoft='.$soft['IDSoft']);
		}
	}
}

?>
