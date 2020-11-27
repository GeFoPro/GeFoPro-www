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

if(isset($_POST['AjoutSuivi'])) {
	$date = date("Y-m-d",strtotime($_POST['DateSuivi']));
	$IDTheme = $_POST['IDThemeNew'];
	$remarque = $_POST['RemarqueSuivi'];
	if(empty($_POST['DateSuivi'])) {
		// date du jour
		$date = date("Y-m-d");
	}
	$uid = $_SESSION['user_login'];
	// ajout d'un attribut
   	//$requete = "INSERT INTO suiviprojet (IDProjet, DateSaisie, RemarqueSuivi, UserId) values ($IDProjet, \"$date\", \"$remarque\", \"$uid\")";
	$requete = "INSERT INTO remarquesuivi (IDEleve, IDTheme, DateSaisie, Remarque, TypeRemarque, UserId) values ($IDEleve, $IDTheme, \"$date\", \"$remarque\",1,\"$uid\")";
    	//echo $requete;
	$resultat =  mysql_query($requete);
}

// effacement d'une ligne
if(isset($_GET['IDSuivi'])) {
	//$requete = "DELETE FROM suiviprojet where IDSuivi=$_GET[IDSuivi]";
	$requete = "DELETE FROM remarquesuivi where IDRemSuivi=$_GET[IDSuivi]";
	//echo $requete;
	mysql_query($requete);
	
}

// modification d'une ligne
if(isset($_POST['suivi'])&&!empty($_POST['suivi'])) {
	$suivi = $_POST['suivi'];
	$dateMaj = date("Y-m-d",strtotime($_POST['DateSuivi'.$suivi]));
	$remarqueMaj = $_POST['RemarqueSuivi'.$suivi];
	//$requete = "update suiviprojet set DateSaisie=\"".$dateMaj."\", RemarqueSuivi=\"".$remarqueMaj."\" where IDSuivi=".$suivi;
	$requete = "update remarquesuivi set DateSaisie=\"".$dateMaj."\", Remarque=\"".$remarqueMaj."\" where IDRemSuivi=".$suivi;
	//echo $requete;
    	$resultat =  mysql_query($requete);

}
//$filtre = "1";
//if(isset($_POST['triProjet'])) {
//	$filtre = $_POST['triProjet'];
//}
//$filtreSQL = "";
//if($filtre!=10) {
//	$filtreSQL = " and ep.IDEtatProjet = ".$filtre;
//}
include("entete.php");
if(!hasAdminRigth()) {
	echo "<br><br><center><b>Contenu non autorisé.</b></center><br><br>";
	exit;
} 
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

function submitSuivi(suivi) {
	document.getElementById('myForm').suivi.value=suivi;
	document.getElementById('myForm').submit();
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

echo "<FORM id='myForm' ACTION='suiviEleve.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<input type='hidden' name='suivi' value=''>";

echo "<div class='post'>";
//echo "<center> <font color='#088A08'>Nouveauté: suivi simple de projets (21.03.2013)<br>Seuls les projets attribués à l'élève sont disponibles lors de l'ajour d'un suivi (15.01.2014)<br>Par défaut, seuls les suivi de projets en cours sont affichés. Un filtre de recherche est disponible (15.01.2014)<br>Le suivi est modifiable en cliquant simplement sur la ligne! (21.01.2014)<br>Support d'une syntaxe wiki simplifiée pour écriture en gras, italique et insertion de listes (21.01.2014)</font></center>";
echo "<br><table border='0' width='100%'><tr><td><h2>";
foreach($listeId as $key => $valeur) {
	if($valeur[0]==$IDEleve) {
		if($key!=0) {
			echo "<a href='suiviEleve.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img src='/iconsFam/resultset_previous.png'></a>";
		}
		echo $nom." ".$prenom;
		if($key<count($listeId)-1) {
			echo "<a href='suiviEleve.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img src='/iconsFam/resultset_next.png'></a>";
		}
		$classe = $listeId[$key][3];
		break;
	}
}
echo "</h2></td><td align='right'>\n";

// construction de la liste de etats de projet
//$selectEtat = "";
//$requete = "SELECT * FROM etatprojet";
//$resultat =  mysql_query($requete);
//while ($ligne = mysql_fetch_assoc($resultat)) {
//	$selectEtat .= "<option value='".$ligne['IDEtatProjet']."'";
//	if($ligne['IDEtatProjet']==$filtre) {
//		$selectEtat .= " selected";
//	}
//	$selectEtat .= ">".$ligne['LibelleEtatProjet']."</option>";
//}

if($IDTheme==0) {
	// recherche du theme en cours selon journal de l'apprenti
	$requete = "SELECT jo.IDTheme from journal as jo join theme as th on jo.IDTheme=th.IDTheme  where IDEleve=".$IDEleve." and TypeTheme < 2 order by DateJournal desc limit 1";
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_row($resultat);
	$IDTheme = $ligne[0];
}

// liste des activités
$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme, pr.IDProjet FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (TypeTheme=1 and pr.IDEleve = $IDEleve) group by IDTheme order by TypeTheme, NomTheme";
//echo $requete;
//$requete = "SELECT * FROM projets ep join theme th on ep.IDTheme=th.IDTheme where IDEleve = $IDEleve ".$filtreSQL." order by NomTheme";
// IDEtatProjet=1 and, supprimé le 12.06.2015
$resultat =  mysql_query($requete);
$option = "";
//while ($ligne = mysql_fetch_assoc($resultat)) {
//	$option .= "<option value=".$ligne['IDProjet'].">".$ligne['NomTheme']."</option>";
//}
$selTh = "";
while ($ligne = mysql_fetch_assoc($resultat)) {
	if(empty($selTh)) {
		$selTh = $ligne['IDTheme'];
	}
	$option .= "<option value=".$ligne['IDTheme'];
	//echo $ligne['IDTheme']."/".$IDTheme."/".$ligne['IDProjet']."<br>";
	if($ligne['IDTheme']==$IDTheme) {
		$option .= " selected";
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




echo "<b>Thème/projet:</b> <select name='IDTheme' onchange='submit()'>".$option."</select></td></tr></table><br>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Suivi</div>";
echo "<table id='hor-minimalist-b' border='0' width='100%'>\n";
echo "<tr><th width='250'>Projet</th><th width='80'>Date</th><th width='650'>Remarque</th><th width='10'></th></tr>";
// recherche des attributs généraux (1 à 6)
//$requete = "SELECT * FROM suiviprojet sp join projets pr on sp.IDProjet=pr.IDProjet join typeprojet tp on pr.IDTypeProjet=tp.IDTypeProjet join etatprojet ep on pr.IDEtatProjet=ep.IDEtatProjet join theme th on pr.IDTheme=th.IDTheme where pr.IDEleve = $IDEleve ".$filtreSQL." order by pr.IDTheme, DateSaisie";
$requete = "SELECT * FROM remarquesuivi rem join theme th on rem.IDTheme=th.IDTheme where rem.IDEleve = $IDEleve and rem.IDTheme=".$IDTheme." order by rem.IDTheme, DateSaisie";
//echo $requete;
$resultat =  mysql_query($requete);
$cnt=0;
$last = 0;
if(!empty($resultat)) {
	while ($ligne = mysql_fetch_assoc($resultat)) {
		//$idSuivi = $ligne['IDSuivi'];
		$idSuivi = $ligne['IDRemSuivi'];
		
		//if($last!=$ligne['IDProjet']) {
		if($last!=$ligne['IDTheme']) {
			echo "<tr><td colspan='4' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
			//echo "<tr><td valign='top' colspan='4' bgColor='#DEDEDE'><b>".$ligne['NomTheme']."</b><br><i>Projet ".$ligne['LibelleTypeProjet']."</i></td></tr>";
			echo "<tr><td valign='top' colspan='4' bgColor='#DEDEDE'><b>".$ligne['NomTheme']."</b></td></tr>";
			//echo "<tr><td colspan='4' valign='bottom' valign='bottom' bgColor='#DEDEDE'></td></tr>";
			echo "<tr suivi$idSuivi='1' onclick='toggle(\"suivi$idSuivi\")'><td></td>";
			//$last = $ligne['IDProjet'];
			$last = $ligne['IDTheme'];
		} else {
			echo "<tr suivi$idSuivi='1' onclick='toggle(\"suivi$idSuivi\")'><td></td>";
		}
		//echo "<td valign='top'>".date('d.m.Y', strtotime($ligne['DateSaisie']))."</td><td>".wiki2html($ligne['RemarqueSuivi'])."</td><td align='center' valign='top'><a href='suiviEleve.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDSuivi=$ligne[IDSuivi]'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a></td></tr>";
		echo "<td valign='top'>".date('d.m.Y', strtotime($ligne['DateSaisie']))."</td><td>".wiki2html($ligne['Remarque'])."</td><td align='center' valign='top'><a href='suiviEleve.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDSuivi=$ligne[IDRemSuivi]&IDTheme=$IDTheme'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a></td></tr>";
		// seconde ligne pour modification
		echo "<tr suivi$idSuivi='1' style='display:none' onclick='toggle(\"suivi$idSuivi\")'><td></td>";
		echo "<td valign='top'><input type='texte' name='DateSuivi".$idSuivi."' size='8' maxlength='10' value='".date('d.m.Y', strtotime($ligne['DateSaisie']))."' onclick='limitEvent(event)'></input></td>";
		//echo "<td><textarea name='RemarqueSuivi".$idSuivi."' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$ligne['RemarqueSuivi']."</textarea></td>";
		echo "<td><textarea name='RemarqueSuivi".$idSuivi."' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$ligne['Remarque']."</textarea></td>";
		echo "<td valign='top'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitSuivi(\"$idSuivi\")'></td></tr>";
		// nl2br
		$cnt++;
	}
}
if ($cnt==0) {
	echo "<tr><td colspan='4' align='center'><i>Aucun enregistrement</i></td></tr>";
} 

// ligne d'ajout
echo "<tr><td colspan='4' bgColor='#5C5C5C'></td></tr>";
echo "<tr newProjet='1' ><td></td><td></td><td></td><td align='center'><img src='/iconsFam/add.png' onmouseover=\"Tip('Nouvelle remarque dans le suivi')\" onmouseout='UnTip()' onclick='toggle(\"newProjet\");' align='absmiddle'></td></tr>";
echo "<tr newProjet='1' style='display:none'><td colspan='4' valign='bottom' height='30'><b>Nouvelle entrée dans le suivi:<b></td></tr>";
echo "<tr newProjet='1' style='display:none'><td valign='top'><select name='IDThemeNew'>".$option."</select></td>";
echo "<td valign='top'><input name='DateSuivi' size='8' maxlength='10' value='".date('d.m.Y')."'></input></td>";
echo "<td valign='top'><textarea name='RemarqueSuivi' COLS=60 ROWS=20>'''Situation'''\n* Point 1\n* point 2\n\n'''Constats'''\n\n'''Décisions'''\n</textarea></td>";
echo "<td valign='top'><input type='submit' name='AjoutSuivi' value='Ajouter'></input></td></tr>";
echo "</table></div><br>";
?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
