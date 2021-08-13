<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:54
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: theorie.php
# @Last modified by:   degehi
# @Last modified time: 25.06.2021 15:03:99
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");
date_default_timezone_set('Europe/Zurich');
$noSemaine = date('W');
$anneeCalc = date('Y');
$semestreAct = 1;
if($noSemaine<30 && $noSemaine>=$noSemaineSem2) {
	$semestreAct = 2;
	// utilisation de l'année précédente pour le 2ème semestre
	$anneeCalc = $anneeCalc - 1; 
}
function arrondi($note, $fact) {
	return round($note*$fact)/$fact;
}

if(isset($_GET['vue'])&& 'app'==$_GET['vue']) {
	reconnect_app();
}

if(hasAdminRigth()) {
	if(isset($_GET['nom'])) {
		$nom = $_GET['nom'];
		$prenom = $_GET['prenom'];
		$IDEleve = $_GET['idEleve'];
		$classe = (isset($_GET['Classe'])?$_GET['Classe']:"");
	} else if(isset($_POST['nom'])) {
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$IDEleve = $_POST['IDEleve'];
		$classe = $_POST['Classe'];
	}
	$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where el.IDGDN=".$IDEleve;
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
	$classe = $ligne['Classe'];
} else {
	if(isset($_GET['vue'])&& 'app'==$_GET['vue']) {
		$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where el.IDGDN=".$IDEleve;
	} else {
		// tentative de recherche par userid
		$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where Userid='".$_SESSION['user_login']."'";
    }
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
	$nom = $ligne['Nom'];
	//echo "Nom: ".$nom;
	$prenom = $ligne['Prenom'];
	$IDEleve = $ligne['IDGDN'];
	//echo "IDEleve=".$IDEleve;
	$classe = $ligne['Classe'];
	if(isset($_GET['vue'])&& 'app'==$_GET['vue']) {
		$_SESSION['user_login'] = $ligne['Userid'];
		$_SESSION['user_nom'] = $ligne['Nom']." ".$ligne['Prenom'];
	}
}

if($_POST['actionNote']=='actionUpdNote') {
	// recherche de l'arrondi de la saisie de la note
	$requete = "select ArrondiNote from planept as plan join noteept as note on plan.Branche=note.IDBranche and plan.Annee = note.Annee and (plan.NoSemestre = note.NoSemestre OR plan.NoSemestre = 0) where IDNoteEPT=".$_POST['note'];
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
	$fact = 10;
	if(!empty($ligne)) {
		$fact = $ligne['ArrondiNote'];
	}
	// mise à jour de la note ou suppression
	if(empty($_POST['valueUpd'])) {
		$requete = "delete from noteept where IDNoteEPT=".$_POST['note'];
	} else {
		$requete = "update noteept set ".$_POST['fieldUpd']."='".addslashes(arrondi($_POST['valueUpd'],$fact))."' where IDNoteEPT=".$_POST['note'];
	}
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
}

if(!empty($_POST['ajouterNote'])) {
	// ajouter une note
	$requete = "INSERT INTO noteept (Note, IDEleve, IDBranche, NoSemestre, Annee, Classe) values ('".$_POST['NewNote']."',".$IDEleve.",".$_POST['NewIDBranche'].",".$semestreAct.",".$anneeCalc.",'".$classe."')";
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
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
function writeMoy(branche, moy) {
	
	td=document.getElementsByTagName('td')
	for (i=0;i<td.length;i++){
		if (td[i].getAttribute('idbranche')){
			if(td[i].getAttribute('idbranche')==branche) {
				td[i].innerHTML = moy;
				//alert(branche+" "+moy);
			}
		}
	}
}
function updateField(id,field,value) {
	document.getElementById('myForm').actionNote.value='actionUpdNote';
	document.getElementById('myForm').note.value=id;
	document.getElementById('myForm').fieldUpd.value=field;
	document.getElementById('myForm').valueUpd.value=value;
	//alert("Update theme "+id+", champ "+field+", valeur "+value);
	document.getElementById('myForm').submit();
}
</script>
<?php

function generate_branch ($IDBranche, $LibelleBranche, $notes, $notesID, $arrondiFact)  {
	global $connexionDB, $groupe, $groupe1, $groupe2, $moyenneBranche, $moyenneBulletin;
	if($IDBranche!=0) {
		// génération du groupe parents niveau 1 si nécessaire
		$groupe = floor($IDBranche/100);
		if($groupe1!=$groupe) {
			
			// changement de groupe -> calcul de la moyenne du groupe précédent
			if(count($moyenneBranche)!=0) {
				$moy = array_sum($moyenneBranche)/count($moyenneBranche);
				//echo "<tr><td colspan='7'><b>Moyenne de groupe</b></td><td align='center' onmouseover=\"Tip('Moyenne ".$moy." au 1/10')\" onmouseout='UnTip()'><b>";
				//echo sprintf("%01.1f",arrondi($moy,10));
				//echo "</b></td><td></td></tr>";
				if($moy!=0) {
					echo "<tr style='display:none'><td colpsan='9'><script>writeMoy(".$groupe1.",'".sprintf("%01.1f",arrondi($moy,10))."');</script></td></tr>";
					// mémorisation moyenne pour calcul du bulletin
					$moyenneBulletin[$groupe1] = arrondi($moy,10);
				}
				
			}
			$groupe1=$groupe;
			$moyenneBranche = array();
			// recherche du libellé du niveau parent 1
			$requeteG = "select Libelle from brancheept where IDBranche = ".$groupe1;
			//echo "<br>".$requeteG;
			$resultatG =  mysqli_query($connexionDB,$requeteG);
			$ligneG = mysqli_fetch_assoc($resultatG);
			if($ligneG!=null) {
				echo "<tr bgColor='#DEDEDE' style='font-size:14px;font-weight: bold;'><td colspan='7'>".$ligneG['Libelle']."</td><td idbranche=".$groupe." align='center'>-</td><td></td></tr>";
			}
		}
		// génération du groupe parents nuveau 2 si nécessaire
		$groupe = floor($IDBranche/10);
		if($groupe2!=$groupe) {
			$groupe2=$groupe;
			// recherche du libellé du niveau parent 2
			$requeteG = "select Libelle from brancheept where IDBranche = ".$groupe2;
			//echo "<br>".$requeteG;
			$resultatG =  mysqli_query($connexionDB,$requeteG);
			$ligneG = mysqli_fetch_assoc($resultatG);
			if($ligneG!=null) {
				echo "<tr><td colspan='9'><b>".$ligneG['Libelle']."</b></td></tr>";
			}
		}
		// génération de la ligne pour la branche en question avec toutes les notes récupérées
		$notesStr = "";
		foreach($notes as $id => $val) {
			if(!empty($notesID[$id])) {
				$notesStr .= "<td width='50' align='center'>".dispUpdField($notesID[$id],'Note',$val,2,'right')."</td>";
			} else {
				$notesStr .= "<td width='50' align='center'> </td>";
			}
		}
		for($i=count($notes);$i<5;$i++) {
			$notesStr .= "<td width='50' align='center'> </td>";
		}
		
		if($groupe==0) {
			// IDBranche < 10 -> groupe principal avec note (p. ex Pratique)
			echo "<tr bgColor='#DEDEDE'>";
			echo "<td colspan='2' style='font-size:14px;font-weight: bold;'>".$LibelleBranche;
			//echo "(".$IDBranche."/".$groupe.")";
			echo "</td>";
		} else {
			echo "<tr>";
			// IDBranche > 10 -> branche avec note (p. ex Pratique)
			echo "<td width='10'></td><td >".$LibelleBranche;
			//echo "(".$IDBranche."/".$groupe.")";
			echo "</td>";
		}
		echo $notesStr;
		$moy = array_sum($notes)/count($notes);
		if($groupe==0) {
			// mémorisation moyenne pour calcul du bulletin
			$moyenneBulletin[$IDBranche] = arrondi($moy,$arrondiFact);
			echo "<td style='font-size:14px;font-weight: bold;' ";
		} else {
			echo "<td ";
		}
		if($moy!=0) {
			echo "align='center' onmouseover=\"Tip('Moyenne ".$moy." au 1/".$arrondiFact."')\" onmouseout='UnTip()'>".sprintf("%01.1f",arrondi($moy, $arrondiFact))."</td>";
			// mémorisation des notes pour calcul de moyenne de groupe
			$moyenneBranche[] =  arrondi($moy, $arrondiFact);
		} else {
			echo "align='center'>-</td>";
		}
		echo "<td></td>";	
		echo "</tr>";
		
	} 
}
include("../../userInfo.php");
/* en-tête */

echo "<FORM id='myForm' ACTION='theorie.php'  METHOD='POST'>";
// transfert info
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
// update note
echo "<input type='hidden' name='actionNote' value=''>";
echo "<input type='hidden' name='note' value=''>";
echo "<input type='hidden' name='fieldUpd' value=''>";
echo "<input type='hidden' name='valueUpd' value=''>";

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
					echo "<a href='theorie.php?nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img id='prev' src='/iconsFam/resultset_previous.png'></a>";
				}
				echo $nom." ".$prenom;
				if($key<count($listeId)-1) {
					echo "<a href='theorie.php?nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img id='next' src='/iconsFam/resultset_next.png'></a>";
				}
				break;
			}
		}
	}
	echo "</h2><br>\n";
}
echo "<br><br><div id='corners'>";
echo "<div id='legend'>Calcul du bulletin, semestre ".$semestreAct."/".$anneeCalc."</div>";
echo "<table width='100%'><tr height='50'><td></td><td width='200' align='right'>Moyenne actuelle du bulletin:</td><td idbranche='bulletin' align='center' width='110' style='font-size:25px;font-weight: bold;'>-</td><td width='20'></td></tr></table>";
echo "<table id='hor-minimalist-b' width='100%'>\n";
echo "<tr><th colspan='2'>Branches</th><th colspan='5'>Notes</th><th width='100' align='center'>Moyenne</th>";
echo "<th width='10'></th></tr>";

// requete pour liste des cours trouvé pour l'élève

$requeteH = "select bra.IDBranche, bra.Libelle, ArrondiMoyenne, IDNoteEPT, Note from planept as pla join brancheept as bra on pla.Branche=bra.IDBranche and '".$classe."' LIKE CONCAT(pla.Classe, '%') and (pla.NoSemestre = ".$semestreAct." OR pla.NoSemestre = 0) and pla.annee = ".$anneeCalc;
$requeteH .= " left join noteept as no on pla.Branche = no.IDBranche and '".$classe."' LIKE no.Classe and no.NoSemestre = ".$semestreAct." and pla.Annee = no.Annee and no.IDEleve=".$IDEleve;
//$requeteH .= " where 
$requeteH .= " ORDER BY RPAD(bra.IDBranche,3,0), IDNoteEPT";
//echo $requeteH;
//echo "Semestre ".$semestreAct."/".$anneeCalc."<br>";
//echo "Classe ".$classe."<br>";
//echo "Eleve ".$IDEleve."<br>";
$resultat =  mysqli_query($connexionDB,$requeteH);
$groupe = 0; // numéro de groupe en cours (1,2,11,12,101,etc.)
$groupe1 = 0;
$groupe2 = 0;
$IDBranche = 0;
$arrondiFact = 0;
$LibelleBranche = "";
$moyenneBranche = array();
$moyenneBulletin = array();
$count =0;
while($ligne = mysqli_fetch_assoc($resultat)) {
	
	if($IDBranche!=$ligne['IDBranche']) {
		// nouvelle branche
		generate_branch($IDBranche, $LibelleBranche, $notes, $notesID, $arrondiFact);
		
		// initialisation pour prochaine branche
		unset($notes);
		unset($notesID);
		
		$IDBranche = $ligne['IDBranche'];
		$LibelleBranche = $ligne['Libelle'];
		$arrondiFact = $ligne['ArrondiMoyenne'];
	}	
	// mémorisation de la note
	$notes[] = $ligne['Note'];
	$notesID[] = $ligne['IDNoteEPT'];
	$count++;
}
// dernière branche
generate_branch($IDBranche, $LibelleBranche, $notes, $notesID, $arrondiFact);

if(empty($count)) {
	echo "<tr><td colspan='9'>Fonctionnalité non paramétrée</td></tr>";
} else {
	echo "<tr><td colspan='9' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
	echo "<tr newNote='1' ><td colspan='8'></td><td align='center'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter une note')\" onmouseout='UnTip()' onclick='toggle(\"newNote\");' align='absmiddle'></td></tr>";
	echo "<tr newNote='1' style='display:none' ><td colspan='9' valign='bottom' height='30'><b>Ajouter une note:<b></td></tr>";
	echo "<tr newNote='1' style='display:none' >";
	$requeteH = "select bra.IDBranche, bra.Libelle from planept as pla join brancheept as bra on pla.Branche=bra.IDBranche and '".$classe."' LIKE CONCAT(pla.Classe, '%') and (pla.NoSemestre = ".$semestreAct." OR pla.NoSemestre = 0) and pla.annee = ".$anneeCalc;
	$requeteH .= " ORDER BY RPAD(bra.IDBranche,3,0)";
	//echo $requeteH;
	$resultat =  mysqli_query($connexionDB,$requeteH);
	$listBranche = "<select name='NewIDBranche'>";
	while($ligne = mysqli_fetch_assoc($resultat)) {
		$listBranche .= "<option value='".$ligne['IDBranche']."'>".$ligne['Libelle']."</option>";
	}
	$listBranche .= "</select>";
	echo "<td valign='top' colspan='2'>".$listBranche."</td>";
	echo "<td valign='top' colspan='5'><input name='NewNote' value='' size='2' style='text-align: right'></input></td>";
	echo "<td valign='top' colspan='2' align='right'><input type='submit' name='ajouterNote' value='Ajouter'></input></td></tr>";

	echo "</table></div>";

	// calcul de la moyenne du bulletin
	// Recherche des groupes à prendre en compte (pourcentage spécifié en DB)
	$requeteH = "select IDBranche, CalculPrc from brancheept where CalculPrc is not null";
	//echo $requeteH;
	$resultat =  mysqli_query($connexionDB,$requeteH);
	$moyBul = 0;
	$displayBulletin = true;
	while($ligne = mysqli_fetch_assoc($resultat)) {
		// pour chaque groupe, recherche des moyennes
		if($moyenneBulletin[$ligne['IDBranche']]!=0) {
			$moyBul += $moyenneBulletin[$ligne['IDBranche']] * $ligne['CalculPrc'] / 100;
		} else {
			$displayBulletin = false;
		}
		//echo "<br>".$ligne['IDBranche'].": ".sprintf("%01.1f",$moyenneBulletin[$ligne['IDBranche']])." * ".$ligne['CalculPrc']."%";
	}
	//echo "<br>Moyenne bulletin: ".sprintf("%01.1f",$moyBul);
	if($moyBul!=0 && $displayBulletin) {
		echo "<script>writeMoy('bulletin','".sprintf("%01.1f",arrondi($moyBul,10))."');</script>";
	}
}
?>


</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
