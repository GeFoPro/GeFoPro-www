<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:54
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: docEleveCIE.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:99
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

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

if(isset($_GET['effacerCours'])) {
	$IDCours = $_GET['effacerCours'];
	// effacer le cours
	$requete = "DELETE FROM docelevecie where IDCours=$IDCours and IDEleve=$IDEleve";
	$resultat =  mysqli_query($connexionDB,$requete);
	//echo $requete;
}
include("entete.php");
//if(!hasAdminRigth()) {
//	echo "<br><br><center><b>Contenu non autorisé.</b></center><br><br>";
//	exit;
//}
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
</script>
<?php
include("../../userInfo.php");
/* en-tête */

echo "<FORM id='myForm' ACTION='docEleveCIE.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";

echo "<div class='post'>";
if(empty($msg)) {
	echo "<center> <font color='#088A08'></font></center>";
} else {
	echo "<center>".$msg."</center>";
}
if(hasAdminRigth()) {
	echo "<br><h2>";
	if(isset($listeId)&&!empty($listeId)) {
		foreach($listeId as $key => $valeur) {
			if($valeur[0]==$IDEleve) {
				if($key!=0) {
					echo "<a href='docEleveCIE.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img id='prev' src='/iconsFam/resultset_previous.png'></a>";
				}
				echo $nom." ".$prenom;
				if($key<count($listeId)-1) {
					echo "<a href='docEleveCIE.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img id='next' src='/iconsFam/resultset_next.png'></a>";
				}
				break;
			}
		}
	}
	echo "</h2><br>\n";
}
echo "<br><br><div id='corners'>";
echo "<div id='legend'>Cours CIE</div>";
echo "<table id='hor-minimalist-b' width='100%'>\n";
echo "<tr><th width='250'>Thèmes</th><th width='150'>Dates</th><th width='30'>Jours</th><th align='center'>AE/ANE</th>";
if(hasAdminRigth()) {
	echo "<th align='center'>Eval APP</th><th align='center'>Eval MAI</th><th width='10'>Doc</th>";
} else {
	echo "<th align='center'></th><th align='center'></th><th width='10'></th>";
}
echo "<th width='10'>Scan</th><th width='10'></th></tr>";

// requete pour liste des cours trouvé pour l'élève
$requeteH = "SELECT el.IDCours, doc.TitreCIE, cours.Dates, cours.NbrJours, el.PDFSigne, el.AbsencesEx, el.AbsencesNonEx, sum(if(EvalAPP is NULL OR EvalAPP = 0,0,1)) as evalAPP, sum(if(EvalMAI is NULL OR EvalMAI = 0,0,1)) as evalMAI FROM docelevecie as el join courscie as cours on el.IDCours=cours.IDCours join doccie as doc on cours.IDDoc=doc.IDDoc left join appcompetencecie as app on el.IDDocEleve=app.IDDocEleve WHERE IDEleve=$IDEleve group by el.IDCours order by TitreCIE,Dates";
//echo $requeteH;
$resultat =  mysqli_query($connexionDB,$requeteH);
$cntJours = 0;
$absencesEx = 0;
$absencesNonEx = 0;
while($ligne = mysqli_fetch_assoc($resultat)) {
	if(!empty($ligne['PDFSigne'])&&!hasAdminRigth()) {
		echo "<tr>";
	} else {
		echo "<tr onClick='document.location.href=\"evalCoursCIE.php?nom=".$nom."&prenom=".$prenom."&idEleve=".$IDEleve."&IDCours=".$ligne['IDCours']."\"' >";
	}
	echo "<td>".$ligne['TitreCIE']."</td><td>".$ligne['Dates']."</td><td align='center'>".$ligne['NbrJours']."</td>";
	echo "<td align='center'>".$ligne['AbsencesEx']."/".$ligne['AbsencesNonEx']."</td>";
	if(hasAdminRigth()) {
		echo "<td align='center'>".$ligne['evalAPP']."</td><td align='center'>".$ligne['evalMAI']."</td>";
		echo "<td align='center'><a href='impressionCIE.php?IDEleve=".$IDEleve."&IDCours=".$ligne['IDCours']."''><img src='/iconsFam/page_word.png'></a></td>";
	} else {
		echo "<td align='center'></td><td align='center'></td>";
		echo "<td align='center'></td>";
	}
	echo "<td align='center'>";
	if(!empty($ligne['PDFSigne'])) {
		echo "<a href='lireScanCIE.php?IDEleve=".$IDEleve."&IDCours=".$ligne['IDCours']."'><img src='/iconsFam/page_white_acrobat.png'></a>";
	}
	echo "</td><td align='center'>";
	if(empty($ligne['PDFSigne'])&&$ligne['evalAPP']==0&&$ligne['evalMAI']==0&&hasAdminRigth()) {
		echo "<a href='docEleveCIE.php?nom=".$nom."&prenom=".$prenom."&idEleve=".$IDEleve."&effacerCours=".$ligne['IDCours']."'><img src='/iconsFam/table_row_delete.png'></a>";
	}
	echo "</td></tr>";
	$cntJours += $ligne['NbrJours'];
	$absencesEx += $ligne['AbsencesEx'];
	$absencesNonEx += $ligne['AbsencesNonEx'];
}
echo "<tr><td colspan='9' valign='bottom' bgColor='#5C5C5C'></td></tr>";
if(!empty($cntJours)) {
	echo "<tr><td colspan='2'>Total</td><td align='center'>$cntJours</td><td align='center'>".$absencesEx."/".$absencesNonEx."</td><td colspan='5'></td></tr>";
} else {
	echo "<tr><td colspan='9' align='center'>Aucun cours trouvé</td></tr>";
}
//echo "<tr><td colspan='9' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";

//echo "<tr newCours='1' ><td colspan='3'></td><td align='center'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un cours')\" onmouseout='UnTip()' onclick='toggle(\"newCours\");' align='absmiddle'></td></tr>";

//echo "<tr newCours='1' style='display:none' ><td colspan='4' valign='bottom' height='30'><b>Ajouter cours CIE:<b></td></tr>";
//echo "<tr newCours='1' style='display:none' >";
//echo "<td valign='top' colspan='2'><input name='NomTheme' value='' size='40'></input></td>";
//echo "<td valign='top' colspan='2' align='right'><input type='submit' name='ajoutCours' value='Ajouter'></input></td></tr>";

echo "</table></div>";
?>


</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
