<?php 
include("../../appHeader.php");

$annee = date('Y');
$anneeTri = $annee;
if(isset($_POST['annee'])) {
	$anneeTri = $_POST['annee'];
}
if(isset($_POST['inscrire'])) {
	$classeIns = $_POST['classe'];
	$coursIns = $_POST['ajCours'];
	//echo "Recherche pour ".$classeIns." au cours ".$coursIns;
	$requete = "SELECT IDGDN from elevesbk where classe like '".$classeIns."%' and IDGDN not in (select IDEleve from docelevecie where IDCours=".$coursIns.")";
	$resultat =  mysql_query($requete);
	//echo $requete;
	if(!empty($resultat)) {
		while($ligne = mysql_fetch_assoc($resultat)) {
			//echo "<br>inscrire ".$ligne['IDGDN'].": ";
			$requeteIns = "INSERT INTO docelevecie (IDEleve, IDCours) values (".$ligne['IDGDN'].", ".$coursIns.")";
			//echo "<br>".$requeteIns;
			$resultatIns =  mysql_query($requeteIns);
		}
	}
}
if(isset($_POST['ajouterCours'])) {
	$docAj = $_POST['IDDoc'];
	$datesAj = $_POST['Dates'];
	$joursAj = $_POST['Jours'];
	$respAj = $_POST['Responsable'];
	$requete = "INSERT INTO courscie (IDDoc,Dates,NbrJours,Responsable) value ($docAj,\"$datesAj\",$joursAj,\"$respAj\")";
	//echo $requete;
	$resultat =  mysql_query($requete);
}

if(isset($_GET['effacerCours'])) {
	$IDCours = $_GET['effacerCours'];
	// effacer cours
	$requete = "DELETE FROM courscie where IDCours=$IDCours";
	$resultat =  mysql_query($requete);
	// rechercher les évlauationa et observation pour chaque inscriptions à effacer
	$requete = "SELECT IDDocEleve from docelevecie where IDCours=$IDCours";
	$resultat =  mysql_query($requete);
	while($ligne = mysql_fetch_assoc($resultat)) {
		// évaluations
		$requeteDel = "DELETE FROM appcompetencecie where IDDocEleve=".$ligne['IDDocEleve'];
		mysql_query($requeteDel);
		// observation
		$requeteDel = "DELETE FROM appbloccie where IDDocEleve=".$ligne['IDDocEleve'];
		mysql_query($requeteDel);
	}
	// effacer fianlement les inscription
	$requete = "DELETE FROM docelevecie where IDCours=$IDCours";
	$resultat =  mysql_query($requete);
	//echo $requete;
}

if(isset($_GET['effacerInscription'])) {
	$IDCours = $_GET['effacerInscription'];
	// rechercher les évlauations et observation pour chaque inscription à effacer
	$requete = "SELECT IDDocEleve from docelevecie where IDCours=$IDCours";
	$resultat =  mysql_query($requete);
	while($ligne = mysql_fetch_assoc($resultat)) {
		// évaluations
		$requeteDel = "DELETE FROM appcompetencecie where IDDocEleve=".$ligne['IDDocEleve'];
		mysql_query($requeteDel);
		// observation
		$requeteDel = "DELETE FROM appbloccie where IDDocEleve=".$ligne['IDDocEleve'];
		mysql_query($requeteDel);
	}
	$requete = "DELETE FROM docelevecie where IDCours=$IDCours";
	$resultat =  mysql_query($requete);

}
if(isset($_GET['effacerEvaluation'])) {
	$IDCours = $_GET['effacerEvaluation'];
	// rechercher les évlauations et observation pour chaque inscription concernée
	$requete = "SELECT IDDocEleve from docelevecie where IDCours=$IDCours";
	$resultat =  mysql_query($requete);
	while($ligne = mysql_fetch_assoc($resultat)) {
		// évaluations
		$requeteDel = "DELETE FROM appcompetencecie where IDDocEleve=".$ligne['IDDocEleve'];
		mysql_query($requeteDel);
		// observation
		$requeteDel = "DELETE FROM appbloccie where IDDocEleve=".$ligne['IDDocEleve'];
		mysql_query($requeteDel);
	}
}
$id = 0;
if(isset($_GET['effacerCompetence'])) {
	$comp = $_GET['effacerCompetence'];
	$id = $_GET['IDDoc'];
	$requete = "DELETE FROM competencedoccie where IDCompetence=$comp AND IDDoc=$id";
	//echo $requete;
	$resultat =  mysql_query($requete);
}

if(isset($_POST['actionCours'])) {
	if("newCompetence"==$_POST['actionCours']) {
		$id = $_POST['doc'];
		$comp = $_POST['comp'.$id];
		$requete = "INSERT INTO competencedoccie (IDCompetence, IDDoc) values ($comp,$id)";	
		$resultat =  mysql_query($requete);
	}
}

if(isset($_POST['ajouterDoc'])) {
	$titreAj = $_POST['NomDoc'];
	$versionAj = $_POST['VersionDoc'];
	
	$requete = "INSERT INTO doccie (TitreCIE,Version) value (\"$titreAj\",\"$versionAj\")";
	//echo $requete;
	$resultat =  mysql_query($requete);
}
if(isset($_GET['effacerDoc'])) {
	$id = $_GET['effacerDoc'];
	$requete = "DELETE FROM doccie where IDDoc=$id";
	//echo $requete;
	$resultat =  mysql_query($requete);
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
	input=document.getElementsByTagName('input')
	for (i=0;i<input.length;i++){
		if (input[i].getAttribute(thisname)){
			if ( input[i].style.display=='none' ){
				input[i].style.display = '';
			} else {
				input[i].style.display = 'none';
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

if(<?=$id?>!=0) {
	toggle(doc<?=$id?>);
}

</script>
<?
include($app_section."/userInfo.php");
/* en-tête */
echo "<FORM id='myForm' ACTION='listeCoursCIE.php'  METHOD='POST'>";
echo "<input type='hidden' name='actionCours' value=''>";
echo "<input type='hidden' name='cours' value=''>";
echo "<input type='hidden' name='doc' value=''>";
echo "<div class='post'>";
echo "<center> <font color='#088A08'></font>";
echo "</center>";

$optionAnnee = "";
for($cntA=0;$cntA<5;$cntA++) {
	$optionAnnee .= "<option value='".($annee-$cntA)."'";
	if(($annee-$cntA)==$anneeTri) {
		$optionAnnee .= " selected ";	
	}
	$optionAnnee .= ">".($annee-$cntA)."</option>";
}
// construction de la liste des cours
echo "<br><table border=0 width='100%'><tr><td><!-- h2>Liste des cours CIE</h2 --></td><td align='right'>Année civile: <select name='annee' onchange='submit();'>".$optionAnnee."</select></td></tr></table>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Liste des cours CIE</div>";
echo "<table id='hor-minimalist-b' width='100%'>\n";
echo "<tr><th width='250'>Cours</th><th width='80'>Version CC</th><th width='150'>Dates du cours</th><th width='50'>Jours</th><th width='100'>Responsable</th><th>Inscrits</th><th>Scannés</th><th>Eval APP</th><th>Eval MAI</th><th></th></tr>";
//$requeteH = "SELECT TitreCIE, Version, cours.IDCours, Dates, NbrJours, Responsable, sum(if(IDEleve is NULL,0,1)) as Inscrits, sum(if(''=PDFSigne OR PDFSigne is NULL, 0, 1)) AS pdf FROM courscie as cours join doccie as doc on cours.IDDoc=doc.IDDoc left join docelevecie as docel on cours.IDCours=docel.IDCours group by cours.IDCours order by TitreCIE, Dates";
$requeteH = "SELECT TitreCIE, Version, cours.IDCours, Dates, NbrJours, Responsable, sum(if(IDEleve is NULL,0,1)) as Inscrits, coalesce(sum(Uploaded)) AS pdf FROM courscie as cours join doccie as doc on cours.IDDoc=doc.IDDoc left join docelevecie as docel on cours.IDCours=docel.IDCours where Dates like '%".$anneeTri."' group by cours.IDCours order by TitreCIE, Dates";
//echo $requeteH;
$resultat =  mysql_query($requeteH);
$totalJour=0;
//$totalApp=0;
while($ligne = mysql_fetch_assoc($resultat)) {
	echo "<tr>";
	echo "<td><input type='radio' name='ajCours' value='".$ligne['IDCours']."' insCours='1' style='display:none'><b> ".$ligne['TitreCIE']."</b></td>";
	echo "<td align='center'>".$ligne['Version']."</td>";
	echo "<td>".$ligne['Dates']."</td>";
	echo "<td align='center'>".$ligne['NbrJours']."</td>";
	$totalJour = $totalJour + $ligne['NbrJours'];
	echo "<td>".$ligne['Responsable']."</td>";
	echo "<td align='center'>".$ligne['Inscrits']."</td>";
	echo "<td align='center'";
	if($ligne['Inscrits']!=$ligne['pdf']) {
		echo " style='color:#FF0000'";
	}
	echo ">".$ligne['pdf']."</td>";
	// recherche des éventuelles évaluations présentes
	$requeteEval = "select IDEleve from docelevecie as docel join appcompetencecie as app on docel.IDDocEleve=app.IDDocEleve where IDCours=".$ligne['IDCours']." and EvalAPP <> 0 group by IDEleve";
	$resultEval = mysql_query($requeteEval);
	$num = mysql_num_rows($resultEval);
	echo "<td align='center'";
	if($num!=$ligne['Inscrits']&&$ligne['Inscrits']!=$ligne['pdf']) {
		echo " style='color:#FF0000'";
	}
	echo ">".$num."</td>";
	$requeteEval = "select IDEleve from docelevecie as docel join appcompetencecie as app on docel.IDDocEleve=app.IDDocEleve where IDCours=".$ligne['IDCours']." and EvalMAI <> 0 group by IDEleve";
	$resultEval = mysql_query($requeteEval);
	$num = mysql_num_rows($resultEval);
	echo "<td align='center'";
	if($num!=$ligne['Inscrits']&&$ligne['Inscrits']!=$ligne['pdf']) {
		echo " style='color:#FF0000'";
	}
	echo ">".$num."</td>";
	echo "<td align='center'>";
	if($ligne['pdf']==0) {
		echo "<a href='listeCoursCIE.php?effacerCours=".$ligne['IDCours']."' onclick='return confirm(\"Supprimer ce cours ainsi que toutes les évaluations déjà saisies?\")'><img src='/iconsFam/book_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer ce cours')\" onmouseout='UnTip()'></a>";
		if($ligne['Inscrits']!=0) {
			echo "<a href='listeCoursCIE.php?effacerInscription=".$ligne['IDCours']."' onclick='return confirm(\"Désinscrire la classe et supprimer toutes les évaluations déjà saisies?\")'><img src='/iconsFam/user_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer les inscriptions')\" onmouseout='UnTip()'></a>";
		}
	}
	else if($ligne['pdf']==$ligne['Inscrits']) {
		// possibilité de purger les évaluations et observations
		echo "<a href='listeCoursCIE.php?effacerEvaluation=".$ligne['IDCours']."' onclick='return confirm(\"Supprimer les évaluations saisies et ne conserver que les documents scannés?\")'><img src='/iconsFam/folder_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer les évaluations')\" onmouseout='UnTip()'></a>";
	}
	echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='10' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
echo "<tr><td colspan='3'><b>Total</b></td><td align='center'>".sprintf("%01.1f",$totalJour)."</td><td colspan='5'></td><td align='center'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un cours')\" onmouseout='UnTip()' onclick='toggle(\"newCours\");' align='absmiddle'><img src='/iconsFam/user_add.png' onmouseover=\"Tip('Inscrire à un cours')\" onmouseout='UnTip()' onclick='toggle(\"insCours\");' align='absmiddle'></td></tr>";
echo "<tr newCours='1' style='display:none' ><td colspan='10' valign='bottom' height='30'><b>Ajouter un cours CIE:</b></td></tr>";
// liste des documents
$requeteD = "SELECT * from doccie ORDER BY TitreCIE";
$resultatD =  mysql_query($requeteD);
$options = "";
while($li = mysql_fetch_assoc($resultatD)) {
	$options .= "<option value='".$li['IDDoc']."'>".$li['TitreCIE']." (".$li['Version'].")</option>"; 
}

echo "<tr newCours='1' style='display:none'><td colspan='2'><select name='IDDoc'>".$options."</select></td><td><input name='Dates' value='' size='15'></td><td><input name='Jours' value='' size='1'></td><td colspan='2'><input name='Responsable' value='' size='15'></td><td colspan='4' align='right'><input type='submit' name='ajouterCours' value='Ajouter'></td></tr>";
echo "<tr insCours='1' style='display:none'><td colspan='10' valign='bottom' height='30'><b>Inscriptions aux cours CIE:</b></td></tr>";
echo "<tr insCours='1' style='display:none'><td colspan='8'>Inscrire la classe <select name='classe'><option value=''></option><option value='".$app_section." 1'>".$app_section." 1</option><option value='".$app_section." 2'>".$app_section." 2</option><!-- option value='".$app_section." 3'>".$app_section." 3</option --></select> pour le cours sélectionné ci-dessus</td><td colspan='2' align='right'><input type='submit' name='inscrire' value='Inscrire'></td></tr>";
echo "</table></div><br>";

// construction de la liste des documents
echo "<br><table border=0 width='100%'><tr><td><!-- h2>Liste des documents \"Contrôle de compétences\" et contenu</h2 --></td><td align='right'></td></tr></table>";
echo "<br><div id='corners'>";
echo "<div id='legend'>Liste des documents \"Contrôle de compétences\" et contenu</div>";
echo "<table id='hor-minimalist-b' width='100%'>\n";
echo "<tr><th width='250' colspan='3'>Document</th><th width='150'>Version</th><th></th width='10'></tr>";
// liste des ressources
$requeteR = "SELECT * from competencecie where Archive is null ORDER BY IDBlocRessource,Numero";
$resultatR =  mysql_query($requeteR);
$optionsR = "<option value='0'></option>";
while($liR = mysql_fetch_assoc($resultatR)) {
	$optionsR .= "<option value='".$liR['IDCompetence']."'>".$liR['Numero']." ";
	//$optionsR .= $liR['Niveau']." ";
	if($liR['Niveau']=='P') {
		$optionsR .= " - ";
	} else {
		$optionsR .= " --- ";
	}	
	if(strlen($liR['Description'])>100) {
		$optionsR .= substr($liR['Description'],0,100)."...";
	} else {
		$optionsR .= $liR['Description'];
	}
	$optionsR .= "</option>";
	 
}
//$requeteH = "SELECT doc.IDDoc, doc.TitreCIE, doc.Version, com.Numero, com.Description, com.IDCompetence, sum(if(IDAppComp is NULL,0,1)) as eval FROM doccie as doc left join competencedoccie as cdc on doc.IDDoc=cdc.IDDoc left join competencecie as com on cdc.IDCompetence=com.IDCompetence left join appcompetencecie as app on com.IDCompetence=app.IDCompetence group by doc.IDDoc,com.IDCompetence order by TitreCIE, IDBlocRessource, Numero";
$requeteH = "SELECT doc.IDDoc, doc.TitreCIE, doc.Version, com.Numero, com.Description, com.IDCompetence FROM doccie as doc left join competencedoccie as cdc on doc.IDDoc=cdc.IDDoc left join competencecie as com on cdc.IDCompetence=com.IDCompetence order by TitreCIE, IDBlocRessource, Numero";
//echo "<tr><td>".$requeteH."</td></tr>";
$resultatH =  mysql_query($requeteH);
$doc = 0;
$ressource = "";
while($ligne = mysql_fetch_assoc($resultatH)) {
	if($doc!=$ligne['IDDoc']) {
		if($doc!=0) {
			echo "<tr doc$doc='1' style='display:none'><td width='25'></td><td width='10'></td><td colspan='2'><select name='comp".$doc."' onChange='submitNewCompetence(".$doc.")'>".$optionsR."</select></td><td></td></tr>";
		}
		$doc = $ligne['IDDoc'];
		$ressource = "";
		echo "<tr><td colspan='3'><img src='/iconsFam/Bullet_arrow_down.png' onclick='toggle(\"doc$doc\")'><b>".$ligne['TitreCIE']."</td><td>".$ligne['Version']."</b></td>";
		if(!empty($ligne['Numero'])) {
			// terminer la ligne avec lien sur document
			echo "<td width='10'><a href='impressionBaseCIE.php?IDDoc=".$doc."'><img src='/iconsFam/page_word.png'></a></td></tr>";
		} else {
			// permettre d'effacer le document
			echo "<td width='10'><a href='listeCoursCIE.php?effacerDoc=".$doc."'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer ce document')\" onmouseout='UnTip()'></a></td></tr>";
		} 
	}
	if(!empty($ligne['Numero'])) {
		if($ressource!=substr($ligne['Numero'], 0, 3 )) {
			$ressource = substr($ligne['Numero'], 0, 3 );
			echo "<tr doc$doc='1' style='display:none'><td width='25'></td><td colspan='4'><i>".$competencesCIE[$ressource]."</i></td></tr>";
		}
		echo "<tr doc$doc='1' style='display:none'><td width='25'></td><td width='10'></td><td>".$ligne['Numero']." ".$ligne['Description']."</td><td></td>";
		echo "<td align='right'>";
		//if($ligne['eval']==0) {
			echo "<a href='listeCoursCIE.php?effacerCompetence=".$ligne['IDCompetence']."&IDDoc=".$doc."'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette compétence')\" onmouseout='UnTip()'></a>";
		//}
		echo "</td></tr>";
	} 
}
if($doc!=0) {
	echo "<tr doc$doc='1' style='display:none'><td width='25'></td><td width='10'></td><td colspan='2'><select name='comp".$doc."' onChange='submitNewCompetence(".$doc.")'>".$optionsR."</select></td><td></td></tr>";
}

echo "<tr><td colspan='5' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
echo "<tr><td colspan='4'></td><td align='center'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un document')\" onmouseout='UnTip()' onclick='toggle(\"newDoc\");' align='absmiddle'></td></tr>";
echo "<tr newDoc='1' style='display:none' ><td colspan='5' valign='bottom' height='30'><b>Ajouter un document de contrôle de compétences:</b></td></tr>";
echo "<tr newDoc='1' style='display:none' ><td colspan='3'><input type='text' name='NomDoc' size='30'></input></td><td><input type='text' name='VersionDoc' size='10'></td><td><input type='submit' name='ajouterDoc' value='Ajouter'></input></td></tr>";
echo "</table></div>"; 

?>
<script>
if(<?=$id?>!=0) {
	toggle("doc<?=$id?>");
}
</script>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>