<?php
include("../../appHeader.php");
$messageErreur = "";

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

$from = "";
if(isset($_GET['from'])) {
	$from = $_GET['from'];
}
if(isset($_POST['from'])) {
	$from = $_POST['from'];
}
$IDTheme=0;
if(isset($_POST['IDTheme'])) {
	$IDTheme = $_POST['IDTheme'];
} else if(isset($_GET['IDTheme'])) {
	$IDTheme = $_GET['IDTheme'];
}

$anneeTri = '%';
if(isset($_POST['annee'])) {
	$anneeTri = $_POST['annee'];
} else if(isset($_GET['annee'])) {
	$anneeTri = $_GET['annee'];
}
//if(isset($_GET['pond'])) {
//	$pondMode = $_GET['pond'];
//	$_SESSION['pond'] = $pondMode;
//} else {
//	$pondMode = $_SESSION['pond'];
//}
if(isset($_GET['expand'])) {
	if($_GET['expand']=="expand") {
		$displayExpand = "";
		$displayDefault = "none";
		$_SESSION['expand'] = "expand";
	} else {
		$displayExpand = "none";
		$displayDefault = "";
		$_SESSION['expand'] = "normal";
	}
} else {
	if(isset($_SESSION['expand'])) {
		if($_SESSION['expand']=="expand") {
			$displayExpand = "";
			$displayDefault = "none";
		} else {
			$displayExpand = "none";
			$displayDefault = "";
		}
	} else {
		$displayExpand = "none";
		$displayDefault = "";
	}
}

// nouvelle version d'action sur les notes
if(isset($_POST['actionNote']) && !empty($_POST['actionNote'])) {
	$noteAuto = 0;
	if($_POST['actionNote']=='update') {
		// maj: récupération de l'id et des attributs
		$IDNote = $_POST['idNote'];
		$remarque = $_POST['RemarqueNote'.$IDNote];
		if(!empty($_POST['Note'.$IDNote])) {
			$note = $_POST['Note'.$IDNote];
			$ponderation = $_POST['Ponderation'.$IDNote];
			if(empty($note)) {
				$note = 'NULL';
			}
			// maj de la note en DB
	    		$requete = "UPDATE notes set Note=".$note.", RemarqueNote=\"".$remarque."\", Ponderation=".$ponderation." where IDNote=".$IDNote;
		} else {
			// modification de la remarque générale
			$requete = "UPDATE notes set RemarqueNote=\"".$remarque."\" where IDNote=".$IDNote;
		}
    		//echo $requete;

	} else if($_POST['actionNote']=='delete') {
		// effacer la note
		$IDNote = $_POST['idNote'];
		$requete = "DELETE FROM notes where IDNote=$IDNote";
	} else if($_POST['actionNote']=='deleteTheme') {
		// effacer les notes du thème
		$annee = $_POST['anneeNote'];
		$semestre = $_POST['semestreNote'];
		$theme = $_POST['themeNote'];
		$requete = "DELETE FROM notes where IDTheme=".$theme." and Annee=".$annee." and Nosemestre=".$semestre." and IDEleve=".$IDEleve;
		//echo $requete;
	} else if($_POST['actionNote']=='deletePeriode') {
		// effacer les notes du thème
		$annee = $_POST['anneeNote'];
		$semestre = $_POST['semestreNote'];
		$requete = "DELETE FROM notes where Annee=".$annee." and Nosemestre=".$semestre." and IDEleve=".$IDEleve;
		//echo $requete;
	} else if($_POST['actionNote']=='updatePond') {
		$annee = $_POST['anneeNote'];
		$semestre = $_POST['semestreNote'];
		$theme = $_POST['themeNote'];
		$updPond = $_POST['NewPond'.$annee.$semestre.$theme];
		//echo 'NewPond'.$annee.$semestre.$theme;
		//print_r($_POST);
		$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$theme." and IDTypeNote=0 and Annee=".$annee." and NoSemestre=".$semestre;
		$resultat =  mysql_query($requete);
		if(mysql_num_rows($resultat)==0) {
			// l'entrée avec IDTypeNote=0 n'existe pas pour stocker la pondération, on l'ajoute
			$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (NULL, ".$IDEleve.", ".$theme.", 0, ".$semestre.", ".$annee.", '', ".$updPond.")";
		} else {
			// mise à jour de la pondération pour le thème (ligne avec IDTypeNote =0)
			$requete = "update notes set ponderation=".$updPond." where IDEleve=".$IDEleve." and IDTheme=".$theme." and IDTypeNote=0 and NoSemestre=".$semestre." and Annee=".$annee;
		}
		//echo $requete;
	} else {
		// ajout: récupération des données de base et construction de l'identifiant unique
		$annee = $_POST['anneeNote'];
		$semestre = $_POST['semestreNote'];
		$theme = $_POST['themeNote'];
		if(empty($theme)) {
			// création de théme ou nouvelle remarque
			$idTypeNote = $_POST['typeNote'];
			if($idTypeNote == 0) {
				// création d'une note fictive pour nouvelle année ou nouveau thème (IDTypeNote=0)
				if(isset($_POST['IDTheme'.$annee.$semestre])) {
					// utilisation du nouveau thème
					$theme = $_POST['IDTheme'.$annee.$semestre];
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
				$remarque = '';
			} else if($idTypeNote == 6) {
				// création de remarque
				$theme = 1; // ligne pour ajout remarque
				$ponderation = 0;
				$remarque = $_POST['RemarqueNote'.$annee.$semestre];
			}
		} else {
			$idChamp = $annee.$semestre.$theme;
			// récupération des valeurs
			$idTypeNote = $_POST['IDTypeNote'.$idChamp];
			$note = $_POST['Note'.$idChamp];

			if($idTypeNote==1) {
				// TE -> ponderation par défaut: 1
				$ponderation = $_POST['Ponderation'.$idChamp];
				if(empty($ponderation)) {
					$ponderation = 1;
				}
			} else {
				// on laisse à zéro -> utiliser la pondération du thème
				$ponderation = 0;
				// pour les notes autres que TE, tester si pas déjà existante pour le thème
				$requete = "SELECT count(IDNote) from notes where IDEleve=$IDEleve and NoSemestre=$semestre and Annee=$annee and IDTheme=$theme and IDTypeNote=$idTypeNote";
				$result =  mysql_query($requete);
				$resultat=mysql_fetch_row($result);
				if($resultat[0]!=0) {
					// la note existe déjà
					$messageErreur = "La note existe déjà!";
				}

			}
			$remarque = $_POST['RemarqueNote'.$idChamp];
		}
		if(empty($note)) {
			$note = 'NULL';
		}


		// ajout de la note en DB
		if($modeEvaluation=="theme" || $idTypeNote!=0) {
	    	$requete = "INSERT INTO notes (IDEleve, NoSemestre, Annee, IDTypeNote, Note, RemarqueNote, IDTheme, Ponderation) values ($IDEleve, $semestre, $annee, $idTypeNote, $note, \"$remarque\", $theme, $ponderation)";
    		//echo $requete;
		} else {
			// mode sans theme - > création automatique des notes pour le theme 10
			$theme = 10;
			$noteAuto=2;
		}
	}
	if(empty($messageErreur)) {
    		$resultat =  mysql_query($requete);
	}
	// ajout automatique des notes TARS
	//while($noteAuto>0&&$noteAuto<6) {
	if($noteAuto!=0) {
		$compToAdd = $competencesEvaluation;
		if(!empty($lastAnneeEvalOld) && !empty($lastSemestreEvalOld)) {
			if($annee<$lastAnneeEvalOld || ($annee==$lastAnneeEvalOld && $semestre <= $lastSemestreEvalOld)) {
				$compToAdd = $competencesEvaluationOld;
			}
		}
		foreach ($compToAdd as $key => $value) {
			// ajout de la note en DB
	    	$requete = "INSERT INTO notes (IDEleve, NoSemestre, Annee, IDTypeNote, Note, RemarqueNote, IDTheme, Ponderation) values ($IDEleve, $semestre, $annee, ".($key+1).", $note, \"$remarque\", $theme, 0)";
			//echo $requete;
			//$noteAuto++;
			if(empty($messageErreur)) {
				$resultat =  mysql_query($requete);
			}
		}
	}
}
include("entete.php");
if(!hasAdminRigth()) {
	echo "<br><br><center><b>Contenu non autorisé.</b></center><br><br>";
	exit;
}
?>

<div id="page">
<script>
function submitNew(annee, semestre, theme) {
	document.getElementById('myForm').actionNote.value='new';
	document.getElementById('myForm').anneeNote.value=annee;
	document.getElementById('myForm').semestreNote.value=semestre;
	document.getElementById('myForm').themeNote.value=theme;
	document.getElementById('myForm').idBlock.value=annee+""+semestre+""+theme;
	document.getElementById('myForm').submit();
}
function submitNewGen(annee, semestre, type) {
	//alert(annee);
	document.getElementById('myForm').actionNote.value='new';
	document.getElementById('myForm').anneeNote.value=annee;
	document.getElementById('myForm').semestreNote.value=semestre;
	document.getElementById('myForm').typeNote.value=type;
	//document.getElementById('myForm').idBlock.value=annee+""+semestre+""+theme;
	document.getElementById('myForm').submit();
}
function submitUpdate(idNote, idBlock) {
	document.getElementById('myForm').actionNote.value='update';
	document.getElementById('myForm').idNote.value=idNote;
	document.getElementById('myForm').idBlock.value=idBlock;
	//document.getElementById('myForm').action ="detailEleve.php#block"+idBlock;
	document.getElementById('myForm').submit();
}
function submitDelete(idNote, idBlock) {
	document.getElementById('myForm').actionNote.value='delete';
	document.getElementById('myForm').idNote.value=idNote;
	document.getElementById('myForm').idBlock.value=idBlock;
	document.getElementById('myForm').submit();
}

function submitDeleteTheme(idTheme, annee, semestre) {
	//alert("del theme");
	document.getElementById('myForm').actionNote.value='deleteTheme';
	document.getElementById('myForm').anneeNote.value=annee;
	document.getElementById('myForm').semestreNote.value=semestre;
	document.getElementById('myForm').themeNote.value=idTheme;
	//document.getElementById('myForm').idBlock.value=annee+""+semestre+""+theme;
	document.getElementById('myForm').submit();
}
function submitDeletePeriode(annee, semestre) {
	document.getElementById('myForm').actionNote.value='deletePeriode';
	document.getElementById('myForm').anneeNote.value=annee;
	document.getElementById('myForm').semestreNote.value=semestre;
	document.getElementById('myForm').submit();
}
function submitUpdatePond(annee, semestre, theme) {
	document.getElementById('myForm').actionNote.value='updatePond';
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


function toggle(thisname, focus) {
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
	focus = document.getElementById(focus);
	focus.focus();
}

function toggleEdit(thisname) {
	td=document.getElementsByTagName('td')
	for (i=0;i<td.length;i++){
		if (td[i].getAttribute(thisname)){
			if ( td[i].style.display=='none' ){
				td[i].style.display = '';
			} else {
				td[i].style.display = 'none';
			}
		}
	}
}

function toggleTD(thisname) {
	td=document.getElementsByTagName('td')
	for (i=0;i<td.length;i++){
		if (td[i].getAttribute(thisname)){
			if ( td[i].style.display=='none' ){
				td[i].style.display = '';
			} else {
				td[i].style.display = 'none';
			}
		}
	}
}

function limitEvent(e) {
    if (window.event) { //IE
        window.event.cancelBubble = true;
    } else if (e && e.stopPropagation) { //standard
        e.stopPropagation();
    }
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

echo "<FORM id='myForm' ACTION='notesEleve.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<input type='hidden' name='actionNote' value=''>";
echo "<input type='hidden' name='anneeNote' value=''>";
echo "<input type='hidden' name='semestreNote' value=''>";
echo "<input type='hidden' name='themeNote' value=''>";
echo "<input type='hidden' name='typeNote' value=''>";
echo "<input type='hidden' name='idNote' value=''>";
echo "<input type='hidden' name='idBlock' value=''>";

echo "<div class='post'>";

	echo "<center> <font color='#088A08'></font></center>";
if(!empty($messageErreur)) {
	echo "<script>var options = {'title': 'Erreur','button': 'OK','className':'error','modal':'true'};msg.open('".$messageErreur."',options)</script>";
}

$navApp = "<br><h2>";
foreach($listeId as $key => $valeur) {
	if($valeur[0]==$IDEleve) {
		if($key!=0) {
			$navApp .= "<a href='notesEleve.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img id='prev'src='/iconsFam/resultset_previous.png'></a>";
		}
		$navApp .= $nom." ".$prenom;
		if($key<count($listeId)-1) {
			$navApp .= "<a href='notesEleve.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img id='next' src='/iconsFam/resultset_next.png'></a>";
		}
		$classe = $listeId[$key][3];
		break;
	}
}
$navApp .= "</h2><br>\n";

/*
$requete = "SELECT * FROM typenote where IDTypeNote < 6 and IDTypeNote > 0";
$resultat =  mysql_query($requete);
while ($ligne = mysql_fetch_assoc($resultat)) {
	$optionTypeNote .= "<option value=".$ligne['IDTypeNote'].">".$ligne['LibelleTypeNote']."</option>";
}
*/

// construction de la liste déroulante pour ajout Theme
//$requete = "SELECT th.IDTheme, pr.IDProjet, pr.IDEleve, th.NomTheme, th.TypeTheme FROM theme th left join projets pr on th.IDTheme=pr.IDTheme where th.IDTheme >= 10 and th.TypeTheme < 2 order by th.TypeTheme,th.NomTheme";
$requete = "SELECT th.IDTheme, pr.IDProjet, pr.IDEleve, th.NomTheme, th.TypeTheme FROM theme th left join projets pr on th.IDTheme=pr.IDTheme where (th.TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (th.TypeTheme = 1 and pr.IDEleve=$IDEleve) group by th.IDTheme order by th.TypeTheme,th.NomTheme";
$resultat =  mysql_query($requete);
$optionThemes = "";
while ($ligne = mysql_fetch_assoc($resultat)) {
	if(empty($ligne['IDProjet']) || $ligne['IDEleve']==$IDEleve) {
		$texte = $ligne['NomTheme'];
		if($ligne['TypeTheme']==1) {
			$texte = "Projet - ".$texte;
		} else {
			$texte = "Thème - ".$texte;
		}
		$optionThemes .= "<option value=".$ligne['IDTheme'].">".$texte."</option>";
	}
}

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


echo "<table border='0' width='100%'><tr><td>".$navApp."<!-- h3>Notes:</h3><a href='notesEleve.php?nom=$nom&prenom=$prenom&idEleve=$IDEleve&expand=expand'><img src='/iconsFam/bullet_arrow_down.png'></a><a href='notesEleve.php?nom=$nom&prenom=$prenom&idEleve=$IDEleve&expand=default'><img src='/iconsFam/bullet_arrow_up.png'></a --></td><td align='right'><select name='annee' onchange='submit();'><option value='%'>Tous</option>".$optionAnnee."</select><img src='/iconsFam/calendar_add.png' onmouseover=\"Tip('Ajouter un semestre')\" onmouseout='UnTip()' onclick='limitEvent(event);toggle(\"newSemestre\");' align='absmiddle'></td></tr></table>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Notes  <a href='notesEleve.php?nom=$nom&prenom=$prenom&idEleve=$IDEleve&expand=expand'><img src='/iconsFam/bullet_arrow_down.png' valign='bottom'></a><a href='notesEleve.php?nom=$nom&prenom=$prenom&idEleve=$IDEleve&expand=default'><img src='/iconsFam/bullet_arrow_up.png' valign='bottom'></a></div>";
echo "<br><table id='hor-minimalist-b' border='0' width='100%'>\n";

// ligne d'ajout d'un semestre
echo "<tr newSemestre='1' style='display:none'><td colspan='9'>Nouvelle période: <br><select name='anneeNew'>".$optionAnnee."</select>\n";
echo "<select name='semestreNew'><option value='1'>1er semestre</option><option value='2'>2ème semestre</option></select>\n";
echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitNewGen(document.getElementsByName(\"anneeNew\")[0].value,document.getElementsByName(\"semestreNew\")[0].value,\"0\")'>";
echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Annuler')\" onmouseout='UnTip()' onclick='toggle(\"newSemestre\")'></td>";
echo "</tr>";

// recherche des notes
//$requete = "SELECT * FROM notes no join typenote ty on no.IDTypeNote=ty.IDTypeNote join theme th on no.IDTheme=th.IDTheme where IDEleve = $IDEleve and annee like '".$anneeTri."' order by annee desc, nosemestre, nomtheme, no.IDTypeNote, no.IDNote";
$requete = "SELECT * FROM notes no join theme th on no.IDTheme=th.IDTheme where IDEleve = $IDEleve and annee like '".$anneeTri."' order by annee desc, nosemestre, nomtheme, no.IDTypeNote, no.IDNote";
//echo $requete;
$resultat =  mysql_query($requete);
$cnt=0;
$cntNoteTheme = 0;
$annee = 0;
$semestre = 0;
$theme = 0;
$nomTheme = '';
$pondTheme = 0;
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

$cntThemeSem = 0;


function clotureTE() {
	global $te, $cntTe, $moyenneTE, $annee, $semestre, $theme, $displayExpand;
	//termine la ligne de TE et calcule la moyenne
	if($te!=0) {
		// calcule de la moyenne TE
		//$moyenneTE = sprintf("%01.1f",$te/$cntTe);
		//$moyenneTE = $te/$cntTe;
		$moyenneTE = sprintf("%01.1f",round($te/$cntTe,1));
		//echo "<td colspan='5' width='500'></td></tr>";
		echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."'><td width='15'></td><td colspan='2' width='290'><i>Moyenne TE</i></td><td align='center' width='40'>".$moyenneTE."</td><td colspan='5'></td></tr>";
		//$te = 0;
		//$cntTe = 0;
	}
	$te = 0;
	$cntTe = 0;

}

function clotureTheme() {
	global $theme, $semestre, $annee, $nomTheme, $pondTheme, $notesComp, $cntNoteTheme, $displayDefault, $displayExpand, $compAbbr, $compPond, $modeEvaluation;
	// ajouter ligne récap pour affichage sans détail
	$iconSuppr = "";
	if($cntNoteTheme==0) {
		// theme vide -> possibilité de le supprimer
		$iconSuppr = "<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer le thème')\" onmouseout='UnTip()' onclick='submitDeleteTheme(\"".$theme."\", \"".$annee."\", \"".$semestre."\")'> ";
	}
	if($theme!=0 && $theme>=10) {	// ne pas afficher les lignes fictives
		// calcule moyenne du thème
		$moyPnd = 0;
		$cntPnd = 0;
		foreach ($compPond as $key => $value) {
			$moyPnd += $notesComp[$key] * $value;
			$cntPnd += $value;
		}
		$moyTheme = sprintf("%01.1f",($moyPnd/$cntPnd));
		if($cntNoteTheme!=0&&$moyTheme!=0&&$modeEvaluation=="theme") {
			echo "\n<tr block$annee$semestre$theme='1' style='display:".$displayExpand."'><td width='15'></td><td width='250'><i>Moyenne Thème</i></td><td width='40'></td><td width='40' align='center'>".$moyTheme."</td><td width='660' colspan='5'></td></tr>";
		}
		echo "\n<tr block$annee$semestre$theme='1' id='hidethis' style='display:".$displayDefault."' onclick='toggle(\"block$annee$semestre$theme\");'><td colspan='2' width='265'><font size='2' face='Verdana'><img src='/iconsFam/bullet_arrow_down.png'> ".$nomTheme."</font></td><td width='40' align='center'><b>Pond</b><br>".$pondTheme."</td>";
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
		echo "</td><td colspan='1' width='500' align='right'>".$iconSuppr."</td></tr>";
		//$appl=0;$rdmnt=0;$saet=0;$moyenneTech=0;
		$notesComp = array_fill(1, 4, 0);
		$theme=0;
	}
}

function clotureSemestre() {
	global $libellePeriode, $moyennesComp, $cntNotesComp, $semestre, $remGen, $annee, $IDEleve, $displayExpand,$compAbbr,$compEval,$compPond,$cntThemeSem;

	if(!empty($libellePeriode)  && (array_sum($cntNotesComp)!=0) ) {

		// avant prochain bloc, calculer la moyenne du semestre précédant
		$ligneMoy = "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";


		$ligneMoy .= "<tr><td colspan='3' width='305'><i>Moyenne calculée ".$libellePeriode."</i></td>";
		$cntNoteTot = 0;
		foreach ($compAbbr as $key => $value) {
			if($cntNotesComp[$key]!=0) {
				$moyennesComp[$key] = sprintf("%01.1f",round($moyennesComp[$key]/$cntNotesComp[$key],1));
				$ligneMoy .= "<td width='40' align='center'><b>".$value."</b><br>".$moyennesComp[$key]."</td>";
				$cntNoteTot++;
			} else {
				if(empty($value)) {
					$ligneMoy .= "<td width='40' align='center'></td>";
				} else {
					$ligneMoy .= "<td width='40' align='center'><b>".$value."</b><br>-</td>";
				}
			}
		}

		/*
		if($cntNoteT!=0) {
			$moyenneSemestreT = sprintf("%01.1f",round($moyenneSemestreT/$cntNoteT,1));
			//$moyenneSemestreT = sprintf("%01.1f",$moyenneSemestreT/$cntNoteT);
			echo "<td width='40' align='center'><b>T</b><br>".$moyenneSemestreT."</td>";
		} else {
			echo "<td width='40' align='center'><b>T</b><br>-</td>";
		}
		if($cntNoteA!=0) {
			$moyenneSemestreA = sprintf("%01.1f",round($moyenneSemestreA/$cntNoteA,1));
			//$moyenneSemestreA = sprintf("%01.1f",$moyenneSemestreA/$cntNoteA);
			echo "<td width='40' align='center'><b>A</b><br>".$moyenneSemestreA."</td>";
		} else {
			echo "<td width='40' align='center'><b>A</b><br>-</td>";
		}
		if($cntNoteR!=0) {
			$moyenneSemestreR = sprintf("%01.1f",round($moyenneSemestreR/$cntNoteR,1));
			//$moyenneSemestreR = sprintf("%01.1f",$moyenneSemestreR/$cntNoteR);
			echo "<td width='40' align='center'><b>R</b><br>".$moyenneSemestreR."</td>";
		} else {
			echo "<td width='40' align='center'><b>R</b><br>-</td>";
		}
		if($cntNoteS!=0) {
			$moyenneSemestreS = sprintf("%01.1f",round($moyenneSemestreS/$cntNoteS,1));
			//$moyenneSemestreS = sprintf("%01.1f",$moyenneSemestreS/$cntNoteS);
			echo "<td width='40' align='center'><b>S</b><br>".$moyenneSemestreS."</td>";
		} else {
			echo "<td width='40' align='center'><b>S</b><br>-</td>";
		}
		*/
		// fin de la ligne
		$ligneMoy .= "<td colspan='2' width='540'>&nbsp;</td></tr>";
		if($cntThemeSem>1) {
			echo $ligneMoy;
		}
		if($cntNoteTot==count($compEval)) {
			// calcule moyenne du semestre
			$moyPnd = 0;
			$cntPnd = 0;
			foreach ($compPond as $key => $value) {
				$moyPnd += $moyennesComp[$key] * $value;
				$cntPnd += $value;
			}
			$moyenneSemestre = $moyPnd/$cntPnd;
			//$moyenneSemestre = ($moyenneSemestreT * 3 + $moyenneSemestreA + $moyenneSemestreR + $moyenneSemestreS)/6;
			// $moyenneSemDix = sprintf("%01.1f",$moyenneSemestre);
			$moyenneSemDix = sprintf("%01.1f",round($moyenneSemestre,1));
			// $moyenneSemDix = round($moyenneSemestre*2)/2;
			$moyenneSemCent = sprintf("%01.2f",round($moyenneSemestre,2));
			// $moyenneSemCent = sprintf("%01.2f",$moyenneSemestre);
			//if($moyenneSemestre!=0) {
				echo "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";
				echo "<tr><td colspan='3' width='305'><font size='2' face='Verdana'><b>Moyenne bulletin</b></font></td><td align='center' width='40'><font size='2' face='Verdana'><b>".$moyenneSemDix."</b></font></td><td colspan='5'><font size='2' face='Verdana'>(".$moyenneSemCent.")</font></td></tr>";			//}
		}
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

	}
		// afficher la remarque générale si présente
		if(empty($remGen)) {
			// si aucune remarque présent, ajouter les champs de création
			$remGen = "<tr onclick='toggleEdit(\"tdRem$annee$semestre\");'><td tdRem$annee$semestre='1' id='hidethis' style='display:none' colspan='3' valign='top' width=305><b>Remarque sur la période</b></td>";
			$remGen .= "<td tdRem$annee$semestre='1' id='hidethis' style='display:none' colspan='6'><textarea name='RemarqueNote$annee$semestre' COLS=60 ROWS=2 onclick='limitEvent(event)'></textarea>\n";
			$remGen .= "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Ajouter la remarque')\" onmouseout='UnTip()' onClick='submitNewGen(\"$annee\",\"$semestre\",\"6\")'>";
			$remGen .= "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Annuler')\" onmouseout='UnTip()' onClick='toggleEdit(\"tdRem$annee$semestre\");'></td>";
			$remGen .= "</tr>";
		}
		echo $remGen;
		$remGen = "";
		$cntThemeSem = 0;
		// ajout remarque de l'historique
		/*
		if($semestre==1) {
			$dateReq = "and Date > '".$annee."-08-01' and Date <= '".($annee+1)."-01-31'";
		} else {
			$dateReq = "and Date > '".($annee+1)."-01-31' and Date <= '".($annee+1)."-07-31'";
		}
		// recherche des attributs généraux (1 à 6)
		$requete = "SELECT * FROM AttribEleves el join Attribut att on el.IDAttribut=att.IDAttribut where IDEleve = $IDEleve and el.IDAttribut in (102,103,104,105)  ".$dateReq." order by Date";
		//echo $requete;

		$resultat =  mysql_query($requete);
		$histo = "";
		while ($ligne = mysql_fetch_assoc($resultat)) {
			$histo .= date('d.m.Y', strtotime($ligne['Date']))." [".$ligne['Nom']."] ".$ligne['Remarque']."<br>";
		}
		if(!empty($histo)&&$annee!=0) {
			echo "<tr><td colspan='3'><b>Historique période</b></td><td colspan='6'>".$histo."</td></tr>";
		}
		// ligne d'espacement
		if(!empty($libellePeriode)) {
			$libellePeriode = "";
			echo "\n<tr><td colspan='9' valign='bottom' height='20'></td></tr>";
		}
		*/


}

while ($ligne = mysql_fetch_assoc($resultat)) {
	if($ligne['Annee'] != $annee || $ligne['NoSemestre'] != $semestre) {
		// nouvelle année
		// clôture de la ligne des TE si nécessaire
		clotureTE();
		// clôture du théme précédent si nécessaire
		clotureTheme();
		// clôturer la période avec le calcul de la moyenne et l'éventuelle remarque sur la période
		clotureSemestre();

		switch($ligne['NoSemestre']) {
			case 1: $libellePeriode = "1er semestre";
					break;
			case 2: $libellePeriode = "2ème semestre";
					break;
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
		/*
		if($annee>$lastAnneeEvalOld || ($annee==$lastAnneeEvalOld && $semestre > $lastSemestreEvalOld)) {
			$compEval = $competencesEvaluation;
			$compAbbr = $competencesAbbr;
			$compPond = $competencesPonderation;
		} else {
			$compEval = $competencesEvaluationOld;
			$compAbbr = $competencesAbbrOld;
			$compPond = $competencesPonderationOld;
		}
		*/
		echo "\n<tr><td colspan='9' bgColor='#5C5C5C'></td></tr>";
		echo "<tr><td colspan='2' width='265'><font size='2' face='Verdana'><nobr><b>Année ".$ligne['Annee']."/".($ligne['Annee']+1)." - ".$libellePeriode."</b></nobr></font></td><td width='40'></td><td width='40'></td><td colspan='5' width='660' align='right'>";
		if($modeEvaluation=="theme") {
			echo "<img src='/iconsFam/folder_add.png' onmouseover=\"Tip('Ajouter un thème pour la période')\" onmouseout='UnTip()' onclick='limitEvent(event);toggle(\"newTheme$annee$semestre\");'>";
		}
		echo "&nbsp;<img src='/iconsFam/comment_add.png' onmouseover=\"Tip('Ajouter une remaque sur la période')\" onmouseout='UnTip()' onclick='toggleEdit(\"tdRem$annee$semestre\");'>";
		//echo "&nbsp;<img src='/iconsFam/page_add.png' onmouseover=\"Tip('Ajouter le bulletin de la période')\" onmouseout='UnTip()' onclick='toggleEdit(\"tdBul$annee$semestre\");'>";

		if($ligne['IDTheme']<10 && $ligne['IDTypeNote']==0) { // il ne reste que l'année -> on peut effacer la ligne
			echo "&nbsp;<img src='/iconsFam/cross.png' onmouseover=\"Tip('Supprimer la période')\" onmouseout='UnTip()' onclick='submitDeletePeriode(\"".$annee."\", \"".$semestre."\")'>";
		}
		echo "</td></tr>";

		echo "<tr newTheme$annee$semestre='1' id='hidethis' style='display:none'><td colspan='2' width='265'><select name='IDTheme$annee$semestre'>".$optionThemes."</select></td>\n";
		echo "<td width='40' align='center'><input type='text' size='1' name='PonderationTheme$annee$semestre' value='' onmouseover=\"Tip('Pondération du thème, laisser vide pour utiliser la pondération de base de ce thème')\" onmouseout='UnTip()'></td>";
		echo "<td width='40'><input type='hidden' name='IDTypeNote$annee$semestre' value='0'></td>\n";
		echo "<td colspan='5' width='660'>\n";
		echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitNewGen(\"$annee\",\"$semestre\",\"0\")'>";
		echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Annuler')\" onmouseout='UnTip()' onclick='toggle(\"newTheme$annee$semestre\")'></td>";
		echo "</tr>";
	}
	if($ligne['IDTheme']>=10 && $ligne['IDTheme']!=$theme) {
		// clôture de la ligne des TE si nécessaire
		clotureTE();
		// clôture du théme précédent si nécessaire
		clotureTheme();
		// nouveau thème pour la même période
		$cntNoteTheme = 0;
		$moyenneTE = 0;
		$cntThemeSem++;

		$theme = $ligne['IDTheme'];
		$nomTheme = $ligne['NomTheme'];
		if($ligne['TypeTheme']==1) {
			$nomTheme = "Projet - ".$nomTheme;
		}

		// recherche du nombre de semaine selon journaux pour pondération automatique
		$dateCalc=mktime(0,0,0,1,4,($ligne['Annee']+1));
		$jour_semaine=date("N",$dateCalc);
		$lundisem2=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaineSem2-1);
		$requetePon = "select count(*) as nbrday from (select count(DateJournal) from journal where IDEleve=".$IDEleve." and IDTheme=".$ligne['IDTheme']." and DateJournal between ";
		if($ligne['NoSemestre']==1) {
			$requetePon .= "'".$ligne['Annee']."-08-01' and '".date("Y-m-d",($lundisem2-86400))."'";
		} else {
			$requetePon .= "'".date("Y-m-d",$lundisem2)."' and '".($ligne['Annee']+1)."-07-31'";
		}
		$requetePon .= "group by DateJournal) as datej";
		//echo $requetePon."<br>";
		$resultatPon =  mysql_query($requetePon);
		$lignePon = mysql_fetch_assoc($resultatPon);
		if(!empty($joursATE[$classe])) {
			$pondAuto = round($lignePon['nbrday']/$joursATE[$classe],1);
		} else {
			$pondAuto = 1;
		}

		if($pondMode=="auto") {
			$pondTheme = $pondAuto;
		} else {
			$pondTheme = $ligne['Ponderation'];
			if($pondTheme==0) {
				$pondTheme = $ligne['PonderationTheme'];
			}
		}

		echo "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";
		echo "\n<tr block$annee$semestre$theme='1' id='hidethis' onclick='toggle(\"block$annee$semestre$theme\");' style='display:".$displayExpand."'><td colspan='2' width='265'><font size='2' face='Verdana'><img src='/iconsFam/bullet_arrow_up.png'> ".$nomTheme."</font></td>";
		echo "<td width='40' align='center' pond$annee$semestre$theme='1' onclick='toggleTD(\"pond$annee$semestre$theme\");limitEvent(event);' onmouseover=\"Tip('Pondération calculée: ".$pondAuto."')\" onmouseout='UnTip()'><b>Pond</b><br>".$pondTheme."</td>";
		echo "<td width='40' align='center' style='display:none' pond$annee$semestre$theme='1' onclick='toggleTD(\"pond$annee$semestre$theme\");limitEvent(event);'><input type='text' size='2' maxlength='2' name='NewPond$annee$semestre$theme' style='text-align:center;' value='".$pondTheme."' onClick='limitEvent(event);' onChange='submitUpdatePond(\"$annee\",\"$semestre\",\"$theme\")'></td>";
		echo "<td width='40' align='center'><b>Note</b><br>&nbsp;</td><td colspan='5' align='right' width='660'><img src='/iconsFam/script_add.png' onmouseover=\"Tip('Ajouter une note au thème')\" onmouseout='UnTip()' onclick='limitEvent(event);toggle(\"new$annee$semestre$theme\",\"focus$annee$semestre$theme\");'></td></tr>";
		// ajouter une ligne d'insertion
		$optionTypeNote = "<option value='1'>TE</option>"; // construction de la liste déroulante pour ajout Note
		foreach ($compEval as $key => $value) {
			$optionTypeNote .= "<option value=".($key+1).">".$value."</option>";
		}
		echo "<tr new$annee$semestre$theme='1' style='display:none'><td width='15'></td><td width='250'><select name='IDTypeNote$annee$semestre$theme' onChange='document.getElementById(\"focus$annee$semestre$theme\").focus()'>".$optionTypeNote."</select></td>\n";
		echo "<td width='40' align='center'><input type='text' size='1' name='Ponderation$annee$semestre$theme' value=''></td>";
		echo "<td width='40'><input type='text' size='2' name='Note$annee$semestre$theme' id='focus$annee$semestre$theme' value='' onkeydown='if (event.keyCode == 13) submitNew(\"$annee\",\"$semestre\",\"$theme\")'></td>\n";
		echo "<td colspan='5' width='660'><textarea name='RemarqueNote$annee$semestre$theme' COLS=60 ROWS=2></textarea>\n";
		echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitNew(\"$annee\",\"$semestre\",\"$theme\")'>";
		echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Annuler')\" onmouseout='UnTip()' onclick='toggle(\"new$annee$semestre$theme\")'></td>";
		echo "</tr>";

	}
	if($ligne['IDTypeNote'] > 1) {
		// clôture de la ligne des TE si nécessaire
		clotureTE();
	}
	if($ligne['IDTypeNote']>1 && $ligne['IDTypeNote']<6 ) { //&& empty($ligne['Note'])) {
		// recherche des moyennes des évaluations du thème concerné
		$requeteMoy = "SELECT AVG(Note) as NoteMoyenne, AVG(Niveau) as NiveauMoyen, GROUP_CONCAT(Remarque SEPARATOR '\n') as RemarqueConcat FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$theme and IDCompetence=".($ligne['IDTypeNote']-1)." and IDTypeEval=2";
		//$requeteMoy .= " and (Annee between ".$ligne['Annee']." and ".($ligne['Annee']+1).")";
		if($ligne['NoSemestre']==1) {
			$requeteMoy .= " and ((NoSemaine > 30 and Annee = ".$ligne['Annee'].") or (NoSemaine <= ".$noSemaineSem2." and  Annee = ".($ligne['Annee']+1)."))";
		} else {
			$requeteMoy .= " and (NoSemaine between ".$noSemaineSem2." and 30) and Annee = ".($ligne['Annee']+1);
		}
		$requeteMoy .= " and DateValidation is not null group by IDCompetence, IDTypeEval order by IDCompetence";
		//echo "<br>".$requeteMoy;
		$resultatMoy =  mysql_query($requeteMoy);
		$moyEstimee  = "";
		$remConcat = "";
		if(!empty($resultatMoy)&&mysql_num_rows($resultatMoy)>0) {
			$ligneMoy = mysql_fetch_assoc($resultatMoy);
			if($typeEvaluation=="abcd") {
				$moyEstimee = "(".niveauMoy($ligneMoy['NiveauMoyen']).")";
			} else {
				$moyEstimee = "(".noteMoy($ligneMoy['NoteMoyenne']).")";
			}
			$remConcat = $ligneMoy['RemarqueConcat'];
		}

		// test
		$requeteMoy = "SELECT AVG(Note) as NoteMoyenne, AVG(Niveau) as NiveauMoyen FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$theme and IDCompetence=".($ligne['IDTypeNote']-1)." and IDTypeEval=2";
		//$requeteMoy .= " and (Annee between ".$ligne['Annee']." and ".($ligne['Annee']+1).")";
		if($ligne['NoSemestre']==1) {
			$requeteMoy .= " and ((NoSemaine > 30 and Annee = ".$ligne['Annee'].") or (NoSemaine <= ".$noSemaineSem2." and  Annee = ".($ligne['Annee']+1)."))";
		} else {
			$requeteMoy .= " and (NoSemaine between ".$noSemaineSem2." and 30) and Annee = ".($ligne['Annee']+1);
		}
		$requeteMoy .= " group by IDCompetence, IDTypeEval order by IDCompetence";
		//echo "<br>".$requeteMoy;
		$resultatMoy =  mysql_query($requeteMoy);
		$moyEstimeeNoValid  = "";
		if(!empty($resultatMoy)&&mysql_num_rows($resultatMoy)>0) {
			$ligneMoy = mysql_fetch_assoc($resultatMoy);
			if($typeEvaluation=="abcd") {
				$moyEstimeeNoValid = "(".niveauMoy($ligneMoy['NiveauMoyen']).")";
			} else {
				$moyEstimeeNoValid = "(".noteMoy($ligneMoy['NoteMoyenne']).")";
			}
		}

		//echo $requeteMoy."<br>";
	}
	$idNote = $ligne['IDNote'];
	switch($ligne['IDTypeNote']) {
		case 1:
			// TE
			echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."' onclick='toggleEdit(\"tdTE$idNote\");'><td width='15'></td><td width='250'>TE ".($cntTe+1)."</td>\n";
			//echo "<td tdTE$idNote='1' id='hidethis' align='center' width='40'></td>";
			echo "<td tdTE$idNote='1' id='hidethis' align='center' width='40'>".$ligne['Ponderation']."</td>";
			echo "<td tdTE$idNote='1' id='hidethis' style='display:none' width='40'><input type='text' size='2' name='Ponderation$idNote' value='".$ligne['Ponderation']."' onclick='limitEvent(event)'></td>\n";
			echo "<td tdTE$idNote='1' id='hidethis' align='center' width='40'><b>".$ligne['Note']."</b></td>\n";
			echo "<td tdTE$idNote='1' id='hidethis' style='display:none' width='40'><input type='text' size='2' name='Note$idNote' value='".$ligne['Note']."' onclick='limitEvent(event)'></td>\n";
			echo "<td tdTE$idNote='1' id='hidethis' td colspan='5' width='660'>".$ligne['RemarqueNote']."</td>\n";
			echo "<td tdTE$idNote='1' id='hidethis' style='display:none' colspan='5' width='660'><textarea name='RemarqueNote$idNote' COLS=60 ROWS=2 onclick='limitEvent(event)'>".$ligne['RemarqueNote']."</textarea>\n";
			echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' onClick='submitUpdate(".$idNote.",\"".$annee.$semestre.$theme."\")'>";
			echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer la note')\" onmouseout='UnTip()' onClick='submitDelete(".$idNote.",\"".$annee.$semestre.$theme."\")'></td>";
			echo "</tr>";
			if(!empty($ligne['Note'])) {
				$te += $ligne['Note'] * $ligne['Ponderation'];
				$cntTe += $ligne['Ponderation'];
				$cntNoteTheme++;
			}
			break;
		case 2:
			// Technique
			echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."' onclick='toggleEdit(\"tdT$idNote\");'><td width='15'></td><td width='250'><b>".$compEval[$ligne['IDTypeNote']-1]."</b></td>\n";
			echo "<td width='40' align='center'><input type='hidden' name='Ponderation$idNote' value='".$ligne['Ponderation']."'></td>";
			//echo "<td width='40' align='center'>2<input type='hidden' name='Ponderation$idNote' value='".$ligne['Ponderation']."'></td>";
			echo "<td tdT$idNote='1' id='hidethis' align='center' width='60'";
			if(!empty($ligne['Note'])) {
				if(!empty($moyEstimee)) {
					echo " onmouseover=\"Tip('Moyenne des évaluations: ".$moyEstimee.", yc non validées: ".$moyEstimeeNoValid."')\" onmouseout='UnTip()'";
				}
				echo "><b>".$ligne['Note']."</b></td>\n";
			} else {
				echo ">".$moyEstimee."</td>\n";
			}
			echo "<td tdT$idNote='1' id='hidethis' style='display:none' width='40'><input type='text' size='2' name='Note$idNote' value='".$ligne['Note']."' onclick='limitEvent(event)'></td>\n";
			echo "<td tdT$idNote='1' id='hidethis' td colspan='5' width='660'>";
			if(!empty($ligne['RemarqueNote']) || strpos($remConcat, "\n")) {
				echo $ligne['RemarqueNote'];
			} else {
				echo "<i>".$remConcat."</i>";
			}
			echo "</td>\n";
			echo "<td tdT$idNote='1' id='hidethis' style='display:none' colspan='5' width='660'><textarea name='RemarqueNote$idNote' COLS=60 ROWS=2 onclick='limitEvent(event)'>";
			if(!empty($ligne['RemarqueNote'])) {
				echo $ligne['RemarqueNote'];
			} else {
				echo $remConcat;
			}
			echo "</textarea>\n";
			echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' onClick='submitUpdate(".$idNote.",\"".$annee.$semestre.$theme."\")'>";
			echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer la note')\" onmouseout='UnTip()' onClick='submitDelete(".$idNote.",\"".$annee.$semestre.$theme."\")'></td>";
			echo "</tr>";
			// calcul et affichage de la moyenne de technique
			if(!empty($ligne['Note'])) {
				if($moyenneTE!=0) {
					$notesComp[$ligne['IDTypeNote']-1] = sprintf("%01.1f",round(($ligne['Note']*$moyenneCompPonderation+$moyenneTE)/($moyenneCompPonderation+1),1));
					//$moyenneTech = sprintf("%01.1f",($ligne['Note']*2+$moyenneTE)/3);
					echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."'><td width='15'></td><td width='250'><i>Moyenne ".$compEval[$ligne['IDTypeNote']-1]."</i></td>";
					//echo "<td width='40' align='center'>3</td><td align='center' width='40'>".$moyenneTech."</td>";
					echo "<td width='40' align='center'></td><td align='center' width='40'>".$notesComp[$ligne['IDTypeNote']-1]."</td>";
					echo "<td colspan='5'></td></tr>";
				} else {
					$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				}
				$moyenneTE = 0;
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				$cntNoteTheme++;
			}
			break;
		case 3:
			// Application
			echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."' onclick='toggleEdit(\"tdA$idNote\");'><td width='15'></td><td width='250'><b>".$compEval[$ligne['IDTypeNote']-1]."</b></td>\n";
			echo "<td width='40' align='center'><input type='hidden' name='Ponderation$idNote' value='".$ligne['Ponderation']."'></td>";
			echo "<td tdA$idNote='1' id='hidethis' align='center' width='60'";
			if(!empty($ligne['Note'])) {
				if(!empty($moyEstimee)) {
					echo " onmouseover=\"Tip('Moyenne des évaluations: ".$moyEstimee.", yc non validées: ".$moyEstimeeNoValid."')\" onmouseout='UnTip()'";
				}
				echo "><b>".$ligne['Note']."</b></td>\n";
			} else {
				echo ">".$moyEstimee."</td>\n";
			}
			echo "<td tdA$idNote='1' id='hidethis' style='display:none' width='40'><input type='text' size='2' name='Note$idNote' value='".$ligne['Note']."' onclick='limitEvent(event)'></td>\n";
			echo "<td tdA$idNote='1' id='hidethis' td colspan='5' width='660'>";
			if(!empty($ligne['RemarqueNote']) || strpos($remConcat, "\n")) {
				echo $ligne['RemarqueNote'];
			} else {
				echo "<i>".$remConcat."</i>";
			}
			echo "</td>\n";
			echo "<td tdA$idNote='1' id='hidethis' style='display:none' colspan='5' width='660'><textarea name='RemarqueNote$idNote' COLS=60 ROWS=2 onclick='limitEvent(event)'>";
			if(!empty($ligne['RemarqueNote'])) {
				echo $ligne['RemarqueNote'];
			} else {
				echo $remConcat;
			}
			echo "</textarea>\n";
			echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' onClick='submitUpdate(".$idNote.",\"".$annee.$semestre.$theme."\")'>";
			echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer la note')\" onmouseout='UnTip()' onClick='submitDelete(".$idNote.",\"".$annee.$semestre.$theme."\")'></td>";
			echo "</tr>";
			if(!empty($ligne['Note'])) {
				$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				$cntNoteTheme++;
			}
			break;
		case 4:
			// Rendement
			echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."' onclick='toggleEdit(\"tdR$idNote\");'><td width='15'></td><td width='250'><b>".$compEval[$ligne['IDTypeNote']-1]."</b></td>\n";
			echo "<td width='40' align='center'><input type='hidden' name='Ponderation$idNote' value='".$ligne['Ponderation']."'></td>";
			echo "<td tdR$idNote='1' id='hidethis' align='center' width='60'";
			if(!empty($ligne['Note'])) {
				if(!empty($moyEstimee)) {
					echo " onmouseover=\"Tip('Moyenne des évaluations: ".$moyEstimee.", yc non validées: ".$moyEstimeeNoValid."')\" onmouseout='UnTip()'";
				}
				echo "><b>".$ligne['Note']."</b></td>\n";
			} else {
				echo ">".$moyEstimee."</td>\n";
			}
			echo "<td tdR$idNote='1' id='hidethis' style='display:none' width='40'><input type='text' size='2' name='Note$idNote' value='".$ligne['Note']."' onclick='limitEvent(event)'></td>\n";
			echo "<td tdR$idNote='1' id='hidethis' td colspan='5' width='660'>";
			if(!empty($ligne['RemarqueNote']) || strpos($remConcat, "\n")) {
				echo $ligne['RemarqueNote'];
			} else {
				echo "<i>".$remConcat."</i>";
			}
			echo "</td>\n";
			echo "<td tdR$idNote='1' id='hidethis' style='display:none' colspan='5' width='660'><textarea name='RemarqueNote$idNote' COLS=60 ROWS=2 onclick='limitEvent(event)'>";
			if(!empty($ligne['RemarqueNote'])) {
				echo $ligne['RemarqueNote'];
			} else {
				echo $remConcat;
			}
			echo "</textarea>\n";
			echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' onClick='submitUpdate(".$idNote.",\"".$annee.$semestre.$theme."\")'>";
			echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer la note')\" onmouseout='UnTip()' onClick='submitDelete(".$idNote.",\"".$annee.$semestre.$theme."\")'></td>";
			echo "</tr>";
			if(!empty($ligne['Note'])) {
				$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				$cntNoteTheme++;
			}
			break;
		case 5:
			// Savoir-être
			echo "<tr block$annee$semestre$theme='1' style='display:".$displayExpand."' onclick='toggleEdit(\"tdS$idNote\");'><td width='15'></td><td width='250'><b>".$compEval[$ligne['IDTypeNote']-1]."</b></td>\n";
			echo "<td width='40' align='center'><input type='hidden' name='Ponderation$idNote' value='".$ligne['Ponderation']."'></td>";
			echo "<td tdS$idNote='1' id='hidethis' align='center' width='60'";
			if(!empty($ligne['Note'])) {
				if(!empty($moyEstimee)) {
					echo " onmouseover=\"Tip('Moyenne des évaluations: ".$moyEstimee.", yc non validées: ".$moyEstimeeNoValid."')\" onmouseout='UnTip()'";
				}
				echo "><b>".$ligne['Note']."</b></td>\n";
			} else {
				echo ">".$moyEstimee."</td>\n";
			}
			echo "<td tdS$idNote='1' id='hidethis' style='display:none' width='40'><input type='text' size='2' name='Note$idNote' value='".$ligne['Note']."' onclick='limitEvent(event)'></td>\n";
			echo "<td tdS$idNote='1' id='hidethis' td colspan='5' width='660'>";
			if(!empty($ligne['RemarqueNote']) || strpos($remConcat, "\n")) {
				echo $ligne['RemarqueNote'];
			} else {
				echo "<i>".$remConcat."</i>";
			}
			echo "</td>\n";
			echo "<td tdS$idNote='1' id='hidethis' style='display:none' colspan='5' width='660'><textarea name='RemarqueNote$idNote' COLS=60 ROWS=2 onclick='limitEvent(event)'>";
			if(!empty($ligne['RemarqueNote'])) {
				echo $ligne['RemarqueNote'];
			} else {
				echo $remConcat;
			}
			echo "</textarea>\n";
			echo "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' onClick='submitUpdate(".$idNote.",\"".$annee.$semestre.$theme."\")'>";
			echo "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer la note')\" onmouseout='UnTip()' onClick='submitDelete(".$idNote.",\"".$annee.$semestre.$theme."\")'></td>";
			echo "</tr>";
			if(!empty($ligne['Note'])) {
				$notesComp[$ligne['IDTypeNote']-1] = $ligne['Note'];
				$moyennesComp[$ligne['IDTypeNote']-1] += $notesComp[$ligne['IDTypeNote']-1] * $pondTheme;
				$cntNotesComp[$ligne['IDTypeNote']-1] += $pondTheme;
				$cntNoteTheme++;
			}
			break;
		case 6:
			// Remarque générale
			//echo "edfgdfgdfg ";
			$remGen = "";
			$remGen .= "<tr><td colspan='9' valign='bottom' bgColor='#DEDEDE'></td></tr>";
			$remGen .= "<tr onclick='toggleEdit(\"tdRem$annee$semestre\");'><td colspan='3' valign='top' width=305><b>Remarque sur la période</b></td>";
			$remGen .= "<td tdRem$annee$semestre='1' id='hidethis' colspan='6'>".nl2br($ligne['RemarqueNote'])."</td>";
			$remGen .= "<td tdRem$annee$semestre='1' id='hidethis' style='display:none' colspan='6'><textarea name='RemarqueNote$idNote' COLS=60 ROWS=2 onclick='limitEvent(event)'>".$ligne['RemarqueNote']."</textarea>\n";
			$remGen .= "&nbsp;<img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' onClick='submitUpdate(".$idNote.",\"\")'>";
			$remGen .= "&nbsp;<img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer la note')\" onmouseout='UnTip()' onClick='submitDelete(".$idNote.",\"\")'></td>";
			$remGen .= "</tr>";

			break;
		case 0:
			// ligne vide pour ajout theme
			//if($ligne['Ponderation']!=0) {
			//	$pondTheme = $ligne['Ponderation'];
			//}
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
if(isset($_POST['idBlock']) && !empty($_POST['idBlock'])&&$_SESSION['expand']!="expand") {
	// ouvrir le thème précédemment ouvert
	echo "<script>toggle(\"block".$_POST['idBlock']."\");</script>";
	//echo "<script>alert(\"hello\");</script>";
}
?>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
