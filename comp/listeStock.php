<?php 
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:98
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: listeStock.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:38
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");

/* fonction champs afficher */
function getFieldToPrint($value, $ligne, $pos) {
	switch ($value) {
		case 0: if($pos==2) return $ligne['Valeur'];
		case 1: return $ligne['Description'];
		case 2: return $ligne['Valeur'];
		case 3: return $ligne['Caracteristiques'];
		case 4: return $ligne['LibelleBoitier'];
		case 5: return $ligne['LibelleGenre'];
		case 6: return $ligne['LibelleType'];
	}
}

function formatCell($value) {
	return iconv("ISO-8859-1", "UTF-8", "$value");
}
// get
$critere = $_GET['IDStock'];
$genre = $_GET['IDGenre'];

// date du jour
$ajd = date("d.m.Y");

/* librairies pour Excel */
require_once 'PHPExcel/IOFactory.php';
require_once 'PHPExcel/Writer/Excel5.php';

$errorMsg = "";


$objReader = PHPExcel_IOFactory::createReader('Excel5');
//$objReader = new PHPExcel_Reader_Excel5();
$objPHPExcel = $objReader->load("../docBase/listeStock.xls");

/* requete pour ent�te*/
if(isset($critere) && !empty($critere)) {
	$requete = "SELECT * FROM $tableStock where IDStock=$critere";
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_row($resultat);

	// remplissage stock
	$objPHPExcel->getActiveSheet()->setCellValue('D3', $ligne[1]);
}
if(isset($genre) && !empty($genre)) {
	$requete = "SELECT * FROM $tableGenre where IDGenre=$genre";
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_row($resultat);

	// remplissage stock
	$objPHPExcel->getActiveSheet()->setCellValue('D4', formatCell($ligne[1]));
}
// date cr�ation
// $objPHPExcel->getActiveSheet()->setCellValue('D35', $ajd);

/* requete pour lsite*/
$requete = "SELECT * FROM $tableComp comp join $tableStockage stock on comp.IDComposant=stock.IDComposant
left outer join $tableGenre ge on comp.IDGenre=ge.IDGenre
left outer join $tableType ty on comp.IDType=ty.IDType
left outer join $tableBoitier bo on comp.IDBoitier=bo.IDBoitier";

if(isset($critere) && !empty($critere)) {
	$requete = $requete . " where IDStock = $critere";
	if(isset($genre) && !empty($genre)) {
		$requete = $requete . " and ge.IDGenre = $genre";
	}
}
$requete .= " order by ge.LibelleGenre, stock.tirroir";
$resultat =  mysqli_query($connexionDB,$requete);
// remplissage de la liste
if(!empty($resultat) && empty($errorMsg)) {
	$cnt = 7;
	while ($ligne = mysqli_fetch_assoc($resultat) ) {

			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, formatCell($ligne['LibelleGenre']));
			//$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, formatCell($ligne['LibelleType']));
			$objPHPExcel->getActiveSheet()->duplicateStyle( $objPHPExcel->getActiveSheet()->getStyle('A7'), 'A'.$cnt.':E'.$cnt );
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, formatCell(getFieldToPrint($ligne['PosLigne1'],$ligne,1)));
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, formatCell(getFieldToPrint($ligne['PosLigne2'],$ligne,2)));
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, $ligne['Tirroir']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, $ligne['Quantite']);
			$cnt++;

	}
}

if(!empty($errorMsg)) {
	$objPHPExcel->getActiveSheet()->setCellValue('A12', $errorMsg);
}

// g�n�rer excel
$writer = new PHPExcel_Writer_Excel5($objPHPExcel);

header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment;Filename=composants.xls");
$writer->save('php://output');

?>
