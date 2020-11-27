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

if(isset($_POST['vue'])) {
	$vue = $_POST['vue'];
	$_SESSION['vue'] = $vue;
} else if(isset($_SESSION['vue'])) {
	$vue=$_SESSION['vue'];
} else {
	$vue=2;
	$_SESSION['vue'] = $vue;
}
//echo "vue: ".$_SESSION['vue']."/".$_POST['vue']."/".$_GET['vue'];
/*
if($vue==1) {
	$afftab1 = "";
	$afftab2 = "style='display:none'";
} else {
	$afftab1 = "style='display:none'";
	$afftab2 = "";
}
*/
if($vue==1) {
	if(!isset($_POST['noSemaine']) || "" == $_POST['noSemaine']) {
		//echo "pas trouvé en POST: ".$_POST['noSemaine'];
		if(!isset($_SESSION['noSemaine']) || "" == $_SESSION['noSemaine']) {
			//echo "- pas trouvé en session: ".$_SESSION['noSemaine'];
			$noSemaine = date('W');
			$anneeCalc = date('Y');
		} else {
			//echo "- trouvé en session: ".$_SESSION['noSemaine'];
			$noSemaine = $_SESSION['noSemaine'];
			$anneeCalc = $_SESSION['anneeCalc'];
		}
	} else {
		//echo "trouvé en POST: ".$_POST['noSemaine'];
		$noSemaine = $_POST['noSemaine'];
		$anneeCalc = $_POST['anneeCalc'];
		$_SESSION['noSemaine'] = $noSemaine;
		$_SESSION['anneeCalc'] = $anneeCalc;
		
	}
} else {
	$noSemaine = date('W');
	$anneeCalc = date('Y');
}

// calcul lundi et vendredi

$dateCalc=mktime(0,0,0,1,4,$anneeCalc);
$jour_semaine=date("N",$dateCalc);
$lundi=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaine-1);
$vendredi = $lundi + 86400*4;
$lundiSemEncours = $dateCalc-86400*($jour_semaine)+604800*(date('W')-1);
//$lundi=$dateCalc*($jour_semaine-1)+604800*($noSemaine-1);

/* année en cours */
$annee = date('Y');
$mois = date('m');
/* années pour en-tête */
if($mois<8) {
	$anneePlus = $annee;
	$annee = $annee-1;
} else {
	$anneePlus= $annee+1;
}
if($modeEvaluation=="hebdo") {
	// si mode hebdo on affiche les 4 dernières année (bidouille en attendant)
	$annee = $annee-5;
}

$semestreAct = 1;
if($noSemaine<30 && $noSemaine>=$noSemaineSem2) {
	$semestreAct = 2;
}

$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}

/*
$filtre = "1";
if(isset($_POST['triProjet'])) {
	$filtre = $_POST['triProjet'];
}
$filtreSQL = "";
if($filtre!=10) {
	$filtreSQL = " and ep.IDEtatProjet = ".$filtre;
}

*/
function progressB($valeur) {
	if(isset($valeur)) {
		return "<div id='progressbar'><div id='indicator' style='width:".$valeur."px'></div></div>";
	} else {
		return "";
	}
}
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
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').anneeCalc.value=annee;
	document.getElementById('myForm').submit();
}
function submitSemaine(nosemaine) {
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').submit();
}

</script>
<?
include($app_section."/userInfo.php");
/* en-tête */

function niveauMoy($moyenne) {
	return chr(round($moyenne,0,PHP_ROUND_HALF_DOWN)+64);
}
function noteMoy($moyenne) {
	return round($moyenne,1,PHP_ROUND_HALF_UP);
}

echo "<FORM id='myForm' ACTION='journaux.php'  METHOD='POST'>";

echo "<input type='hidden' name='vue' value='".$vue."'>";
echo "<input type='hidden' name='noSemaine' value=''>";
echo "<input type='hidden' name='anneeCalc' value=''>";

echo "<div class='post'>";
echo "<center> <font color='#088A08'></font></center>";
/*
echo "<br><table border='0' width='850'><tr><td><h2>";
foreach($listeId as $key => $valeur) {
	if($valeur[0]==$IDEleve) {
		if($key!=0) {
			echo "<a href='/journaux/activites.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img src='/iconsFam/resultset_previous.png'></a>";
		}
		echo $nom." ".$prenom;
		if($key<count($listeId)-1) {
			echo "<a href='/journaux/activites.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img src='/iconsFam/resultset_next.png'></a>";
		}
		break;
	}
}
echo "</h2></td><td align='right'>\n";
*/

	
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
//$anneePlusUn = date('Y',$lundi + 86400*7);

if($vue==2) {
	echo "<table border='0' width='100%'><tr><!-- td><h2>Journaux ".$annee."/".$anneePlus."</h2></td -->";
	//echo "<br><table border='0' width='1000' vueTable='1' ".$afftab1."><tr><td><h2>Journaux ".$annee."/".$anneePlus."</h2></td>";
	//echo "<td align='right'>Vue: <select name='vue' onChange='toggle(\"vueTable\")'><option value='1'>Par thème</option><option value='2' ";
	echo "<td align='right'>Vue: <select name='vue' onChange='document.getElementById(\"myForm\").submit();'><option value='2'>Par thème</option><option value='1' ";
	echo ">Par semaine</option></select></td></tr>";
	echo "</table>";
} else {
	echo "<table border='0' width='100%'><tr><!-- td><h2>Journaux ".$annee."/".$anneePlus."</h2></td -->";
	//echo "<br><table border='0' width='1000' vueTable='1' ".$afftab2."><tr><td><h2>Journaux ".$annee."/".$anneePlus."</h2></td>";
	echo "<td width='33%'></td><td align='center' width='33%'><h2><img id='up' src='/iconsFam/resultset_previous.png' onClick='submitSemaineAnnee(".$noSemMoinsUn.",".$anneeMoinsUn.")'>Semaine ".$noSemaine;
	if($vendredi<mktime(0, 0, 0, date('m'), date('d'), date('y'))) {
		echo "<img id='down' src='/iconsFam/resultset_next.png' onClick='submitSemaineAnnee(".$noSemPlusUn.",".$anneePlusUn.")'>";
	}
	echo "</h2></td>";
	echo "<td align='right'>Vue: <select name='vue' onChange='document.getElementById(\"myForm\").submit();'><option value='2'>Par thème</option><option value='1' selected>Par semaine</option></select></td></tr>";
	//echo "<td align='right'>Vue: <select name='vue' onChange='toggle(\"vueTable\")'><option value='1'>Par thème</option><option value='2' selected>Par semaine</option></select></td></tr>";
	$lundiTxt =  "Semaine du lundi " . date('d.m', $lundi) . " au vendredi ".date('d.m.Y', $vendredi) . "";
	echo "<tr><td></td><td align='center'>".$lundiTxt."</td><td></td></tr>";
	echo "</table><br>";
}
$whereclasse = "";
foreach ($configurationJRN as $pos => $value) {
	if($pos!=1) {
		$whereclasse .= " OR ";
	}
	$whereclasse .= "Classe LIKE '";
	$whereclasse .= $value;
	$whereclasse .= "'";
}
	
if($vue==2) {
	echo "<br><div id='corners'>";
	echo "<div id='legend'>Journaux ".$annee."/".$anneePlus."</div>";
	echo "<table id='hor-minimalist-b' width='100%'>\n";
	//echo "<table id='hor-minimalist-b' vueTable='1' ".$afftab1.">\n";
	echo "<tr><th width='50'>Thème</th><th width='150'>Nom</th><th width='50' align='center'>Nbr. jour</th><th width='80' align='center'>Nbr. heures</th>";
	if($modeEvaluation=="hebdo") {
		echo "<th></th>";
	} else {
		echo "<th>Eval. semestre ".$semestreAct."</th>";
	}
	//echo "<th width='100'>Motivation</th><th width='100'>Progrets</th><th width='100'>Difficultés</th><th width='100'>Notes</th>";
	echo "<th width='10' align='right'>Journaux</th></tr>";

	
	// recherche
	//Classe LIKE 'ELT %' OR Classe LIKE 'AUT 1'
$requete = "SELECT IDTheme, TypeTheme, NomTheme, Objectif, IDGDN, Classe, Nom, Prenom, sum( heures ) AS heures, count( heures ) AS jours
FROM (
SELECT  NomTheme, jo.IDTheme as IDTheme,TypeTheme,Objectif,jo.IDEleve as IDGDN, Classe, Nom, Prenom, DateJournal, sum( Heures ) AS heures
FROM elevesbk
JOIN journal jo ON IDGDN = jo.IDEleve
JOIN theme th ON jo.IDTheme=th.IDTheme
WHERE (".$whereclasse.") and (DateJournal between '".$annee."-08-01' and '".$anneePlus."-07-31')
GROUP BY jo.IDEleve, jo.IDTheme, jo.DateJournal
) AS res
GROUP BY IDTheme, IDGDN
order by TypeTheme,NomTheme,Nom,Prenom";
//LEFT JOIN evaluation ev on jo.IDTheme=ev.IDTheme and jo.IDEleve=ev.IDEleve and (ev.Annee is null OR ev.Annee = ".$annee.") 
	
	//echo $requete;
	$resultat =  mysql_query($requete);
	$cnt=0;
	$lastTheme = 0;
	while ($ligne = mysql_fetch_assoc($resultat)) {
		if($lastTheme!=$ligne['IDTheme']) {
			// nouveau theme
			if($cnt!=0) {
				//sauf pour la première ligne
				echo "<tr><td colspan='9' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
			}
			echo "<tr><td colspan='3' valign='top' bgColor='#DEDEDE'><b>";
			if($ligne['TypeTheme']==1) {
				echo "Projet - ";
			}
			$objtxt = "";
			if($ligne['Objectif']!=0) {
		 		$objtxt = $ligne['Objectif']."h";
			}
			echo $ligne['NomTheme']."</b></td><td align='center' valign='top' bgColor='#DEDEDE'onmouseover=\"Tip('Objectif de temps')\" onmouseout='UnTip()'><b>".$objtxt."</b></td><td colspan='5' valign='top' bgColor='#DEDEDE'></td></tr>";
			$lastTheme = $ligne['IDTheme'];
		} 
		echo "<tr onclick='document.location.href=\"../detail/activites.php?from=journaux&triPeriode=1&idEleve=".$ligne['IDGDN']."&nom=".$ligne['Nom']."&prenom=".$ligne['Prenom']."&IDTheme=".$ligne['IDTheme']."\"'><td></td><td valign='top'>";
		if($modeEvaluation=="hebdo") {
			//echo $ligne['Classe']."-";
		}
		echo $ligne['Nom']." ".$ligne['Prenom']."</td><td valign='top' align='center'>".$ligne['jours']."</td><td align='center' valign='top'>".sprintf("%5.1f",$ligne['heures'])."h</td>";
		
		// recherche des évaluations
		$requeteEval = "SELECT distinct NoSemaine, Annee, DateValidation FROM evalhebdo where IDEleve = ".$ligne['IDGDN']." and IDTheme=".$ligne['IDTheme'];
		// tri sur année en cours
		//if($noSemaine>30) {
			$requeteEval .= " and (Annee between ".$annee." and ".$anneePlus.")";
		//} else {
		//	$requeteEval .= " and (Annee between ".($annee-1)." and ".$annee.")";
		//}
		// uniquement semestre en cours
		if($semestreAct==1) {
			$requeteEval .= " and (NoSemaine > 30 or NoSemaine < ".$noSemaineSem2.")";
		} else {
			$requeteEval .= " and (NoSemaine between ".$noSemaineSem2." and 30)";
		}
		$requeteEval .=	" order by Annee, NoSemaine LIMIT 20";
		$resultatEval =  mysql_query($requeteEval);
		$numEval = mysql_num_rows($resultatEval);
		echo "<td valign='top'>";
		if(!empty($resultatEval)&&$numEval>0) {
			while ($ligneEval = mysql_fetch_assoc($resultatEval)) {
				echo "<a id='circle";
				if(empty($ligneEval['DateValidation'])) {
					echo "red";
				}
				//echo "' href='#' onclick='submitSemaineAnnee(".$ligne['NoSemaine'].",".$ligne['Annee'].")'>";
				echo "'>";
				echo "<i>".$ligneEval['NoSemaine']."</i>";
				echo "</a>";
			}
		}
		echo "</td>";
		//echo "<td valign='top'>".progressB($ligne['TravailPond'])."</td><td valign='top'>".progressB($ligne['ProgretsPond'])."</td><td valign='top'>".progressB($ligne['DifficultesPond'])."</td>";
		//echo "<td>";
		//echo <img src='/iconsFam/exclamation.png' align='absmiddle' onmouseover=\"Tip('Avertir oubli évaluation')\" onmouseout='UnTip()'>";
		//if(!empty($ligne['NoteTechnique'])) {
		//	echo $ligne['NoteTechnique']." | ".$ligne['NoteApplication']." | ".$ligne['NoteRendement']." | ".$ligne['NoteSavoirEtre'];
		//}
		//echo "</td>";
		echo "<td align='center'>";
		// recherche journaux non validés
		$requeteEntrees = "SELECT count(IDGDN) as Entrees FROM elevesbk JOIN journal jo ON IDGDN = IDEleve WHERE (DateJournal between '".$annee."-08-01' and '".date("Y-m-d",$lundiSemEncours)."') and DateValidation is null and IDGDN=".$ligne['IDGDN']." and IDTheme=".$ligne['IDTheme'];
		//echo $requeteEntrees;
		$resultatEntrees =  mysql_query($requeteEntrees);
		$ligneEntrees = mysql_fetch_array($resultatEntrees);
		if($ligneEntrees[0]!=0) {
			echo "<img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('".$ligneEntrees[0]." entrée(s) dans les journaux non validée(s) avant le ".date("d.m.Y",$lundiSemEncours)."')\" onmouseout='UnTip()'>";
		} else {
			echo "<img src='/iconsFam/tick.png' align='absmiddle'>";
		}
		//echo "<a href='../detail/evaluations.php?from=journaux&nom=".$ligne['Nom']."&prenom=".$ligne['Prenom']."&idEleve=".$ligne['IDGDN']."&IDTheme=".$ligne['IDTheme']."'>";		
		//if(empty($ligne['DateValidationAuto']) || $ligne['DateValidationAuto']=="0000-00-00") {
		//	echo " <img src='/iconsFam/bullet_red.png' align='absmiddle' onmouseover=\"Tip('Evaluation non validée')\" onmouseout='UnTip()'>";	
		//} else {
		//	if(empty($ligne['Annee'])) {
		//		echo " <img src='/iconsFam/bullet_orange.png' align='absmiddle' onmouseover=\"Tip('Evaluation validée, pas assignée à une période')\" onmouseout='UnTip()'>";
		//	} else {
		//		echo " <img src='/iconsFam/bullet_green.png' align='absmiddle' onmouseover=\"Tip('Evaluation validée, année ".$ligne['Annee']."/".($ligne['Annee']+1)."')\" onmouseout='UnTip()'>";
		//	}
		//}
		//echo "</a>";
		echo "</td></tr>";
		$cnt++;
	}
	if ($cnt==0) {
		echo "<tr><td colspan='9' align='center'><i>Aucun enregistrement</i></td></tr>";
	} 
	
	echo "</table></div>";
} else {
	$requete = "SELECT Classe, elbk.IDGDN, Nom, Prenom, DateJournal, DateValidation, Heures FROM elevesbk elbk join eleves el on elbk.IDGDN=el.IDGDN and el.IDEntreprise=1 left join attribeleves att on att.IDEleve=elbk.IDGDN and IDAttribut=13 left join journal jo on elbk.IDGDN=jo.IDEleve and (jo.DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') where IDAttribut is NULL and (".$whereclasse.") order by Classe desc ,Nom,Prenom,DateJournal";
	$resultat =  mysql_query($requete);
	//echo $requete;
	echo "<br><div id='corners'>";
	echo "<div id='legend'>Journaux hebdomadaires</div>";
	echo "<br><table id='hor-minimalist-b' width='100%' border='0'>\n";
	//echo "<br><table id='hor-minimalist-b' vueTable='1' ".$afftab2.">\n";
	echo "<tr><th width='50'>Classe</th><th width='250'>Nom</th><th width='30' align='center'>Lu</th><th width='30' align='center'>Ma</th><th width='30' align='center'>Me</th><th width='30' align='center'>Je</th><th width='30' align='center'>Ve</th><th width='50' align='center'>Nbr. jour</th><th width='80' align='center'>Nbr. heures</th><th width='10' align='right'>Validation</th></tr>";
	//echo "<tr><td colspan='10' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";

	$lastClasse = "";
	$lastEleve = 0;
	$jour = $lundi;
	$cumulJour = 0;
	$nbrjour = 0;
	$cumulsemaine = 0;
	$cntlock = 0;
	$cntunlock = 0;
	$msgErreur = '';

	while ($ligne = mysql_fetch_assoc($resultat)) {
		if($lastClasse!=$ligne['Classe'] || $lastEleve!=$ligne['IDGDN']) {
			// nouvelle classe ou nouvel élève -> terminer la ligne précédente
			if($lastEleve!=0) {
				if($cumulJour!=0) {
					// inscrire dernier cumul
					if($cumulJour>9) {
						echo "<td valign='top' align='center'><font color='#FF0000'>".sprintf("%2.1f",$cumulJour)."</font></td>";
						$msgErreur .= "Heures journalières > 9h<br>";
					} else {
						echo "<td valign='top' align='center'>".sprintf("%2.1f",$cumulJour)."</td>";
					}
					$cumulJour = 0;
					$nbrjour++;
					$jour += 86400;
				}
				// terminer les jours non renseignés
				while($jour<=$vendredi) {
					echo "<td valign='top' align='center'>-</td>";
					$jour += 86400;	
				}
				// terminer la ligne précédante
				echo "<td align='center'>".$nbrjour."</td><td align='center'>".sprintf("%4.1f",$cumulsemaine)."h</td><td align='right'>";
				if($joursATE[$lastClasse] != $nbrjour) {
					$msgErreur .= "Semaine incomplète<br>";
				} 
				if(!empty($msgErreur)) {
					echo "<img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('".$msgErreur."')\" onmouseout='UnTip()'>";
				}
				if($cntlock!=0 && $cntunlock==0) {
					echo " <img src='/iconsFam/bullet_green.png' align='absmiddle' onmouseover=\"Tip('Journal validé')\" onmouseout='UnTip()'>";
				} else if($cntlock!=0 && $cntunlock==0) {
					echo " <img src='/iconsFam/bullet_orange.png' align='absmiddle' onmouseover=\"Tip('Journal partiellement validé')\" onmouseout='UnTip()'>";
				} else {
					echo " <img src='/iconsFam/bullet_red.png' align='absmiddle' onmouseover=\"Tip('Journal non validé')\" onmouseout='UnTip()'>";
				}
				echo "</td></tr>";
			}
		}
		if($lastClasse!=$ligne['Classe']) {
			// nouvelle classe à afficher
			echo "<tr><td colspan='10' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
			echo "<tr bgColor='#DEDEDE'><td colspan='10' valign='top'><b>".$ligne['Classe']."</b></td></tr>";
			$lastClasse = $ligne['Classe'];
		}
		if($lastEleve!=$ligne['IDGDN']) {
			// nouvel élève à traiter
			$jour = $lundi;
			$cumulsemaine = 0;
			$nbrjour = 0;
			$cntlock = 0;
			$cntunlock = 0;
			$msgErreur = '';
			echo "<tr onclick='document.location.href=\"../detail/activites.php?from=journaux&idEleve=".$ligne['IDGDN']."&nom=".$ligne['Nom']."&prenom=".$ligne['Prenom']."&Classe=".urlencode($ligne['Classe'])."\"'><td></td><td valign='top'>".$ligne['Nom']." ".$ligne['Prenom']."</td>";
			$lastEleve = $ligne['IDGDN'];
		}
		
		while((empty($ligne['Heures']) && $jour<=$vendredi) || (!empty($ligne['DateJournal']) && date('Y-m-d', $jour)!=$ligne['DateJournal'])) {
			if($cumulJour!=0) {
				// nouveau jour
				if($cumulJour>9) {
					echo "<td valign='top' align='center'><font color='#FF0000'>".sprintf("%2.1f",$cumulJour)."</font></td>";
					$msgErreur .= "Heures journalières > 9h<br>";
				} else {
					echo "<td valign='top' align='center'>".sprintf("%2.1f",$cumulJour)."</td>";
				}
				$cumulJour = 0;
				$nbrjour++;
			} else {
				echo "<td valign='top' align='center'>-</td>";
			}
			$jour += 86400;
		}
		if(!empty($ligne['Heures'])) {
			$cumulJour += $ligne['Heures'];
			$cumulsemaine += $ligne['Heures'];
			if($ligne['DateValidation']!=0) {
				$cntlock++;
			} else {
				$cntunlock++;
			}
		} 
	}
	if($lastEleve!=0) {
		if($cumulJour!=0) {
			// inscrire dernier cumul
			if($cumulJour>9) {
				echo "<td valign='top' align='center'><font color='#FF0000'>".sprintf("%2.1f",$cumulJour)."</font></td>";
				$msgErreur .= "Heures journalières > 9h<br>";
			} else {
				echo "<td valign='top' align='center'>".sprintf("%2.1f",$cumulJour)."</td>";
			}
			$cumulJour = 0;
			$nbrjour++;
			$jour += 86400;
		}
		// terminer les jours non renseignés
		while($jour<=$vendredi) {
			echo "<td valign='top' align='center'>-</td>";
			$jour += 86400;	
		}
	
		// terminer la ligne précédante
		echo "<td align='center'>".$nbrjour."</td><td align='center'>".sprintf("%4.1f",$cumulsemaine)."h</td><td align='right'>";
		if(substr($lastClasse, -1)==4 && ($nbrjour<4 || $nbrjour>4)) {
			//echo "<td align='right'><img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('Semaine incomplète')\" onmouseout='UnTip()'>";
			$msgErreur .= "Semaine incomplète<br>";
					
		} else if($nbrjour<3 || $nbrjour>3) {
			//echo "<td align='right'><img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('Semaine incomplète".$msgErreur."')\" onmouseout='UnTip()'>";
			$msgErreur .= "Semaine incomplète<br>";
		} else {
			//echo "<td align='right'>";
		}
		if(!empty($msgErreur)) {
			echo "<img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('".$msgErreur."')\" onmouseout='UnTip()'>";
		}
		if($cntlock!=0 && $cntunlock==0) {
			echo " <img src='/iconsFam/bullet_green.png' align='absmiddle' onmouseover=\"Tip('Journal validé')\" onmouseout='UnTip()'>";
		} else if($cntlock!=0 && $cntunlock==0) {
			echo " <img src='/iconsFam/bullet_orange.png' align='absmiddle' onmouseover=\"Tip('Journal partiellement validé')\" onmouseout='UnTip()'>";
		} else {
			echo " <img src='/iconsFam/bullet_red.png' align='absmiddle' onmouseover=\"Tip('Journal non validé')\" onmouseout='UnTip()'>";
		}
		echo "</tr>";
	}

	echo "</table></div><br>";
}

?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>