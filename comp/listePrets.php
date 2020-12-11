<?php
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

include("entete.php");
?>

<div id="page">
<script>

</script>
<?
include($app_section."/userInfo.php");
/* en-tête */
echo "<FORM id='myForm' ACTION='listePrets.php'  METHOD='POST'>";
echo "<div class='post'>";

// construction de la liste des élèves
mysql_select_db(DBAdmin);
$listeSCT = "<option value='0'></option>";
// recherche des élèves
$requete = "SELECT * FROM elevesbk ele join eleves el on ele.IDGDN=el.IDGDN where Classe like '".$app_section."%' and IDEntreprise=1 order by Classe desc, Nom, Prenom";
// Classe in ('".$app_section." 3','".$app_section." 4', '".$app_section." 3+1') order by Classe desc, Nom, Prenom";
//echo $requete;
$resultat =  mysql_query($requete);
while ($ligne = mysql_fetch_assoc($resultat)) {
	$listeSCT .= "<option value='".$ligne['Userid']."'";
	if($ligne['Userid']==$uid) {
				$listeSCT .= " selected";
	}
	$listeSCT .= ">".$ligne['Classe']." - ".$ligne['Nom']." ".$ligne['Prenom']."</option>";
}

/* Construction listes des emplacements */

mysql_select_db(DBComp);
$listeEmp = "<option value='0'></option>";
$requete = "SELECT * FROM $tableStock order by Emplacement";
$resultat =  mysql_query($requete);
while ($listeLigne = mysql_fetch_array($resultat)) {
	$listeEmp .= "<option value='$listeLigne[0]'";
	if($listeLigne[0]==$IDStock) {
		$listeEmp .= " selected";
	}
	$listeEmp .= ">$listeLigne[1] </option>";
}


echo "<br><div id='corners'>";
echo "<div id='legend'>Liste des prêts</div>";
if(hasAdminRigth()) {
	echo "<table width='100%' border='0'><tr><td align='right'>Apprenti: <select name='Userid' onChange='document.getElementById(\"myForm\").submit();'>".$listeSCT."</select></td></tr>";
	echo "<tr><td align='right'>Emplacement: <select name='IDStock' onChange='document.getElementById(\"myForm\").submit();'>".$listeEmp."</select></td></tr>";
	echo "</table>";
}

echo "<table id='hor-minimalist-b' width='100%' border='0'>\n";
echo "<tr><th width='300'>Appareil</th><th width='100' align='center'>No Inventaire</th><th width='100' align='center'>Emplacement</th><th  width='200'>Utilisé par</th><th>Depuis</th><th width='10'></th></tr>";
mysql_select_db(DBComp);
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
	$requete = "select Userid, DateEmprunt, Tirroir, Emplacement, emp.IDInventaire as IDInv, NoInventaire, Stg.IDComposant as IDComp, Description, Caracteristiques from stockage stg";
	$requete .= " left join stock sto on stg.IDStock=sto.IDStock";
	$requete .= " join composant comp on stg.IDComposant= comp.IDComposant";
	$requete .= " join inventaire inv on comp.IDComposant=inv.IDComposant";
	$requete .= " join emprunt emp on (inv.IDInventaire=emp.IDInventaire OR stg.IDStockage=emp.IDStockage)";
	$requete .= " WHERE DateRetour is null".$filtreSQL;
	$requete .= " group by Userid,emp.IDInventaire,emp.IDStockage order by Emplacement, Tirroir";
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
$resultat =  mysql_query($requete);

$cnt = 0;
while ($ligne = mysql_fetch_assoc($resultat)) {
	$desc = "";
	//if($ligne['Nbr']==1) {
		$desc = $ligne['Description']."<br>".$ligne['Caracteristiques'];
	//} else {
	//	$desc = $ligne['Emplacement'];
	//}
	echo "<tr onClick='location.href=\"comp.php?IDComposant=".$ligne['IDComp']."\"'><td>".$desc."</td>";
	echo "<td align='center'>";
	//echo $ligne['IDInv'];
	if(!empty($ligne['IDInv'])) {
		echo $ligne['NoInventaire'];
	}
	echo "</td>";
	echo "<td align='center'>".$ligne['Emplacement']."<br>".$ligne['Tirroir']."</td>";
	$usebyTxt = "";
	if(!empty($ligne['Userid'])) {
		mysql_select_db(DBAdmin);
		$requete = "SELECT * FROM elevesbk bk join eleves el on bk.IDGDN=el.IDGDN where Userid='".$ligne['Userid']."'";
		$resultatUser =  mysql_query($requete);
		if($resultatUser!=null && !empty($resultatUser) && mysql_num_rows($resultatUser)==1) {
			$user = mysql_fetch_assoc($resultatUser);
			$usebyTxt = $user['Nom']." ".$user['Prenom'];
		} else {
			// on essai avec la table des profs
			$requete = "SELECT * FROM prof where userid='".$ligne['Userid']."'";
			$resultatUser =  mysql_query($requete);
			if($resultatUser!=null && !empty($resultatUser) && mysql_num_rows($resultatUser)==1) {
				$user = mysql_fetch_assoc($resultatUser);
				$usebyTxt = $user['abbr'];
			} else {
				$usebyTxt = $ligne['Userid'];
			}
		}
	}
	echo "<td>".$usebyTxt."</td>";
	echo "<td>".date("d.m.Y",strtotime($ligne['DateEmprunt']))."</td>";
	echo "<td></td></tr>";
	$cnt++;
}
if ($cnt==0) {
	echo "<tr><td colspan='6' align='center'><i>Aucun enregistrement</i></td></tr>";
}

echo "</table></div><br>";
?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
