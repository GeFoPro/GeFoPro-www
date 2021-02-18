<?php
include("../appHeader.php");
include("entete.php");
?>

<div id="page">
<SCRIPT language="Javascript">
function callDetail(id) {
		//document.location.href='comp.php?IDComposant='+id;
		//alert(id);
		document.location.href='compList.php?recherche='+id+'&Rechercher=Rechercher&IDGenre=&IDType=&IDBoitier=&Modifier=&Reset=';
}
function commitChange(name,line) {
	//alert(name+" -- "+ line);
	document.getElementById('myForm').nameChanged.value=name;
	document.getElementById('myForm').lineChanged.value=line;
	document.getElementById('myForm').submit();
}

function callPage(sel) {
	var vue = sel.value;
	if(vue==1) {
		document.getElementById("myForm").submit();
	} else {
		document.location.href='commande.php';
	}
}
function submitRec(form) {
	form.recu.value='non';
	form.submit();
}
function toggleRec(form, id) {
	form.ToggleRec.value=id;
	form.recu.value='non';
	form.submit();
}
function submitRecherche(form) {
	form.recu.value='non';
	form.submit();
}
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
function rechercheArticle(num) {
	document.getElementById('myForm').numRecherche.value=num;
	document.getElementById('myForm').submit();
}

</SCRIPT>
<?php
include("../userInfo.php");
$critere = "";
if(isset($_POST['fournisseur'])) {
	$critere = $_POST['fournisseur'];
	$_SESSION['fournisseur'] = $critere;
} else {
	if(isset($_GET['fournisseur'])) {
		$critere = $_GET['fournisseur'];
		$_SESSION['fournisseur'] = $critere;
	} else {
		if(isset($_SESSION['fournisseur'])) {
			$critere = $_SESSION['fournisseur'];
		}
	}
}

$IDPageCommande = "";
if(isset($_GET['IDPageCommande'])) {
	$IDPageCommande = $_GET['IDPageCommande'];
}
if(isset($_POST['IDPageCommande'])) {
	$IDPageCommande = $_POST['IDPageCommande'];
}
$remarque = "";
if(isset($_GET['remarque'])) {
	$remarque = $_GET['remarque'];
}
if(isset($_POST['remarque'])) {
	$remarque = $_POST['remarque'];
}

$recu = "";
if(isset($_GET['recu'])) {
	$recu = $_GET['recu'];
}
if(isset($_POST['recu'])) {
	$recu = $_POST['recu'];
}

if(!empty($IDPageCommande)) {
	$critere = "";
}

$action = "";
if(isset($_GET['action'])) {
	$action = $_GET['action'];
}
if("Supprimer"==$action) {
	$IDCommandeExt = $_GET['IDCommandeExt'];
	$requete = "delete from $tableCommandeExt where IDCommandeExt = $IDCommandeExt";
	$resultat =  mysqli_query($connexionDB,$requete);
}

if("Ajouter"==$action) {
    $IDCommande = $_GET['IDCommande'];
    $libelle = $_GET['Libelle'];

	$uid = $_SESSION['user_login'];
	$nomCom = $_SESSION['user_nom'];
    /* recherche max */
    //echo "Ajouter Fournisseur";
    $requete = "select max(IDCommandeExt) from $tableCommandeExt";
    $resultat =  mysqli_query($connexionDB,$requete);
    $line = mysqli_fetch_row($resultat);
    $newId = $line[0]+1;
	// recherche commande
	$requete = "select * from $tableCommande where IDCommande=".$IDCommande;
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
    $line = mysqli_fetch_array($resultat);
	$prix = $line['PrixPce'];
	//echo "prix: ".$prix;
	$IDFourn = $line['IDFournisseur'];
	$numArt = $line['NoArticle'];
    //echo "$newId";
    $requete = <<<REQ
INSERT INTO $tableCommandeExt
(IDCommandeExt, Nombre, PrixUnite, Libelle, Userid, Commandepar, IDFournisseur, NumArticle) values
($newId, 1, $prix, "$libelle", "$uid", "$nomCom",$IDFourn,"$numArt")
REQ;
//echo $requete;
    $resultat =  mysqli_query($connexionDB,$requete);
    mysqli_query($connexionDB,'COMMIT');
    // tri sur fournisseur
    $critere = $_GET['IDFournisseur'];
    $_SESSION['fournisseur'] = $critere;
  }


// action maj si nécessaire

if(isset($_POST['lineChanged']) && !empty($_POST['lineChanged'])) {
	$lineToChange = $_POST['lineChanged'];
	if(isset($_POST['nameChanged']) && !empty($_POST['nameChanged'])) {
		$toChange = $_POST['nameChanged'];
		if(isset($_POST[$toChange.$lineToChange])) {
			$valueChanged = $_POST[$toChange.$lineToChange];
			if(!is_numeric($valueChanged)) {
				$valueChanged = addslashes($valueChanged);
				$requete = "UPDATE $tableCommandeExt set $toChange = '$valueChanged' where IDCommandeExt = $lineToChange";
			} else {
				$requete = "UPDATE $tableCommandeExt set $toChange = $valueChanged where IDCommandeExt = $lineToChange";
			}
			$resultat =  mysqli_query($connexionDB,$requete);
		}
	}
}
if(isset($_POST['recherche']) && !empty($_POST['recherche'])) {
	$likeRec = $_POST['recherche'];

}
if(isset($_POST['compar']) && !empty($_POST['compar'])) {
	$likeComPar = $_POST['compar'];

}
if(isset($_POST['ToggleRec']) && !empty($_POST['ToggleRec'])) {
	$idToggle = ($_POST['ToggleRec']);
	//echo "idToggle=".$idToggle."<br>";
	if($idToggle!='act') {
		if(is_numeric($idToggle)) {
			$requete .= "UPDATE commandeext set DateReception=IF(DateReception is null ,NOW(),null) where IDCommandeExt=$idToggle";
		} else {
			//$requete = "UPDATE commandeext commext left outer join $tableCommande comm on comm.IDCommande=commext.IDCommande left outer join $tableFournisseur four on comm.IDFournisseur=four.IDFournisseur";
			$requete = "UPDATE commandeext commext left outer join $tableFournisseur four on commext.IDFournisseur=four.IDFournisseur";
			if($idToggle=='all') {
				$requete .= " set DateReception=NOW()";
			} else {
				$requete .= " set DateReception=null";
			}
			if(!empty($IDPageCommande)) {
				$requete .= " where commext.IDPageCommande=$IDPageCommande";
			} else {
				$requete = $requete .= "  where commext.IDPageCommande is not null";
				if(!empty($likeRec)) {
					$requete = $requete .= " and replace(NumArticle, ' ','') like '".str_replace(' ', '', $likeRec)."%'";
				}
				if(!empty($likeComPar)) {
					$requete = $requete .= " and Userid = '".$likeComPar."'";
				}
				if(empty($likeRec) && empty($likeComPar)) {
					$requete = $requete .= "  and (DateRecption is null OR DateReception = DATE(NOW()))";
				}
				if(isset($critere) && !empty($critere)) {
					$requete = $requete . " and commext.IDFournisseur = $critere";
				}
			}
		}
		//echo $requete;
		$resultat =  mysqli_query($connexionDB,$requete);
	}
}
function getFieldToPrint($value, $ligne, $pos) {
	switch ($value) {
		case 0: if($pos!=1) return $ligne['Valeur'];
		case 1:
			if($pos!=0) {
				return substr($ligne['Description'],0,18);
			} else {
				// si une seule position utilisée, on coupe à 45
				return substr($ligne['Description'],0,45);;
			}
		case 2: return $ligne['Valeur'];
		case 3:
			if($pos!=0) {
				return substr($ligne['Caracteristiques'],0,25);
			} else {
				// si une seule position utilisée, on coupe à 45
				return substr($ligne['Caracteristiques'],0,45);
			}
		case 4: return $ligne['LibelleBoitier'];
		case 5: return $ligne['LibelleGenre'];
		case 6: return $ligne['LibelleType'];
	}
}
$numArticleNew = "";
$libelleNew = "";
$prixPce = "";
if(isset($_POST['numRecherche']) && !empty($_POST['numRecherche'])) {

	$requete = "select * from $tableCommande as com join $tableComp as comp on com.IDComposant=comp.IDComposant join $tableGenre as ge on comp.IDGenre=ge.IDGenre join $tableType as ty on comp.IDType=ty.IDType where IDFournisseur=".$critere." and replace(NoArticle, ' ','') like '".str_replace(' ', '', $_POST['numRecherche'])."%'";
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	$line = mysqli_fetch_array($resultat);

	if($line!=null) {
		$numArticleNew = $line['NoArticle'];
		//$libelleNew = $line['LibelleGenre']." ".$line['Valeur'];
		//echo "ligne 1: ".getFieldToPrint($ligne['PosLigne1'],$ligne,1);
		//echo "ligne 1: ".$line['PosLigne1'];
		if($line['PosLigneC1']!=$line['PosLigneC2']) {
			$libelleNew = getFieldToPrint($line['PosLigneC1'],$line,1)." ".getFieldToPrint($line['PosLigneC2'],$line,2);
		} else {
				$libelleNew = getFieldToPrint($line['PosLigneC1'],$line,0);
		}
		$prixPce = (float)$line['PrixPce'];
		$parts = explode(".",$prixPce);
		if(count($parts)>1) {
			if(strlen($parts[1])==0) {
				$prixPce = $prixPce . ".00";
			}
			if(strlen($parts[1])==1) {
				$prixPce = $prixPce . "0";
			}
		} else {
			$prixPce = $prixPce . ".00";
		}
	} else {
		$numArticleNew = $_POST['numRecherche'];
	}
}

if(isset($_POST['ajoutCommande']) && !empty($_POST['ajoutCommande'])) {
	$nbr = $_POST['Nombre'];
	if(empty($nbr)) {
		$nbr=0;
	}
	$prix = $_POST['PrixUnite'];
	if(empty($prix)) {
		$prix=0;
	}
	$libelle = addslashes(substr($_POST['Libelle'],0,50));
	$uid = $_SESSION['user_login'];
	$nomCom = $_SESSION['user_nom'];
	$IDFourn = $critere;
	$numArt = $_POST['NumArticle'];
    /* recherche max */
    //echo "Ajouter Fournisseur";
    $requete = "select max(IDCommandeExt) from $tableCommandeExt";
    $resultat =  mysqli_query($connexionDB,$requete);
    $line = mysqli_fetch_row($resultat);
    $newId = $line[0]+1;

    $requete = <<<REQ
INSERT INTO $tableCommandeExt
(IDCommandeExt, Nombre, PrixUnite, Libelle, Userid, Commandepar, IDFournisseur, NumArticle) values
($newId, $nbr, $prix, "$libelle", "$uid", "$nomCom",$IDFourn,"$numArt")
REQ;
//echo $requete;
    $resultat =  mysqli_query($connexionDB,$requete);
    mysqli_query($connexionDB,'COMMIT');
}

?>
<FORM id="myForm" ACTION="commande.php"  METHOD="POST">
<input type="hidden" name="ToggleRec" value="">
<input type="hidden" name="recu" value="">
<input type="hidden" name="IDPageCommande" value="<?=$IDPageCommande?>">
<input type="hidden" name="remarque" value="<?=$remarque?>">
<input type="hidden" name="numRecherche" value="">

<div class='post'>

<?php
if(empty($IDPageCommande)&&empty($recu)) {
?>

<!-- h2>Liste des articles à commander</h2 -->
<table border='0' width='100%'>
<tr><td align='right'>
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
</select></td></tr></table><br>
<div id='corners'>
<div id='legend'>Liste des articles à commander</div>
<?php
	} else { // fin if $IDPageCommande et recu
?>
<!-- h2>Historique des commandes</h2 -->
<?php if(empty($IDPageCommande)) { ?>
<table border='0' width='100%'>
<tr><td width='33%'></td><td width='33%'><td align='right'>
  Vue: <select name='vue' onChange="document.location.href='historique.php'">
  <option value='1'>Par commandes</option><option value='2' selected>Par articles</option></select>
   </td></tr>
<tr><td>Commandé par: <input type='text' name='compar' onChange='submitRecherche(this.form)' value='<?=(!empty($likeComPar)?$likeComPar:"")?>'></input></td>
<td align='center'>N° article: <input type='text' name='recherche' onChange='submitRecherche(this.form)' value='<?=(!empty($likeRec)?$likeRec:"")?>'></input></td>
<td align='right'>
Fournisseur :
<select name='fournisseur' onChange='submitRec(this.form);'>
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
</table><br>
<?php } ?>
<br>
<div id='corners'>
<div id='legend'>Historique des commandes</div>
<?php
	if(!empty($remarque)) {
		echo "<br>".$remarque;
	}
?>



<?php
} // fin else $IDPageCommande
/* liste composants */
$requete = "SELECT * FROM $tableCommandeExt commext
left outer join $tableFournisseur four on commext.IDFournisseur=four.IDFournisseur";
// left outer join $tableCommande comm on comm.IDCommande=commext.IDCommande
//echo $requete;
if(empty($IDPageCommande)) {
	if(empty($recu)) {
		$requete = $requete . " where IDPageCommande is null";
	} else {
		$requete = $requete . " where IDPageCommande is not null";
		if(!empty($likeRec)) {
			$requete .=  " and replace(NumArticle, ' ','') like '".str_replace(' ', '', $likeRec)."%'";
		}
		if(!empty($likeComPar)) {
			$requete .= " and Userid = '".$likeComPar."'";
		}
		if(empty($likeRec) && empty($likeComPar)) {
			$requete .= "  and (DateReception is null OR DateReception = DATE(NOW())) ";
		}
	}
	if(isset($critere) && !empty($critere)) {
		$requete = $requete . " and commext.IDFournisseur = $critere";
	}
} else {
	$requete = $requete . " where IDPageCommande = $IDPageCommande";
}

if(!hasAdminRigth()) {
	// afficher uniquement ses propres composants commandés
	$requete = $requete . " and Userid=\"".$_SESSION['user_login']."\"";
}
//if(!empty($IDPageCommande)||!empty($recu)) {
	$requete = $requete . "  order by commext.IDFournisseur, NumArticle";
//}

$resultat =  mysqli_query($connexionDB,$requete);

//echo "<br>".$requete;
?>
<br>

<table id="hor-minimalist-b" border='0' width='100%'><tr>

<?php


$countImp = 0;
if(isset($idToggle)&&!is_numeric($idToggle)) {
	if($idToggle=='all') {
		$impTxt = "<input type='checkbox' name='recQ' onClick='toggleRec(this.form,\"none\");' checked>";
	} else {
		$impTxt = "<input type='checkbox' name='recQ' onClick='toggleRec(this.form,\"all\");'>";
	}
} else {
	$impTxt = "Reçu";
	//if(!empty($recu)) {
		$impTxt = "<a href='javascript:toggleRec(document.getElementById(\"myForm\"),\"act\")'>".$impTxt."</a>";
	//}
}


$rowCounter = 0;
$totalList = 0;
// créer entête
			if(!empty($IDPageCommande)||!empty($recu)) {
				echo "<th align='left' width='25'>".$impTxt."</th>";
			}
			if(empty($critere) && empty($IDPageCommande)) {
				echo "<th align='left'>Fournisseur</th>";
			}
			echo "<th align='left'>No fournisseur</th>";
			echo "<th align='left'>Libellé</th>";
			echo "<th align='right'>Quantité</th>";
			echo "<th align='right'>Prix unitaire</th>";
			echo "<th align='right' width='60'>Total</th>";
			echo "<th align='left'>Commandé par</th><th></th></tr>";
if(!empty($resultat)) {
	$noArticle = "";
	while ($ligne = mysqli_fetch_assoc($resultat) ) {
		//if($rowCounter==0) {

		//}
		$rowCounter++;
		$link = $ligne['LienArticle'];
		$ancArticle = $noArticle;
		$noArticle= str_replace(" ","",$ligne['NumArticle']);
		$noArticle= str_replace(".","",$noArticle);
		$noArticle= str_replace("-","",$noArticle);

		// recherche si article semble exister dans la gestion du matériel
		/* liste composants */
		/*
		$requeteFound = "SELECT count(*) FROM $tableComp comp where ";
		$words = explode(" ",$ligne['Libelle']);
		$requeteFoundWhere = "";
		foreach ($words as $keyword) {
			if(!empty($requeteFoundWhere)) $requeteFoundWhere .= " OR ";
			$requeteFoundWhere .= 	"description like \"%$keyword%\" OR valeur like \"%$keyword%\" OR caracteristiques like \"%$keyword%\"";
		}
		//echo $requeteFound.$requeteFoundWhere;
		$resultatFound =  mysqli_query($connexionDB,$requeteFound.$requeteFoundWhere);
		$ligneFound = mysqli_fetch_row($resultatFound);
		$compFound = "";
		if($ligneFound!=null && $ligneFound[0]>0) {
			$compFound = $ligneFound[0];
		}
		*/
		eval( "\$link = \"$link\";" );
		if(!empty($IDPageCommande)||!empty($recu)) {
			echo "<tr id='comp".$ligne['IDCommandeExt']."' onclick='callDetail(\"".$ligne['NumArticle']."\");'>";
		} else {
			echo "<tr id='comp".$ligne['IDCommandeExt']."'>";
		}
		if(!empty($IDPageCommande)||!empty($recu)) {
			if($ligne['DateReception'] != null) $checked = "CHECKED";
			else $checked = "";
			echo "<td align='center' onclick='event.stopImmediatePropagation();'><input type='checkbox' name='rec[]' value='".$ligne['IDCommandeExt']."' ".$checked." onClick='toggleRec(this.form,".$ligne['IDCommandeExt'].");'></td>";
		}
		if(empty($critere) && empty($IDPageCommande)) {
			echo "<td onclick='event.stopImmediatePropagation();' ><a href='commande.php?fournisseur=".$ligne['IDFournisseur']."'>".$ligne['NomFournisseur']."</a></td>";
		}
		if($ancArticle!=$noArticle) {
			if(!empty($link)) {
				echo "<td align='left'><a href='$link' target='_fournisseur'>".$ligne['NumArticle']."</a></td>";
			} else {
				echo "<td align='left'>".$ligne['NumArticle']."</td>";
			}
		} else {
			echo "<td></td>";
		}
		$total = $ligne['Nombre']*$ligne['PrixUnite'];
		$prixUnite = (float)$ligne['PrixUnite'];
		$parts = explode(".",$prixUnite);
		if(count($parts)>1) {
			if(strlen($parts[1])==0) {
				$prixUnite = $prixUnite . ".00";
			}
			if(strlen($parts[1])==1) {
				$prixUnite = $prixUnite . "0";
			}
		} else {
			$prixUnite = $prixUnite . ".00";
		}

		$total = round($total,2);
		$totalList = $totalList + $total;
		$totalStr = sprintf("%01.2f", $total);
		if(empty($IDPageCommande)&&empty($recu)) {
			if($ancArticle!=$noArticle) {
				echo "<td><input type='text' name='Libelle$ligne[IDCommandeExt]' value=\"".$ligne['Libelle']."\" size='40' onChange='commitChange(\"Libelle\",\"$ligne[IDCommandeExt]\")'></td>";
			} else {
				echo "<td></td>";
			}
			echo "<td align='right'><input type='text' name='Nombre$ligne[IDCommandeExt]' value='$ligne[Nombre]' style='text-align: right' size='5' onChange='commitChange(\"Nombre\",\"$ligne[IDCommandeExt]\")'></td>";
			echo "<td align='right'><input type='text' name='PrixUnite$ligne[IDCommandeExt]' value='$prixUnite' style='text-align: right' size='10' onChange='commitChange(\"PrixUnite\",\"$ligne[IDCommandeExt]\")'></td>";
			echo "<td align='right'>$totalStr</td><td>&nbsp;".$ligne['Commandepar']."</td>";
			echo "<td>&nbsp;<a href='commande.php?action=Supprimer&IDCommandeExt=$ligne[IDCommandeExt]'><img src='/iconsFam/basket_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a></td><tr>";
		} else {
			if($ancArticle!=$noArticle) {
				echo "<td>$ligne[Libelle]</td>";
			} else {
				echo "<td></td>";
			}
			echo "<td align='right'>$ligne[Nombre]</td>";
			echo "<td align='right'>$prixUnite</td>";
			echo"<td align='right'>$totalStr</td>";
			echo"<td>&nbsp;".$ligne['Commandepar']."</td><td></td></tr>";
		}

	}
}
if($rowCounter==0) {
	echo "<tr newArticle='1'><td colspan='8' align='center' height='80'>Aucun article";
	if(empty($IDPageCommande) && empty($recu)) {
		echo " à commander";
	} else {
		if(!empty($likeRec)) {
			echo " trouvé";
		} else {
			echo " non reçu";
		}
	}
	echo "</td></tr>";
}
if(empty($IDPageCommande)&&empty($recu)) {
	echo "<tr newArticle='1' style='display:none' >";
	echo "<td><input type='text' name='NumArticle' value='$numArticleNew' size='8' onChange='rechercheArticle(this.value)'></td>";
	echo "<td><input type='text' name='Libelle' value='$libelleNew' size='40' ></td>";
	echo "<td align='right'><input type='text' name='Nombre' value='' style='text-align: right' size='5'></td>";
	echo "<td align='right'><input type='text' name='PrixUnite' value='$prixPce' style='text-align: right' size='10'></td>";
	$totalStr = "";
	echo "<td align='right'>$totalStr</td>";
	echo "<td colspan='2' align='right'><input type='submit' name='ajoutCommande' value='Ajouter'></input></td><tr>";
}
if(empty($recu) && (!empty($critere) || !empty($IDPageCommande))) {
	$totalListStr = sprintf("%01.2f", $totalList);
	echo "<tr newArticle='1' >";
	if(!empty($IDPageCommande)||!empty($recu)) echo "<td></td>";
	echo "<td colspan='4' align='right'><b>Total</b></td><td align='right'><b>$totalListStr</b></td><td><b>CHF</b></td><td>";
	if(empty($IDPageCommande)) {
		echo "<img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un article à commander')\" onmouseout='UnTip()' onclick='toggle(\"newArticle\");document.getElementById(\"myForm\").NumArticle.focus();' align='right'>";
	}
	echo "</td></tr>";
}

if(!empty($numArticleNew)) {
	echo "<script>toggle(\"newArticle\");document.getElementById('myForm').Libelle.focus()</script>";
}

?>
</table>
<input type="hidden" name="nameChanged" value="">
<input type="hidden" name="lineChanged" value="">
</div>
</div></form>
<?php
if(isset($critere) && !empty($critere) && $rowCounter!=0 && hasAdminRigth()&&empty($recu)) { ?>
<br>

<FORM id="myOrder" ACTION="excel.php"  METHOD="GET">
<div class='post'>
<div id='corners'>
<div id='legend'>Commande</div>
<br>
<!-- h2>Commande</h2 -->
<table border=0>
<tr><td>Créateur :</td><td><input type='text' size='4' name='Abbr' value=''>
</td></tr>
<tr><td>Utilisation :</td><td><select name='Util'>
<option>Consommables</option>
<option>CFC</option>
<option>CFC / TPI</option>
<option>Consommables</option>
<option>Entité existante</option>
<option>Investissement</option>
<option>Maintenance</option>
<option>Nouvelle entité</option>
<option>Remplacement</option>
<option>Réparation</option>
<option>Revente élèves</option>
<option>Travaux pour tiers</option>
</select></td></tr>
<tr><td>Avec TVA :</td><td><input type="checkbox" name="Tva"></tr>
<tr><td>Intitulé :</td><td><input type="texte" name="Remarque" value="" size="35"</td></tr>
<tr><td></td><td>
<input type="submit" name="Submit" value="Commander"> <input type="checkbox" name="Definitif">Commande définitive
<input type="hidden" name="IDFournisseur" value="<?=$critere?>">
</td></tr></table>
</div>
</div> <!-- post -->

</form>
<?php }

if(!empty($toChange) && !empty($lineToChange)) {
?>



<script>
document.getElementById('myForm').<?=$toChange?><?=$lineToChange?>.parentNode.nextSibling.childNodes[0].select();
//document.getElementById('myForm').PrixUnite1.focus();
</script>
<?php }

?>
</div> <!-- page -->

<?php include("../piedPage.php"); ?>
