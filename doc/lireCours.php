<?php
include("../appHeader.php");
$IDDocument = "";
if(isset($_GET['IDDocument'])) {
	$IDDocument = $_GET['IDDocument'];
}

$requete = "SELECT Document, Libelle, Taille, mime FROM document doc join type ty on doc.IDType=ty.IDType WHERE IDDocument=".$IDDocument;
$result=mysqli_query($connexionDB,$requete);
$data=mysqli_fetch_array($result);
//echo $pdfdata[0];
if(!empty($data['Document'])) {
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=".$data['Libelle'].".pdf");
	header('Content-Type: application/pdf');
	//header('Content-type: '.$data['mime']);
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');
	echo $data['Document'];
} else {
	echo "<font color=red>Aucun cours disponible</font>";
}

?>
