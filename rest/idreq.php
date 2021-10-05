<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:12
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: idreq.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:84
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

$scturl = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
require("Config_".$scturl.".php");
$id = "";
if(isset($_GET['id'])) {
	$id = $_GET['id'];
}
if(isset($_POST['id'])) {
	$id = $_POST['id'];
}

$connexion = connexionAdmin(DBServer,DBUserAdmin,DBPwdAdmin);

// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods:  GET, PUT, POST, DELETE, HEAD");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// recherche si identifiant existant parmi les apprentis
mysqli_select_db($connexion,DBAdmin);
$result = mysqli_query($connexion,"select nom,prenom from eleves as el join elevesbk as elbk on el.IDGDN = elbk.IDGDN where IDCard = 0x".$id);
if($result!=null && !empty($result)) {
	if(mysqli_num_rows($result)==1) {
		$user = mysqli_fetch_assoc($result);
		http_response_code(200);
		echo "USR,".$user['nom']." ".$user['prenom'];
		return;
	}
}

// pas trouvé, on essaie les profs
$result = mysqli_query($connexion,"select Nom from prof where IDCard = 0x".$id);
if($result!=null && !empty($result)) {
	if(mysqli_num_rows($result)==1) {
		$user = mysqli_fetch_assoc($result);
		http_response_code(200);
		echo "USR,".$user['Nom'];
		return;
	}
}

// pas trouvé -> on essaie dans les appareils inventoriés
mysqli_select_db($connexion,DBComp);
$result = mysqli_query($connexion,"select Description,Caracteristiques,NoInventaire from inventaire as inv join composant as comp on inv.IDComposant = comp.IDComposant where IDTag = 0x".$id);
if($result!=null && !empty($result)) {
	if(mysqli_num_rows($result)==1) {
		// appareil inventorié
		$stock = mysqli_fetch_assoc($result);
		http_response_code(200);
		echo "DEV,".$stock['NoInventaire']."/".$stock['Description'];
		return;
	}
}

// pas trouvé -> on essaie dans les emplacements
$result = mysqli_query($connexion,"select Description,Caracteristiques,Emplacement,Tirroir from stockage as stg join composant as comp on stg.IDComposant = comp.IDComposant join stock as sto on stg.IDStock = sto.IDStock where IDTag = 0x".$id);
if($result!=null && !empty($result)) {
	if(mysqli_num_rows($result)>1) {
		// plus d'un appareil dans un même endroit (ne peut pas arriver UNIQUE)
		$stock = mysqli_fetch_assoc($result);
		http_response_code(200);
		echo "STO,".$stock['Emplacement']."/".$stock['Tirroir'];
		return;
	} else if(mysqli_num_rows($result)==1) {
		// un seul élément stocké à cet endroit -> on affiche l'appareil
		$stock = mysqli_fetch_assoc($result);
		http_response_code(200);
		echo "STO,".$stock['Emplacement']."/".$stock['Tirroir']."/".$stock['Description'];
		return;
	}
}

// pas trouvé -> erreur
http_response_code(404);
?>
