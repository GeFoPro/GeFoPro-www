<?php
include("../../appHeader.php");

// chargement des librairies PHPWord
require_once 'PHPWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();


// Cours CIE concerné
$IDDoc = 0;
if(isset($_GET['IDDoc'])) {
	$IDDoc = $_GET['IDDoc'];
}

// charger la template
$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('../../docBase/ciebaseRow.docx');
$templateProcessor->setValue('profession',  Profession);

// Recherche des information du document et mise à jour
$requeteH = "SELECT * FROM doccie WHERE IDDoc=$IDDoc";
$resultat =  mysqli_query($connexionDB,$requeteH);
$ligne = mysqli_fetch_assoc($resultat);
$denom = $ligne['TitreCIE'];
$templateProcessor->setValue("denominationCours", $ligne['TitreCIE']);
$templateProcessor->setValue("responsable", "");
$templateProcessor->setValue("nbrJours", "");
$templateProcessor->setValue("datesCours", "");

// Recherche des ressources professionnelles du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[1]."%' order by Numero";
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRP', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRP#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRP#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		$templateProcessor->setValue('A'.$apid.'RP#'.$id, "");
		$templateProcessor->setValue('M'.$apid.'RP#'.$id, "");
	}
}

// Recherche des ressources méthodologiques du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[2]."%' order by Numero";
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRM', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRM#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRM#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		$templateProcessor->setValue('A'.$apid.'RM#'.$id, "");
		$templateProcessor->setValue('M'.$apid.'RM#'.$id, "");
	}
}

// Recherche des ressources sociales du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[3]."%' order by Numero";
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRS', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRS#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRS#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		$templateProcessor->setValue('A'.$apid.'RS#'.$id, "");
		$templateProcessor->setValue('M'.$apid.'RS#'.$id, "");
	}
}

// Recherche des ressources de sécurité et protection du cours et mise à jour
$requeteH = "SELECT * FROM competencedoccie as cdc join competencecie as com on cdc.IDCompetence=com.IDCompetence WHERE cdc.IDDoc=$IDDoc AND Numero like '".$groupesCompetences[4]."%' order by Numero";
$resultat =  mysqli_query($connexionDB,$requeteH);
// cloner les lignes en fonction du nombre d'éléments
$numrow = mysqli_num_rows($resultat);
$templateProcessor->cloneRow('idRA', $numrow);
// remplir avec les éléments trouvés
for ($id=1;$ligne = mysqli_fetch_assoc($resultat);$id++) {
	$templateProcessor->setValue('idRA#'.$id, $ligne['Numero']);
	$templateProcessor->setValue('nomRA#'.$id, $ligne['Description']);
	for($apid=1;$apid<=4;$apid++) {
		$templateProcessor->setValue('A'.$apid.'RA#'.$id, "");
		$templateProcessor->setValue('M'.$apid.'RA#'.$id, "");
	}
}


// partie du document propre à chaque apprenti -> vide
$templateProcessor->setValue("nom", "");
$templateProcessor->setValue("prenom", "");
$templateProcessor->setValue("dateNaissance", "");
// Entreprise
$templateProcessor->setValue("entrepriseNom","");
$templateProcessor->setValue("entrepriseCompl","");
$templateProcessor->setValue("entrepriseRue","");
$templateProcessor->setValue("entrepriseLieu","");
$templateProcessor->setValue("anneeSem", "");
$templateProcessor->setValue("ae", "");
$templateProcessor->setValue("an", "");
$templateProcessor->setValue("dateDiscussion","");
$templateProcessor->setValue("encouragement", "");
$templateProcessor->setValue("origine", "");

//observations
$templateProcessor->setValue("observETB",'');
$templateProcessor->setValue("observXXM",'');
$templateProcessor->setValue("observXXS",'');
$templateProcessor->setValue("observXXA",'');

// envoi du fichier
$file = "DIVTEC - FOR - MOD 2.10 Contrôle de compétences CIE.docx";
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
$templateProcessor->saveAs('php://output');


?>
