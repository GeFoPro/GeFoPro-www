<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:81
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: tachesHebdo.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:35
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

if(!isset($_POST['noSemaine']) || "" == $_POST['noSemaine']) {
	//pas trouvé en POST
	if(!isset($_SESSION['noSemaine']) || "" == $_SESSION['noSemaine']) {
		//pas trouvé en session
		$noSemaine = date('W');
		$anneeCalc = date('Y');
	} else {
		$noSemaine = $_SESSION['noSemaine'];
		$anneeCalc = $_SESSION['anneeCalc'];
	}
} else {
	$noSemaine = $_POST['noSemaine'];
	$anneeCalc = $_POST['anneeCalc'];
	$_SESSION['noSemaine'] = $noSemaine;
	$_SESSION['anneeCalc'] = $anneeCalc;

}

// effacement ou changement de type d'une remarque
if(isset($_GET['IDRemSuivi'])) {
	if($_GET['action']=='delete') {
		// supprimer le suivi
		$requete = "DELETE FROM remarquesuivi where IDRemSuivi=$_GET[IDRemSuivi]"; // and Remarque like '".$_GET[idtache]."%'";
		//echo "<br>".$requete;
		mysqli_query($connexionDB,$requete);
	}
	if($_GET['action']=='done') {
		// supprimer le suivi
		//$requete = "DELETE FROM remarquesuivi where IDRemSuivi=$_GET[IDRemSuivi]";
		$requete = "UPDATE remarquesuivi set TypeRemarque=6 where IDRemSuivi=$_GET[IDRemSuivi]";// and Remarque like '".$_GET[idtache]."%'";
		//echo "<br>".$requete;
		mysqli_query($connexionDB,$requete);
	}
	if($_GET['action']=='notdone') {
		// ajouter une remarque administrative
		$IDEleve = $_GET['IDEleve'];
		$date = $_GET['Date'];
		// date du jour
		//$date = date("Y-m-d");
		$requete = "UPDATE remarquesuivi set TypeRemarque=7 where IDRemSuivi=$_GET[IDRemSuivi]";// and Remarque like '".$_GET[idtache]."%'";
		//$requete = "INSERT INTO $tableAttribEleves (IDAttribut, IDEleve, Remarque, Date) values (107, $IDEleve, \"Ajout automatique\",\"$date\")";
		//echo "<br>".$requete;
		mysqli_query($connexionDB,$requete);
	}
}
if($_GET['action']=='deleteAll') {
	// supprimer toutes les tâches
	$requete = "DELETE FROM remarquesuivi where DateSaisie='$_GET[Date]' and (TypeRemarque=4 or TypeRemarque=5 or TypeRemarque=6 or TypeRemarque=7)"; 
	//echo "<br>".$requete;
	mysqli_query($connexionDB,$requete);
}
// calcul lundi et vendredi
$dateCalc=mktime(0,0,0,1,4,$anneeCalc);
$jour_semaine=date("N",$dateCalc);
$lundi=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaine-1);
$vendredi = $lundi + 86400*5 - 3600*2; // retrait de 2h -> vendredi soir même si GMT +1 ou +2
$dateTache = date('Y-m-d', $lundi);
$noSemMoinsUn = date('W',$lundi - 86400*7);
if($noSemaine==1) {
	$anneeMoinsUn = $anneeCalc-1;
} else {
	$anneeMoinsUn = $anneeCalc;
}
//$anneeMoinsUn = date('Y',$lundi - 86400*7);
$noSemPlusUn = date('W',$lundi + 86400*7);
if($noSemPlusUn==1) {
	$anneePlusUn = $anneeCalc+1;
} else {
	$anneePlusUn = $anneeCalc;
}

if(isset($_POST['assigner'])||$_POST['action']=='assigner') {
	// récupéré la position à assigner si transmise
	$posAssign = $_POST['posAssign'];
	// assigner les tâches pour la classe donnée
	// 1) Etablir la liste des élèves (sans les hors liste -8- et sans les stages -13-) 
	$classeAssign = $_POST['ClasseAssign'];
	$requete = "SELECT * FROM $tableElevesBK el join eleves eli on el.IDGDN=eli.IDGDN";
	if($triEntreprises) {
		$requete .= " and IDEntreprise=1";
	}
	$requete .= " left outer join $tableAttribEleves at on (el.IDGDN = at.IDEleve and (at.IDAttribut = 8 OR at.IDAttribut = 13)) where Classe like '$classeAssign%' order by Nom, Prenom";
	$resultat =  mysqli_query($connexionDB,$requete);
	$elID = array();
	while ($ligne = mysqli_fetch_assoc($resultat) ) {
		if(empty($ligne['IDAttribut'])) {
			$elID[] = $ligne['IDGDN'];
			//echo " el: ".$ligne['Nom'] . ' ' . $ligne['Prenom']."<br>";
		}
	}
	// 2) calculer la position de départ
	$cntEleves = count($elID);
	if($cntEleves!=0) {
		$cntTaches = count($configurationTJ);
		if($cntTaches % $cntEleves == 0) {
			// même nombre d'élèves que de tâches (ou multiple) -> on incrémente d'une position par semaine
			$debut = ($noSemaine-1) % $cntEleves;
		} else {
			// sinon on décale du nombre de tâches par semaine
			$debut = (($noSemaine-1) * $cntTaches) % $cntEleves;
		}
		// 3) insérer dans DB
		if(!empty($posAssign)) {
			// assignation pour une posistion précise
			$IDEleve = $_POST['IDEleve'];
			$newID = 0;
			for($cnt=0;$cnt<$cntEleves&&$newID==0;$cnt++) {
				if($elID[$cnt]==$IDEleve) {
					// apprenti actuel trouvé
					if($cnt==($posAssign+$debut-1)) {
						// assignation initiale -> on recherche le premier des viennent ensuite
						$newID = $elID[($debut+$cntTaches)%$cntEleves];
					} else {
						// sinon on incrémente
						if(($cnt+1)%$cntEleves==($posAssign+$debut-1)) {
							// saut de 2 positions pour ne pas reprendre la personne initiale
							$newID = $elID[($cnt+2)%$cntEleves];
						} else {
							$newID = $elID[($cnt+1)%$cntEleves];
						}
					}
				}
			}
			if($newID!=0) {
				$requete = "UPDATE remarquesuivi set IDEleve = ".$newID." where DateSaisie=\"".$dateTache."\" and Remarque = \"".$posAssign."\" and TypeRemarque=4";
				//echo $requete;
				mysqli_query($connexionDB,$requete);
			}
		} else {
			// assignation globale (pour toutes les tâches)
			for($cnt=1;$cnt<=$cntTaches;$cnt++) {
				$requete = "INSERT INTO remarquesuivi (IDTheme, IDEleve, DateSaisie, Remarque, TypeRemarque, UserId) values (1, ".$elID[$debut].", \"".$dateTache."\", \"".$cnt."\", 4, \"".$_SESSION[user_login]."\")";
				//echo $requete;
				mysqli_query($connexionDB,$requete);
				$debut++;
				if($debut>=$cntEleves) {
					$debut=0;
				}
			}
		}
	}
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
function submitSemaineAnnee(nosemaine,annee) {
	//alert(nosemaine);
	//alert(annee);
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').anneeCalc.value=annee;
	document.getElementById('myForm').submit();
}
function submitSemaine(nosemaine) {
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').submit();
}

function submitAssign(pos,id) {
	//alert(pos+"/"+id);
	document.getElementById('myForm').IDEleve.value=id;
	document.getElementById('myForm').posAssign.value=pos;
	document.getElementById('myForm').action.value='assigner';
	document.getElementById('myForm').submit();
}

</script>
<?php
include("../../userInfo.php");

/* en-tête */
echo "<FORM id='myForm' ACTION='tachesHebdo.php' METHOD='POST'>";
echo "<input type='hidden' name='noSemaine' value=''>";
echo "<input type='hidden' name='anneeCalc' value=''>";
echo "<table border='0' width='100%'><tr>";
echo "<td width='33%'></td><td align='center' width='33%'><h2>";
echo "<img id='up' src='/iconsFam/resultset_previous.png' onClick='submitSemaineAnnee(".$noSemMoinsUn.",".$anneeMoinsUn.")'>";
echo "Semaine ".$noSemaine;
if($vendredi<mktime(0, 0, 0, date('m'), date('d'), date('y'))) {
	echo "<img id='down' src='/iconsFam/resultset_next.png' onClick='submitSemaineAnnee(".$noSemPlusUn.",".$anneePlusUn.")'>";
}
echo "</h2></td>";
echo "<td align='right'></td></tr>";
$lundiTxt =  "Semaine du lundi " . date('d.m', $lundi) . " au vendredi ".date('d.m.Y', $vendredi) . "";
echo "<tr><td></td><td align='center'>".$lundiTxt."</td><td></td></tr>";
echo "</table>";
echo "<br>\n";
echo "<div class='post'>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Tâches atelier</div>";

echo "<table id='hor-minimalist-b' width='100%'><tr>";
echo "<th>Tâches</th>";
echo "<th align='left' width='300'>Attribuée à</th>";
echo "<th width='10'></th><th width='50'></th><th width='10'></th>";
echo "</tr>";

$assigned = false;
$classeAssigned = "";
// Pour chaque tâches:
foreach ($configurationTJ as $postache => $libtache) {
	$text = str_replace("\n", "<br />", $libtache);
	echo "<tr><td><b>".$text."</b></td>";
	// recherche de l'attribution
	$requete = "SELECT * FROM elevesbk el";
	$requete .= " left join remarquesuivi rem on el.IDGDN=rem.IDEleve where (rem.TypeRemarque=4 or rem.TypeRemarque=5 or rem.TypeRemarque=6 or rem.TypeRemarque=7) and DateSaisie = '".$dateTache."' and rem.Remarque like '".$postache."%'";
	//$requete .= " left join attribeleves att on el.IDGDN=att.IDEleve and att.IDAttribut=107 and att.Date = '".$dateTache."'";
	//$requete .= " where IDGDN=".$IDEleve;
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	echo "<td>";
	$ligne = mysqli_fetch_assoc($resultat);
	if(!empty($ligne)) {
		$assigned = true;
		$classeAssigned = $ligne['Classe'];
		$stateAct = $ligne['TypeRemarque'];
		// devrait être unique mais si plusieurs assignation de la même tâche, on l'indique tout de même
		switch($stateAct) {
			case 4:
				
			case 7:
				if($stateAct==4&&(strtotime($ligne['DateSaisie'])>time()||time()-strtotime($ligne['DateSaisie'])<(86400*7))) {
					echo "<font color='#9900cc'>".$ligne['Nom']." ".$ligne['Prenom']."</font>";
				} else {
					echo "<font color='#FF0000'>".$ligne['Nom']." ".$ligne['Prenom']."</font>";
				}
				break;
			case 5:
				echo "<font color='#FF7F00'>".$ligne['Nom']." ".$ligne['Prenom']."</font>";
				break;
			case 6:
				echo "<font color='#007F00'>".$ligne['Nom']." ".$ligne['Prenom']."</font>";
				break;
			default:
				echo $ligne['Nom']." ".$ligne['Prenom'];
		}
		echo "</td>";
		if(($stateAct==4||$stateAct==5)&& time()-strtotime($ligne['DateSaisie'])>(86400*3)) {
			echo " <td><a href='tachesHebdo.php?Date=$dateTache&IDEleve=$ligne[IDGDN]&IDRemSuivi=$ligne[IDRemSuivi]&action=done&idtache=$postache'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Tâche effectuée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td>";
			echo " <td><a href='tachesHebdo.php?Date=$dateTache&IDEleve=$ligne[IDGDN]&IDRemSuivi=$ligne[IDRemSuivi]&action=notdone&idtache=$postache'><img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Tâche non effectuée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td>";
		} else {
			echo "<td></td><td></td>";
		}
		//echo "<td><a href='tachesHebdo.php?modeHTML&Date=$dateTache&IDEleve=$ligne[IDGDN]&IDRemSuivi=$ligne[IDRemSuivi]&action=delete&idtache=$postache'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer l\'attribution')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td>";
		echo "<td>";
		if($stateAct==4) {
			echo "<img src='/iconsFam/arrow_refresh.png' align='absmiddle' onmouseover=\"Tip('Autre attribution')\" onmouseout='UnTip()' onClick='submitAssign(".$postache.",".$ligne['IDGDN'].")'> ";
		}
		echo "</td>";
	} else {
		echo "</td><td></td><td></td><td></td>";
	}
	echo "</tr>";
}

// ligne pour bouton
if(!$assigned) {
	// construction de la liste des classes
	$optionsClasses = "";
	foreach ($configurationATE as $pos => $value) {
		$optionsClasses .= "<option value='$value'>$value</option>";
	}
	echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
	echo "<tr newAssign='1'><td colspan='5' align='right'><img src='/iconsFam/user_add.png' onmouseover=\"Tip('Assigner les tâches')\" onmouseout='UnTip()' onclick='toggle(\"newAssign\");'></td></tr>";
	echo "<tr newAssign='1' style='display:none' >";
	echo "<td></td><td valign='top' colspan='1'><select name='ClasseAssign'>".$optionsClasses."</select></input></td>";
	echo "<td valign='top' colspan='3' align='right'><input type='submit' name='assigner' value='Attribuer'></input></td></tr>";
} else {
	echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
	echo "<tr><td colspan='5' align='right'><a href='tachesHebdo.php?Date=$dateTache&action=deleteAll'><img src='/iconsFam/user_delete.png' onmouseover=\"Tip('Effacer les attributions')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
	// champs cachés pour envoi par position
	echo "<tr><td colspan='5'>";
	echo "<input type='hidden' name='action' value=''>";
	echo "<input type='hidden' name='ClasseAssign' value='".$classeAssigned."'>";
	echo "<input type='hidden' name='posAssign' value=''>";
	echo "<input type='hidden' name='IDEleve' value=''>";
	echo "</td></tr>";
}

?>
</table></div>
<br><br>
<div id='corners'><div id='legend'>Légende</div>
<br><table border='0' width='100%'>
<!-- tr><td colspan><b>".libelleTradUpd('liste_eleve').":</b></td></tr -->
<tr><td><b>Significations des couleurs: </b></td><td width='20%' align='center'><font color='#9900cc'>Attibuée</font></td><td width='20%' align='center'><font color='#FF7F00'>Effectuée mais non vérifiée</font></td><td width='20%' align='center'><font color='#007F00'>Effectuée et vérifiée</font></td><td width='20%' align='center'><font color='#FF0000'>Non effectuée/validée</font></td></tr>
</table></div>
<br><br>
<div id='corners'><div id='legend'>Statistiques</div>
<br><table border='0'>
<tr><td colspan='3'><b>Attributions déjà effectuées sur l'année en cours: </b></td></tr>
<?php
// recherche des classes déjà sélectionnée pour le semestre en cours
$requete = "SELECT Classe, count(*) as nbr FROM elevesbk el";
$requete .= " left join remarquesuivi rem on el.IDGDN=rem.IDEleve where (rem.TypeRemarque=4 or rem.TypeRemarque=5 or rem.TypeRemarque=6 or rem.TypeRemarque=7)";
//$requete .= " left join attribeleves att on el.IDGDN=att.IDEleve and att.IDAttribut=107 and att.Date = '".$dateTache."'";
if($noSemaine>30) {
	$requete .= " and DateSaisie >='".$anneeCalc."-08-01'";
} else {
	$requete .= " and DateSaisie >='".($anneeCalc-1)."-08-01'";
}
$requete .= " group by Classe";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);

while ($ligne = mysqli_fetch_assoc($resultat)) {
	echo "<tr><td width='50'></td><td>".$ligne['Classe']."</td><td>".ceil($ligne['nbr']/count($configurationTJ))."</td></tr>";
}
?>
</table></div>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>

