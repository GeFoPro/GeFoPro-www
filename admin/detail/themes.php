<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:66
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: themes.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:21
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");


	if(isset($_GET['nom'])) {
		$nom = $_GET['nom'];
		$prenom = $_GET['prenom'];
		$IDEleve = $_GET['idEleve'];
	} else if(isset($_POST['nom'])) {
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$IDEleve = $_POST['IDEleve'];
	}

$from = "";
if(isset($_GET['from'])) {
	$from = $_GET['from'];
}
if(isset($_POST['from'])) {
	$from = $_POST['from'];
}

if(isset($_GET['anneeAff'])) {
	$annee = $_GET['anneeAff'];
	$anneePlus= $annee+1;
}
if(isset($_POST['anneeAff'])) {
	$annee = $_POST['anneeAff'];
	$anneePlus= $annee+1;
}

if(isset($_GET['semestre'])) {
	$semestre = $_GET['semestre'];
}
if(isset($_POST['semestreAction'])) {
	$semestre = $_POST['semestreAction'];
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



if(isset($_POST['validation'])&&!empty($_POST['validation'])) {
	//echo "action: ".$_POST['validation'];
	if($_POST['validation']=="lock") {
		//echo "lock";
		$anneeN = $_POST['anneeNew'];
		$semestreN = $_POST['semestreNew'];
		$requete = "update evaluation set DateValidationAuto = \"".date('Y-m-d')."\", Annee=".$annee.", NoSemestre=1 where IDEvaluation = $IDEvaluation";
		//echo $requete;
		mysql_query($requete);

	} else if($_POST['validation']=="unlock") {
		//echo "unlock";
		$requete = "update evaluation set DateValidationAuto = null, Annee=null, NoSemestre=null where IDEvaluation = $IDEvaluation";
		//echo $requete;
		mysql_query($requete);
	}
}

if(isset($_POST['action'])&&!empty($_POST['action'])) {
	// notes et remarques
	$updPond = $_POST['ponderation'];
	if(empty($updPond)) $updPond=0;
	$tesql = $_POST['te'];
	if(empty($tesql)) $tesql='null';
	$apsql = $_POST['ap'];
	if(empty($apsql)) $apsql='null';
	$resql = $_POST['re'];
	if(empty($resql)) $resql='null';
	$sesql = $_POST['se'];
	if(empty($sesql)) $sesql='null';
	$teremsql = addslashes($_POST['remte']);
	$apremsql = addslashes($_POST['remap']);
	$reremsql = addslashes($_POST['remre']);
	$seremsql = addslashes($_POST['remse']);

	if($_POST['action']=="new") {
		// nouvelles notes pour autre semestre (th�me n'existe pas, p�riode � tester)
		// notes et remarques vides
		$tesql='null';
		$apsql='null';
		$resql='null';
		$sesql='null';
		$teremsql = "";
		$apremsql = "";
		$reremsql = "";
		$seremsql = "";
	} else if($_POST['action']=="insert") {
		// aucune note pr�sente (peut-�tre ni p�riode, ni th�me)
		$semestre = $_POST['semestreNew'];
	} else if($_POST['action']=="update") {
		// mise � jour (p�riode et th�me existent)

	}


	// test p�riode
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=1 and IDTypeNote=0 and Annee=".$annee." and NoSemestre=".$semestre;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		// cr�er le th�me avec pond�ration par d�faut (0)
		$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (NULL, ".$IDEleve.", 1, 0, ".$semestre.", ".$annee.", '', 0)";
		//echo $requete."<br>";
		mysql_query($requete);
	}

	// test th�me
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=0 and Annee=".$annee." and NoSemestre=".$semestre;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		// cr�er le th�me avec pond�ration par d�faut (0)
		$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (NULL, ".$IDEleve.", ".$IDTheme.", 0, ".$semestre.", ".$annee.", '', ".$updPond.")";
	} else {
		// mise � jour de la pond�ration dans le th�me
		$requete = "update notes set ponderation=".$updPond." where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=0 and NoSemestre=".$semestre." and Annee=".$annee;

	}
	//echo $requete."<br>";
	mysql_query($requete);

	// test note technique
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=2 and Annee=".$annee." and NoSemestre=".$semestre;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		// pas de note -> insertion
		$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (".$tesql.", ".$IDEleve.", ".$IDTheme.", 2, ".$semestre.", ".$annee.", '".$teremsql."', 0)";
	} else {
		// note trouv�e -> mise � jour
		$requete = "update notes set Note=".$tesql.", RemarqueNote='".$teremsql."' where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=2 and NoSemestre=".$semestre." and Annee=".$annee;
	}
	//echo $requete."<br>";
	mysql_query($requete);

	// test note application
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=3 and Annee=".$annee." and NoSemestre=".$semestre;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		// pas de note -> insertion
		$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (".$apsql.", ".$IDEleve.", ".$IDTheme.", 3, ".$semestre.", ".$annee.", '".$apremsql."', 0)";
	} else {
		// note trouv�e -> mise � jour
		$requete = "update notes set Note=".$apsql.", RemarqueNote='".$apremsql."' where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=3 and NoSemestre=".$semestre." and Annee=".$annee;
	}
	//echo $requete."<br>";
	mysql_query($requete);

	// test note rendement
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=4 and Annee=".$annee." and NoSemestre=".$semestre;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		// pas de note -> insertion
		$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (".$resql.", ".$IDEleve.", ".$IDTheme.", 4, ".$semestre.", ".$annee.", '".$reremsql."', 0)";
	} else {
		// note trouv�e -> mise � jour
		$requete = "update notes set Note=".$resql.", RemarqueNote='".$reremsql."' where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=4 and NoSemestre=".$semestre." and Annee=".$annee;
	}
	//echo $requete."<br>";
	mysql_query($requete);

	// test note savoir �tre
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=5 and Annee=".$annee." and NoSemestre=".$semestre;
	$resultat =  mysql_query($requete);
	if(mysql_num_rows($resultat)==0) {
		// pas de note -> insertion
		$requete = "insert into notes (Note, IDEleve, IDTheme, IDTypeNote, NoSemestre, Annee, RemarqueNote, Ponderation) values (".$sesql.", ".$IDEleve.", ".$IDTheme.", 5, ".$semestre.", ".$annee.", '".$seremsql."', 0)";
	} else {
		// note trouv�e -> mise � jour
		$requete = "update notes set Note=".$sesql.", RemarqueNote='".$seremsql."' where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and IDTypeNote=5 and NoSemestre=".$semestre." and Annee=".$annee;
	}
	//echo $requete."<br>";
	mysql_query($requete);

}

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
function selNote(thisname) {
	//alert(thisname);
	td=document.getElementsByTagName('td')
	for (i=0;i<td.length;i++){
		if (td[i].getAttribute('noteSel')){
			if(td[i].getAttribute('noteSel')==thisname) {
				td[i].style.backgroundColor = "#EEEEEE";
				td[i].style.fontWeight = "bold";
			} else {
				td[i].style.backgroundColor = "#FFFFFF";
				td[i].style.fontWeight = "normal";
			}
		}
	}
	ta=document.getElementsByTagName('textarea')
	for (i=0;i<ta.length;i++){
		if (ta[i].getAttribute('noteSel')){
			if(ta[i].getAttribute('noteSel')==thisname) {
				ta[i].style.display = '';
			} else {
				ta[i].style.display = 'none';
			}
		}
	}
	im=document.getElementsByTagName('img')
	for (i=0;i<im.length;i++){
		if (im[i].getAttribute('noteSel')){
			if(im[i].getAttribute('noteSel')==thisname) {
				im[i].style.display = '';
			} else {
				im[i].style.display = 'none';
			}
		}
	}
}

function selSuivi(thisname) {
	//alert(thisname);

	ta=document.getElementsByTagName('table')
	for (i=0;i<ta.length;i++){
		if (ta[i].getAttribute('tableSuivi')){
			if(ta[i].getAttribute('tableSuivi')==thisname) {
				ta[i].style.display = '';
			} else {
				ta[i].style.display = 'none';
			}
		}
	}
}

function submitValidation(action) {
	document.getElementById('myForm').validation.value=action;
	document.getElementById('myForm').submit();
}

function submitNote(semestre,action) {
	document.getElementById('myForm').action.value=action;
	document.getElementById('myForm').semestreAction.value=semestre;
	document.getElementById('myForm').submit();
}

</script>
<?
include($app_section."/userInfo.php");
/* en-t�te */

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
	$text = str_replace('"', "'", $text);
        //$text = '<p>'.$text.'</p>';
        return $text;
}

echo "<FORM id='myForm' ACTION='themes.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<input type='hidden' name='from' value='".$from."'>";
echo "<input type='hidden' name='validation' value=''>";
echo "<input type='hidden' name='action' value=''>";
echo "<input type='hidden' name='semestreAction' value=''>";

echo "<div class='post'>";
if(!empty($msg)) {
	echo "<center>".$msg."</center>";
}
//echo "<center> <font color='#088A08'>Nouveaut�: suivi simple de projets (21.03.2013)<br>Seuls les projets attribu�s � l'�l�ve sont disponibles lors de l'ajour d'un suivi (15.01.2014)<br>Par d�faut, seuls les suivi de projets en cours sont affich�s. Un filtre de recherche est disponible (15.01.2014)<br>Le suivi est modifiable en cliquant simplement sur la ligne! (21.01.2014)<br>Support d'une syntaxe wiki simplifi�e pour �criture en gras, italique et insertion de listes (21.01.2014)</font></center>";
echo "<br><table border='0' width='1000'><tr><td width='500'><h2>";

foreach($listeId as $key => $valeur) {
	if($valeur[0]==$IDEleve) {
		if($key!=0) {
			echo "<a href='themes.php?from=".$from."&nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."&IDTheme=".$IDTheme."'><img src='/iconsFam/resultset_previous.png'></a>";
		}
		echo $nom." ".$prenom;
		if($key<count($listeId)-1) {
			echo "<a href='themes.php?from=".$from."&nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."&IDTheme=".$IDTheme."'><img src='/iconsFam/resultset_next.png'></a>";
		}
		$classe = $listeId[$key][3];
		break;
	}
}
echo "</h2></td><td align='center' width='500'><h2>";

// si IDTheme pas impos�, prendre celui en cours
if($IDTheme==0) {
	// recherche du theme en cours selon journal de l'apprenti
	$requete = "SELECT jo.IDTheme from journal as jo join theme as th on jo.IDTheme=th.IDTheme  where IDEleve=".$IDEleve." and TypeTheme < 2 order by DateJournal desc limit 1";
	//echo $requete;
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_row($resultat);
	$IDTheme = $ligne[0];
	//echo "Actu: ".$IDTheme;
}

// recherche des themes � afficher
$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (TypeTheme=1 and pr.IDEleve = $IDEleve) group by IDTheme order by TypeTheme, NomTheme";
//echo $requete;
$resultat =  mysql_query($requete);
//$option = "<option value='0'></option>";
$option = "";
$selTh = "";
while ($ligne = mysql_fetch_assoc($resultat)) {
	if(empty($selTh)) {
		$selTh = $ligne['IDTheme'];
	}
	$option .= "<option value=".$ligne['IDTheme'];
	if($ligne['IDTheme']==$IDTheme) {
		$option .= " selected";
		$selTh = $ligne['IDTheme'];
	}
	$option .= ">";
	if($ligne['TypeTheme']==1) {
		$option .= "Projet - ";
	}
	$option .= $ligne['NomTheme']."</option>";
}
if($IDTheme==0) {
	$IDTheme = $selTh;
}
//echo "Annee aff:".$annee.", IDTheme:".$IDTheme;






// Rechercher l'ann�e correspondant au th�me choisi (IDTheme transmis)

$requete = "SELECT min(DateJournal) as min, max(DateJournal) as max FROM journal jou where IDEleve = $IDEleve and jou.IDTheme = $IDTheme";
$resultat =  mysql_query($requete);
if(!empty($resultat)&&mysql_num_rows($resultat)>0) {
	$ligne = mysql_fetch_assoc($resultat);
	$max = $ligne['max'];
	$min = $ligne['min'];
} else {
	$max = 0;
	$min = 0;
}


// d�finition de l'annee et mois de la date max
if($max!=0) {
	// recherche mois et ann�e de la date r�cup�r�e
	$moisAct = date('m',strtotime($max));
	$anneeAct = date('Y',strtotime($max));
	//echo "date: ".$max.", mois: ".$moisAct.", ann�e: ".$anneeAct;
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

// d�finition de l'annee et mois min
if($min!=0) {
	// recherche mois et ann�e de la date r�cup�r�e
	$moisActMin = date('m',strtotime($min));
	$anneeActMin = date('Y',strtotime($min));
	//echo "date: ".$min.", mois: ".$moisActMin.", ann�e: ".$anneeActMin;
	if($moisActMin<8) {
		$anneeMin = $anneeActMin-1;
	} else {
		$anneeMin = $anneeActMin;
	}

} else {
	$anneeMin = date('Y') - 5;
}


/* ann�e en cours */
//$annee = date('Y');

//$mois = date('m');
/* ann�es pour en-t�te */
//if($mois<8) {
//	$anneePlus = $annee;
//	$annee = $annee-1;
//} else {
//	$anneePlus= $annee+1;
//}
//echo $annee."/".$anneeMin."/".$anneeMax
// construction de la liste d'ann�es
$optionAnneeEval = "<select id='anneeAff' name='anneeAff' onChange='document.getElementById(\"myForm\").submit();'>";
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

echo "</h2></td><td width='150' align='right'><b>Theme/Projet:</b></td><td><select name='IDTheme' onChange='document.getElementById(\"anneeAff\").value=0;document.getElementById(\"myForm\").submit();'>".$option."</select></td></tr>";
	echo "<tr><td></td><td></td><td width='150' align='right'><b>Ann�e:</b></td><td>".$optionAnneeEval."</td></tr>";
	echo "</table>";

if($IDTheme!=0) {
	//echo "IDTheme: ".$IDTheme;
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
	//echo "Heures: ".$requete."<br>";
	$resultat =  mysql_query($requete);
	$ligneHeures = mysql_fetch_assoc($resultat);

	// selection de l'�valuation
	//if($mois<8) {
	//	$anneeToday = $anneeToday-1;
	//}
	// recherche de l'auto-�valuation concern�e
	//if($annee!=$anneeToday) {
	//	$requete = "SELECT * FROM evaluation where IDTheme=".$IDTheme." and IDEleve=".$IDEleve." and Annee = ".$annee;
	//} else {
		$requete = "SELECT * FROM evaluation where IDTheme=".$IDTheme." and IDEleve=".$IDEleve." and (Annee is null OR Annee = ".$annee.")";
	//}
	//echo $requete;
	// lecture de l'auto�valuation
	$resultat =  mysql_query($requete);
	if(!empty($resultat)) {
		$num_rows = mysql_num_rows($resultat);
		if($num_rows==1) {
			$ligne = mysql_fetch_assoc($resultat);
			$anneeEval  = $ligne['Annee'];
			$semestreEval = $ligne['NoSemestre'];
			$trpond = $ligne['TravailPond'];
			$prpond = $ligne['ProgretsPond'];
			$dipond = $ligne['DifficultesPond'];
			$trtxt = wiki2html($ligne['TravailRemarque']);
			//$trtxt = str_replace('"', "'", $ligne['TravailRemarque']);
			//$trtxt = str_replace("\r\n", '<br/>', $trtxt);
			$prtxt = wiki2html($ligne['ProgretsRemarque']);

			$ditxt = wiki2html($ligne['DifficultesRemarque']);
			//echo $ditxt;
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
			echo "Oups, plus d'un enregistrement trouv�...";
		}
	}

	// recherche de la p�riode en fonction des journaux
	// requete pour le theme donn� (retir�: join theme th on jou.IDTheme=th.IDTheme)
	$requete = "SELECT min(DateJournal) as min, max(DateJournal) as max FROM journal jou where IDEleve = $IDEleve and jou.IDTheme = $IDTheme ";
	$requete .= "and (DateJournal between '".$annee."-08-01' and '".$anneePlus."-07-31') order by DateJournal";
	//echo "P�riode:".$requete."<br>";
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_assoc($resultat);
	$min = $ligne['min'];
	$max = $ligne['max'];
	$semMin = date('W',strtotime($min));
	$semMax = date('W',strtotime($max));
	$maxVendredi = $max;
	if($max!='') {
		//$vendredi = strtotime($max)+(7+5-date("N",strtotime($max)))*86400;
		$vendredi = strtotime($max)+(5-date("N",strtotime($max)))*86400;
		//echo date("N",strtotime($max))." ,vendredi: ".date('d.m.Y',$vendredi);
		$maxVendredi = date('Y-m-d',$vendredi);

	}

	// recherche de l'�valuation
	$notesTE = array();
	$pondsTE = array();
	$remsTE = array();
	$notesTECnt[1] = 0;
	$notesTECnt[2] = 0;
	$requete = "SELECT * from notes where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and Annee=".$annee." and IDTypeNote <> 6";
	//echo $requete;
	$resultatNotes =  mysql_query($requete);
	if(!empty($resultatNotes)) {
		while ($ligne = mysql_fetch_assoc($resultatNotes)) {
			$noSemestre = $ligne['NoSemestre'];
			switch($ligne['IDTypeNote']) {
				case 0: $ponderation[$noSemestre] = $ligne['Ponderation'];break;
				case 1:	$notesTE[$noSemestre][$notesTECnt[$noSemestre]] = $ligne['Note'];$pondsTE[$noSemestre][$notesTECnt[$noSemestre]]=$ligne['Ponderation'];$remsTE[$noSemestre][$notesTECnt[$noSemestre]]=$ligne['RemarqueNote'];$notesTECnt[$noSemestre]++;break;
				case 2: $notePte[$noSemestre] = $ligne['Note']; $remPte[$noSemestre]=$ligne['RemarqueNote'];break;
				case 3: $notePap[$noSemestre] = $ligne['Note']; $remPap[$noSemestre]=$ligne['RemarqueNote'];break;
				case 4: $notePre[$noSemestre] = $ligne['Note']; $remPre[$noSemestre]=$ligne['RemarqueNote'];break;
				case 5: $notePse[$noSemestre] = $ligne['Note']; $remPse[$noSemestre]=$ligne['RemarqueNote'];break;
			}
		}
	}

	$pondDef="";
	// si pas de pond�ration, recherche de la pod�ration par d�faut du th�me
	$requete = "SELECT * from theme where IDTheme = ".$IDTheme;
	$resultatTheme = mysql_query($requete);
	$ligne = mysql_fetch_assoc($resultatTheme);
	if(isset($ponderation[1]) && $ponderation[1]==0) {
		$ponderation[1] = $ligne['PonderationTheme'];
	}
	if(isset($ponderation[2]) && $ponderation[2]==0) {
		$ponderation[2] = $ligne['PonderationTheme'];
	}
	$pondDef="Pond�ration par d�faut: ".$ligne['PonderationTheme'];
	//print_r($ponderation);

	echo "<br><div id='corners'>";
	echo "<div id='legend'>Evaluation</div><br>";
	echo "<table border='0'>";
	$href="activites.php?from=theme&idEleve=".$IDEleve."&anneeAff=".$annee."&nom=".urlencode($nom)."&prenom=".$prenom."&IDTheme=".$IDTheme;
	$objtxt = "";
	if($ligne['Objectif']!=0) {
		 $objtxt = "Objectif: ".$ligne['Objectif']."h";
	}
	echo "<tr><td width='150'><b><a href=$href>Heures consacr�es <img src='/iconsFam/external.png'>:</a></b></td><td>".sprintf("%2.1f",$ligneHeures['heures'])."h / ".sprintf("%d",$ligneHeures['jours'])." jours</td><td width='10'></td><td width='150'>".$objtxt."</td>";

	//echo "<td width='50'></td><td width='100'><b>Suivi:</b></td><td onmouseover='Tip(\"<table><tr><td>19.02.2016</td><td>endort sur son bureau</td></tr><tr><td>23.02.2016</td><td>Est tomb� de sa chaise</td></tr></table>\")' onmouseout='UnTip()'>12 entr�es</td></tr>";

	// 2�me colonne : partie prof
	echo "<td width='60'></td><td width='10'></td><td width='450'></td></tr>";
	// fin premi�re ligne

	if($min!='') {
		if($semMax>=$semMin) {
			$nombreSem = $semMax-$semMin+1;
		} else {
			//echo $semMax." / ".$semMin;
			$nombreSem = $semMax+52-$semMin+1;
		}
		$txtsemaines = $nombreSem." semaine(s)";

		echo "<tr><td><b>P�riode:</b></td><td colspan='3'>du ".date('d.m.Y', strtotime($min))." au ".date('d.m.Y', strtotime($max))." / ".$txtsemaines."</td>";
	} else {
		echo "<tr><td><b>P�riode:</b></td><td colspan='3'>Aucun journal trouv�</td>";
	}

	if(empty($semestre)) {
		if(isset($ponderation[1])) {
			$semestre = 1;
		}
		if(isset($ponderation[2])) {
			$semestre = 2;
		}
	}

	echo "<td></td><td></td><td></td></tr>";

	echo "<tr height='20'><td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
	$href = "evaluations.php?from=theme&anneeAff=".$annee."&nom=".urlencode($nom)."&prenom=".$prenom."&idEleve=".$IDEleve."&IDTheme=".$IDTheme;
	echo "<tr><td><b><a href=$href>Auto-�valuation <img src='/iconsFam/external.png'></a></b><td></td>";
	$href = "notesEleve.php?from=theme&annee=".$annee."&nom=".urlencode($nom)."&prenom=".$prenom."&expand=expand&idEleve=".$IDEleve."&IDTheme=".$IDTheme;
	$hrefSem1 = "themes.php?from=theme&anneeAff=".$annee."&semestre=1&nom=".urlencode($nom)."&prenom=".$prenom."&idEleve=".$IDEleve."&IDTheme=".$IDTheme;
	$hrefSem2 = "themes.php?from=theme&anneeAff=".$annee."&semestre=2&nom=".urlencode($nom)."&prenom=".$prenom."&idEleve=".$IDEleve."&IDTheme=".$IDTheme;
	echo "<td></td><td><b>Evaluation </b>";
	if(isset($ponderation[1])&&isset($ponderation[2])) {
		if($semestre==1) {
			echo "(<a href=$hrefSem1><b>S1</b></a> | <a href=$hrefSem2>S2</a>) ";
		} else {
			echo "(<a href=$hrefSem1>S1</a> | <a href=$hrefSem2><b>S2</b></a>) ";
		}
	}
	echo "<a href=$href><img src='/iconsFam/external.png'></a></td><td></td><td></td><td></td></tr>";

	echo "<tr valign='bottom'><td>Travail accompli:</td><td onmouseover=\"Tip('".addslashes($trtxt)."')\" onmouseout='UnTip()'>".progressB($trpond)."</td>";

	echo "<td rowspan='8' onmouseover='selNote(\"\")'></td><td rowspan='2' valign='middle'>Pond�ration th�me:</td><td rowspan='2'><input type='texte' name='ponderation' value='".$ponderation[$semestre]."' size ='3' onmouseover=\"Tip('".$pondDef."')\" onmouseout='UnTip()'></td><td rowspan='2'></td><td rowspan='2'></td></tr>";
	echo "<tr><td>Progrets:</td><td onmouseover=\"Tip('".addslashes($prtxt)."')\" onmouseout='UnTip()'>".progressB($prpond)."</td></tr>";
	echo "<tr onmouseover='selNote(\"\")'><td>Difficult�s:</td><td onmouseover=\"Tip('".addslashes($ditxt)."')\" onmouseout='UnTip()'>".progressB($dipond)."</td>";
	echo "<td rowspan='2'>Travaux �crits:</td><td rowspan='2' colspan='3'>";
	$moyenne = 0;
	$cntMoy = 0;
	if(!empty($notesTE[$semestre])) {
		foreach($notesTE[$semestre] as $key => $valeur) {
			$txt = $remsTE[$semestre][$key];
			if($pondsTE[$semestre][$key]!=1) $txt.="<br>Pond�ration x".($pondsTE[$semestre][$key]);
			echo "<input type='text' name='note$key' value='$valeur' size='3' readonly onmouseover=\"Tip('".addslashes($txt)."')\" onmouseout='UnTip()'>";
			$moyenne += $valeur*$pondsTE[$semestre][$key];
			$cntMoy += $pondsTE[$semestre][$key];
		}
		if(count($notesTE[$semestre])!=0) {
			echo " = <input type='text' name='moyenne' value='".sprintf("%01.1f",round($moyenne/($cntMoy),1))."' size='3' readonly onmouseover=\"Tip('Moyenne travaux �crits')\" onmouseout='UnTip()'>";
		}
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr><td height='15'></td><td></td></tr>";
	echo "<tr><td>Technique:</td><td><input type='text' name='tero' value='".$notete."' size='3' readonly></td>";
	echo "<td noteSel='te' onmouseover='selNote(\"te\")'>Technique:</td><td noteSel='te' onmouseover='selNote(\"te\")'><input type='text' name='te' value='".$notePte[$semestre]."' size='3' ></td><td><img noteSel='te' src='/iconsFam/fleche.png' style='display:none'></td>";

	echo "<td rowspan='4' valign='middle'>";
	echo "<textarea noteSel='te' style='display:none' name='remte' COLS=60 ROWS=7>".$remPte[$semestre]."</textarea>";
	echo "<textarea noteSel='ap' style='display:none' name='remap' COLS=60 ROWS=7>".$remPap[$semestre]."</textarea>";
	echo "<textarea noteSel='re' style='display:none' name='remre' COLS=60 ROWS=7>".$remPre[$semestre]."</textarea>";
	echo "<textarea noteSel='se' style='display:none' name='remse' COLS=60 ROWS=7>".$remPse[$semestre]."</textarea>";
	echo "</td>";

	echo "</tr>";
	echo "<tr><td>Application:</td><td><input type='text' name='apro' value='".$noteap."' size='3' readonly></td>";
	echo "<td noteSel='ap' onmouseover='selNote(\"ap\")'>Application:</td><td noteSel='ap' onmouseover='selNote(\"ap\")'><input type='text' name='ap' value='".$notePap[$semestre]."' size='3'></td></td><td><img noteSel='ap' src='/iconsFam/fleche.png' style='display:none'></td></tr>";
	echo "<tr><td>Rendement:</td><td><input type='text' name='rero' value='".$notere."' size='3' readonly></td>";
	echo "<td noteSel='re' onmouseover='selNote(\"re\")'>Rendement:</td><td noteSel='re' onmouseover='selNote(\"re\")'><input type='text' name='re' value='".$notePre[$semestre]."' size='3'></td></td><td><img noteSel='re' src='/iconsFam/fleche.png' style='display:none'></td></tr>";
	echo "<tr><td>Savoir-�tre:</td><td ><input type='text' name='sero' value='".$notese."' size='3' readonly></td>";
	echo "<td noteSel='se' onmouseover='selNote(\"se\")'>Savoir-�tre:</td><td noteSel='se' onmouseover='selNote(\"se\")'><input type='text' name='se' value='".$notePse[$semestre]."' size='3'></td></td><td><img noteSel='se' src='/iconsFam/fleche.png' style='display:none'></td></tr>";

	echo "<tr style='height:15px;' onmouseover='selNote(\"\")'></tr><tr onmouseover='selNote(\"\")'><td><i>Auto-�valuation valid�e le:</i></td><td colspan ='2'><i>";
	if($num_rows==0) {
		echo "pas encore saisie";
	} else {
		if(empty($dateValidation) || $dateValidation=="0000-00-00") {
			echo "pas encore valid�e&nbsp;&nbsp;<img src='/iconsFam/page_go.png' onmouseover=\"Tip('Valider l\'auto-�valuation')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"lock\")'>";
		} else {
			echo date('d.m.Y', strtotime($dateValidation))."&nbsp;&nbsp;<img src='/iconsFam/page_delete.png' onmouseover=\"Tip('D�valider l\'auto-�valuation')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"unlock\")'>";
		}
	}
	//echo "</i></td><td></td><td></td><td></td><td></td></tr>";
	//echo "<tr><td><i>P�riode concern�e:</i></td><td colspan ='2'><i>";
	//if($num_rows==0) {
	//	echo "-";
	//} else {
	//	if(empty($dateValidation) || $dateValidation=="0000-00-00") {
	//		echo "-";
	//	} else {
	//		echo $anneeEval."/".($anneeEval+1).", semestre ".$semestreEval;;
	//	}
	//}
	echo "</i></td><td><i>P�riode concern�e:</i></td><td colspan='3'>";
	if(!isset($ponderation[$semestre])) {
		echo "<select name='semestreNew'><option value='1'>1er semestre</option><option value='2'>2�me semestre</option></select><img src='/iconsFam/page_add.png' onmouseover=\"Tip('Ajouter les notes au semestre')\" onmouseout='UnTip()' align='absmiddle' onClick='submitNote(0,\"insert\")'>";
	} else {
		echo "<i>".$annee."/".$anneePlus.", semestre ".$semestre."</i> <img src='/iconsFam/page_save.png' onmouseover=\"Tip('Appliquer les modifications')\" onmouseout='UnTip()' align='absmiddle' onClick='submitNote(".$semestre.",\"update\")'>";
		if(!isset($ponderation[1])||!isset($ponderation[2])) {
			echo " <img src='/iconsFam/page_add.png' onmouseover=\"Tip('Ajouter les nouvelles notes au semestre ".($semestre%2+1)."')\" onmouseout='UnTip()' align='absmiddle' onClick='submitNote(".($semestre%2+1).",\"new\")'>";
		}
	}
	echo "</td></tr></table></div><br>";

	// a voir...
	echo "<br><div id='corners'>";
	echo "<div id='legend'>Historique</div>";
	echo "<table border='0'><tr><td valign='top'>";
	echo "<table id='hor-minimalist-b'><tr><th>Source</th><th>Nbr</th></tr>";

	// Remarques g�n�rales
	$requete = "select * from attribeleves where IDEleve=".$IDEleve." and IDAttribut=102 and (Date between '".$min."' and '".$maxVendredi."') order by Date";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableRem\")'><td>Remarques g�n�rales</td><td align='right'>".$num_rows."</td></tr>";
	$tableRem = "<table id='hor-minimalist-b' tableSuivi='tableRem' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarque</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableRem .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['Date']))."</td><td>".$ligne['Remarque']."</td></tr>";
	}
	$tableRem .= "</table>";


	// Annonce maladie
	$requete = "select * from attribeleves where IDEleve=".$IDEleve." and IDAttribut=101 and (Date between '".$min."' and '".$maxVendredi."') order by Date";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableMal\")'><td>Annonce maladie</td><td align='right'>".$num_rows."</td></tr>";
	$tableMal = "<table id='hor-minimalist-b' tableSuivi='tableMal' style='display:none'><tr><th width='70'>Date</th><th width='700'>Motif maladie</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableMal .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['Date']))."</td><td>".$ligne['Remarque']."</td></tr>";
	}
	$tableMal .= "</table>";


	// Annonce cong�
	$requete = "select * from attribeleves where IDEleve=".$IDEleve." and IDAttribut=106 and (Date between '".$min."' and '".$maxVendredi."') order by Date";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableCong\")'><td>Annonce cong�</td><td align='right'>".$num_rows."</td></tr>";
	$tableCong = "<table id='hor-minimalist-b' tableSuivi='tableCong' style='display:none'><tr><th width='70'>Date</th><th width='700'>Motif cong�</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableCong .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['Date']))."</td><td>".$ligne['Remarque']."</td></tr>";
	}
	$tableCong .= "</table>";


	// Carnet non sign�/pr�sent�
	$requete = "select * from attribeleves where IDEleve=".$IDEleve." and IDAttribut=103 and (Date between '".$min."' and '".$maxVendredi."') order by Date";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	$style = "";
	if($num_rows>0) {
		$style = "style='color:#ff0000'";
	}
	echo "<tr onmouseover='selSuivi(\"tableCar\")'><td>Carnet non sign�/pr�sent�</td><td align='right' ".$style.">".$num_rows."</td></tr>";
	$tableCar = "<table id='hor-minimalist-b' tableSuivi='tableCar' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarques</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableCar .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['Date']))."</td><td>".$ligne['Remarque']."</td></tr>";
	}
	$tableCar .= "</table>";


	// Absence sans appel t�l�phonique
	$requete = "select * from attribeleves where IDEleve=".$IDEleve." and IDAttribut=105 and (Date between '".$min."' and '".$maxVendredi."') order by Date";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	$style = "";
	if($num_rows>0) {
		$style = "style='color:#ff0000'";
	}
	echo "<tr onmouseover='selSuivi(\"tableAbs\")'><td>Absence sans appel t�l�phonique</td><td align='right' ".$style.">".$num_rows."</td></tr>";
	$tableAbs = "<table id='hor-minimalist-b'  tableSuivi='tableAbs' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarques</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableAbs .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['Date']))."</td><td>".$ligne['Remarque']."</td></tr>";
	}
	$tableAbs .= "</table>";


	// Activit�s journaux
	$requete = "select * from journal where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and (DateJournal between '".$min."' and '".$maxVendredi."') order by DateJournal";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableJrn\")'><td>Activit�s jounaux</td><td align='right'>".$num_rows."</td></tr>";
	$tableJrn = "<table id='hor-minimalist-b' tableSuivi='tableJrn' style='display:none'><tr><th width='70'>Date</th><th width='700'>Activit�s</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		if(empty($ligne['DateValidation']) || $ligne['DateValidation']=='0000-00-00') {
			$tableJrn .= "<tr><td style='color:#C0C0C0' valign='top'>".date('d.m.Y', strtotime($ligne['DateJournal']))."</td><td style='color:#C0C0C0'>".wiki2html($ligne['Commentaires'])."</td></tr>";
		} else {
			$tableJrn .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['DateJournal']))."</td><td>".wiki2html($ligne['Commentaires'])."</td></tr>";
		}
	}
	$tableJrn .= "</table>";

	// Activit�s journaux non valid�es
	$requete = "select * from journal where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and (DateJournal between '".$min."' and '".$maxVendredi."') and DateValidation is null order by DateJournal";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	$style = "";
	if($num_rows>0) {
		$style = "style='color:#ff0000'";
	}
	echo "<tr onmouseover='selSuivi(\"tableJnv\")'><td>&nbsp &nbsp non valid�es</td><td align='right' ".$style.">".$num_rows."</td></tr>";
	$tableJnv = "<table id='hor-minimalist-b' tableSuivi='tableJnv' style='display:none'><tr><th width='70'>Date</th><th width='700'>Activit�s</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableJnv .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['DateJournal']))."</td><td>".wiki2html($ligne['Commentaires'])."</td></tr>";
	}
	$tableJnv .= "</table>";


	// Corrections demand�es sur journaux
	$requete = "select * from remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and (DateSaisie between '".$min."' and '".$maxVendredi."') and TypeRemarque=2 order by DateSaisie";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableJer\")'><td>&nbsp &nbsp corrections demand�es</td><td align='right'>".$num_rows."</td></tr>";
	$tableJer = "<table id='hor-minimalist-b' tableSuivi='tableJer' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarques</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableJer .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['DateSaisie']))."</td><td>".wiki2html($ligne['Remarque'])."</td></tr>";
	}
	$tableJer.= "</table>";


	// Corrections sur journaux � valider
	$requete = "select * from remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and (DateSaisie between '".$min."' and '".$maxVendredi."') and TypeRemarque=3 order by DateSaisie";
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableJco\")'><td>&nbsp &nbsp corrections � valider</td><td align='right'>".$num_rows."</td></tr>";
	$tableJco = "<table id='hor-minimalist-b' tableSuivi='tableJco' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarques</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableJco .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['DateSaisie']))."</td><td>".wiki2html($ligne['Remarque'])."</td></tr>";
	}
	$tableJco .= "</table>";


	// Suivi hebdomadaire
	$requete = "select * from remarquesuivi where IDEleve=".$IDEleve." and (IDTheme=".$IDTheme." or IDTheme=1)  and (DateSaisie between '".$min."' and '".$maxVendredi."') and TypeRemarque=1 order by DateSaisie";
	//echo $requete;
	$resultat =  mysql_query($requete);
	$num_rows = mysql_num_rows($resultat);
	echo "<tr onmouseover='selSuivi(\"tableShe\")'><td>Suivi</td><td align='right'>".$num_rows."</td></tr>";
	$tableShe = "<table id='hor-minimalist-b' tableSuivi='tableShe' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarques</th></tr>";
	while ($ligne = mysql_fetch_assoc($resultat)) {
		$tableShe .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['DateSaisie']))."</td><td>".wiki2html($ligne['Remarque'])."</td></tr>";
	}
	$tableShe .= "</table>";

	// Suivi projet
	//$requete = "select * from suiviprojet suivi join projets proj on suivi.IDProjet=proj.IDProjet where IDEleve=".$IDEleve." and IDTheme=".$IDTheme." and (DateSaisie between '".$min."' and '".$maxVendredi."') order by DateSaisie";
	//$resultat =  mysql_query($requete);
	//$num_rows = mysql_num_rows($resultat);
	//echo "<tr onmouseover='selSuivi(\"tableSpr\")'><td>Suivi projet</td><td align='right'>".$num_rows."</td></tr>";
	//$tableSpr = "<table id='hor-minimalist-b' tableSuivi='tableSpr' style='display:none'><tr><th width='70'>Date</th><th width='700'>Remarques</th></tr>";
	//while ($ligne = mysql_fetch_assoc($resultat)) {
	//	$tableSpr .= "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['DateSaisie']))."</td><td>".wiki2html($ligne['RemarqueSuivi'])."</td></tr>";
	//}
	//$tableSpr .= "</table>";

	echo "</table></td><td width='800' valign='top' height='250'><div style='overflow-y:auto; height:250px'>";

	echo $tableRem;
	echo $tableMal;
	echo $tableCong;
	echo $tableCar;
	echo $tableAbs;
	echo $tableJrn;
	echo $tableJnv;
	echo $tableJer;
	echo $tableJco;
	echo $tableShe;
	//echo $tableSpr;

	echo "</div></td></tr></table></div>";


}


?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
