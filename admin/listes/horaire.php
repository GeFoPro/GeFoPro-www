<?php
include("../../appHeader.php");

/* action */
$actionModifier = "";
if(isset($_POST['Modifier'])) {
	$actionModifier = $_POST['Modifier'];
}
$actionValider = "";
if(isset($_POST['Valider'])) {
	$actionValider = $_POST['Valider'];
}
$actionAnnuler = "";
if(isset($_POST['Annuler'])) {
	$actionAnnuler = $_POST['Annuler'];
}

/* teste is modification possible */
if(!empty($actionModifier)) {
	if(isset($_SESSION['APP']) && $_SESSION['APP']['LockHoraire']) {
		//$actionModifier = "";
		//$msgLock = "L'horaire est actullement modifié par un autre utilisateur";
	} else {
		$_SESSION['APP']['LockHoraire'] = true;
		application_save();
	}
}

/* jours semaine */
$tab_jour = array(1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi');

/* fonction constuction d'un id de cellule */
function getIDCell($jr,$pf,$pr,$tabJ) {
	return $tabJ[$jr] . $pf. $pr;
}
/* table des profs */
$requete = "SELECT * FROM $tableProf order by IDProf";
$resultat =  mysqli_query($connexionDB,$requete);
$profs = array();
$theorieProfs = array();
$timeProfs = array();
while ($ligne = mysqli_fetch_assoc($resultat) ) {
	$profs[$ligne['IDProf']] = $ligne['Nom'];
	$theorieProfs[$ligne['IDProf']] = $ligne['Theorie'];
	$timeProfs[] = 0;
}
$cntProfs = count($profs);

/* table des périodes */
$requete = "SELECT * FROM $tablePeriode where Duree > 0 order by IDPeriode";
$resultat =  mysqli_query($connexionDB,$requete);
$periods = array();
$dureePeriode = array();
while ($ligne = mysqli_fetch_assoc($resultat) ) {
	$periods[$ligne['IDPeriode']] = $ligne['Heure'];
	$dureePeriode[$ligne['IDPeriode']] = $ligne['Duree'];
}
$cntPeriode = count($periods);

// année et noSemaine
//$annee = date('Y');

/*
if(!isset($_POST['noSemaine']) || "" == $_POST['noSemaine']) {

	if(!isset($_SESSION['noSemaine']) || "" == $_SESSION['noSemaine']) {
		$noSemaine = date('W');
	} else {
		$noSemaine = $_SESSION['noSemaine'];
	}
} else {
	$noSemaine = $_POST['noSemaine'];
}
*/
if(!isset($_POST['noSemaine']) || "" == $_POST['noSemaine']) {
	//echo "pas trouvé en POST: ".$_POST['noSemaine'];
	if(!isset($_SESSION['noSemaine']) || "" == $_SESSION['noSemaine']) {
		//echo "- pas trouvé en session: ".$_SESSION['noSemaine'];
		$noSemaine = date('W');
		$anneeCalc = date('Y');
	} else {
		//echo "- trouvé en session: ".$_SESSION['noSemaine']."/".$_SESSION['anneeCalc'];
		$noSemaine = $_SESSION['noSemaine'];
		$anneeCalc = $_SESSION['anneeCalc'];
	}
} else {
	//echo "trouvé en POST: ".$_POST['noSemaine']."/".$_POST['anneeCalc'];
	$noSemaine = $_POST['noSemaine'];
	$anneeCalc = $_POST['anneeCalc'];
	$_SESSION['noSemaine'] = $noSemaine;
	$_SESSION['anneeCalc'] = $anneeCalc;
}

if(!empty($actionValider)) {
	/* effacer les anciens enregistrements */
	$requete = "delete from $tableSemaine where noSemaine = $noSemaine and annee=$anneeCalc";
	//echo "<br>".$requete."<br>";
	mysqli_query($connexionDB,$requete);

	// enregister le défaut pour l'année en cours
	$requete = "select max(IDSemaine) from $tableSemaine";
	$resultat =  mysqli_query($connexionDB,$requete);
	$line = mysqli_fetch_row($resultat);
	$IDSemaine = $line[0]+1;

	/* recherche des éventuels coches */
	for($jour=1;$jour<=5;$jour++) {
		foreach ($profs as $pos => $value) {
		    foreach ($periods as $cell => $value) {
			$idCell = getIDCell($jour,$pos,$cell,$tab_jour);
			$cellContent = "";
			if(isset($_POST[$idCell])) {
				$cellContent = $_POST[$idCell];
			}
			if(!empty($cellContent)) {
				//echo "-$idCell-";
				$requete = <<<REQ
INSERT INTO $tableSemaine
(IDSemaine, Annee, NoSemaine, NoJour, IDPeriode, IDProf) values
($IDSemaine, $anneeCalc, $noSemaine, $jour, $cell, $pos)
REQ;
				//echo "<br>".$requete."<br>";
				$resultat =  mysqli_query($connexionDB,$requete);
				$IDSemaine++;
			}
		    }
		}
	}
	// enlever le lock
	$_SESSION['APP']['LockHoraire'] = false;
	application_save ();
}

if(!empty($actionAnnuler)) {
	// enlever le lock
	$_SESSION['APP']['LockHoraire'] = false;
	application_save ();
}

// output de la page
$pageOut = "";

include("entete.php");
?>

<div id="page">
<SCRIPT language="Javascript">
var switchCheck = false;
function setCheckBGColor(id) {
	//alert("enter");
	check = document.getElementById(id);
	if(check.checked) {
		check.parentNode.bgColor = "#32cd32";
	} else {
		check.parentNode.bgColor = "#FFFFFF";
	}
}
function setBGColor(id,color) {
	//alert("enter");
	cell = document.getElementById(id);
	cell.parentNode.bgColor = color;
}
function setBGColorFillRight(id) {
	//alert("ok");
	check = document.getElementById(id);
	if(check.checked) {
		check.parentNode.bgColor = "#32cd32";
		if(switchCheck) {
			next = check.parentNode.nextSibling.firstChild;
			alert(''+next.nodeName);
			if(next!=null) {
				next.checked = true;
				setBGColorFillRight(next.id);
			}
		}
	} else {
		check.parentNode.bgColor = "#FFFFFF";
		if(switchCheck) {
			next = check.parentNode.nextSibling.firstChild;
			//alert(''+next.nodeName);
			if(next!=null) {
				next.checked = false;
				setBGColorFillRight(next.id);
			}
		}
	}
}
function switchAll() {
	switchCheck = true;
}
function switchOne() {
	switchCheck = false;
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
</SCRIPT>
<?php
include("../../userInfo.php");
// print_r($profs);echo "<br>";
// print_r($periods);echo "<br>";
// création des éléments sélectionnés
$checked = array();
//$noSemaineToUse = $noSemaine;
$cntBoucle = 0;
//while(count($checked)==0 && $cntBoucle<2) {
	$requete = "SELECT * FROM $tableSemaine where noSemaine=$noSemaine and annee=$anneeCalc";
	$resultat =  mysqli_query($connexionDB,$requete);
	// construction de la table
	while ($ligne = mysqli_fetch_assoc($resultat) ) {
		$checked[] = getIDCell($ligne['NoJour'],$ligne['IDProf'],$ligne['IDPeriode'],$tab_jour);
	}
	$cntBoucle++;
	if(count($checked)==0) {
		$requete = "SELECT * FROM $tableSemaine where noSemaine=0";
		$resultat =  mysqli_query($connexionDB,$requete);
		// construction de la table
		while ($ligne = mysqli_fetch_assoc($resultat) ) {
			$checked[] = getIDCell($ligne['NoJour'],$ligne['IDProf'],$ligne['IDPeriode'],$tab_jour);
		}
		$cntBoucle++;
	}
	//$noSemaineToUse = 0;
	//$cntBoucle++;
//}
//print_r($checked);echo "<br>";
/* table des heures de théories */
$requete = "SELECT * FROM $tableTheorie where annee=$anneeCalc";
$resultat =  mysqli_query($connexionDB,$requete);
$theories = array();
while ($ligne = mysqli_fetch_assoc($resultat) ) {
	$theories[] = getIDCell($ligne['NoJour'],$ligne['IDProf'],$ligne['IDPeriode'],$tab_jour);
}

$errors = array();
// création des période en erreur
for($jour=1;$jour<=5;$jour++) {
	foreach ($periods as $cell => $value) {
		$periodeOK = false;
		$periodProf = array();
		foreach ($profs as $pos => $value) {
			$idCell = getIDCell($jour,$pos,$cell,$tab_jour);
			$periodProf[] = $idCell;
			if(in_array($idCell,$checked)) {
				// au moins un enseignant dans la période -> OK
				$periodeOK = true;
			}
		}
		// si periodOK false -> enregister les cellules en erreur
		if(!$periodeOK) {
			//echo "Période $cell erreur<br>";
			foreach ($periodProf as $cnt => $value) {
				$errors[] = $periodProf[$cnt];
			}
		}
	}
}
?>
<br>
<FORM id="myForm" ACTION="horaire.php"  METHOD="POST">
<input type='hidden' name='noSemaine' value=''>
<input type='hidden' name='anneeCalc' value=''>
<div class="post">
<table border='0' width='100%'><tr>
<?php
/* calcul du lundi */
//$anneeCalc = date('Y');
$dateCalc=mktime(0,0,0,1,4,$anneeCalc);
$jour_semaine=date("N",$dateCalc);
$lundi=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaine-1);
$vendredi = $lundi + 86400*4;
//$lundi=$dateCalc*($jour_semaine-1)+604800*($noSemaine-1);
$lundiTxt =  "Semaine du lundi " . date('d.m', $lundi) . " au vendredi ".date('d.m.Y', $vendredi) . "";
if(!empty($msgLock)) {
	$lundiTxt = "<font color='red'><b>$msgLock</b></font>";
}
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

if(empty($actionModifier)) {
	echo "<td align='left'><h2><img src='/iconsFam/resultset_previous.png' onClick='submitSemaineAnnee(".$noSemMoinsUn.",".$anneeMoinsUn.")'>Semaine ".$noSemaine;
	//if($vendredi<mktime(0, 0, 0, date('m'), date('d'), date('y'))) {
		echo "<img src='/iconsFam/resultset_next.png' onClick='submitSemaineAnnee(".$noSemPlusUn.",".$anneePlusUn.")'>";
	//}
	echo "</h2></td>";
	//echo "<td><h2>Horaire semaine no <input type='texte' name='noSemaine' value='$noSemaine' size='2'><input type='submit' name='Afficher' value='Afficher'> </h2></td><td>$lundiTxt</td>";
	if($cntBoucle>1) {
		echo "<td align='right' onClick='submitSemaineAnnee(0,".$anneeCalc.")'><i><font color='green'>Horaire semaine $noSemaine par défaut</font></i></td>";
	} else {
		echo "<td align='right'><i><font color='red'>Horaire semaine $noSemaine personnalisé</font></i></td>";
	}
} else {
	echo "<td><h2>Semaine $noSemaine</h2></td>";
}

$lundiTxt =  "Semaine du lundi " . date('d.m', $lundi) . " au vendredi ".date('d.m.Y', $vendredi) . "";
echo "</tr><tr><td align='left'>".$lundiTxt."</td><td></td></tr>";
?>
</table><br>
<br><div id='corners'>
<div id='legend'>Horaires</div>
<table id="hor-minimalist-c" align='center'><tr>
<th></th><th></th>
<?php

/* liste périodes */
$precedent=0;
foreach ($periods as $per => $value) {
	if($precedent!=0) {
		echo "<th align='center'>$precedent<br>à<br>$value</th>";
	}
	$precedent = $value;
}
echo "<th align='center'>Total<br>jour<br>min.</th></tr>";

$strGoogle = "";
for($jour=1;$jour<=5;$jour++) {
	echo "<tr><td rowspan='$cntProfs' width='80'><b>$tab_jour[$jour]</b></td>";
	$strGoogle .= $tab_jour[$jour]."%3A%20";
	$jourTab = array();
	foreach ($profs as $pos => $value) {
            $time = 0;
	    echo "<td width='50'>$profs[$pos]</td>";
	    foreach ($periods as $cell => $value) {
		if(end($periods)!=$value) {
			$idCell = getIDCell($jour,$pos,$cell,$tab_jour);
			echo "<td align='center'><input type='checkbox' id='$idCell' name='$idCell' onClick='setBGColorFillRight(\"$idCell\")' onkeydown='switchAll()' onkeyup='switchOne()'";
			if(in_array($idCell,$checked)) {
				echo " checked";
				// incrémenter compteur prof
				$time = $time + $dureePeriode[$cell];
				if($cell==1) {
					$jourTab[0] = "Matin%20".utf8_encode($profs[$pos]);
				}
				if($cell==15) {
					$jourTab[1] = "%20-%20Soir%20".utf8_encode($profs[$pos]);
				}
			}
			echo ">";
			echo "<script>";
			if((empty($actionModifier) || in_array($idCell,$theories)) ) {// || strpos($_SESSION['user_nom'], $profs[$pos])==false) {
				echo "document.getElementById(\"$idCell\").style.display = 'none';";
			}
			if(in_array($idCell,$theories)) {
				echo "setBGColor(\"$idCell\",\"#FF9933\");";
				// incrémenter compteur prof si théorie
				$time = $time + $dureePeriode[$cell];
			} else if(in_array($idCell,$errors)) {
				echo "setBGColor(\"$idCell\",\"#CC3333\");";
			} else {
				echo "setCheckBGColor(\"$idCell\");";
			}
			echo "</script></td>\n";
		}
	    }
	    $strTime = floor($time/60).'h'.sprintf("%02d",($time%60));
	    echo "<td align='center'><b>$strTime</b></td></tr>\n<tr>";
			if(!isset($timeProfs[$pos])) {
					$timeProfs[$pos]=0;
			}
	    $timeProfs[$pos] = $timeProfs[$pos] + $time;
	}
	$colspantr = $cntPeriode+2;
	echo "</tr>\n<tr><td colspan='$colspantr' bgcolor='#5C5C5C'></td></tr>";
	if(isset($jourTab[0])) {
		$strGoogle .= $jourTab[0];
	}
	if(isset($jourTab[1])) {
		$strGoogle .= $jourTab[1];
	}
	$strGoogle .= "%0A";
}

echo $pageOut;
?>
</table><div><br>
<?php
// construction du récapitulatif par prof
$span = $cntProfs+1;
echo "<table border='0' width='100%'><tr><td rowspan='$span' width='850' valign='top'>";

// boutons modifier, valider, annuler
if(isMAIAEL()) {
	if(empty($actionModifier)) {
		echo "<input type='submit' name='Modifier' value='Modifier'>";
		echo "<br><a href='http://www.google.com/calendar/event?action=TEMPLATE&text=Horaire%20atelier&dates=".date('Ymd', $lundi)."/".date('Ymd', $vendredi+86400)."&details=".$strGoogle."&location=&trp=false&sprop=&sprop=name:' target='_blank'><img src='http://www.google.com/calendar/images/ext/gc_button1_fr.gif' border=0></a>";
	} else {
		echo "<b>Astuce: </b>Sélectionner/désélectionner toute la ligne restante en cliquant sur la case à cocher en maintenant la touche Ctrl.<br><br>";
		echo "<input type='submit' name='Valider' value='Valider'> ";
		echo "<input type='submit' name='Annuler' value='Annuler'>";
	}
}

echo "</td><td colspan='4'><b>Total de la semaine (minimum):</b></td></tr>";
foreach ($profs as $pos => $value) {
	// soustraire temps de théorie
	$strTime = floor($timeProfs[$pos]/60).'h'.sprintf("%02d",($timeProfs[$pos]%60));
	echo "<tr><td>$profs[$pos]:</td><td align='right'>$strTime - ";
	$strTime = floor($theorieProfs[$pos]/60).'h'.sprintf("%02d",($theorieProfs[$pos]%60));
	echo "$strTime</td>";
	$time = $timeProfs[$pos] - $theorieProfs[$pos];
	$strTime = floor($time/60).'h'.sprintf("%02d",($time%60));
	echo "<td align='center'>=</td><td align='right'><b>$strTime</b></td></tr>";
}
echo "</table>";


?>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
