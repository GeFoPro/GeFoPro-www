<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:10
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: borrow.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:14
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

$scturl = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
require("Config_".$scturl.".php");
$idDevice = "";
$idUser = "";
$ret = false;
if(isset($_POST['idUser'])) {
	$idUser = $_POST['idUser'];
}
if(isset($_POST['idDevice'])) {
	$idDevice = $_POST['idDevice'];
}
if(isset($_POST['ret'])) {
	$ret = true;
}

$connexion = connexionAdmin(DBServer,DBUserAdmin,DBPwdAdmin);

// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods:  GET, PUT, POST, DELETE, HEAD");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// recherche du userid de la personne
mysql_select_db(DBAdmin);
$uid = "";
$message = "";
$result = mysql_query("select nom,prenom,Userid from eleves as el join elevesbk as elbk on el.IDGDN = elbk.IDGDN where IDCard = 0x".$idUser);
if($result!=null && !empty($result)) {
	if(mysql_num_rows($result)==1) {
		$user = mysql_fetch_assoc($result);
		$uid = $user['Userid'];
		$message = $user['nom']." ".$user['prenom'];
	} else {
		// pas trouv� chez APP, on essaie chez prof
		$result = mysql_query("select abbr,userid from prof where IDCard = 0x".$idUser);
		if($result!=null && !empty($result)) {
			if(mysql_num_rows($result)==1) {
				$user = mysql_fetch_assoc($result);
				$uid = $user['userid'];
				$message = $user['abbr'];
			}
		}
	}
}

$idInventaire = "";
$idStockage = "";
if(!empty($uid)) {
	// utilisateur trouv�, recherche de l'appareil concern�
	$message .= ",";
	mysql_select_db(DBComp);
	$result = mysql_query("select IDInventaire,Description,Caracteristiques,NoInventaire from inventaire as inv join composant as comp on inv.IDComposant = comp.IDComposant where IDTag = 0x".$idDevice);
	if($result!=null && !empty($result)) {
		if(mysql_num_rows($result)==1) {
			// appareil inventori�
			$stock = mysql_fetch_assoc($result);
			// recherche si l'appareil est d�j� emprunt�
			$resultEm = mysql_query("select * from emprunt where IDInventaire = ".$stock['IDInventaire']." and DateRetour is null");
			if($resultEm!=null && !empty($resultEm) && mysql_num_rows($resultEm)!=0 && !$ret) {
				// l'appareil est d�j� emprunt�
				http_response_code(403);
				return;
			} else {
				$idInventaire = $stock['IDInventaire'];
				$message .= $stock['Description'].",".$stock['Caracteristiques'].",".$stock['NoInventaire'];
			}
		} else {
			// pas dans inventaire, on recheche dans les emplacements
			$result = mysql_query("select IDStockage,Description,Caracteristiques,Emplacement,Tirroir from stockage as stg join composant as comp on stg.IDComposant = comp.IDComposant join stock as sto on stg.IDStock = sto.IDStock where IDTag = 0x".$idDevice);
			if($result!=null && !empty($result)) {
				if(mysql_num_rows($result)>1) {
					// plus d'un appareil dans un m�me endroit (ne peut pas arriver, UNIQUE)
					$stock = mysql_fetch_assoc($result);
					$idStockage = $stock['IDStockage'];
					$message .= $stock['Emplacement'].",".$stock['Tirroir'];
				} else if(mysql_num_rows($result)==1) {
					// un seul �l�ment stock� � cet endroit -> on affiche l'appareil
					$stock = mysql_fetch_assoc($result);
					$idStockage = $stock['IDStockage'];
					$message .= $stock['Description'].",".$stock['Caracteristiques'].",".$stock['Emplacement'].",".$stock['Tirroir'];
				}
			}
		}
	}

	if(!empty($idInventaire) || !empty($idStockage)) {
		// appareil trouv� (et utilisateur)
		if(!$ret) {
			// si pas retour
			if(!empty($idInventaire)) {
				$requete = "INSERT into emprunt (Userid, IDInventaire, DateEmprunt) values (\"".$uid."\",".$idInventaire.",\"".date('Y-m-d')."\")";
			} else {
				$requete = "INSERT into emprunt (Userid, IDStockage, DateEmprunt) values (\"".$uid."\",".$idStockage.",\"".date('Y-m-d')."\")";
			}
		} else {
			// si retour
			if(!empty($idInventaire)) {
				$requete = "UPDATE emprunt set DateRetour = \"".date('Y-m-d')."\" where IDInventaire = ".$idInventaire." and Userid=\"".$uid."\" and DateRetour is null";
			} else {
				$requete = "UPDATE emprunt set DateRetour = \"".date('Y-m-d')."\" where IDStockage = ".$idStockage." and Userid=\"".$uid."\" and DateRetour is null limit 1";
			}
		}
		//echo $requete;
		$resultat =  mysql_query($requete);
		if($resultat) {
			if($ret && mysql_affected_rows()!=1) {
					// retour sur pr�t non trouv�
					http_response_code(404);
					return;
			}
			mysql_query('COMMIT');
			http_response_code(201);
			echo $message;
		} else {
			// requete SQL impossible
			http_response_code(500);
		}
	} else {
		// appareil pas trouv� -> erreur
		http_response_code(404);
	}
} else {
	// utilisateur pas trouv� -> erreur
	http_response_code(404);
}
?>
