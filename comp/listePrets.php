<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:96
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: listePrets.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:14
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");

$filtre = "1"; // tri par utilisateur
if(isset($_POST['triPrets'])) {
	$filtre = $_POST['triPrets'];
} else {
	if(isset($_GET['triPrets'])) {
		$filtre = $_GET['triPrets'];
	}
}

$uid = "";
if(isset($_POST['Userid'])) {
	$uid = $_POST['Userid'];
}

$IDStock = "";
if(isset($_POST['IDStock'])) {
	$IDStock = $_POST['IDStock'];
}

$IDStockage = "";
if(isset($_GET['IDStockage'])) {
	$IDStockage = $_GET['IDStockage'];
}
if(isset($_POST['IDStockage'])) {
	$IDStockage = $_POST['IDStockage'];
}
$actionEmp = "";
if(isset($_POST['actionEmp'])) {
	$actionEmp = $_POST['actionEmp'];
}
$IDEmprunt = "";
if(isset($_POST['IDEmprunt'])) {
	$IDEmprunt = $_POST['IDEmprunt'];
}

include("entete.php");
?>

<div id="page">
<script>

function toggle(thisname) {
	tr=document.getElementsByTagName('tr')
	for (i=0;i<tr.length;i++){
		if (tr[i].getAttribute(thisname)){
			if ( tr[i].style.display=='none' ){
				tr[i].style.display = '';
			} else {
				tr[i].style.display = 'none';
			}
		}
	}
}

function submitRetour(id) {
	//alert(id);
	document.getElementById('myForm').IDEmprunt.value=id;
	document.getElementById('myForm').submit();
}

</script>
<?php
include("../userInfo.php");

/* action emprunt *) */
if(isset($actionEmp)) {

	//mysqli_select_db($connexionDB,DBComp);
	if($actionEmp=="Ajouter") {
		$idStock = $_POST['IDStockage'];
		$newuid = $_POST['newUID'];

		$requete = "INSERT into emprunt (Userid, IDStockage, DateEmprunt) values (\"".$newuid."\",".$idStock.",\"".date('Y-m-d')."\")";
		//echo $requete;
		$resultat =  mysqli_query($connexionDB,$requete);

	}
	if(!empty($IDEmprunt)) {
		$requete = "UPDATE emprunt set DateRetour = \"".date('Y-m-d')."\" where IDEmprunt=".$IDEmprunt;
		//echo $requete;
		$resultat =  mysqli_query($connexionDB,$requete);

	}
}

/* en-tête */
echo "<FORM id='myForm' ACTION='listePrets.php'  METHOD='POST'>";
echo "<input type='hidden' name='IDEmprunt' value=''>";
echo "<div class='post'>";

// construction de la liste des élèves
mysqli_select_db($connexionDB,DBAdmin);
$listeSCT = "<option value='0'></option>";
// recherche des élèves
$requete = "SELECT * FROM elevesbk ele join eleves el on ele.IDGDN=el.IDGDN where Classe like '".$app_section."%'";
if($triEntreprises) {
	$requete .= " and IDEntreprise=1";
}
$requete .= " order by Classe desc, Nom, Prenom";
// Classe in ('".$app_section." 3','".$app_section." 4', '".$app_section." 3+1') order by Classe desc, Nom, Prenom";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$listeSCT .= "<option value='".$ligne['Userid']."'";
	if($ligne['Userid']==$uid) {
				$listeSCT .= " selected";
	}
	$listeSCT .= ">".$ligne['Classe']." - ".$ligne['Nom']." ".$ligne['Prenom']."</option>";
}

/* Construction listes des emplacements */

mysqli_select_db($connexionDB,DBComp);
$listeEmp = "<option value='0'></option>";
$requete = "SELECT * FROM $tableStock order by Emplacement";
$resultat =  mysqli_query($connexionDB,$requete);
while ($listeLigne = mysqli_fetch_array($resultat)) {
	$listeEmp .= "<option value='$listeLigne[0]'";
	if($listeLigne[0]==$IDStock) {
		$listeEmp .= " selected";
	}
	$listeEmp .= ">$listeLigne[1] </option>";
}

if(hasAdminRigth() && empty($IDStockage)) {
	echo "<table width='100%' border='0'><tr><td align='right'>Apprenti: <select name='Userid' onChange='document.getElementById(\"myForm\").submit();'>".$listeSCT."</select></td></tr>";
	echo "<tr><td align='right'>Emplacement: <select name='IDStock' onChange='document.getElementById(\"myForm\").submit();'>".$listeEmp."</select></td></tr>";
	echo "</table>";
}

echo "<br><div id='corners'>";
echo "<div id='legend'>Liste des prêts";
if(!empty($IDStockage)) {
	echo " de l'appareil";
}
echo "</div>";
echo "<table id='hor-minimalist-b' width='100%' border='0'>\n";
echo "<tr><th width='300'>Appareil</th><th width='100' align='center'>No Inventaire</th><th width='100' align='center'>Emplacement</th><th  width='200'>Utilisé par</th><th>Depuis</th><th width='10'></th></tr>";
mysqli_select_db($connexionDB,DBComp);
$filtreSQLUser = "";
if(empty($uid)) {
	if($uid=="0" || hasAdminRigth()) {
		$filtreSQLUser = " and Userid like '%'";
	} else {
		$filtreSQLUser = " and Userid='".$_SESSION['user_login']."'";
	}
} else {
	$filtreSQLUser = " and Userid='".$uid."'";
}

/* if(empty($IDStock)) {
	$requete = "select Userid, DateEmprunt, Tirroir, Emplacement, NoInventaire, Description, Caracteristiques from emprunt emp";
	$requete .= " left join stockage stg on emp.IDStockage=stg.IDStockage";
	$requete .= " left join stock sto on stg.IDStock=sto.IDStock";
	$requete .= " left join inventaire inv on emp.IDInventaire=inv.IDInventaire";
	$requete .= " left join composant comp on (inv.IDComposant=comp.IDComposant OR stg.IDComposant=comp.IDComposant)";
	$requete .= " where DateRetour is null".$filtreSQLUser;
	$requete .= " order by IDEmprunt";
} else { */
	$filtreSQL = $filtreSQLUser;
	if(!empty($IDStock)) {
			$filtreSQL .= " and stg.IDStock=".$IDStock; //." or stg2.IDStock=".$IDStock;
	}
	if(!empty($IDStockage)) {
			$filtreSQL .= " and stg.IDStockage=".$IDStockage; //." or stg2.IDStock=".$IDStock;
	}
	$requete = "select Userid, DateEmprunt, Tirroir, Emplacement, emp.IDInventaire as IDInv, emp.IDEmprunt as IDEmp, NoInventaire, stg.IDComposant as IDComp, stg.IDStockage as IDStockage, Description, Caracteristiques from stockage stg";
	$requete .= " left join stock sto on stg.IDStock=sto.IDStock";
	$requete .= " join composant comp on stg.IDComposant=comp.IDComposant";
	$requete .= " left join inventaire inv on comp.IDComposant=inv.IDComposant";
	$requete .= " join emprunt emp on (inv.IDInventaire=emp.IDInventaire OR stg.IDStockage=emp.IDStockage)";
	$requete .= " WHERE DateRetour is null".$filtreSQL;
	$requete .= " group by Userid,emp.IDInventaire,emp.IDStockage,DateEmprunt order by Emplacement, Tirroir";
	/*
	$requete .= " join stockage stg on emp.IDStockage=stg.IDStockage";
	$requete .= " join stock sto on stg.IDStock=sto.IDStock";
	$requete .= " join composant comp on stg.IDComposant=comp.IDComposant";
	$requete .= " join inventaire inv on (comp.IDComposant=inv.IDComposant)";
	$requete .= " left join stockage stg2 on (stg2.IDComposant=comp.IDComposant and emp.IDStockage is null and DateRetour is null)";
	$requete .= " where DateRetour is null".$filtreSQL;
	$requete .= " order by IDEmprunt";
	*/
//}
//echo $requete;
//
$resultat =  mysqli_query($connexionDB,$requete);

$cnt = 0;
if(!empty($resultat)) {
	while ($ligne = mysqli_fetch_assoc($resultat)) {
		$desc = "";
		//if($ligne['Nbr']==1) {
			$desc = $ligne['Description']."<br>".$ligne['Caracteristiques'];
		//} else {
		//	$desc = $ligne['Emplacement'];
		//}
		echo "<tr><td onClick='location.href=\"comp.php?IDComposant=".$ligne['IDComp']."\"'>".$desc."</td>";
		echo "<td align='center'>";
		//echo $ligne['IDInv'];
		if(!empty($ligne['IDInv'])) {
			echo $ligne['NoInventaire'];
		}
		echo "</td>";
		echo "<td align='center'>".$ligne['Emplacement']."<br>".$ligne['Tirroir']."</td>";
		$usebyTxt = "";
		if(!empty($ligne['Userid'])) {
			mysqli_select_db($connexionDB,DBAdmin);
			$requete = "SELECT * FROM elevesbk bk join eleves el on bk.IDGDN=el.IDGDN where Userid='".$ligne['Userid']."'";
			$resultatUser =  mysqli_query($connexionDB,$requete);
			if($resultatUser!=null && !empty($resultatUser) && mysqli_num_rows($resultatUser)==1) {
				$user = mysqli_fetch_assoc($resultatUser);
				$usebyTxt = $user['Nom']." ".$user['Prenom'];
			} else {
				// on essai avec la table des profs
				$requete = "SELECT * FROM prof where userid='".$ligne['Userid']."'";
				$resultatUser =  mysqli_query($connexionDB,$requete);
				if($resultatUser!=null && !empty($resultatUser) && mysqli_num_rows($resultatUser)==1) {
					$user = mysqli_fetch_assoc($resultatUser);
					$usebyTxt = $user['abbr'];
				} else {
					$usebyTxt = $ligne['Userid'];
				}
			}
		}
		echo "<td>".$usebyTxt."</td>";
		echo "<td>".date("d.m.Y",strtotime($ligne['DateEmprunt']))."</td>";
		echo "<td>";
		if(hasAdminRigth()) {
			echo "<img src='/iconsFam/user_delete.png' onmouseover=\"Tip('Retour de l\'emprunt')\" onmouseout='UnTip()' onclick='submitRetour(".$ligne['IDEmp'].");' align='absmiddle'>";
		}
		echo "</td></tr>";
		$cnt++;
	}
}
if ($cnt==0) {
	echo "<tr><td colspan='6' align='center'><i>Aucun enregistrement</i></td></tr>";
}
echo "<tr><td colspan='6' bgColor='#5C5C5C'></td></tr>";
if(hasAdminRigth() && !empty($IDStockage)) {
	// ligne d'ajout

	echo "<tr newEmp='1'><td colspan='5'></td><td align='right'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un emprunt')\" onmouseout='UnTip()' onclick='toggle(\"newEmp\");' align='absmiddle'></td></tr>";
	echo "<tr newEmp='1' style='display:none'><td colspan='3'><input type='hidden' name='IDStockage' value='".$IDStockage."'></td><td><input type='text' name='newUID' size='10' value=''></td><td colspan='2' align='right'><input type='submit' name='actionEmp' value='Ajouter'></td></tr>";
}
echo "</table></div><br>";
?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../piedPage.php"); ?>
