<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:59
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: impressionCIE.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:46
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

// chargement des librairies PHPWord
require_once 'PHPWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();


$IDEleve = 0;
if(isset($_GET['IDEleve'])) {
	$IDEleve = $_GET['IDEleve'];
}

// Cours CIE concerné
$IDCours = 0;
if(isset($_GET['IDCours'])) {
	$IDCours = $_GET['IDCours'];
}

// charger la template
$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($_SERVER['DOCUMENT_ROOT']."/".$_SESSION['home']."docBase/ciebaseRow.docx");
//$templateProcessor->cloneBlockTest("CLONEME",2, true, false);


$templateProcessor->setValue('profession', Profession);
$templateProcessor->setValue('serviceFormation', ServiceFormation);

// Recherche coordonnées école/lieu (IDEntreprise=1)
$requeteEnt = "SELECT * FROM entreprise where IDEntreprise=1";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteEnt);
$ligne = mysqli_fetch_assoc($resultat);
// Lieu du cours
$templateProcessor->setValue("lieuNom",$ligne['NomEntreprise']);
$templateProcessor->setValue("lieuCompl",$ligne['ComplementEntreprise']);
$templateProcessor->setValue("lieuRue",$ligne['RueEntreprise']);
$templateProcessor->setValue("lieuLieu",$ligne['NPAEntreprise']." ".$ligne['LieuEntreprise']);

// partie du document propre à chaque apprenti
// Recherche des information du cours pour la personne et mise à jour
$requeteH = "SELECT * FROM docelevecie as doc join elevesbk el on doc.IDEleve=el.IDGDN join eleves as elex on doc.IDEleve=elex.IDGDN left join entreprise as ent on elex.IDEntreprise=ent.IDEntreprise WHERE IDCours=$IDCours AND IDEleve=$IDEleve";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteH);
$ligne = mysqli_fetch_assoc($resultat);
$templateProcessor->setValue("nom", $ligne['Nom']);
$templateProcessor->setValue("prenom", $ligne['Prenom']);
$nomApp = $ligne['Nom'];
$prenomApp = $ligne['Prenom'];
$date = strtotime($ligne['DateNaissance']);
$templateProcessor->setValue("dateNaissance", date('d.m.Y', $date));
// Entreprise formatrice
$templateProcessor->setValue("entrepriseNom",$ligne['NomEntreprise']);
$templateProcessor->setValue("entrepriseCompl",$ligne['ComplementEntreprise']);
$templateProcessor->setValue("entrepriseRue",$ligne['RueEntreprise']);
$templateProcessor->setValue("entrepriseLieu",$ligne['NPAEntreprise']." ".$ligne['LieuEntreprise']);
// année/semestre
$mois = date('m');
if($mois<8) {
	$sem = "2ème";
} else {
	$sem = "1er";
}
if(strpos($ligne['Classe'],$app_section.' 1') !== false) {
	$templateProcessor->setValue("anneeSem", "1ère/".$sem);
} else {
	$templateProcessor->setValue("anneeSem", "2ème/".$sem);
}

$templateProcessor->setValue("ae", $ligne['AbsencesEx']);
$templateProcessor->setValue("an", $ligne['AbsencesNonEx']);
if(empty($ligne['DateDiscussion']) || "0000-00-00"==$ligne['DateDiscussion']) {
	$templateProcessor->setValue("dateDiscussion","");
} else {
	$date = strtotime($ligne['DateDiscussion']);
	$templateProcessor->setValue("dateDiscussion", date('d.m.Y', $date));
}
$templateProcessor->setValue("encouragement", $ligne['Encouragement']);
$templateProcessor->setValue("origine", $ligne['Origine']);

$IDDocEleve = $ligne['IDDocEleve'];

//Observations
$requete = "SELECT * FROM appbloccie where IDDocEleve=$IDDocEleve";
$resultat =  mysqli_query($connexionDB,$requete);
while($ligne = mysqli_fetch_assoc($resultat)) {
	$templateProcessor->setValue("observ".$ligne['IDBlocRessource'],$ligne['Observation']);
}
$templateProcessor->setValue("observ".$groupesCompetences[1],"");
$templateProcessor->setValue("observ".$groupesCompetences[2],"");
$templateProcessor->setValue("observ".$groupesCompetences[3],"");
$templateProcessor->setValue("observ".$groupesCompetences[4],"");

// partie commune pour chaque apprenti pour le cours donné

// Recherche des information du cours et mise à jour
$requeteH = "SELECT * FROM courscie as cours join doccie as doc on cours.IDDoc=doc.IDDoc WHERE IDCours=$IDCours";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteH);
$ligne = mysqli_fetch_assoc($resultat);
$denom = $ligne['TitreCIE'];
$templateProcessor->setValue("denominationCours", $ligne['TitreCIE']);
$templateProcessor->setValue("responsable", $ligne['Responsable']);
$templateProcessor->setValue("nbrJours", $ligne['NbrJours']);
$templateProcessor->setValue("datesCours", $ligne['Dates']);
$IDDoc = $ligne['IDDoc'];

// Recherche des ressources professionnelles du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence left join appcompetencecie as app on com.IDCompetence=app.IDCompetence and app.IDDocEleve=$IDDocEleve WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[1]."%' order by Numero";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRP', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRP#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRP#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		if($ligne['EvalAPP']==$apid) {
			$templateProcessor->setValue('A'.$apid.'RP#'.$id, "X");
		} else {
			$templateProcessor->setValue('A'.$apid.'RP#'.$id, "");
		}
		if($ligne['EvalMAI']==$apid) {
			$templateProcessor->setValue('M'.$apid.'RP#'.$id, "X");
		} else {
			$templateProcessor->setValue('M'.$apid.'RP#'.$id, "");
		}
	}
}

// Recherche des ressources méthodologiques du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence left join appcompetencecie as app on com.IDCompetence=app.IDCompetence and app.IDDocEleve=$IDDocEleve WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[2]."%' order by Numero";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRM', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRM#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRM#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		if($ligne['EvalAPP']==$apid) {
			$templateProcessor->setValue('A'.$apid.'RM#'.$id, "X");
		} else {
			$templateProcessor->setValue('A'.$apid.'RM#'.$id, "");
		}
		if($ligne['EvalMAI']==$apid) {
			$templateProcessor->setValue('M'.$apid.'RM#'.$id, "X");
		} else {
			$templateProcessor->setValue('M'.$apid.'RM#'.$id, "");
		}
	}
}

// Recherche des ressources sociales du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence left join appcompetencecie as app on com.IDCompetence=app.IDCompetence and app.IDDocEleve=$IDDocEleve WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[3]."%' order by Numero";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRS', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRS#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRS#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		if($ligne['EvalAPP']==$apid) {
			$templateProcessor->setValue('A'.$apid.'RS#'.$id, "X");
		} else {
			$templateProcessor->setValue('A'.$apid.'RS#'.$id, "");
		}
		if($ligne['EvalMAI']==$apid) {
			$templateProcessor->setValue('M'.$apid.'RS#'.$id, "X");
		} else {
			$templateProcessor->setValue('M'.$apid.'RS#'.$id, "");
		}
	}
}

// Recherche des ressources de sécurité et protection du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence left join appcompetencecie as app on com.IDCompetence=app.IDCompetence and app.IDDocEleve=$IDDocEleve WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[4]."%' order by Numero";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRA', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRA#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRA#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		if($ligne['EvalAPP']==$apid) {
			$templateProcessor->setValue('A'.$apid.'RA#'.$id, "X");
		} else {
			$templateProcessor->setValue('A'.$apid.'RA#'.$id, "");
		}
		if($ligne['EvalMAI']==$apid) {
			$templateProcessor->setValue('M'.$apid.'RA#'.$id, "X");
		} else {
			$templateProcessor->setValue('M'.$apid.'RA#'.$id, "");
		}
	}
}




// envoi du fichier
//$file = "DIVTEC - FOR - MOD 2.10 Contrôle de compétences CIE ".$denom." ".$nomApp." ".$prenomApp;
$file = "Contrôle de compétences CIE ".$denom." ".$nomApp." ".$prenomApp.".docx";
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
$templateProcessor->saveAs('php://output');


?>
