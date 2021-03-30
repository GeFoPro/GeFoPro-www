<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:57
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: evaluations.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:20
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

if(hasAdminRigth()) {
	if(isset($_GET['nom'])) {
		$nom = $_GET['nom'];
		$prenom = $_GET['prenom'];
		$IDEleve = $_GET['idEleve'];
	} else if(isset($_POST['nom'])) {
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$IDEleve = $_POST['IDEleve'];
	}
} else {
	// tentative de recherche par userid
	$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where Userid='".$_SESSION['user_login']."'";
    	//echo $requete;
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_assoc($resultat);
	$nom = $ligne['Nom'];
	//echo "Nom: ".$nom;
	$prenom = $ligne['Prenom'];
	$IDEleve = $ligne['IDGDN'];
	$classe = $ligne['Classe'];
}

$from = "";
if(isset($_GET['from'])) {
	$from = $_GET['from'];
}
if(isset($_POST['from'])) {
	$from = $_POST['from'];
}

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

if(isset($_GET['anneeAff'])) {
	$annee = $_GET['anneeAff'];
}
if(isset($_POST['anneeAff'])) {
	$annee = $_POST['anneeAff'];
}

$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}
$IDTheme=0;
if(isset($_POST['IDTheme'])) {
	$IDTheme = $_POST['IDTheme'];
} else if(isset($_GET['IDTheme'])) {
	$IDTheme = $_GET['IDTheme'];
}

if(isset($_POST['IDEvaluation'])) {
	$IDEvaluation = $_POST['IDEvaluation'];
}

$trpond = -1;
$prpond = -1;
$dipond = -1;
// modification ou ajout d'une évaluation
if(isset($_POST['action'])&&!empty($_POST['action'])) {
	$action = $_POST['action'];
	$trpond = $_POST['travail'];
	//echo "tr pond:".$trpond;
	$prpond = $_POST['progrets'];
	$dipond = $_POST['difficultes'];
	$trtxt = addslashes($_POST['remarqueTravail']);
	$prtxt = addslashes($_POST['remarqueProgres']);
	$ditxt = addslashes($_POST['remarqueDifficultes']);
	$amtxt = addslashes($_POST['remarqueAmelioration']);
	$notete = $_POST['technique'];
	$noteap = $_POST['application'];
	$notere = $_POST['rendement'];
	$notese = $_POST['savoirEtre'];
	if(!isset($trpond) || !isset($prpond) || !isset($dipond) || empty($trtxt) || empty($prtxt) || empty($ditxt) || empty($amtxt) || empty($notete) || empty($noteap) || empty($notere)  || empty($notese)) {
		//$msg="<font color='#FF0000'>Tous les champs doivent être renseignés</font><script>alert(\"<font color='#FF0000'>Tous les champs doivent être renseignés!</font>\");</script>";
		$msg="<script>alert(\"Tous les champs doivent être renseignés!\");</script>";
		if(!isset($trpond)) {
			$trpond = -1;
		}
		if(!isset($prpond)) {
			$prpond = -1;
		}
		if(!isset($dipond)) {
			$dipond = -1;
		}
	} else if(!is_numeric($notete) || $notete>6 || !is_numeric($noteap) || $noteap>6 || !is_numeric($notere) || $notere>6 || !is_numeric($notese) || $notese>6) {
		//$msg="<font color='#FF0000'>Veuillez saisir une note correcte!</font>";
		$msg="<script>alert(\"Veuillez saisir une note correcte!\");</script>";
	} else {
		if($action=='Modifier') {
			$requete = "update evaluation set IDTheme=".$IDTheme.", IDEleve=".$IDEleve.", TravailPond=".$trpond.", TravailRemarque=\"".$trtxt."\", ProgretsPond=".$prpond.", ProgretsRemarque=\"".$prtxt."\", DifficultesPond=".$dipond.", DifficultesRemarque=\"".$ditxt."\", Ameliorations=\"".$amtxt."\", NoteTechnique=".$notete.", NoteApplication=".$noteap.", NoteRendement=".$notere.", NoteSavoirEtre=".$notese." where IDEvaluation=".$IDEvaluation;
			//echo $requete;
    			$resultat =  mysql_query($requete);
			$msg = "<font color='#088A08'>Evaluation modifiée avec succès</font>";
		} else {
			$requete = "INSERT INTO evaluation (IDTheme, IDEleve, TravailPond, TravailRemarque, ProgretsPond, ProgretsRemarque, DifficultesPond, DifficultesRemarque, Ameliorations, NoteTechnique, NoteApplication, NoteRendement, NoteSavoirEtre) values ($IDTheme, $IDEleve, $trpond, \"$trtxt\", $prpond, \"$prtxt\", $dipond, \"$ditxt\", \"$amtxt\", $notete, $noteap, $notere, $notese)";
	    		//echo $requete;
			$resultat =  mysql_query($requete);
			$msg = "<font color='#088A08'>Evaluation ajoutée</font>";
		}
	}
}
// test de la période pour notes
$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=1 and IDTypeNote=0 and Annee=".$annee;
//echo $requete;
$resultat =  mysql_query($requete);
if(!$resultat || mysql_num_rows($resultat)==0) {
	$msgNote1 = "La période ainsi que le thème n'existent pas dans la gestion des notes. Les créer avec la pondération de base?";
} else {
	// test du thème
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=0 and Annee=".$annee;
	//echo $requete;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		$msgNote2 = "Le thème n'existe pas dans la gestion des notes. Le créer avec la pondération de base?";
	}
}
if(isset($_POST['validation'])&&!empty($_POST['validation'])) {

	if($_POST['validation']=="lock") {
		//echo "lock";
		$anneeN = $_POST['anneeNew'];
		$semestreN = $_POST['semestreNew'];
		$requete = "update evaluation set DateValidationAuto = \"".date('Y-m-d')."\", Annee=".$anneeN.", NoSemestre=".$semestreN." where IDEvaluation = $IDEvaluation";
		//echo $requete;
		mysql_query($requete);
		// ajouts nécessaire à la saisie des notes
		if(!empty($msgNote1) ) {
			// créer la période
			$requete = "INSERT INTO notes (IDEleve, NoSemestre, Annee, IDTypeNote, Note, RemarqueNote, IDTheme, Ponderation) values ($IDEleve, $semestreN, $anneeN, 0, null, \"\", 1, 0)";
			mysql_query($requete);
			$msg = "<font color='#088A08'>Période ajoutée dans la gestion des notes</font>";
		}
		if(!empty($msgNote2) || !empty($msgNote1)) {
			// créer le thème
			$requete = "INSERT INTO notes (IDEleve, NoSemestre, Annee, IDTypeNote, Note, RemarqueNote, IDTheme, Ponderation) values ($IDEleve, $semestreN, $anneeN, 0, null, \"\", $IDTheme, 0)";
			mysql_query($requete);
			$msg = "<font color='#088A08'>Période ajoutée dans la gestion des notes</font>";
		}

	} else {
		//echo "unlock";
		$requete = "update evaluation set DateValidationAuto = null, Annee = null, NoSemestre = null where IDEvaluation = $IDEvaluation";
		//echo $requete;
		mysql_query($requete);
	}
}

function ponderation($nom,$pond,$action) {
	$styleborder=""; if(!empty($action)&&$pond==-1) $styleborder="style='border:1px solid red;float:left'";
	$txt = '';
	for($i=0;$i<5;$i++) {
		$txt .= "<td width='10'><div ".$styleborder."><input type='radio' name='".$nom."' value='".($i*25)."'";
		if($pond==$i*25) {
			$txt .= " checked";
		}
		$txt .= "></div></td>";
	}

	return $txt;
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

function submitValidation(action) {
	if (action!="lock" || (""=="<?=$msgNote1?>" && ""=="<?=$msgNote2?>") || confirm("<?=$msgNote1?><?=$msgNote2?>")) {
		document.getElementById('myForm').validation.value=action;
		document.getElementById('myForm').submit();
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

function wiki2html($text)
{
        //$text = preg_replace('/&lt;source lang=&quot;(.*?)&quot;&gt;(.*?)&lt;\/source&gt;/', '<pre lang="$1">$2</pre>', $text);
        $text = preg_replace('/======(.*?)======/', '<h5>$1</h5>', $text);
        $text = preg_replace('/=====(.*?)=====/', '<h4>$1</h4>', $text);
        $text = preg_replace('/====(.*?)====/', '<h3>$1</h3>', $text);
        $text = preg_replace('/===(.*?)===/', '<h2>$1</h2>', $text);
        $text = preg_replace('/==(.*?)==/', '<h1>$1</h1>', $text);
        $text = preg_replace("/'''(.*?)'''/", '<strong>$1</strong>', $text);
        $text = preg_replace("/''(.*?)''/", '<em>$1</em>', $text);
        //$text = preg_replace('/&lt;s&gt;(.*?)&lt;\/s&gt;/', '<strike>$1</strike>', $text);
        //$text = preg_replace('/\[\[Image:(.*?)\|(.*?)\]\]/', '<img src="$1" alt="$2" title="$2" />', $text);
        //$text = preg_replace('/\[(.*?) (.*?)\]/', '<a href="$1" title="$2">$2</a>', $text);
        //$text = preg_replace('/&gt;(.*?)\n/', '<blockquote>$1</blockquote>', $text);

        $text = preg_replace('/\* (.*?)\n/', '<ul><li>$1</li></ul>', $text);
        $text = preg_replace('/<\/ul><ul>/', '', $text);

        $text = preg_replace('/# (.*?)\n/', '<ol><li>$1</li></ol>', $text);
        $text = preg_replace('/<\/ol><ol>/', '', $text);

        //$text = str_replace("\r\n\r\n", '</p><p>', $text);
        $text = str_replace("\r\n", '<br/>', $text);
        //$text = '<p>'.$text.'</p>';
        return $text;
}

echo "<FORM id='myForm' ACTION='evaluations.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<input type='hidden' name='from' value='".$from."'>";
echo "<input type='hidden' name='validation' value=''>";

echo "<div class='post'>";
if(!empty($msg)) {
	echo "<center>".$msg."</center>";
}
//echo "<center> <font color='#088A08'>Nouveauté: suivi simple de projets (21.03.2013)<br>Seuls les projets attribués à l'élève sont disponibles lors de l'ajour d'un suivi (15.01.2014)<br>Par défaut, seuls les suivi de projets en cours sont affichés. Un filtre de recherche est disponible (15.01.2014)<br>Le suivi est modifiable en cliquant simplement sur la ligne! (21.01.2014)<br>Support d'une syntaxe wiki simplifiée pour écriture en gras, italique et insertion de listes (21.01.2014)</font></center>";
echo "<br><table border='0' width='1000'><tr><td width='500'><h2>";
if(isset($listeId)&&!empty($listeId)) {
	foreach($listeId as $key => $valeur) {
		if($valeur[0]==$IDEleve) {
			if($key!=0) {
				echo "<a href='evaluations.php?IDTheme=".$IDTheme."&from=".$from."&nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img src='/iconsFam/resultset_previous.png'></a>";
			}
			echo $nom." ".$prenom;
			if($key<count($listeId)-1) {
				echo "<a href='evaluations.php?IDTheme=".$IDTheme."&from=".$from."&nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img src='/iconsFam/resultset_next.png'></a>";
			}
			$classe = $listeId[$key][3];
			break;
		}
	}
}

// si IDTheme pas imposé, prendre celui en cours
if($IDTheme==0) {
	// recherche du theme en cours selon journal de l'apprenti
	$requete = "SELECT jo.IDTheme from journal as jo join theme as th on jo.IDTheme=th.IDTheme  where IDEleve=".$IDEleve." and TypeTheme < 2 order by DateJournal desc limit 1";
	//echo $requete;
	$resultat =  mysql_query($requete);
	if(!empty($resultat)) {
		$ligne = mysql_fetch_row($resultat);
		$IDTheme = $ligne[0];
	}
	//echo "Actu: ".$IDTheme;
}

// recherche des themes à afficher
$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme left outer join evaluation ev on (pr.IDTheme=ev.IDTheme and pr.IDEleve=ev.IDEleve) where (IDEtatProjet=1 and pr.IDEleve = $IDEleve) OR (IDEvaluation is not null and pr.IDEleve = $IDEleve) OR (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (th.IDTheme=".$IDTheme.") group by IDTheme order by TypeTheme, NomTheme";
//echo $requete;
$resultat =  mysql_query($requete);
$option = "<option value='0'></option>";
$themeAct = "";
if(!empty($resultat)) {
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$option .= "<option value=".$ligne['IDTheme'];
		if($ligne['IDTheme']==$IDTheme) {
			$option .= " selected";
			$themeAct = $ligne['NomTheme'];
		}
		$option .= ">";
		if($ligne['TypeTheme']==1) {
			$option .= "Projet - ";
		}
		$option .= $ligne['NomTheme']."</option>";
	}
}


$requete = "SELECT min(DateJournal) as min, max(DateJournal) as max FROM journal jou where IDEleve = $IDEleve and jou.IDTheme = $IDTheme";
$resultat =  mysql_query($requete);
if(!empty($resultat)) {
	$ligne = mysql_fetch_assoc($resultat);
	$max = $ligne['max'];
	$min = $ligne['min'];
} else {
	$max = 0;
	$min = 0;
}


// définition de l'annee et mois de la date max
if($max!=0) {
	// recherche mois et année de la date récupérée
	$moisAct = date('m',strtotime($max));
	$anneeAct = date('Y',strtotime($max));
	//echo "date: ".$max.", mois: ".$moisAct.", année: ".$anneeAct;
	if($moisAct<8) {
		if(empty($annee)) {
			$annee = $anneeAct-1;
			$anneePlus = $anneeAct;
		}
		$anneeMax=$anneeAct-1;
	} else {
		if(empty($annee)) {
			$annee = $anneeAct;
			$anneePlus = $anneeAct+1;
		}
		$anneeMax=$anneeAct;
	}
} else {
	$anneeMax=date('Y');
}

// définition de l'annee et mois min
if($min!=0) {
	// recherche mois et année de la date récupérée
	$moisActMin = date('m',strtotime($min));
	$anneeActMin = date('Y',strtotime($min));
	//echo "date: ".$min.", mois: ".$moisActMin.", année: ".$anneeActMin;
	if($moisActMin<8) {
		$anneeMin = $anneeActMin-1;
	} else {
		$anneeMin = $anneeActMin;
	}

} else {
	$anneeMin = date('Y') - 5;
}

// construction de la liste d'années
$optionAnneeEval = "<select name='anneeAff' onChange='document.getElementById(\"myForm\").submit();'>";
$anneeToday = date('Y');
for($cntA=0;$cntA<5;$cntA++) {
	//echo $annee."/".$anneeMin."/".$anneeMax."//".($anneeToday-$cntA)."<br>";
	if($anneeMin<=($anneeToday-$cntA)&&$anneeMax>=($anneeToday-$cntA)) {
		$optionAnneeEval .= "<option value='".($anneeToday-$cntA)."'";
		if(($anneeToday-$cntA)==$annee) {
			$optionAnneeEval .= " selected ";
		}
		$optionAnneeEval .= ">".($anneeToday-$cntA)."/".($anneeToday-$cntA+1)."</option>";
	}
}
$optionAnneeEval .= "</select>";

// recherche du nombre d'heures
$requete = "SELECT IDTheme, sum( heures ) AS heures, count( heures ) AS jours
FROM (
SELECT  jo.IDTheme as IDTheme, sum( Heures ) AS heures
FROM elevesbk
JOIN journal jo ON IDGDN = IDEleve
JOIN theme th ON jo.IDTheme=th.IDTheme
WHERE (DateJournal between '".$annee."-08-01' and '".$anneePlus."-07-31') and IDGDN=".$IDEleve." and jo.IDTheme=".$IDTheme."
GROUP BY jo.IDTheme, DateJournal
) AS res
GROUP BY IDTheme";
//echo $requete;
$resultat =  mysql_query($requete);
if(!empty($resultat)) {
	$ligne = mysql_fetch_assoc($resultat);
} else {
	$ligne = array();
}
echo "</h2></td>";
echo "<td width='200'><!-- h2>Auto-évaluation";
echo "</h2 --></td><td width='150' align='right'><b>Theme/Projet:</b></td><td><select name='IDTheme' onChange='document.getElementById(\"myForm\").submit();'>".$option."</select></td></tr>";
echo "<tr><td></td><td></td><td width='150' align='right'><b>Année:</b></td><td>".$optionAnneeEval."</td></tr>";
echo "<tr><td></td><td></td><td width='150' align='right'><b>Heures consacrées:</b></td><td>".sprintf("%2.1f",$ligne['heures'])."h / ".sprintf("%d",$ligne['jours'])." jours</td></tr>";
echo "</table><br>";

if($IDTheme!=0) {
	// selection de l'évaluation
	if($mois<8) {

		$anneeToday = $anneeToday-1;
	}
	if($annee!=$anneeToday) {
		$requete = "SELECT * FROM evaluation where IDTheme=".$IDTheme." and IDEleve=".$IDEleve." and Annee = ".$annee;
	} else {
		$requete = "SELECT * FROM evaluation where IDTheme=".$IDTheme." and IDEleve=".$IDEleve." and (Annee is null OR Annee = ".$annee.")";
	}
	//echo $requete;
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	if($num_rows==1) {
		$ligne = mysql_fetch_assoc($resultat);
		$anneeEval  = $ligne['Annee'];
		$semestreEval = $ligne['NoSemestre'];
		$trpond = $ligne['TravailPond'];
		$prpond = $ligne['ProgretsPond'];
		$dipond = $ligne['DifficultesPond'];
		$trtxt = $ligne['TravailRemarque'];
		$prtxt = $ligne['ProgretsRemarque'];
		$ditxt = $ligne['DifficultesRemarque'];
		$amtxt = $ligne['Ameliorations'];
		$notete = $ligne['NoteTechnique'];
		$noteap = $ligne['NoteApplication'];
		$notere = $ligne['NoteRendement'];
		$notese = $ligne['NoteSavoirEtre'];
		$dateValidation = $ligne['DateValidationAuto'];
		//echo "--->".$dateValidation;
		echo "<input type='hidden' name='IDEvaluation' value='".$ligne['IDEvaluation']."'>";
	} else if($num_rows>0) {
		// ne devrait pas arriver
		echo "Oups, plus d'un enregistrement trouvé...";
	}
	//echo "Pondération travail: ".$trpond;
	echo "<br><div id='corners'>";
	echo "<div id='legend'>Auto-évaluation du thème/projet</div>";
	echo "<br><table border='0' >";

	echo "<tr><td width='300'><b>Mon sentiment sur le travail accompli:</b></td><td width='70' align='right'>bof</td>".ponderation('travail',$trpond,$action) ."<td>super</td></tr>";
	$styleborder=""; if(!empty($action)&&empty($trtxt)) $styleborder="style='border-color: #ff0000'";
	echo "<tr><td valign='top'>Parce que:</td><td colspan='7'><textarea name='remarqueTravail' COLS=60 ROWS=4 ".$styleborder.">".$trtxt."</textarea></td></tr>";
	echo "<tr><td colspan='8' height='30'></td></tr>";
	echo "<tr><td><b>J'ai progressé et appris de nouvelles choses:</b></td><td width='70' align='right'>pas du tout</td>".ponderation('progrets',$prpond,$action) ."<td>énormément</td></tr>";
	$styleborder=""; if(!empty($action)&&empty($prtxt)) $styleborder="style='border-color: #ff0000'";
	echo "<tr><td valign='top'>Ce que j'ai appris:</td><td colspan='7'><textarea name='remarqueProgres' COLS=60 ROWS=4 ".$styleborder.">".$prtxt."</textarea></td></tr>";
	echo "<tr><td colspan='8' height='30'></td></tr>";
	echo "<tr><td><b>J'ai rencontré des difficultés:</b></td><td width='70' align='right'>aucune</td>".ponderation('difficultes',$dipond,$action) ."<td>constamment</td></tr>";
	$styleborder=""; if(!empty($action)&&empty($ditxt)) $styleborder="style='border-color: #ff0000'";
	echo "<tr><td valign='top'>Lesquelles et pourquoi:</td><td colspan='7'><textarea name='remarqueDifficultes' COLS=60 ROWS=4 ".$styleborder.">".$ditxt."</textarea></td></tr>";
	echo "<tr><td colspan='8' height='30'></td></tr>";
	$styleborder=""; if(!empty($action)&&empty($amtxt)) $styleborder="style='border-color: #ff0000'";
	echo "<tr><td valign='top'><b>Ce que je dois encore améliorer:</b></td><td colspan='7'><textarea name='remarqueAmelioration' COLS=60 ROWS=4 ".$styleborder.">".$amtxt."</textarea></td></tr>";
	echo "<tr><td colspan='8' height='30'></td></tr>";
	echo "<tr><td><b>J'évalue mon travail avec les notes de:</b></td><td width='70'>";
	$styleborder=""; if(!empty($action)&&(empty($notete)||!is_numeric($notete) || $notete>6)) $styleborder="style='border-color: #ff0000'";
	echo "<input type='texte' name='technique' value='".$notete."' size='3' ".$styleborder."></td><td colspan='6'>Technique <img src='/iconsFam/information.png' align='absmiddle' onmouseover=\"Tip('Techniquement parlant, est-ce que je maitrise le sujet?')\" onmouseout='UnTip()'></td></tr>";
	echo "<tr><td>(au demi-point)</td><td width='70'>";
	$styleborder=""; if(!empty($action)&&(empty($noteap)||!is_numeric($noteap) || $noteap>6)) $styleborder="style='border-color: #ff0000'";
	echo "<input type='texte' name='application' value='".$noteap."' size='3' ".$styleborder."></td><td colspan='6'>Application <img src='/iconsFam/information.png' align='absmiddle' onmouseover=\"Tip('Ai-je été capable d\'appliquer ce que je connais?<br>Ai-je mis tout en oeuvre pour parvenir à terminer les exercices, les montages ou le projet?<br>Est-ce complet?<br>Suis-je allé au bout des choses?')\" onmouseout='UnTip()'></td></tr>";
	echo "<tr><td></td><td width='70'>";
	$styleborder=""; if(!empty($action)&&(empty($notere)||!is_numeric($notere) || $notere>6)) $styleborder="style='border-color: #ff0000'";
	echo "<input type='texte' name='rendement' value='".$notere."' size='3' ".$styleborder."></td><td colspan='6'>Rendement <img src='/iconsFam/information.png' align='absmiddle' onmouseover=\"Tip('Ai-je utilisé le temps à ma disposition de manière efficace?<br>Est-ce que j\'ai pu terminé mon travail dans le temps imparti?')\" onmouseout='UnTip()'></td></tr>";
	echo "<tr><td></td><td width='70'>";
	$styleborder=""; if(!empty($action)&&(empty($notese)||!is_numeric($notese) || $notese>6)) $styleborder="style='border-color: #ff0000'";
	echo "<input type='texte' name='savoirEtre' value='".$notese."' size='3' ".$styleborder."></td><td colspan='5'>Savoir-être <img src='/iconsFam/information.png' align='absmiddle' onmouseover=\"Tip('Me suis-je comporté en professionnel?<br>Suis-je capable de rester concentré sur mon travail?<br>Peut-on compter sur moi pour la réalisation de ce qui m\'est demandé de faire?<br>Suis-je en ordre avec mes horaires, journaux, etc.?')\" onmouseout='UnTip()'></td>";
	echo "<td align='right'>";
	if(!hasAdminRigth()) {
		if($num_rows==0) {
			// encore aucun enregistrement
			echo "<input type='submit' name='action' value='Ajouter'>";
		} else {
			if(empty($dateValidation) || $dateValidation=="0000-00-00") {
				echo "<input type='submit' name='action' value='Modifier'>";
			} else {
				echo "<i>Document validé le ".date('d.m.Y', strtotime($dateValidation))."</i>";
			}
		}
	}
	echo "</td></tr>";
	echo "</table></div><br>";

	// partie prof
	if(hasAdminRigth()&&$num_rows==1) {
		if(empty($anneeEval) || $anneeEval=="0000-00-00") {
			$optionAnnee = "<select name='anneeNew'>";
			//$anneeTri = "";
			for($cntA=0;$cntA<5;$cntA++) {
				$optionAnnee .= "<option value='".($annee-$cntA)."'";
				if(($annee-$cntA)==$annee) {
					$optionAnnee .= " selected ";
				}
				$optionAnnee .= ">".($annee-$cntA)."/".($annee-$cntA+1)."</option>";
			}
			$optionAnnee .= "</select><select name='semestreNew'><option value='1'>1er semestre</option><option value='2'>2ème semestre</option></select>";
		} else {
			$optionAnnee = $anneeEval."/".($anneeEval+1).", semestre ".$semestreEval;
		}

		if(empty($dateValidation) || $dateValidation=="0000-00-00") {
			$valTxt = "ouverte </td><td><img src='/iconsFam/page_go.png' onmouseover=\"Tip('Valider l\'auto-évaluation')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"lock\")'>";
		} else {
			$valTxt = "validée le ".date('d.m.Y', strtotime($dateValidation))." </td><td><img src='/iconsFam/page_delete.png' onmouseover=\"Tip('Dévalider l\'auto-évaluation')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"unlock\")'>";
		}
		echo "<br><br><div id='corners'>";
		echo "<div id='legend'>Evaluation et notes</div>";
		echo "<br><!-- hr size='1' --><table border='0' width='1000'><tr><td width='200'><!-- h2><nobr>Evaluation et notes</nobr></h2 --></td>";
		echo "<td align='center' width='400'></td><td width='150' align='right'><b>Auto-évaluation:</b></td><td>".$valTxt."</td></tr>";
		echo "<tr><td width='200'></td><td align='center' width='400'></td><td width='150' align='right'><b>Période:</b></td><td>".$optionAnnee."</td></tr>";
		echo "</table>";

		if(!empty($dateValidation) && $dateValidation!="0000-00-00") {
			if(empty($anneeEval)) {
				$anneeEval = $annee;
			}

			$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and Annee=".$anneeEval." and IDTypeNote in (2,3,4,5)";
			//echo $requete;
			$resultat =  mysql_query($requete);
			while ($ligne = mysql_fetch_assoc($resultat)) {
				switch($ligne['IDTypeNote']) {
					case 2: $notePte = $ligne['Note']; $remPte=$ligne['RemarqueNote']; break;
					case 3: $notePap = $ligne['Note']; $remPap=$ligne['RemarqueNote'];break;
					case 4: $notePre = $ligne['Note']; $remPre=$ligne['RemarqueNote'];break;
					case 5: $notePse = $ligne['Note']; $remPse=$ligne['RemarqueNote'];break;
				}
			}

			echo "<br><table border='0' >";
			echo "<tr><td width='300'></td><td width='70'><input type='texte' name='techniqueP' value='".$notePte."' size='3'></td><td colspan='6'><b>Technique</b><br> ".$remPte."</td></tr>";
			echo "<tr><td width='300'></td><td width='70'><input type='texte' name='applicationP' value='".$notePap."' size='3'></td><td colspan='6'><b>Application</b><br> ".$remPap."</td></tr>";
			echo "<tr><td width='300'></td><td width='70'><input type='texte' name='rendementP' value='".$notePre."' size='3'></td><td colspan='6'><b>Rendement</b><br> ".$remPre."</td></tr>";
			echo "<tr><td width='300'></td><td width='70'><input type='texte' name='savoirEtreP' value='".$notePse."' size='3'></td><td colspan='5'><b>Savoir-être</b><br> ".$remPse."</td></tr>";
			echo "</table><br>";
		}
		echo "</div>";
	}

}


?>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
