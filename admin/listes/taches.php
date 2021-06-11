<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:81
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: taches.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:35
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

/* mode html ou excel */
$modeHTML = false;
if(isset($_GET['modeHTML'])) {
	$modeHTML = true;
}
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
	$modeHTML = true;
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
		$requete = "update remarquesuivi set TypeRemarque=6 where IDRemSuivi=$_GET[IDRemSuivi]";// and Remarque like '".$_GET[idtache]."%'";
		//echo "<br>".$requete;
		mysqli_query($connexionDB,$requete);
	}
	if($_GET['action']=='notdone') {
		// ajouter une remarque administrative
		$IDEleve = $_GET['IDEleve'];
		$date = $_GET['Date'];
		// date du jour
		//$date = date("Y-m-d");
		$requete = "update remarquesuivi set TypeRemarque=7 where IDRemSuivi=$_GET[IDRemSuivi]";// and Remarque like '".$_GET[idtache]."%'";
		//$requete = "INSERT INTO $tableAttribEleves (IDAttribut, IDEleve, Remarque, Date) values (107, $IDEleve, \"Ajout automatique\",\"$date\")";
		//echo "<br>".$requete;
		mysqli_query($connexionDB,$requete);
	}
	

}

$add_assign = false;
if($_GET['action']=='assigner') {
	// effectuer les assignation pour la semaine
	$add_assign = true;
	$modeHTML = true;
}

if(!$modeHTML) {
	/* librairies pour Excel */
	require_once 'PHPExcel/IOFactory.php';
	require_once 'PHPExcel/Writer/Excel5.php';
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	//$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($_SERVER['DOCUMENT_ROOT']."/".$_SESSION['home']."docBase/taches.xls");
} else {
	include("entete.php");
?>

<div id="page">
<script>
function toggle(thisname) {
	tr=document.getElementsByTagName('table')
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

</script>
<?php
include("../../userInfo.php");
}


/* position de départ pour excel */
$configurationColExcel = array(1 => "B","C","D","E","F");
$configurationLiExcel = 7;

/* jour de la semaine */
$tab_jour = array(1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi');


// calcul lundi et vendredi
$dateCalc=mktime(0,0,0,1,4,$anneeCalc);
$jour_semaine=date("N",$dateCalc);
$lundi=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaine-1);
$vendredi = $lundi + 86400*5 - 3600*2; // retrait de 2h -> vendredi soir même si GMT +1 ou +2

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

/* en-tête */
if(!$modeHTML) {
	$objPHPExcel->getActiveSheet()->setCellValue('A'.($configurationLiExcel-1), "Semaine $noSemaine");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.($configurationLiExcel-6), "Section ".$app_section);
} else {
	echo "<FORM id='myForm' ACTION='taches.php' METHOD='POST'>";
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
	//echo "<table id='hor-minimalist-b' width='100%'><tr>";
	//echo "<th></th><th></th>";
	//foreach ($configurationTJ as $pos => $value) {
	//	echo "<th width='120'>".$value."</th>";
	//}
	//echo "</tr><tr>";
}

/* tableau des élèves par jour et construction de l'entête*/
$eleves = array();
$elevesID = array();
$cnt = 1;
foreach ($configurationTCH as $pos => $value) {
	// associer la liste d'élève

		$requete = "SELECT * FROM $tableElevesBK el join eleves eli on el.IDGDN=eli.IDGDN";
		if($triEntreprises) {
			$requete .= " and IDEntreprise=1";
		}
		$requete .= " left outer join $tableAttribEleves at on (el.IDGDN = at.IDEleve and (at.IDAttribut = 8 OR at.IDAttribut = 13)) where Classe like '$value%' order by Nom, Prenom";
		$resultat =  mysqli_query($connexionDB,$requete);
		$classe = array();
		$elID = array();
		//echo "classe: ".$value."<br>";
		while ($ligne = mysqli_fetch_assoc($resultat) ) {
			//echo $ligne['Nom']." - ".$ligne['IDAttribut'];
			if(empty($ligne['IDAttribut'])) {
				$classe[] = $ligne['Nom'] . ' ' . $ligne['Prenom'];
				$elID[] = $ligne['IDGDN'];
				//echo " el: ".$ligne['Nom'] . ' ' . $ligne['Prenom']."<br>";
			}
		}

	//print_r($classe);
	$eleves[$tab_jour[$cnt]] = $classe;
	$elevesID[$tab_jour[$cnt]] = $elID;
	$cnt++;
	// ajout en-tête
	if(!$modeHTML) {
		$objPHPExcel->getActiveSheet()->setCellValue($configurationColExcel[$pos].$configurationLiExcel, iconv("ISO-8859-1", "UTF-8", "$configurationTCH[$pos]"));
	}
}
$tab_taches = array();
$cntJour = 1;
foreach ($eleves as $jour => $classe) {
	if($modeHTML) {
		//echo "<tr><td width='100'><b>$tab_jour[$cntJour]</b></td><td width='100'><b>$configurationTCH[$cntJour]</b></td>\n";
	}
	// nombre d'élève à dispo
	$cntEleves = count($classe);
	$debut = ($noSemaine -1  + $decalageSemaine[$cntJour]) % $cntEleves;
	$posNom = $debut;
	//echo "début: $debut, nom: $classe[$posNom]";
	$posCell = $configurationLiExcel + 2;
	for($cnt=1;$cnt<=$tachesParJour;$cnt++) {
		if(!$modeHTML) {
			// libellé
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$posCell, iconv("ISO-8859-1", "UTF-8", "$configurationTJ[$cnt]"));
		}
		if($posNom >= $cntEleves) {
			$posNom = 0;
		}
		if($cnt>$cntEleves) {
			//$posNomDec = $decalageEleve[$cnt];
			$posNomModulo = ($posNom + $decalageEleve[$cnt]) % $cntEleves;
			//echo "$posNomModulo, $cntEleves, $classe[$posNomModulo]<br>";
			if(!$modeHTML) {
				$objPHPExcel->getActiveSheet()->setCellValue($configurationColExcel[$cntJour].$posCell, iconv("ISO-8859-1", "UTF-8", "$classe[$posNomModulo]"));
			} else {
				//echo "<td>$classe[$posNomModulo]</td>";
				$tab_taches[$cnt][$cntJour] = $elevesID[$jour][$posNomModulo];
			}
		} else {
			//echo "      $classe[$posNom]<br>";
			if(!$modeHTML) {
				$objPHPExcel->getActiveSheet()->setCellValue($configurationColExcel[$cntJour].$posCell, iconv("ISO-8859-1", "UTF-8", "$classe[$posNom]"));
			} else {
				//echo "<td>$classe[$posNom]</td>";
				$tab_taches[$cnt][$cntJour] = $elevesID[$jour][$posNom];
			}
		}
		$posNom++;
		// incrément ligne excel
		$posCell = $posCell + 2;
	}
	//echo "<br>\n";
	$cntJour++;
	if($modeHTML) {
		echo "</tr>\n";
	}
}

// nouvelle table HTML
if($modeHTML) {
	echo "<table id='hor-minimalist-b' width='100%'><tr>";
	echo "<th>Tâches</th>";
	foreach($tab_jour as $cntjour => $nomjour ) {
		echo "<th align='left' colspan='3'>".$nomjour."<br>".$configurationTCH[$cntjour]."</th>";
	}
	echo "</tr>";
	foreach ($configurationTJ as $postache => $libtache) {
		$text = str_replace("\n", "<br />", $libtache);
		echo "<tr><td><b>".$text."</b></td>";
		foreach($tab_jour as $cntjour => $nomjour ) {
			$dateTache = date('Y-m-d', $lundi+(($cntjour-1)*86400));
			$IDEleve = $tab_taches[$postache][$cntjour];
			if($add_assign) {
				// ajouter si assignation
				$requete = $requete = "INSERT INTO remarquesuivi (IDTheme, IDEleve, DateSaisie, Remarque, TypeRemarque, UserId) values (1, $IDEleve, \"$dateTache\", \"$postache\", 4, \"$_SESSION[user_login]\")";
				//echo $requete;
				mysqli_query($connexionDB,$requete);
			}
			$requete = "SELECT * FROM elevesbk el";
			$requete .= " left join remarquesuivi rem on el.IDGDN=rem.IDEleve and (rem.TypeRemarque=4 or rem.TypeRemarque=5 or rem.TypeRemarque=6 or rem.TypeRemarque=7) and DateSaisie = '".$dateTache."' and rem.Remarque like '".$postache."%'";
			//$requete .= " left join attribeleves att on el.IDGDN=att.IDEleve and att.IDAttribut=107 and att.Date = '".$dateTache."'";
			$requete .= " where IDGDN=".$IDEleve;
			//echo $requete;
			$resultat =  mysqli_query($connexionDB,$requete);
			$ligne = mysqli_fetch_assoc($resultat);
			echo "<td>";
			if($ligne['TypeRemarque']==4||$ligne['TypeRemarque']==7) { //||$ligne['IDAttribut']==107) {
				if($ligne['TypeRemarque']==4&&(strtotime($ligne['DateSaisie'])>time()||time()-strtotime($ligne['DateSaisie'])<86400)) {
					echo "<font color='#9900cc'>";
				} else {
					echo "<font color='#FF0000'>";
				}
			}
			if($ligne['TypeRemarque']==5) {
				echo "<font color='#FF7F00'>";
			}
			if($ligne['TypeRemarque']==6) {
				echo "<font color='#007F00'>";
			}
			echo $ligne['Nom']."<br>".$ligne['Prenom'];
			if($ligne['TypeRemarque']==5||$ligne['TypeRemarque']==4||$ligne['TypeRemarque']==6||$ligne['TypeRemarque']==7) { //||$ligne['IDAttribut']==107) {
				echo "</font'></td>";
				//if($ligne['IDAttribut']==107||
				if($ligne['TypeRemarque']==6||strtotime($ligne['DateSaisie'])>time()) {
					echo "<td></td><td></td>";
				} else if($ligne['TypeRemarque']==7) {
					echo "<td width='10'></td><td width='50'><a href='taches.php?modeHTML&Date=$dateTache&IDEleve=$ligne[IDGDN]&IDRemSuivi=$ligne[IDRemSuivi]&action=delete&idtache=$postache'><img src='/iconsFam/delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer l\'attribution')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td>";
				} else {
					echo " <td width='10'><a href='taches.php?modeHTML&Date=$dateTache&IDEleve=$ligne[IDGDN]&IDRemSuivi=$ligne[IDRemSuivi]&action=done&idtache=$postache'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Tâche effectuée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td>";
					echo " <td width='50'><a href='taches.php?modeHTML&Date=$dateTache&IDEleve=$ligne[IDGDN]&IDRemSuivi=$ligne[IDRemSuivi]&action=notdone&idtache=$postache'><img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Tâche non effectuée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td>";
				}
			} else {
				echo "</td><td></td><td></td>";
			}
		}
		echo "</tr>";
		
	}
	//echo "</table>";
	// ligne pour bouton
		echo "<tr><td colspan='16' valign='bottom' bgColor='#DEDEDE'></td></tr>";
		echo "<tr><td colspan='16' align='right'><a href='taches.php' onmouseover=\"Tip('Imprimer la liste')\" onmouseout='UnTip()'><img src='/iconsFam/printer.png'></a> <a href='taches.php?action=assigner'><img src='/iconsFam/user_add.png' onmouseover=\"Tip('Assigner les tâches')\" onmouseout='UnTip()'></a></td></tr>";
}

if(!$modeHTML) {
	// générer la feuille excel
	$writer = new PHPExcel_Writer_Excel5($objPHPExcel);
	header('Content-type: application/vnd.ms-excel');
	header("Content-Disposition: attachment;Filename=taches.xls");
	$writer->save('php://output');

	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
	//header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	//$objWriter->save("php://output");
} else {
?>
</table></div>
<br><br>
<div id='corners'><div id='legend'>Légende</div>
<br><table border='0' width='100%'>
<!-- tr><td colspan><b>".libelleTradUpd('liste_eleve').":</b></td></tr -->
<tr><td colspan='5'>Les noms en noir indiquent que la tâche n'a pas été assignée</td></tr>
<tr><td>Les noms en couleur indiquent: </td><td width='20%'><font color='#9900cc'>A faire</font></td><td width='20%'><font color='#FF7F00'>Effectuée mais non vérifiée</font></td><td width='20%'><font color='#007F00'>Effectuée et vérifiée</font></td><td width='20%'><font color='#FF0000'>Non effectué/validé</font></td></tr>

</table></div>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
<?php } ?>
