<?php
include("../appHeader.php");

// get
$critere = $_GET['IDFournisseur'];
$abbreviation = $_GET['Abbr'];
//$createur = $_GET['User'];
$utilisation = $_GET['Util'];
$tva = "";
if(isset($_GET['Tva'])) {
	$tva = $_GET['Tva'];
}
$remarque = "";
if(isset($_GET['Remarque'])) {
	$remarque = $_GET['Remarque'];
}
// ID page
$IDPageCommande = "";
if(isset($_GET['IDPageCommande'])) {
	$IDPageCommande = $_GET['IDPageCommande'];
}

// date du jour
$ajd = date("d.m.Y");
$ajdSQL = date("Y-m-d");


/* librairies pour Excel */
require_once 'PHPExcel/IOFactory.php';
require_once 'PHPExcel/Writer/Excel5.php';


if(isset($_GET['Definitif'])) {
	$errorMsg = "";
	// maj historique
	if(isset($critere) && !empty($critere)) {
		// ajout commande
		$requete = "select max(IDPageCommande) from $tablePageCommande";
		$resultat =  mysql_query($requete);
		$line = mysql_fetch_row($resultat);
		$IDPageCommande = $line[0]+1;
		$requete = <<<REQ
INSERT INTO $tablePageCommande
(IDPageCommande, Numero, DateCommande, Createur, Type, IDFournisseur, Remarque, TVA) values
($IDPageCommande, 1, "$ajdSQL", "$abbreviation", "$utilisation", $critere, "$remarque", "$tva")
REQ;
		//$errorMsg = $requete;
		$resultat =  mysql_query($requete);
		if(!$resultat) {
			$errorMsg = $errorMsg . " ! Impossible d'ajouter l'historique (".$requete.")";
		}
		// maj commande externe
		   $requete = <<<REQ
UPDATE $tableCommandeExt set
IDPageCommande = "$IDPageCommande"
where IDPageCommande is null and IDFournisseur = $critere LIMIT 20
REQ;
		$resultat =  mysql_query($requete);
		if(!$resultat) {
			$errorMsg = $errorMsg . " ! Impossible d'ajouter l'historique (".$requete.")";
		}
	}

	// committer l'ensemble de l'historique
	if(empty($errorMsg)) {
		mysql_query('COMMIT');
	} else {
		mysql_query('ROLLBACK');
	}
}
PHPExcel_Settings::setLocale('fr-CH');
$objReader = PHPExcel_IOFactory::createReader('Excel5');
//$objReader->setReadDataOnly(true);
//$objReader->setLoadSheetsOnly( array("Sheet 1", "Feuille commande") );
$objPHPExcel = $objReader->load("../docBase/commande.xls");

// requete pour en-tête
 if(isset($critere) && !empty($critere)) {
	$requete = "SELECT * FROM $tableFournisseur where IDFournisseur=$critere";
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_row($resultat);

	// remplissage fournisseur
	$objPHPExcel->getActiveSheet()->setCellValue('F4', iconv("ISO-8859-1", "UTF-8", $ligne[1]));
	$objPHPExcel->getActiveSheet()->setCellValue('F5', iconv("ISO-8859-1", "UTF-8", $ligne[2]));
	$objPHPExcel->getActiveSheet()->setCellValue('F7', $ligne[3]);
	$objPHPExcel->getActiveSheet()->setCellValue('G7', iconv("ISO-8859-1", "UTF-8",$ligne[4]));
	$objPHPExcel->getActiveSheet()->setCellValue('F8', $ligne[5]);
	$objPHPExcel->getActiveSheet()->setCellValue('G8', $ligne[6]);
	$objPHPExcel->getActiveSheet()->setCellValue('F9', $ligne[7]);
}
// date création
$objPHPExcel->getActiveSheet()->setCellValue('D35', $ajd);

// type d'utilisation
$objPHPExcel->getActiveSheet()->setCellValue('C8', $utilisation);
// createur
$objPHPExcel->getActiveSheet()->setCellValue('A35', $abbreviation);
switch($abbreviation) {
	case "DGI":
		$objPHPExcel->getActiveSheet()->setCellValue('C35', 'David Girardin');
		break;
	case "RGR":
		$objPHPExcel->getActiveSheet()->setCellValue('C35', iconv("ISO-8859-1", "UTF-8", "René Grossmann"));
		break;
	case "MRE":
		$objPHPExcel->getActiveSheet()->setCellValue('C35', 'Marco Retti');
		break;
}

// TVA
//$objPHPExcel->getActiveSheet()->setCellValue('F32','Blabla');
if("on" != $tva) {
	// effacer les champs
	$objPHPExcel->getActiveSheet()->setCellValue('F37','');
	$objPHPExcel->getActiveSheet()->setCellValue('G37','');
	$objPHPExcel->getActiveSheet()->setCellValue('H37','');
	$objPHPExcel->getActiveSheet()->setCellValue('F32','Total TTC');
}

// requete pour commande
$requete = "SELECT * FROM $tableCommandeExt";
//left outer join $tableCommande comm on comm.IDCommande=commext.IDCommande";
if(isset($critere) && !empty($critere)) {
	if(empty($IDPageCommande)) {
		$requete = $requete . " where IDPageCommande is null and IDFournisseur = $critere limit 20";
	} else {
		$requete = $requete . " where IDPageCommande= $IDPageCommande";
	}
}
//$errorMsg = $requete;
$resultat =  mysql_query($requete);

// remplissage de la commande
if(!empty($resultat) && empty($errorMsg)) {
	$cnt = 12;
	while ($ligne = mysql_fetch_assoc($resultat) ) {
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, $ligne['NumArticle']);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, iconv("ISO-8859-1", "UTF-8", "$ligne[Libelle]"));
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, $ligne['Nombre']);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, $ligne['PrixUnite']);
		$cnt++;
	}
}

if(!empty($errorMsg)) {
	$objPHPExcel->getActiveSheet()->setCellValue('D20', $errorMsg);
}

// générer excel
$writer = new PHPExcel_Writer_Excel5($objPHPExcel);


//echo "done";
//$records = './fichier.xls';
//$writer->save($records);

header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment;Filename=commande.xls");
$writer->save('php://output');

?>
