<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:57
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: evalCoursCIE.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:10
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

$admin = hasAdminRigth();

if($admin) {
	if(isset($_GET['nom'])) {
		$nom = $_GET['nom'];
		$prenom = $_GET['prenom'];
		$IDEleve = $_GET['idEleve'];
		$classe = (isset($_GET['Classe'])?$_GET['Classe']:"");
	} else if(isset($_POST['nom'])) {
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$IDEleve = $_POST['IDEleve'];
		$classe = (isset($_POST['Classe'])?$_POST['Classe']:"");
	}
} else {
	// tentative de recherche par userid
	$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where Userid='".$_SESSION['user_login']."'";
    	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
	$nom = $ligne['Nom'];
	//echo "Nom: ".$nom;
	$prenom = $ligne['Prenom'];
	$IDEleve = $ligne['IDGDN'];
	$classe = $ligne['Classe'];
}

$IDCours = 0;
if(isset($_GET['IDCours'])) {
	$IDCours = $_GET['IDCours'];
} else if(isset($_POST['IDCours'])) {
	$IDCours = $_POST['IDCours'];
}


$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}

if(isset($_POST['evaluation'])) {
	// appr�ciation apprenti
	//print_r($_POST);
	$IDDocEleve=$_POST['IDDocEleve'];
	$role = $_POST['Role'];
	// effacer les �valuations existantes
	//$requete = "delete from appcompetencecie where IDDocEleve=".$IDDocEleve;
        //echo $requete."<br>";
	//mysqli_query($connexionDB,$requete);
	//echo $requete."<br>";
	foreach ($_POST as $key => $value) {
    		if (strpos($key, $role) === 0) {
			//echo $key." -> ".substr($key,3)." = ".$value."<br>";
			// recherche si d�ja existant
			$requete = "select * from appcompetencecie where IDCompetence=".substr($key,3)." and IDDocEleve=$IDDocEleve";
			//echo $requete."<br>";
			$result = mysqli_query($connexionDB,$requete);
			if(mysqli_num_rows($result)!=0) {
				// update
				$requete = "update appcompetencecie set Eval".$role."=$value where IDDocEleve=$IDDocEleve and IDCompetence=".substr($key,3);
			} else {
				// insert
				$requete = "insert into appcompetencecie (IDDocEleve,IDCompetence,Eval".$role.") values ($IDDocEleve,".substr($key,3).",$value)";
			}
			//echo $requete."<br>";
			mysqli_query($connexionDB,$requete);
    		}
	}
	// mise � jour du cours de l'�l�ve
	if($admin) {
		$requete = "update docelevecie set AbsencesEx=".$_POST['absencesEx'].", AbsencesNonEx=".$_POST['absencesNonEx'].", Encouragement='".addslashes($_POST['encouragement'])."'";
		if(!empty($_POST['dateDiscussion'])) {
			$requete .= ", DateDiscussion='".date("Y-m-d",strtotime($_POST['dateDiscussion']))."'";
		} else {
			$requete .= ", DateDiscussion=NULL";
		}
		if(!empty($_FILES['docSigne']['tmp_name'])) {
			$data = file_get_contents($_FILES['docSigne']['tmp_name']);
			if(!empty($data)) {
				$requete .= ", PDFSigne = '".mysqli_real_escape_string($data)."', Uploaded=1";
			}
		}
		$requete .= " where IDDocEleve=$IDDocEleve";
		//echo $requete."<br>";
		mysqli_query($connexionDB,$requete);
	}
	// mise � jour des observations
	foreach ($competencesCIE as $key => $value) {
		$obs = addslashes($_POST['observ'.$key]);
		if(!empty($obs)) {
			$requete = "select * from appbloccie where IDBlocRessource='".$key."' and IDDocEleve=$IDDocEleve";
			//echo $requete."<br>";
			$result = mysqli_query($connexionDB,$requete);
			if(mysqli_num_rows($result)!=0) {
				// update
				$requete = "update appbloccie set Observation='".$obs."' where IDDocEleve=$IDDocEleve and IDBlocRessource='".$key."'";
			} else {
				// insert
				$requete = "insert into appbloccie (IDDocEleve,IDBlocRessource,Observation) values ($IDDocEleve,'".$key."','".$obs."')";
			}
			//echo $requete."<br>";
			mysqli_query($connexionDB,$requete);
		} else {
			// delete
			$requete = "delete from appbloccie where IDDocEleve=$IDDocEleve and IDBlocRessource='".$key."'";
			mysqli_query($connexionDB,$requete);
		}
	}

}

function ponderationDis($nom,$pond,$disabled) {
	$txt = '';
	for($i=1;$i<=4;$i++) {
		$txt .= "<td width='10' align='center'><input type='radio' name='".$nom."' value='".$i."'";
		if($pond==$i) {
			$txt .= " checked";
		}
		if($disabled==1) {
			$txt .= " disabled";
		}
		$txt .= "></td>";
	}
	return $txt;
}

function ponderation($nom,$pond,$disabled,$mai) {
	$txt = '';
	for($i=1;$i<=4;$i++) {
		$txt .= "<td width='10' align='center'>";
		if($disabled==0||$mai!=0||$pond==0) {
			$txt .= "<input type='radio' name='".$nom."' value='".$i."'";
			if($pond==$i) {
				$txt .= " checked";
			}
			if($disabled!=0) {
				$txt .= " disabled";
			}
			$txt .= ">";

		} else {
			$txt .= "<img src='/iconsFam/contrast_low.png'>";
		}
		$txt .= "</td>";
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
function submitNewCompetence(doc) {
	//alert(theme);
	document.getElementById('myForm').actionCours.value='newCompetence';
	document.getElementById('myForm').doc.value=doc;
	document.getElementById('myForm').submit();
}


</script>
<?php
include("../../userInfo.php");
/* en-t�te */
echo "<FORM id='myForm' ACTION='evalCoursCIE.php'  METHOD='POST' enctype='multipart/form-data'>";
echo "<input type='hidden' name='actionCours' value=''>";
echo "<input type='hidden' name='IDEleve' value='".$IDEleve."'>";
echo "<input type='hidden' name='IDCours' value='".$IDCours."'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<div class='post'>";
echo "<center> <font color='#088A08'></font>";
echo "</center><br>";

// construction de la liste des documents
echo "<table border=0 width='100%'><tr><td><h2>";
if(isset($listeId)&&!empty($listeId)) {
	foreach($listeId as $key => $valeur) {
		if($valeur[0]==$IDEleve) {
			if($key!=0) {
				echo "<a href='evalCoursCIE.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."&IDCours=".$IDCours."'><img src='/iconsFam/resultset_previous.png'></a>";
			}
			echo $nom." ".$prenom;
			if($key<count($listeId)-1) {
				echo "<a href='evalCoursCIE.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."&IDCours=".$IDCours."'><img src='/iconsFam/resultset_next.png'></a>";
			}
			break;
		}
	}
}
echo "</h2></td><td align='right'>";

// liste des cours de l'�l�ve si ID renseign�
if(!empty($IDEleve)) {
	$requeteH = "SELECT cours.IDCours, doc.TitreCIE FROM docelevecie as el join courscie as cours on el.IDCours=cours.IDCours join doccie as doc on cours.IDDoc=doc.IDDoc WHERE IDEleve=$IDEleve AND pdfSigne is null order by TitreCIE,Dates";
	//echo $requeteH."<br>";
	$resultat =  mysqli_query($connexionDB,$requeteH);
	$options = "<option value='0'></option>";
	$cntCours = 0;
	while($ligne = mysqli_fetch_assoc($resultat)) {
		$options .= "<option value='".$ligne['IDCours']."'";
		if($IDCours==$ligne['IDCours']) {
			$options .= " selected";
		}
		$options .= ">".$ligne['TitreCIE']."</option>";
		$cntCours++;
	}
	if($cntCours>0) {
		echo "<b>Cours CIE:</b> <select name='IDCours' onChange='document.getElementById(\"myForm\").submit();'>".$options."</select>";
	}
}

echo "</td></tr></table><br>";

if(!empty($IDCours)&&!empty($IDEleve)) {
// recherche du document
$requeteH = "SELECT doc.TitreCIE, doc.IDDoc, el.IDDocEleve, el.AbsencesEx, el.AbsencesNonEx, el.Encouragement, el.DateDiscussion, el.Uploaded FROM docelevecie as el join courscie as cours on el.IDCours=cours.IDCours join doccie as doc on cours.IDDoc=doc.IDDoc WHERE el.IDEleve=".$IDEleve." AND cours.IDCours= ".$IDCours;
//echo $requeteH."<br>";
$resultatH =  mysqli_query($connexionDB,$requeteH);
$docinfo = mysqli_fetch_assoc($resultatH);
echo "<br><div id='corners'>";
echo "<div id='legend'>Auto-�valuation du cours CIE</div>";
echo "<table id='hor-minimalist-b' width='100%'>\n";
echo "<tr><th width='250' colspan='2'>".$docinfo['TitreCIE']."</th><th width='10' align='center'>A</th><th width='10' align='center'>B</th><th width='10' align='center'>C</th><th width='10' align='center'>D</th>";
$colspan = 6;
if($admin) {
	// partie prof
	echo "<th width='2' bgcolor='#EEEEEE'></th><th width='10' align='center'>A</th><th width='10' align='center'>B</th><th width='10' align='center'>C</th><th width='10' align='center'>D</th>";
	$colspan = 11;
}
echo "</tr>";

// recherche des observations
$requeteObs = "SELECT * from appbloccie where IDDocEleve=".$docinfo['IDDocEleve'];
$resultatObs =  mysqli_query($connexionDB,$requeteObs);
if($resultatObs) {
	$obsArray = array();
	while($obsLigne = mysqli_fetch_assoc($resultatObs)) {
		$obsArray[$obsLigne['IDBlocRessource']] = $obsLigne['Observation'];
	}
}

$requeteH = "SELECT com.Numero, com.Description, com.IDCompetence, app.EvalAPP, app.EvalMAI FROM competencedoccie as cdc left join competencecie as com on cdc.IDCompetence=com.IDCompetence left join appcompetencecie app on com.IDCompetence=app.IDCompetence and IDDocEleve=".$docinfo['IDDocEleve']." where IDDoc= ".$docinfo['IDDoc']." order by IDBlocRessource, Numero";
//echo $requete."<br>";
$resultatH =  mysqli_query($connexionDB,$requeteH);
//$doc = 0;
$ressource = "";
while($ligne = mysqli_fetch_assoc($resultatH)) {

	if(!empty($ligne['Numero'])) {
		if($ressource!=substr($ligne['Numero'], 0, 3 )) {
			// insertion d'une zone d'observation de la cat�gorie pr�c�dente
			if($admin&&!empty($ressource)) {
				echo "<tr><td width='10'></td><td valign='top'>Observations:</td><td colspan='9'><textarea name='observ".$ressource."' COLS=40 ROWS=4>".(isset($obsArray[$ressource])?$obsArray[$ressource]:"")."</textarea></td></tr>";
				echo "<tr><td colspan='".$colspan."' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
			}
			$ressource = substr($ligne['Numero'], 0, 3 );
			echo "<tr><td colspan='".$colspan."'><b><i>".$competencesCIE[$ressource]."</i><br>".$ressourcesExplicatif[$ressource]."</b></td></tr>";
		}
		echo "<tr><td width='10'></td><td>".$ligne['Numero']." ".$ligne['Description']."</td>";

		if($admin) {
			echo ponderation("APP".$ligne['IDCompetence'],$ligne['EvalAPP'],1,$ligne['EvalMAI']);
			//echo ponderation("APP".$ligne['IDCompetence'],$ligne['EvalAPP'],0,0);
			echo "<td bgcolor='#EEEEEE'></td>";
			echo ponderation("MAI".$ligne['IDCompetence'],$ligne['EvalMAI'],0,0);
		} else {
			echo ponderation("APP".$ligne['IDCompetence'],$ligne['EvalAPP'],0,0);
		}
		echo "</tr>";
	}
}
if($admin) {
	if(!empty($ressource)) {
		echo "<tr><td width='10'></td><td valign='top'>Observations:</td><td colspan='9'><textarea name='observ".$ressource."' COLS=40 ROWS=4>".(isset($obsArray[$ressource])?$obsArray[$ressource]:"")."</textarea></td></tr>";
	}
	echo "<tr><td colspan='".$colspan."' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
	echo "<tr><td colspan='2' valign='top'><b>Observations et mesures d'encouragement:</b></td><td colspan='9'><textarea name='encouragement' COLS=40 ROWS=4>".$docinfo['Encouragement']."</textarea></td></tr>";
	echo "<tr><td colspan='2' valign='top'><b>Absences excus�es:</b></td><td colspan='9'><input type='text' name='absencesEx' size='2' value='".$docinfo['AbsencesEx']."'></input></td></tr>";
	echo "<tr><td colspan='2' valign='top'><b>Absences non excus�es:</b></td><td colspan='9'><input type='text' name='absencesNonEx' size='2' value='".$docinfo['AbsencesNonEx']."'></input></td></tr>";
	echo "<tr><td colspan='2' valign='top'><b>Date de discussion de l'�valuation avec l'apprenti:</b></td><td colspan='9'><input type='text' name='dateDiscussion' size='10' ";
	if($docinfo['DateDiscussion']!=0) {
		echo "value='".date('d.m.Y', strtotime($docinfo['DateDiscussion']))."'";
	}
	echo "></input></td></tr>";
	echo "<tr><td colspan='2' valign='top'><b>Document sign�:</b></td><td colspan='9'><input type='hidden' name='MAX_FILE_SIZE' value='5000000'><input type='file' name='docSigne'>";
	if($docinfo['Uploaded']==1)  {
		echo " <img src='/iconsFam/tick.png'> <a href='lireScanCIE.php?IDEleve=".$IDEleve."&IDCours=".$IDCours."' target='pdf'><img src='/iconsFam/page_white_acrobat.png'></a>";
	} else {
		echo " <img src='/iconsFam/cross.png'>";
	}
	echo "</td></tr>";
	echo "<tr><td colspan='".$colspan."' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
	echo "<tr><td colspan='".$colspan."' align='right'><input type='submit' name='evaluation' value='Valider'><input type='hidden' name='IDDocEleve' value='".$docinfo['IDDocEleve']."'><input type='hidden' name='Role' value='MAI'></td></tr>";
	//echo "<tr><td colspan='".$colspan."' align='right'><input type='submit' name='evaluation' value='Valider'><input type='hidden' name='IDDocEleve' value='".$docinfo['IDDocEleve']."'><input type='hidden' name='Role' value='APP'></td></tr>";
} else {
	echo "<tr><td colspan='".$colspan."' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
	echo "<tr><td colspan='".$colspan."' align='right'><input type='submit' name='evaluation' value='Valider'><input type='hidden' name='IDDocEleve' value='".$docinfo['IDDocEleve']."'><input type='hidden' name='Role' value='APP'></td></tr>";
}


echo "</table>";

echo "<br><b>Echelle d'�valuation:</b><br><table border=0>";
echo "<tr><td width='20'><b>A</b></td><td>Exigences d�pass�es</td></tr>";
echo "<tr><td width='20'><b>B</b></td><td>Exigences atteintes</td></tr>";
echo "<tr><td width='20'><b>C</b></td><td>Exigences juste atteintes, mesures de soutien n�cessaires</td></tr>";
echo "<tr><td width='20'><b>D</b></td><td>Exigences pas atteintes, mesures particuli�res n�cessaires</td></tr>";
echo "</table></div>";
}
//if($cntCours==0) {
//	echo "<center>Aucune �valuation en cours.</center>";
//}
?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
