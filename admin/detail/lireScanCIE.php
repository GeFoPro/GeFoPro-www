<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:62
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: lireScanCIE.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:76
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

$IDEleve = 0;
if(isset($_GET['IDEleve'])) {
	$IDEleve = $_GET['IDEleve'];
}

// Cours CIE concerné
$IDCours = 0;
if(isset($_GET['IDCours'])) {
	$IDCours = $_GET['IDCours'];
}

$result=mysqli_query($connexionDB,"SELECT PDFSigne, Nom, Prenom, TitreCIE FROM docelevecie as docel join courscie as cours on docel.IDCours=cours.IDCours join doccie as doc on cours.IDDoc=doc.IDDoc join elevesbk as el on docel.IDEleve=el.IDGDN WHERE docel.IDEleve=$IDEleve AND docel.IDCours=$IDCours");
$pdfdata=mysqli_fetch_array($result);
if(!empty($pdfdata['PDFSigne'])) {
	$file = "DIVTEC - FOR - MOD 2.10 Contrôle de compétences CIE ".$pdfdata['TitreCIE']." ".$pdfdata['Nom']." ".$pdfdata['Prenom'].".pdf";
	header("Content-Description: File Transfer");
	header('Content-Disposition: attachment; filename="' . $file . '"');
	header('Content-Type: application/pdf');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');

	echo $pdfdata['PDFSigne'];
} else {
	echo "<font color=red>Aucun document scanné disponible</font>";
}

?>
