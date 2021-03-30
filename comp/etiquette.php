<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:91
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: etiquette.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:40
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");

/* PDF */
include("phpToPDF.php");

/* fonction champs afficher */
function getFieldToPrint($value, $ligne, $pos) {
	switch ($value) {
		case 0: if($pos==2) return $ligne['Valeur'];
		case 1: return substr($ligne['Description'],0,18);
		case 2: return $ligne['Valeur'];
		case 3: return substr($ligne['Caracteristiques'],0,25);
		case 4: return $ligne['LibelleBoitier'];
		case 5: return $ligne['LibelleGenre'];
		case 6: return $ligne['LibelleType'];
	}
}


/* requete */
$requete = "SELECT * FROM composant comp left outer join genre ge on comp.IDGenre=ge.IDGenre left outer join type ty on comp.IDType=ty.IDType left outer join boitier bo on comp.IDBoitier=bo.IDBoitier where Imprimer<>0";
//$requete = "SELECT * FROM composant where Imprimer=3";
$resultat =  mysqli_query($connexionDB,$requete);
//$num_rows = mysqli_num_rows($resultat);


/* posistion initial */
$posLigne = 10;
$posCol = 10;
/* hauteur et largeur des �tiquettes */
$hauteurCel=6;
$largeurCel=47;
$tailleImage = 10;
$cnt = 1;

$PDF = new FPDF();
$PDF->AddPage();
//$PDF->SetFont("Arial","B",12);
//$PDF->Write(0,$num_rows);
while ($ligne = mysqli_fetch_assoc($resultat) ) {

	/* recherche des champs � afficher */

	/* cr�er l'�tiquette */
	//$PDF->Image("images/etiquettes/fusible.jpg", $posCol+1, $posLigne+1,$tailleImage,$tailleImage);
	$PDF->SetFont("Arial","B",12);
	$PDF->SetXY($posCol,$posLigne+3);
	$PDF->Cell($largeurCel,$hauteurCel-3,getFieldToPrint($ligne['PosLigne1'],$ligne,1),0,1,'C');
	$PDF->SetFont("Arial","B",8);
	$PDF->SetXY($posCol,$posLigne+6);
	$PDF->Cell($largeurCel,$hauteurCel,getFieldToPrint($ligne['PosLigne2'],$ligne,2),0,0,'C');
	$PDF->SetXY($posCol,$posLigne);
	$PDF->Cell($largeurCel,$hauteurCel*2,"",1,'C',0);
	$cnt++;
	/* calcul de position pour �tiquette suivante */
	if($cnt>4) {
		$posCol=10;
		$posLigne=$posLigne+$hauteurCel*2;
		$cnt=1;
	} else {
		$posCol=$posCol+$largeurCel;
	}
}
// effacer les cases � cocher
$requete = "UPDATE $tableComp set Imprimer=0";
$resultat =  mysqli_query($connexionDB,$requete);
$PDF->Output();
?>
