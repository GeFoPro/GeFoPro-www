<?php
include("../../appHeader.php");

if(isset($_GET['nom'])) {
	$nom = $_GET['nom'];
	$prenom = $_GET['prenom'];
	$IDEleve = $_GET['idEleve'];
} else {
	$nom = $_POST['nom'];
	$prenom = $_POST['prenom'];
	$IDEleve = $_POST['IDEleve'];
}

$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}

if(isset($_POST['ajoutAttribut'])) {
	$IDAttribut = $_POST['IDAttribut'];
	$remarque = $_POST['Remarque'];
	// ajout d'un attribut
    	$requete = "INSERT INTO $tableAttribEleves (IDAttribut, IDEleve, Remarque) values ($IDAttribut, $IDEleve, \"$remarque\")";
    	$resultat =  mysql_query($requete);
}

if(isset($_POST['ajoutRemarque'])) {
	$date = date("Y-m-d",strtotime($_POST['DateRem']));
	$IDAttribut = $_POST['IDAttribRem'];
	$remarque = $_POST['RemarqueRem'];
	if(empty($_POST['DateRem'])) {
		// date du jour
		$date = date("Y-m-d");
	}
	// ajout d'un attribut
    	$requete = "INSERT INTO $tableAttribEleves (IDAttribut, IDEleve, Remarque, Date) values ($IDAttribut, $IDEleve, \"$remarque\",\"$date\")";
    	$resultat =  mysql_query($requete);
}


if(isset($_POST['modifEleve']) || isset($_POST['ajoutEleve'])) {
	$dateNaissance = "\"".date("Y-m-d",strtotime($_POST['DateNaissanceNew']))."\"";
	if(empty($_POST['DateNaissanceNew'])) {
		$dateNaissance = 'NULL';
	}
	$noChaise = $_POST['NoChaiseNew'];
	$noBanc = $_POST['NoBancNew'];
	//$noCle = $_POST['NoCleNew'];
	$IDCle = $_POST['IDCleNew'];	
	$noVestiaire = $_POST['NoVestiaireNew'];
	if(empty($noVestiaire)) {
		$noVestiaire = 'NULL';
	}
	$noJeton = $_POST['NoJetonNew'];
	if(empty($noJeton)) {
		$noJeton = 'NULL';
	}
	$noBadge = $_POST['NoBadgeNew'];
	if(empty($noBadge)) {
		$noBadge = 'NULL';
	}
	if(empty($IDCle)) {
		$IDCle = 'NULL';
	}
	$noTel = $_POST['NoTelNew'];
	$noMobile = $_POST['NoMobileNew'];

	// deuxi�me partie

	$nom = $_POST['NomNew'];
	$prenom = $_POST['PrenomNew'];
	$adresse = $_POST['AdresseNew'];
	$origine = $_POST['OrigineNew'];
	$npa = $_POST['NPANew'];
	$localite = $_POST['LocaliteNew'];
	$classe = $_POST['ClasseNew'];
	$email = $_POST['EmailNew'];
	$userid = $_POST['UseridNew'];
	$noSeriePC =  $_POST['NoSeriePCNew'];
	$nomPC =  $_POST['NomPCNew'];
	$macWifi = $_POST['MacAdresseWifiNew'];
	$macEth = $_POST['MacAdresseEthernetNew'];
	
	$entreprise = $_POST['IDEntrepriseNew'];

	// requ�tes SQL
	if(empty($classe)||empty($nom)) {
		$msg = "<font color='#FF0000'>Le nom et la classe sont obligatoires</font>";
	} else {
		if(isset($_POST['ajoutEleve'])) {
			// ajout dans elevesbk
			$requete = "INSERT into elevesbk (Nom,Prenom,Adresse,NPA,Localite,Classe,Email) values (\"$nom\",\"$prenom\",\"$adresse\",\"$npa\",\"$localite\",\"$classe\",\"$email\")";
			//echo $requete;
    			$resultat =  mysql_query($requete);
			$IDEleve = mysql_insert_id();
			$requete = "INSERT into eleves (IDGDN,DateNaissance,noChaise,noBanc,IDCle,NoVestiaire,NoJeton,NoBadge,NoTel,NoMobile,Userid,Origine,NoSeriePC,NomPC,MacAdresseWifi,MacAdresseEthernet,IDEntreprise) values ($IDEleve,$dateNaissance,\"$noChaise\",\"$noBanc\",$IDCle,$noVestiaire,$noJeton,$noBadge,\"$noTel\",\"$noMobile\",\"$userid\",\"$origine\",\"$noSeriePC\",\"$nomPC\",\"$macWifi\",\"$macEth\",$entreprise)";
			//echo $requete;
			$resultat =  mysql_query($requete);
			$msg = "<font color='#088A08'>El�ve ajout�</font>";
		} else {
			// modification table elevesbk
    			$requete = "UPDATE elevesbk set Nom=\"$nom\", Prenom=\"$prenom\", Adresse=\"$adresse\", NPA=\"$npa\", Localite=\"$localite\", Classe=\"$classe\", Email=\"$email\" where IDGDN=$IDEleve";
			$resultat =  mysql_query($requete);
			// modification table eleves
    			$requete = "UPDATE eleves set DateNaissance=$dateNaissance, noChaise=\"$noChaise\", noBanc=\"$noBanc\", IDCle=$IDCle, NoVestiaire=$noVestiaire, NoJeton=$noJeton, NoBadge=$noBadge, NoTel=\"$noTel\", NoMobile=\"$noMobile\", Userid=\"$userid\", Origine=\"$origine\", NoSeriePC=\"$noSeriePC\", NomPC=\"$nomPC\", MacAdresseWifi=\"$macWifi\", MacAdresseEthernet=\"$macEth\", IDEntreprise=$entreprise where IDGDN=$IDEleve";
				//echo $requete;
			$resultat =  mysql_query($requete);
			$msg = "<font color='#088A08'>El�ve modifi�</font>";
		}
	}
}


// effacement d'une ligne
if(isset($_GET['IDAttribEleve'])) {
	$requete = "DELETE FROM $tableAttribEleves where IDAttribEleve=$_GET[IDAttribEleve]";
	mysql_query($requete);
	
}
include("entete.php");
if(!hasAdminRigth()) {
	echo "<br><br><center><b>Contenu non autoris�.</b></center><br><br>";
	exit;
} 
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
</script>
<?
include($app_section."/userInfo.php");
/* en-t�te */

echo "<FORM id='myForm' ACTION='detailEleve.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";

echo "<div class='post'>";
if(empty($msg)) {
	echo "<center> <font color='#088A08'></font></center>";
} else {
	echo "<center>".$msg."</center>";
}
echo "<br><h2>";
foreach($listeId as $key => $valeur) {
	if($valeur[0]==$IDEleve) {
		if($key!=0) {
			echo "<a href='detailEleve.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img src='/iconsFam/resultset_previous.png'></a>";
		}
		echo $nom." ".$prenom;
		if($key<count($listeId)-1) {
			echo "<a href='detailEleve.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img src='/iconsFam/resultset_next.png'></a>";
		}
		break;
	}
}
echo "</h2><br>\n";

$requete = "SELECT * FROM elevesbk bk join eleves el on el.IDGDN=bk.IDGDN left join cleatelier ca on el.IDCle=ca.IDCle where bk.IDGDN = $IDEleve";
$resultat =  mysql_query($requete);
//echo $requete;
$ligne = array();
if(!empty($resultat)) {
 $ligne = mysql_fetch_assoc($resultat);
}

echo "<table border='0'><tr>";

echo "<td><table border='0'>\n";
echo "<tr><td>NOM</td><td>Pr�nom</td><td></td></tr>";
echo "<tr><td><input type='texte' name='NomNew' value='".$ligne['Nom']."'></input></td><td><input type='texte' name='PrenomNew' value='".$ligne['Prenom']."'></input></td><td></td></tr>\n";
echo "<tr><td>Adresse</td><td></td><td></td></tr>";
echo "<tr><td colspan='2'><input type='texte' name='AdresseNew' value=\"".$ligne['Adresse']."\" size='45'></input></td><td></td></tr>\n";
echo "<tr><td>NPA</td><td>Localit�</td><td></td></tr>";
echo "<tr><td><input type='texte' name='NPANew' value='".$ligne['NPA']."' size='4'></input></td><td><input type='texte' name='LocaliteNew' value='".$ligne['Localite']."'></input></td><td></td></tr>\n";
echo "<tr><td>Date de naissance</td><td>Lieu d'origine</td><td></td><td></td></tr>";
$date = date('d.m.Y', strtotime($ligne['DateNaissance']));
if(empty($ligne['DateNaissance'])) {
	$date = '';
}
echo "<tr><td><input type='texte' name='DateNaissanceNew' value='".$date."' size='10'></input></td><td><input type='texte' name='OrigineNew' value=\"".$ligne['Origine']."\"></input></td><td></td></tr>\n";
echo "<tr><td>T�l�phone parents</td><td>T�l�phone personnel</td><td></td></tr>";
echo "<tr><td><input type='texte' name='NoTelNew' value='".$ligne['NoTel']."' size='13'></input></td><td><input type='texte' name='NoMobileNew' value='".$ligne['NoMobile']."' size='13'></input></td><td></td></tr>\n";
echo "<tr><td colspan='3'>Email</td></td></tr>";
echo "<tr><td colspan='3'><input type='texte' name='EmailNew' value='".$ligne['Email']."' size='45'></input></td></tr>\n";
echo "</table></td><td width='100'></td>";

echo "<td><table border='0'>\n";
//echo "<tr><td>Banc</td><td>Chaise</td></tr>";
echo "<tr><td colspan='2'>Employeur</td></tr>";
//echo "<tr><td><input type='texte' name='NoBancNew' value='".$ligne['noBanc']."' size='15'></input></td><td><input type='texte' name='NoChaiseNew' value='".$ligne['noChaise']."' size='15'></input></td></tr>\n";
echo "<tr><td colspan='2'>";
// pr�paration de la liste des entreprises
$requete = "SELECT * FROM entreprise order by NomEntreprise";
$resultat =  mysql_query($requete);
echo "<select name='IDEntrepriseNew'>";
while ($listeLigne = mysql_fetch_assoc($resultat)) {
	if($listeLigne['IDEntreprise']==$ligne['IDEntreprise'])
      		echo "<option value='$listeLigne[IDEntreprise]' selected='selected'>";
    	else
      		echo "<option value='$listeLigne[IDEntreprise]'>";
    	echo "$listeLigne[NomEntreprise]</option>";
}
echo "</select></td></tr>";

echo "<tr><td>Vestiaire</td><td>Jeton</td></tr>";
echo "<tr><td><input type='texte' name='NoVestiaireNew' value='".$ligne['NoVestiaire']."' size='3'></input></td><td><input type='texte' name='NoJetonNew' value='".$ligne['NoJeton']."' size='2'></input></td></tr>\n";

echo "<tr><td>Cl� layette</td><td>Badge</td></tr>";
echo "<tr><td>";
//<input type='texte' name='NoCleNew' value='".$ligne['NumeroCle']."' size='15'></input>
// pr�paration de la liste des cl�s
$requete = "SELECT * FROM cleatelier order by NumeroCle";
$resultat =  mysql_query($requete);
echo "<select name='IDCleNew'><option></option>";
while ($listeLigne = mysql_fetch_assoc($resultat)) {
	if($listeLigne['IDCle']==$ligne['IDCle'])
      		echo "<option value='$listeLigne[IDCle]' selected='selected'>";
    	else
      		echo "<option value='$listeLigne[IDCle]'>";
    	echo "$listeLigne[NumeroCle] </option>";
}
echo "</select>";
echo "</td><td><input type='texte' name='NoBadgeNew' value='".$ligne['NoBadge']."' size='5'></input></td></tr>\n";
echo "<tr><td>PC - No. s�rie</td><td>PC - Nom</td></tr>";
echo "<tr><td><input type='texte' name='NoSeriePCNew' value='".$ligne['NoSeriePC']."' size='15'></input></td><td><input type='texte' name='NomPCNew' value='".$ligne['NomPC']."' size='15'></input></td></tr>\n";
echo "<tr><td>PC - MAC Wifi</td><td>PC - MAC Ethernet</td></tr>";
echo "<tr><td><input type='texte' name='MacAdresseWifiNew' value='".$ligne['MacAdresseWifi']."' size='15'></input></td><td><input type='texte' name='MacAdresseEthernetNew' value='".$ligne['MacAdresseEthernet']."' size='15'></input></td></tr>\n";
echo "<tr><td>Classe</td><td>Userid</td></tr>";
echo "<tr><td><input type='texte' name='ClasseNew' value='".$ligne['Classe']."' size='15'></input></td><td><input type='texte' name='UseridNew' value='".$ligne['Userid']."' size='7'></input></td></tr>\n";
echo "</table></td><td width='100'></td>";
echo "<td valign='top'><img src='/".$app_section."/images/photo/".$ligne['Nom']."_".$ligne['Prenom'].".jpg' height='120' alt='(pas de photo)'>";
echo "</td>";

echo "</tr><tr><td></td><td align='center'>";
if(!empty($IDEleve)) {
	echo "<input type='submit' name='modifEleve' value='Modifier'></input>";
} else {
	echo "<input type='submit' name='ajoutEleve' value='Ajouter'></input>";
}
echo "</td><td colspan='3'></td></tr></table>";


if(!empty($IDEleve)) {

echo "<br><h3>Autres informations:</h3>";
echo "<table id='hor-minimalist-b'>\n";
echo "<tr><th width='270'>Attribut</th><th width='500'>Remarque</th><th width='10'></th></tr>";
// recherche des attributs g�n�raux (1 � 6)
$requete = "SELECT * FROM $tableAttribEleves el join $tableAttribut att on el.IDAttribut=att.IDAttribut where IDEleve = $IDEleve and el.IDAttribut < 100";
$resultat =  mysql_query($requete);
$cnt=0;
while ($ligne = mysql_fetch_assoc($resultat)) {
	echo "<tr><td>".$ligne['Nom']."</td><td>".nl2br($ligne['Remarque'])."</td><td align='right'><a href='detailEleve.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDAttribEleve=$ligne[IDAttribEleve]'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a></td></tr>";
	$cnt++;
}
if ($cnt==0) {
	echo "<tr><td colspan='3' align='center'><i>Aucun enregistrement</i></td></tr>";
} 
// ligne d'ajout
$requete = "SELECT * FROM $tableAttribut where IDAttribut < 100";
$resultat =  mysql_query($requete);
$option = "";
while ($ligne = mysql_fetch_assoc($resultat)) {
	$option .= "<option value=".$ligne['IDAttribut'].">".$ligne['Nom']."</option>";
}
echo "<tr><td colspan='3' bgColor='#5C5C5C'></td></tr>";
echo "<tr newAttribut='1' ><td></td><td></td><td align='right' width='70'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un attribut')\" onmouseout='UnTip()' onclick='toggle(\"newAttribut\");' align='absmiddle'></td></tr>";

echo "<tr newAttribut='1' style='display:none'><td colspan='3' valign='bottom' height='30'><b>Nouvel attribut:<b></td></tr>";
echo "<tr newAttribut='1' style='display:none'><td valign='top'><select name='IDAttribut'>".$option."</select></td>";
echo "<td valign='top'><textarea name='Remarque' COLS=60 ROWS=2></textarea></td>";
echo "<td valign='top' width='70'><input type='submit' name='ajoutAttribut' value='Ajouter'></input></td></tr>";
echo "</table><br><br>";

echo "<h3>Historique:</h3>";
echo "<table id='hor-minimalist-b'>\n";
echo "<tr><th width='80'>Date</th><th width='175'>Raison</th><th width='500'>Remarque</th><th width='10'></th></tr>";
// recherche des attributs g�n�raux (1 � 6)
$requete = "SELECT * FROM $tableAttribEleves el join $tableAttribut att on el.IDAttribut=att.IDAttribut where IDEleve = $IDEleve and el.IDAttribut > 100 order by Date";
$resultat =  mysql_query($requete);
$cnt=0;
while ($ligne = mysql_fetch_assoc($resultat)) {
	echo "<tr><td>".date('d.m.Y', strtotime($ligne['Date']))."</td><td>".$ligne['Nom']."</td><td>".$ligne['Remarque']."</td><td align='right'><a href='detailEleve.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDAttribEleve=$ligne[IDAttribEleve]'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a></td></tr>";
	$cnt++;
}
if ($cnt==0) {
	echo "<tr><td colspan='4' align='center'><i>Aucun enregistrement</i></td></tr>";
} 
// ligne d'ajout
$requete = "SELECT * FROM $tableAttribut where IDAttribut > 100";
$resultat =  mysql_query($requete);
$option = "";
while ($ligne = mysql_fetch_assoc($resultat)) {
	$option .= "<option value=".$ligne['IDAttribut'].">".$ligne['Nom']."</option>";
}
echo "<tr><td colspan='4' bgColor='#5C5C5C'></td></tr>";
echo "<tr newHistorique='1' ><td></td><td></td><td></td><td align='right' width='70'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un attribut')\" onmouseout='UnTip()' onclick='toggle(\"newHistorique\");' align='absmiddle'></td></tr>";

echo "<tr newHistorique='1' style='display:none' ><td colspan='4' valign='bottom' height='30'><b>Nouvelle entr�e dans l'historique:<b></td></tr>";
echo "<tr newHistorique='1' style='display:none' ><td valign='top'><input name='DateRem' size='8' maxlength='10' value='".date('d.m.Y')."'></input></td>";
echo "<td valign='top'><select name='IDAttribRem'>".$option."</select></td>";
echo "<td valign='top'><textarea name='RemarqueRem' COLS=60 ROWS=2></textarea></td>";
echo "<td valign='top' width='70'><input type='submit' name='ajoutRemarque' value='Ajouter'></input></td></tr>";
echo "</table><br>";
} // si ID pas vide
?>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
