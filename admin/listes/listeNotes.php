<?php 
include("../../appHeader.php");

$anneeTri = '%';
if(isset($_POST['annee'])) {
	$anneeTri = $_POST['annee'];
}
$classeTri = $app_section.'%';
if(isset($_POST['classe']) && $_POST['classe']!=0) {
	$classeTri = $configurationNTE[$_POST['classe']];
}

$affichage = 0;
if(isset($_POST['affichage'])) {
	$affichage = $_POST['affichage'];
}

// nouvelle version d'action sur les notes
if(isset($_POST['actionNote']) && !empty($_POST['actionNote'])) {
	$noteAuto = 0;
	$annee = $_POST['anneeNote'];
	$semestre = $_POST['semestreNote'];
		
	// création d'une note fictive pour nouvelle année ou nouveau thème (IDTypeNote=0)
	if(isset($_POST['themeNote'])&& !empty($_POST['themeNote'])) {
		// utilisation du nouveau thème
		$theme = $_POST['themeNote'];
		// activation d'ajout de notes automatique
		$noteAuto=2;
	} else {
		// pour nouvelle année, thème fictif à 1
		$theme = 1;
	}
	if(isset($_POST['PonderationTheme'.$annee.$semestre])&&!empty($_POST['PonderationTheme'.$annee.$semestre])) {
		$ponderation = $_POST['PonderationTheme'.$annee.$semestre];
	} else {
		$ponderation = 0;
	}
	
	// recherche des élève concernés
	$requete = "SELECT ele.IDGDN FROM elevesbk ele join eleves els on ele.IDGDN=els.IDGDN where Classe like '".$classeTri."' and IDEntreprise=1";
	$resultat =  mysql_query($requete);
	if($modeEvaluation=="theme") {
		// ajout d'une ligne pour la période/thème vide
		
		while ($ligne = mysql_fetch_assoc($resultat)) {
			// test si existant
			$exist = mysql_query("select 1 from notes where IDEleve=".$ligne['IDGDN']." and NoSemestre=".$semestre." and Annee=".$annee." and IDTypeNote=0 and IDTheme=".$theme."");
			if (!mysql_fetch_row($exist)) {
				$requeteIns = "INSERT INTO notes (IDEleve, NoSemestre, Annee, IDTypeNote, IDTheme, Ponderation) values (".$ligne['IDGDN'].", ".$semestre.", ".$annee.", 0, ".$theme.", ".$ponderation.")";
				//echo $requeteIns."<br>";
				mysql_query($requeteIns);
			}
		}
	} else {
		// mode sans theme - > création automatique des notes pour le theme 10
		$theme = 10;
		$noteAuto=2;
	}
	// ajout automatique des notes
	if($noteAuto!=0) {
		$compToAdd = $competencesEvaluation;
		if(!empty($lastAnneeEvalOld) && !empty($lastSemestreEvalOld)) {
			if($annee<$lastAnneeEvalOld || ($annee==$lastAnneeEvalOld && $semestre <= $lastSemestreEvalOld)) {
				$compToAdd = $competencesEvaluationOld;
			}
		}
		mysql_data_seek($resultat, 0);
		while ($ligne = mysql_fetch_assoc($resultat)) {
			foreach ($compToAdd as $key => $value) {
				// ajout de la note vide en DB
				$exist = mysql_query("select 1 from notes where IDEleve=".$ligne['IDGDN']." and NoSemestre=".$semestre." and Annee=".$annee." and IDTypeNote=".($key+1)." and IDTheme=".$theme."");
				if (!mysql_fetch_row($exist)) {
					$requeteIns = "INSERT INTO notes (IDEleve, NoSemestre, Annee, IDTypeNote, IDTheme, Ponderation) values (".$ligne['IDGDN'].", ".$semestre.", ".$annee.", ".($key+1).", ".$theme.", 0)";
					//echo $requeteIns."<br>";
					mysql_query($requeteIns);
				}
			}
		}
	}
}
	
	
	
	
include("entete.php");
?>

<div id="page">
<script>
function submitNewGen(annee, semestre) {
	//alert(annee);
	document.getElementById('myForm').actionNote.value='new';
	document.getElementById('myForm').anneeNote.value=annee;
	document.getElementById('myForm').semestreNote.value=semestre;
	document.getElementById('myForm').submit();
}
function submitNewThe(annee, semestre, theme) {
	document.getElementById('myForm').actionNote.value='new';
	document.getElementById('myForm').anneeNote.value=annee;
	document.getElementById('myForm').semestreNote.value=semestre;
	document.getElementById('myForm').themeNote.value=theme;
	document.getElementById('myForm').submit();
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
</script>
<?
include($app_section."/userInfo.php");
/* en-tête */

echo "<FORM id='myForm' ACTION='listeNotes.php'  METHOD='POST'>";
// transfert info
//echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
//echo "<input type='hidden' name='nom' value='$nom'>";
//echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<input type='hidden' name='actionNote' value=''>";
echo "<input type='hidden' name='anneeNote' value=''>";
echo "<input type='hidden' name='semestreNote' value=''>";
echo "<input type='hidden' name='themeNote' value=''>";
//echo "<input type='hidden' name='typeNote' value=''>";
//echo "<input type='hidden' name='idNote' value=''>";
//echo "<input type='hidden' name='idBlock' value=''>";

echo "<div class='post'>";
echo "<center> <font color='#088A08'></font></center>";
//echo "<br><h2>".$nom." ".$prenom."</h2><br>\n";





// construction des 5 dernières années
/* année en cours */
$anneeEncours = date('Y');
$optionAnnee = "";
for($cntA=0;$cntA<5;$cntA++) {
	$optionAnnee .= "<option value='".($anneeEncours-$cntA)."'";
	if(($anneeEncours-$cntA)==$anneeTri) {
		$optionAnnee .= " selected ";	
	}
	$optionAnnee .= ">".($anneeEncours-$cntA)."/".($anneeEncours-$cntA+1)."</option>";
}

// construction de la liste des classes
$optionsClasses = "";	
foreach ($configurationNTE as $pos => $value) {
	if(!empty($_POST['classe']) && $_POST['classe']==$pos) {
		$optionsClasses .= "<option value='$pos' selected='selected'>$value</option>";
	} else {
		$optionsClasses .= "<option value='$pos'>$value</option>";
	}
}

// construction de la liste déroulante pour ajout Theme
$requete = "SELECT IDTheme, NomTheme, TypeTheme FROM theme where (TypeTheme=0 and ClasseTheme like '".$classeTri."') order by NomTheme";
//echo $requete;
$resultat =  mysql_query($requete);
$optionThemes = "";
while ($ligne = mysql_fetch_assoc($resultat)) {
	$optionThemes .= "<option value=".$ligne['IDTheme'].">".$ligne['NomTheme']."</option>";
}


//echo "affichage: ".$affichage;
echo "<table border='0' width='1020'><tr><td><!-- h3>Notes:</h3 --></td><td align='right'>Mode d'affichage ";
echo "<select name='affichage' onChange='submit();'><option value='0'>Résumé</option>";
if($affichage==1) {
	echo "<option value='1' selected='selected'>Détaillé</option>";
} else {
	echo "<option value='1'>Détaillé</option>";
}
echo "</select>";
echo "<select id='classe' name='classe' onChange='submit();'><option value='0'>Tous</option>".$optionsClasses."</select>";
echo "<select name='annee' onchange='submit();'><option value='%'>Tous</option>".$optionAnnee."</select>";
echo " <img src='/iconsFam/calendar_add.png' onmouseover=\"Tip('Ajouter un semestre')\" onmouseout='UnTip()' onclick='toggle(\"newSemestre\");' align='absmiddle'>";
if($modeEvaluation=="theme" && !empty($_POST['classe']) && !empty($optionThemes)) {
	echo " <img src='/iconsFam/folder_add.png' onmouseover=\"Tip('Ajouter un thème pour la classe')\" onmouseout='UnTip()' onclick='toggle(\"newTheme\");' align='absmiddle'>";
}
echo "</tr></table>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Notes</div>";
echo "<table id='hor-minimalist-b' border='0' width='100%'>\n";

// ligne d'ajout d'un semestre
echo "<tr newSemestre='1' style='display:none'><td colspan='9'>Ajouter un nouveau semestre pour ";
if(empty($_POST['classe'])) {
	echo "tous";
} else {
	echo "la classe ".$classeTri;
}
echo ": <select name='anneeNew'>".$optionAnnee."</select>\n";
echo "<select name='semestreNew'><option value='1'>1er semestre</option><option value='2'>2ème semestre</option></select>\n";
echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitNewGen(document.getElementsByName(\"anneeNew\")[0].value,document.getElementsByName(\"semestreNew\")[0].value)'>";
echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Annuler')\" onmouseout='UnTip()' onclick='toggle(\"newSemestre\")'></td>";
echo "</tr>";

// ligne d'ajout d'un thème
echo "<tr newTheme='1' style='display:none'><td colspan='9'>Ajouter un nouveau thème pour ";
echo "la classe ".$classeTri;
echo ": <select name='anneeNewTh'>".$optionAnnee."</select>\n";
echo "<select name='semestreNewTh'><option value='1'>1er semestre</option><option value='2'>2ème semestre</option></select>\n";
echo "<select name='themeNew'>".$optionThemes."</select>\n";
echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitNewThe(document.getElementsByName(\"anneeNewTh\")[0].value,document.getElementsByName(\"semestreNewTh\")[0].value,document.getElementsByName(\"themeNew\")[0].value)'>";
echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Annuler')\" onmouseout='UnTip()' onclick='toggle(\"newSemestre\")'></td>";
echo "</tr>";

// recherche des notes
$requete = "SELECT * FROM elevesbk ele join eleves els on ele.IDGDN=els.IDGDN join notes no on ele.IDGDN=no.IDEleve join theme th on
no.IDTheme=th.IDTheme where annee like '".$anneeTri."' and Classe like '".$classeTri."' and IDEntreprise=1 order by Classe desc, Nom, Prenom, annee desc, nosemestre, nomtheme, no.IDTypeNote, no.IDNote";
//echo $requete;
$resultat =  mysql_query($requete);
$cnt=0;
$cntNoteTheme = 0;
$cntTEVide = 0;
$annee = 0;
$semestre = 0; 
$theme = 0;
$nomTheme = '';
$pondTheme = 0;
$typeTheme = 0;
$te = 0;
$cntTe = 0;
$moyenneTE = 0;

$notesComp = array();
$notesComp = array_fill(1, 4, 0);
//$moyenneTech = 0;
//$appl=0;
//$rdmnt=0;
//$saet=0;
$moyennesComp = array();
$moyennesComp = array_fill(1, 4, 0);
//$moyenneSemestreT = 0;
//$moyenneSemestreA = 0;
//$moyenneSemestreR = 0;
//$moyenneSemestreS = 0;
$cntNotesComp = array();
$cntNotesComp = array_fill(1, 4, 0);
//$cntNoteT = 0;
//$cntNoteA = 0;
//$cntNoteR = 0;
//$cntNoteS = 0;
$libellePeriode = "";
$remGen = "";
$idLastEleve = "";

function clotureTE() {
	global $te, $cntTe, $moyenneTE;
	//termine la ligne de TE et calcule la moyenne
	if($te!=0) {
		// calcule de la moyenne TE
		$moyenneTE = sprintf("%01.1f",round($te/$cntTe,1));
		
		$te = 0;
		$cntTe = 0;
	}
}

function clotureTheme() {
	global $theme, $nomTheme, $pondTheme, $notesComp, $cntNoteTheme, $cntNotesComp, $cntTEVide, $affichage, $typeTheme, $compPond, $compAbbr,$modeEvaluation;
	
	if($theme!=0 && $theme>=10) {	// ne pas afficher les lignes fictives
		if($affichage==1) {
			$colorLine = "#C0C0C0";
			if($cntNoteTheme<=0 && $typeTheme==0) {
				$colorLine = "#FAAC58";
			}
			//if($moyenneTech==0||$appl==0||$rdmnt==0||$saet==0||$cntTEVide!=0) {
			if(array_sum($cntNotesComp)==0||$cntTEVide!=0) {	
				$colorLine = "#FF0000";
			}
			// calcule moyenne du thème
			$moyPnd = 0;
			$cntPnd = 0;
			foreach ($compPond as $key => $value) {
				$moyPnd += $notesComp[$key] * $value;
				$cntPnd += $value;
			}
			$moyTheme = sprintf("%01.1f",($moyPnd/$cntPnd));
			//$moyTheme = sprintf("%01.1f",($moyenneTech * 3 + $appl + $rdmnt + $saet)/6);
			echo "\n<tr><td width='265'><font size='2' face='Verdana' color='".$colorLine."'>".$nomTheme."</font></td><td width='40' align='center'><b>Nbr TE</b><br>".$cntNoteTheme."</td>";
			foreach ($compAbbr as $key => $value) {
				if(empty($value)) {
					echo "<td width='40' align='center'></td>";
				} else {
					echo "<td width='40' align='center'><b>".$value."</b><br>".($notesComp[$key]==0?'-':$notesComp[$key])."</td>";
				}
			}
			//echo "<td width='40' align='center'><b>T</b><br>".($moyenneTech==0?'-':$moyenneTech)."</td>";
			//echo "<td width='40' align='center'><b>A</b><br>".($appl==0?'-':$appl)."</td>";
			//echo "<td width='40' align='center'><b>R</b><br>".($rdmnt==0?'-':$rdmnt)."</td>";
			//echo "<td width='40' align='center'><b>S</b><br>".($saet==0?'-':$saet)."</td>";
			echo "<td width='40' align='center'>";
			if($modeEvaluation=="theme") {
				echo "<i><b>M</b><br>".($moyTheme==0?'-':$moyTheme)."</i>";
			}
			//echo "<i><b>M</b><br>".($moyTheme==0?'-':$moyTheme)."</i>";
			echo "</td>";
			echo "<td colspan='1' width='500' align='right'></td></tr>";
		}
		$notesComp = array_fill(1, 4, 0);
		//$appl=0;$rdmnt=0;$saet=0;$moyenneTech=0;
		//$cntTEVide = 0;
		$theme=0;
	}
} 

function clotureSemestre() {
	global $libellePeriode, $remGen, $compAbbr, $cntNotesComp, $moyennesComp, $compEval, $compPond;
	//if(!empty($libellePeriode) && ($cntNoteT!=0 || $cntNoteA!=0 || $cntNoteR!=0 || $cntNoteS!=0) ) {
	if(!empty($libellePeriode)) {
		// avant prochain bloc, calculer la moyenne du semestre précédant
		//echo "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";

		$texteRes = "";
		$colorLine = "#5C5C5C";
		$cntNoteTot = 0;
		foreach ($compAbbr as $key => $value) {
			if($cntNotesComp[$key]!=0) {
				$moyennesComp[$key] = sprintf("%01.1f",round($moyennesComp[$key]/$cntNotesComp[$key],1));
				$texteRes .= "<td width='40' align='center'><b>".$value."</b><br>".$moyennesComp[$key]."</td>";
				$cntNoteTot++; 
			} else {
				if(empty($value)) {
					$texteRes .= "<td width='40' align='center'></td>";
				} else {
					$texteRes .= "<td width='40' align='center'><b>".$value."</b><br>-</td>";
					$colorLine = "#FF0000";
				}
				
			}
		}
		/*
		if($cntNoteT!=0) {
			$moyenneSemestreT = sprintf("%01.1f",round($moyenneSemestreT/$cntNoteT,1));
			$texteRes .= "<td width='40' align='center'><b>T</b><br>".$moyenneSemestreT."</td>";
		} else {
			$texteRes .= "<td width='40' align='center'><b>T</b><br>-</td>";
			$colorLine = "#FF0000";
		}
		if($cntNoteA!=0) {
			$moyenneSemestreA = sprintf("%01.1f",round($moyenneSemestreA/$cntNoteA,1));
			$texteRes .= "<td width='40' align='center'><b>A</b><br>".$moyenneSemestreA."</td>";
		} else {
			$texteRes .= "<td width='40' align='center'><b>A</b><br>-</td>";
			$colorLine = "#FF0000";
		}
		if($cntNoteR!=0) {	
			$moyenneSemestreR = sprintf("%01.1f",round($moyenneSemestreR/$cntNoteR,1));
			$texteRes .= "<td width='40' align='center'><b>R</b><br>".$moyenneSemestreR."</td>";
		} else {
			$texteRes .= "<td width='40' align='center'><b>R</b><br>-</td>";
			$colorLine = "#FF0000";
		}
		if($cntNoteS!=0) {
			$moyenneSemestreS = sprintf("%01.1f",round($moyenneSemestreS/$cntNoteS,1));
			$texteRes .= "<td width='40' align='center'><b>S</b><br>".$moyenneSemestreS."</td>";
		} else {
			$texteRes .= "<td width='40' align='center'><b>S</b><br>-</td>";
			$colorLine = "#FF0000";
		}
		*/
		echo "<tr><td width='265'><font color='".$colorLine."'>".$libellePeriode."</font></td><td width='40'></td>".$texteRes;
		if($cntNoteTot==count($compEval)) {
			// calcule moyenne du semestre
			$moyPnd = 0;
			$cntPnd = 0;
			foreach ($compPond as $key => $value) {
				$moyPnd += $moyennesComp[$key] * $value;
				$cntPnd += $value;
			}
			$moyenneSemestre = $moyPnd/$cntPnd;
		//if($cntNoteT!=0 && $cntNoteA!=0 && $cntNoteR!=0 && $cntNoteS!=0 ) {
			//$moyenneSemestre = ($moyenneSemestreT * 3 + $moyenneSemestreA + $moyenneSemestreR + $moyenneSemestreS)/6;
			// $moyenneSemDix = sprintf("%01.1f",$moyenneSemestre);
			$moyenneSemDix = sprintf("%01.1f",round($moyenneSemestre,1));
			// $moyenneSemDix = round($moyenneSemestre*2)/2;
			$moyenneSemCent = sprintf("%01.2f",round($moyenneSemestre,2));
			// $moyenneSemCent = sprintf("%01.2f",$moyenneSemestre);
			
			echo "<td width='40' align='center' onmouseover=\"Tip('Moyenne bulletin au centième: ".$moyenneSemCent."')\" onmouseout='UnTip()'><b>B<br>".$moyenneSemDix."</b></td>";			
		} else {
			echo "<td>&nbsp;</td>";
		}
		// fin de la ligne
		// afficher la remarque générale si présente
		if(!empty($remGen)) {
			echo "<td colspan='1'>".$remGen."</td></tr>";
			$remGen = "";
		} else {
			echo "<td colspan='1'>&nbsp;</td></tr>";
		}
/*
		if($cntNoteT!=0 && $cntNoteA!=0 && $cntNoteR!=0 && $cntNoteS!=0 ) {
			$moyenneSemestre = ($moyenneSemestreT * 3 + $moyenneSemestreA + $moyenneSemestreR + $moyenneSemestreS)/6;
			$moyenneSemCent = sprintf("%01.2f",$moyenneSemestre);
			//if($moyenneSemestre!=0) {
				//echo "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";
				echo "<tr><td colspan='3' width='305'><font size='2' face='Verdana'><b>Moyenne bulletin</b></font></td><td align='center' width='40'><font size='2' face='Verdana'><b>".(round($moyenneSemestre*2)/2)."</b></font></td><td colspan='5'><font size='2' face='Verdana'>(".$moyenneSemCent.")</font></td></tr>";			//}
		}
*/
		// remise à zéro
		// remise à zéro
		$moyennesComp = array_fill(1, 4, 0);
		$cntNotesComp = array_fill(1, 4, 0);
		/*
		$moyenneSemestreT = 0;
		$cntNoteT = 0;
		$moyenneSemestreA = 0;
		$cntNoteA = 0;
		$moyenneSemestreR = 0;
		$cntNoteR = 0;
		$moyenneSemestreS = 0;
		$cntNoteS = 0;	
		*/
		$libellePeriode = "";	
	}
		
		
	
	
}

while ($ligne = mysql_fetch_assoc($resultat)) {
	
	if($ligne['Annee'] != $annee || $ligne['NoSemestre'] != $semestre || $ligne['IDEleve'] != $idLastEleve) {
		// nouvelle année
		// clôture de la ligne des TE si nécessaire
		clotureTE();
		// clôture du théme précédent si nécessaire
		clotureTheme();
		// clôturer la période avec le calcul de la moyenne et l'éventuelle remarque sur la période
		clotureSemestre();
		
		if($annee!=0 && ($ligne['Annee'] != $annee || $ligne['IDEleve'] != $idLastEleve)) {
			echo "\n<tr><td colspan='9' valign='bottom' valign='bottom' bgColor='#DEDEDE'></td></tr>";	
		}
		$annee = $ligne['Annee'];
		$semestre = $ligne['NoSemestre'];
		// recherche système d'évaluation en fonction du semestre et de l'année
		$compEval = $competencesEvaluation;
		$compAbbr = $competencesAbbr;
		$compPond = $competencesPonderation;
		if(!empty($lastAnneeEvalOld) && !empty($lastSemestreEvalOld)) {
			if($annee<$lastAnneeEvalOld || ($annee==$lastAnneeEvalOld && $semestre <= $lastSemestreEvalOld)) {
				$compEval = $competencesEvaluationOld;
				$compAbbr = $competencesAbbrOld;
				$compPond = $competencesPonderationOld;
			}
		}
		
		switch($ligne['NoSemestre']) {
			case 1: $libellePeriode = "<i><b>".$annee."/".($annee+1)."</b><br> 1er semestre</i>";
					break;
			case 2: $libellePeriode = "<i><b>".$annee."/".($annee+1)."</b><br> 2ème semestre</i>";
					break;
		}
		
		if($ligne['IDEleve'] != $idLastEleve) {
			$idLastEleve = $ligne['IDEleve'];
			// ligne d'espacement
			echo "\n<tr><td colspan='9' valign='bottom' height='20'></td></tr>";
			echo "<tr><td colspan='9' bgColor='#DEDEDE'><font size='2' face='Verdana'><b>".$ligne['Nom']." ".$ligne['Prenom']." (".$ligne['Classe'].")</b> <a href='../detail/notesEleve.php?nom=".$ligne['Nom']."&prenom=".$ligne['Prenom']."&idEleve=".$ligne['IDEleve']."' onmouseover=\"Tip('Vers ajout/modification des notes')\" onmouseout='UnTip()'></font><img src='/iconsFam/external.png'></a></td></tr>";
		}
	}
	
	if($ligne['IDTheme']>=10 && $ligne['IDTheme']!=$theme) {
		// clôture de la ligne des TE si nécessaire
		clotureTE();
		// clôture du théme précédent si nécessaire
		clotureTheme();
		// nouveau thème pour la même période
		$cntNoteTheme = 0;
		$moyenneTE = 0;

		$theme = $ligne['IDTheme'];
		$nomTheme = $ligne['NomTheme'];
		$pondTheme = $ligne['Ponderation'];
		$typeTheme = $ligne['TypeTheme'];
		if($pondTheme==0) {
			$pondTheme = $ligne['PonderationTheme'];
		}
		

		
	}
	if($ligne['IDTypeNote'] > 1) {
		// clôture de la ligne des TE si nécessaire
		clotureTE();
	}
	$idNote = $ligne['IDNote'];
	switch($ligne['IDTypeNote']) {
		case 1: 
			// TE
			if(!empty($ligne['Note'])) {
				$te += $ligne['Note'] * $ligne['Ponderation'];
				$cntTe += $ligne['Ponderation'];
			} else {
				$cntTEVide++;
			}
			$cntNoteTheme++;
			break;
		case 2:
			// Technique
			
			// calcul et affichage de la moyenne de technique
			if(!empty($ligne['Note'])) {
				if($moyenneTE!=0) {
					//$moyenneTech = sprintf("%01.1f",round(($ligne['Note']*2+$moyenneTE)/3,1));	
					$notesComp[$ligne['IDTypeNote']-1] = sprintf("%01.1f",round(($ligne['Note']*$moyenneCompPonderation+$moyenneTE)/($moyenneCompPonderation+1),1));
				} else {
					//$moyenneTech = $ligne['Note'];
					$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				}
				$moyenneTE = 0;
				//$moyenneSemestreT += $moyenneTech * $pondTheme;
				//$cntNoteT += $pondTheme;
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				//$cntNoteTheme++;
			}
			break;
		case 3:
			// Application 	
			if(!empty($ligne['Note'])) {
				//$appl = $ligne['Note'];
				//$moyenneSemestreA += $appl * $pondTheme;
				//$cntNoteA += $pondTheme;
				$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				//$cntNoteTheme++;
			}
			break;	
		case 4:
			// Rendement
			if(!empty($ligne['Note'])) {
				//$rdmnt = $ligne['Note'];
				//$moyenneSemestreR += $rdmnt * $pondTheme;
				//$cntNoteR += $pondTheme;
				$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				//$cntNoteTheme++;
			}
			break;
		case 5:
			// Savoir-être 	
			if(!empty($ligne['Note'])) {
				//$saet = $ligne['Note'];
				//$moyenneSemestreS += $saet * $pondTheme;
				//$cntNoteS += $pondTheme;
				$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				//$cntNoteTheme++;
			}
			break;	
		case 6:
			// Remarque générale
			$remGen = $ligne['RemarqueNote'];	
			break;
		case 0:
			// ligne vide pour ajout theme
			break;	
		default:
			// inconnu
			echo "<tr block$annee$semestre$theme='1' ><td width='15'></td><td width='250'>Inconnu</td><td width='40'></td><td width='40'><b>".$ligne['Note']."</b></td><td colspan='4' width='620'>".$ligne['RemarqueNote']."</td><td width='40'></td></tr>";
	}
	$cnt++;
}
// clôture de la ligne des TE si nécessaire
clotureTE();
// clôture du théme précédent si nécessaire
clotureTheme();
// clôturer la période avec le calcul de la moyenne et l'éventuelle remarque sur la période
clotureSemestre();

// aucun enregistrement: afficher la situation
if ($cnt==0) {
	echo "<tr><td width='1020' valign='bottom' bgColor='#DEDEDE'></td></tr><tr><td width='1020' align='center'><i>Aucun enregistrement</i></td></tr>";
} 
// ligne d'ajout



$optionAnnee = "";
$anneAct = date("Y");
for($loopAnnee=4;$loopAnnee>=0;$loopAnnee--) {
	$optionAnnee .= "<option value='".($anneAct-$loopAnnee)."'>".($anneAct-$loopAnnee)."/".($anneAct-$loopAnnee+1)."</option>";
}
/*
echo "<tr><td colspan='12' height='30'></td></tr><tr><td colspan='12' valign='bottom'><b>Nouvelle note pour (année, no semestre):<b></td></tr>";
echo "<td valign='top' colspan='12'><select name='Annee'>".$optionAnnee."</select><select name='NoSemestre'><option value='1'>1er timester</option><option value='2'>1er semestre</option><option value='3'>3ème trimestre</option><option value='4'>2ème semestre</option></select></td></tr>";
echo "<tr><td></td><td>Type de note</td><td colspan='3'>Note</td><td colspan='8'>Remarque</td></tr>";
echo "<tr><td></td><td valign='top'><select name='IDTypeNote'>".$option."</select></td>";
echo "<td valign='top' colspan='3'><input name='Note' size='4' maxlength='3' value=''></input></td>";
echo "<td valign='top' colspan='8'><textarea name='RemarqueNote' COLS=60 ROWS=2></textarea></td> </tr>";
echo "</table><input type='submit' name='ajoutNote' value='Ajouter'></input>";
*/

?>
</table></div><br>
<?
if(isset($_POST['idBlock']) && !empty($_POST['idBlock'])) {
	// ouvrir le thème précédemment ouvert
	echo "<script>toggle(\"block".$_POST['idBlock']."\");</script>";
	//echo "<script>alert(\"hello\");</script>";
}
?>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>

