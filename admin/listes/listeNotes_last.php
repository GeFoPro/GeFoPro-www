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
include("entete.php");
?>

<div id="page">
<?
include($app_section."/userInfo.php");
/* en-tête */

echo "<FORM id='myForm' ACTION='listeNotes.php'  METHOD='POST'>";
// transfert info
//echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
//echo "<input type='hidden' name='nom' value='$nom'>";
//echo "<input type='hidden' name='prenom' value='$prenom'>";
//echo "<input type='hidden' name='actionNote' value=''>";
//echo "<input type='hidden' name='anneeNote' value=''>";
//echo "<input type='hidden' name='semestreNote' value=''>";
//echo "<input type='hidden' name='themeNote' value=''>";
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
echo "</tr></table>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Notes</div>";
echo "<table id='hor-minimalist-b' border='0' width='100%'>\n";

// recherche des notes
$requete = "SELECT * FROM elevesbk ele join notes no on ele.IDGDN=no.IDEleve join typenote ty on no.IDTypeNote=ty.IDTypeNote join theme th on
no.IDTheme=th.IDTheme where annee like '".$anneeTri."' and Classe like '".$classeTri."' order by Classe desc, Nom, Prenom, annee desc, nosemestre, nomtheme, no.IDTypeNote, no.IDNote";
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
$moyenneTech = 0;
$appl=0;
$rdmnt=0;
$saet=0;
$moyenneSemestreT = 0;
$moyenneSemestreA = 0;
$moyenneSemestreR = 0;
$moyenneSemestreS = 0;
$cntNoteT = 0;
$cntNoteA = 0;
$cntNoteR = 0;
$cntNoteS = 0;
$libellePeriode = "";
$remGen = "";
$idLastEleve = "";

function clotureTE() {
	global $te, $cntTe, $moyenneTE, $annee, $semestre, $theme;
	//termine la ligne de TE et calcule la moyenne
	if($te!=0) {
		// calcule de la moyenne TE
		$moyenneTE = sprintf("%01.1f",round($te/$cntTe,1));
		
		$te = 0;
		$cntTe = 0;
	}
}

function clotureTheme() {
	global $theme, $semestre, $annee, $nomTheme, $pondTheme, $moyenneTech, $appl, $rdmnt, $saet, $cntNoteTheme, $cntTEVide, $affichage, $typeTheme;
	
	if($theme!=0 && $theme>=10) {	// ne pas afficher les lignes fictives
		if($affichage==1) {
			$colorLine = "#C0C0C0";
			if($cntNoteTheme<=0 && $typeTheme==0) {
				$colorLine = "#FAAC58";
			}
			if($moyenneTech==0||$appl==0||$rdmnt==0||$saet==0||$cntTEVide!=0) {
				$colorLine = "#FF0000";
			}
			$moyTheme = sprintf("%01.1f",($moyenneTech * 3 + $appl + $rdmnt + $saet)/6);
			echo "\n<tr><td colspan='2' width='265'><font size='2' face='Verdana' color='".$colorLine."'>".$nomTheme."</font></td><td width='40' align='center'><b>Nbr TE</b><br>".$cntNoteTheme."</td><td width='40' align='center'><b>T</b><br>".($moyenneTech==0?'-':$moyenneTech)."</td><td width='40' align='center'><b>A</b><br>".($appl==0?'-':$appl)."</td><td width='40' align='center'><b>R</b><br>".($rdmnt==0?'-':$rdmnt)."</td><td width='40' align='center'><b>S</b><br>".($saet==0?'-':$saet)."</td><td width='40' align='center'><i><b>M</b><br>".($moyTheme==0?'-':$moyTheme)."</i></td><td colspan='1' width='500' align='right'></td></tr>";
		}
		$appl=0;$rdmnt=0;$saet=0;$moyenneTech=0;
		$theme=0;
	}
} 

function clotureSemestre() {
	global $libellePeriode, $moyenneSemestreT, $moyenneSemestreA, $moyenneSemestreR, $moyenneSemestreS, $cntNoteT, $cntNoteA, $cntNoteR, $cntNoteS, $semestre, $remGen, $annee;
	//if(!empty($libellePeriode) && ($cntNoteT!=0 || $cntNoteA!=0 || $cntNoteR!=0 || $cntNoteS!=0) ) {
	if(!empty($libellePeriode)) {
		// avant prochain bloc, calculer la moyenne du semestre précédant
		//echo "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";

		$texteRes = "";
		$colorLine = "#5C5C5C";
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
		echo "<tr><td colspan='3' width='305'><font color='".$colorLine."'>".$libellePeriode."</font></td>".$texteRes;
		if($cntNoteT!=0 && $cntNoteA!=0 && $cntNoteR!=0 && $cntNoteS!=0 ) {
			$moyenneSemestre = ($moyenneSemestreT * 3 + $moyenneSemestreA + $moyenneSemestreR + $moyenneSemestreS)/6;
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
		$moyenneSemestreT = 0;
		$cntNoteT = 0;
		$moyenneSemestreA = 0;
		$cntNoteA = 0;
		$moyenneSemestreR = 0;
		$cntNoteR = 0;
		$moyenneSemestreS = 0;
		$cntNoteS = 0;	
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
			echo "<tr><td colspan='9' bgColor='#DEDEDE'><font size='2' face='Verdana'><a href='../detail/notesEleve.php?nom=".$ligne['Nom']."&prenom=".$ligne['Prenom']."&idEleve=".$ligne['IDEleve']."' onmouseover=\"Tip('Vers ajout/modification des notes')\" onmouseout='UnTip()'><b>".$ligne['Nom']." ".$ligne['Prenom']." (".$ligne['Classe'].")</b></a></font></td></tr>";
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
					$moyenneTech = sprintf("%01.1f",round(($ligne['Note']*2+$moyenneTE)/3,1));	
				} else {
					$moyenneTech = $ligne['Note'];
				}
				$moyenneTE = 0;
				$moyenneSemestreT += $moyenneTech * $pondTheme;
				$cntNoteT += $pondTheme;
				//$cntNoteTheme++;
			}
			break;
		case 3:
			// Application 	
			if(!empty($ligne['Note'])) {
				$appl = $ligne['Note'];
				$moyenneSemestreA += $appl * $pondTheme;
				$cntNoteA += $pondTheme;
				//$cntNoteTheme++;
			}
			break;	
		case 4:
			// Rendement
			if(!empty($ligne['Note'])) {
				$rdmnt = $ligne['Note'];
				$moyenneSemestreR += $rdmnt * $pondTheme;
				$cntNoteR += $pondTheme;
				//$cntNoteTheme++;
			}
			break;
		case 5:
			// Savoir-être 	
			if(!empty($ligne['Note'])) {
				$saet = $ligne['Note'];
				$moyenneSemestreS += $saet * $pondTheme;
				$cntNoteS += $pondTheme;
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

