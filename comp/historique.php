<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:93
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: historique.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:65
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");
include("entete.php");
?>

<div id="page">
<SCRIPT language="Javascript">
function callDetail(id, remarque) {
		document.location.href='commande.php?IDPageCommande='+id+'&remarque='+remarque;
}

function callPage(sel) {
	var vue = sel.value;
	if(vue==1) {
		document.getElementById("myForm").submit();
	} else {
		document.location.href='commande.php?recu=non';
	}
}

function commitChange(name,line) {
	//alert(name+" -- "+ line);
	document.getElementById('myForm').nameChanged.value=name;
	document.getElementById('myForm').lineChanged.value=line;
	document.getElementById('myForm').submit();
}


</SCRIPT>
<?php
$annee = date('Y');
$anneeTri = $annee;
if(isset($_POST['annee'])) {
	$anneeTri = $_POST['annee'];
}

$critere = "";
if(isset($_POST['fournisseur'])) {
	$critere = $_POST['fournisseur'];
	$_SESSION['fournisseur'] = $critere;
} else {
	if(isset($_GET['fournisseur'])) {
			$critere = $_GET['fournisseur'];
			//$_SESSION['fournisseur'] = $critere;
	} else {
			if(isset($_SESSION['fournisseur'])) {
			$critere = $_SESSION['fournisseur'];
		}
	}
}
if(isset($_GET['action'])&&'supprimer'==$_GET['action']&&isset($_GET['IDPageCommande'])) {
	$requete = "delete from $tableCommandeExt where IDPageCommande = ".$_GET['IDPageCommande'];
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	$requete = "delete from $tablePageCommande where IDPageCommande = ".$_GET['IDPageCommande'];
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
}
// maj
if(isset($_POST['lineChanged']) && !empty($_POST['lineChanged'])) {
	$lineToChange = $_POST['lineChanged'];
	if(isset($_POST['nameChanged']) && !empty($_POST['nameChanged'])) {
		$toChange = $_POST['nameChanged'];
		if(isset($_POST[$toChange.$lineToChange])) {
			$valueChanged = $_POST[$toChange.$lineToChange];
			if(!is_numeric($valueChanged)) {
				$requete = "UPDATE $tablePageCommande set $toChange = '$valueChanged' where IDPageCommande = $lineToChange";
			} else {
				$requete = "UPDATE $tablePageCommande set $toChange = $valueChanged where IDPageCommande = $lineToChange";
			}
			//echo $requete;
			$resultat =  mysqli_query($connexionDB,$requete);
		}
	}
}

include("../userInfo.php");

$optionAnnee = "";
for($cntA=0;$cntA<5;$cntA++) {
	$optionAnnee .= "<option value='".($annee-$cntA)."'";
	if(($annee-$cntA)==$anneeTri) {
		$optionAnnee .= " selected ";
	}
	$optionAnnee .= ">".($annee-$cntA)."</option>";
}
if(!hasAdminRigth()) {
	echo "<br>Contenu non autoris�.";
} else {
?>

<FORM id="myForm" ACTION="historique.php"  METHOD="POST">
<div class="post">
<table border='0' width='100%'>
<tr><td width='33%'></td><td width='33%'></td><td align='right'>
Vue: <select name='vue' onChange='callPage(this)'>
<option value='1' selected>Par commandes</option><option value='2'>Par articles</option></select></td></tr>
<tr><td></td>
<td align='center'>Ann�e civile: <select name='annee' onchange='submit();'><?=$optionAnnee?></select></td>
<td align='right'>
Fournisseur :
<select name='fournisseur' onChange='submit();'>
  <option selected> </option>
  <?php
  /* Construction listes fournisseurs et fabriquants */
  $requete = "SELECT * FROM $tableFournisseur order by NomFournisseur";
  $resultat =  mysqli_query($connexionDB,$requete);
    while ($listeLigne = mysqli_fetch_array($resultat)) {
	if($critere==$listeLigne[0]) {
		echo "<option value='$listeLigne[0]' selected='selected'>";
	} else {
		echo "<option value='$listeLigne[0]'>";
	}
	echo "$listeLigne[1] </option>";
    }
  ?>
  </select>
</td></tr>
</table>
<br>
<div id='corners'>
<div id='legend'>Historique des commandes</div>


<?php

/* liste des commandes */
$requete = "SELECT page.IDPageCommande, NoCommande, DateCommande, NomFournisseur, Remarque, Createur, Numero, Type, page.IDFournisseur,  TVA, count(ext.IDPageCommande) as NbrComp, TotalFacture, sum(ext.PrixUnite*ext.Nombre) as TotalCommande, sum(case when DateReception is not null then 1 else 0 end) as TotalRecu FROM $tablePageCommande page
left outer join $tableCommandeExt ext on page.IDPageCommande = ext.IDPageCommande
left outer join $tableFournisseur four on page.IDFournisseur=four.IDFournisseur";
$requete = $requete . " where (DateCommande between '".$anneeTri."-01-01' and '".$anneeTri."-12-31')";
if(isset($critere) && !empty($critere)) {
	$requete = $requete . " and page.IDFournisseur = $critere";
}
$requete = $requete . " group by page.IDPageCommande order by DateCommande DESC";
$resultat =  mysqli_query($connexionDB,$requete);

//echo $requete;
?>

<br><br>
<table id="hor-minimalist-b" width='100%'><tr>
<?php
$rowCounter = 0;
$totalYear = 0;
$totalYearCom = 0;
$totalYearTPI = 0;
$totalYearComTPI = 0;
if(!empty($resultat)) {
	while ($ligne = mysqli_fetch_assoc($resultat) ) {
		if($rowCounter==0) {
			// cr�er ent�te
			if(empty($critere)) {
				echo "<th align='left'>Fournisseur</th>";
			}
			echo "<th align='left'>Date</th>";
			echo "<th align='center'>Auteur</th>";
			echo "<th align='left'>Intitul�</th>";
			echo "<th align='center'>Type</th>";
			echo "<th align='center'>Page</th>";
			echo "<th align='center'>Re�us</th>";
			echo "<th align='right'>Montant total</th>";
			echo "<th align='right'>N� commande</th>";
			echo "<th align='right'>Total factur�</th>";

			echo"<th></th></tr>";
		}
		$rowCounter++;
		$dateCommande = explode("-", $ligne['DateCommande']);
		$dateList = date("d.m.Y",mktime(0,0,0, $dateCommande[1], $dateCommande[2], $dateCommande[0]));
		//$rem = "Commande ".$ligne['NomFournisseur'];
		$rem = "<table>";
		$rem .= "<tr><td><b>Fournisseur: </b></td><td>".$ligne['NomFournisseur']."</td></tr>";
		if($ligne['NoCommande']!=0) {
			//$rem.= ", n� ".$ligne['NoCommande'].", ";
			$rem.= "<tr><td><b>N� de commande: </b></td><td>".$ligne['NoCommande']."</td></tr>";
		}
		//$rem.=" du ".$dateList;
		$rem.="<tr><td><b>Date de la commande: </b></td><td>".$dateList."</td></tr>";
		if(!empty($ligne['Remarque'])) {
			//$rem.= " - <i>".$ligne['Remarque']."</i>";
			$rem.= "<tr><td><b>Intitul�: </b></td><td>".$ligne['Remarque']."</td></tr>";
		}
		$rem.="</table>";
		echo "<tr id='comp".$ligne['IDPageCommande']."' onclick='callDetail(".$ligne['IDPageCommande'].",\"".$rem."\");'><td align='left' height='25' onclick='event.returnValue=false;'>";
		if(empty($critere)) {
			echo  "<a href='historique.php?fournisseur=".$ligne['IDFournisseur']."' onclick='event.stopImmediatePropagation();'>".$ligne['NomFournisseur']."</a></td><td align='left'>";
		}

		echo "$dateList</td>";
		echo "<td align='center'>".$ligne['Createur']."</td>";
		echo "<td align='left'>".$ligne['Remarque']."</td>";
		echo "<td align='center'>".$ligne['Type']."</td>";
		echo "<td align='center'>".$ligne['Numero']."</td>";
		$txtColor = "";
		if($ligne['TotalRecu']!=0 && $ligne['NbrComp']!=$ligne['TotalRecu']) {
			$txtColor = " style='color:#FF5555'";
		}
		echo "<td align='center' ".$txtColor.">".$ligne['TotalRecu']."/".$ligne['NbrComp']."</td>";
		// calcul du total avec TVA
		$totalTVA = $ligne['TotalCommande'];
		if("on"==$ligne['TVA']) {
			$totalTVA += $totalTVA * $tauxTVA / 100;
		}
		echo "<td align='right'>".sprintf("%01.2f",$totalTVA)." CHF</td>";
		if($ligne['Type']=='Consommables') {
			$totalYearCom = $totalYearCom + $totalTVA;
		} else {
			$totalYearComTPI = $totalYearComTPI + $totalTVA;
		}

		if($ligne['NoCommande']==0) {
			echo "<td align='right' width='100'><input type='text' name='NoCommande$ligne[IDPageCommande]' value='' size='6' style='text-align:right' onclick='event.stopImmediatePropagation();' onChange='commitChange(\"NoCommande\",\"$ligne[IDPageCommande]\")'></input></td>";
		} else {
			if($ligne['TotalFacture']==0) {
				echo "<td align='right' width='100'><input type='text' name='NoCommande$ligne[IDPageCommande]' value='".$ligne['NoCommande']."' size='6' style='text-align:right' onclick='event.stopImmediatePropagation();' onChange='commitChange(\"NoCommande\",\"$ligne[IDPageCommande]\")'></input></td>";
			} else {
				echo "<td align='right' width='100'><input type='text' name='NoCommande$ligne[IDPageCommande]' value='".$ligne['NoCommande']."' size='6' style='text-align:right' onclick='event.stopImmediatePropagation();' readonly></input></td>";
			}
		}
		if($ligne['TotalFacture']==0) {
			$bgColor = "";
			if($ligne['TotalRecu']!=0) {
				$bgColor  = "background-color:#FFC5C5";
			}
			echo "<td align='right' width='100'><input type='text' name='TotalFacture$ligne[IDPageCommande]' value='' size='6' style='text-align:right;".$bgColor."' onclick='event.stopImmediatePropagation();' onChange='commitChange(\"TotalFacture\",\"$ligne[IDPageCommande]\")'></input></td>";
		} else {
			echo "<td align='right' width='100'><input type='text' name='TotalFacture$ligne[IDPageCommande]' value='".sprintf("%01.2f",$ligne['TotalFacture'])."' size='6' style='text-align:right' onclick='event.stopImmediatePropagation();' onChange='commitChange(\"TotalFacture\",\"$ligne[IDPageCommande]\")'></input></td>";
			if($ligne['Type']=='Consommables') {
				$totalYear = $totalYear + $ligne['TotalFacture'];
			} else {
				$totalYearTPI = $totalYearTPI + $ligne['TotalFacture'];
			}
		}

		echo "<td>&nbsp;<a href='excel.php?Abbr=".$ligne['Createur']."&Util=".$ligne['Type']."&IDFournisseur=".$ligne['IDFournisseur']."&IDPageCommande=".$ligne['IDPageCommande']."&Tva=".$ligne['TVA']."'><img src='/iconsFam/printer.png' align='absmiddle' onmouseover=\"Tip('Imprimer une copie')\" onmouseout='UnTip()'></a> ";
		if($ligne['NoCommande']==0) {
			echo "<a href='historique.php?action=supprimer&IDPageCommande=".$ligne['IDPageCommande']."'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette commande')\" onmouseout='UnTip()'></a>";
		}
		echo "</td></tr>";
	}
}
if($rowCounter==0) {
	echo "<tr><td height='100' align='center'>Aucun enregistrement</td></tr>";
} else {
	$colspan = 7;
	if(isset($critere) && !empty($critere)) $colspan = 6;
	echo "<tr><td colspan='".$colspan."'><b>Total ".$anneeTri." Consommables</b></td><td align='right'><b>".number_format($totalYearCom, 2, '.', "'")." CHF</b></td><td></td><td align='right'><b>".number_format($totalYear, 2, '.', "'")." CHF</b></td><td></td></tr>";
	echo "<tr><td colspan='".$colspan."'><b>Total ".$anneeTri." TPI</b></td><td align='right'><b>".number_format($totalYearComTPI, 2, '.', "'")." CHF</b></td><td></td><td align='right'><b>".number_format($totalYearTPI, 2, '.', "'")." CHF</b></td><td></td></tr>";
}
?>
<input type="hidden" name="nameChanged" value="">
<input type="hidden" name="lineChanged" value="">
</table></div>
</div> <!-- post -->

</form>
<?php } ?>
</div> <!-- page -->

<?php include("../piedPage.php"); ?>
