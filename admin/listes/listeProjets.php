<?php
include("../../appHeader.php");

$filtre = "1";
if(isset($_POST['triProjet'])) {
	$filtre = $_POST['triProjet'];
} else {
	if(isset($_GET['triProjet'])) {
		$filtre = $_GET['triProjet'];
	}
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

if(isset($_POST['annee'])) {
	$anneeTri = $_POST['annee'];
} else if(isset($_GET['annee'])) {
	$anneeTri = $_GET['annee'];
} else {
	$anneeTri = $annee;
}


if(isset($_POST['ajoutProjet'])) {
	// ajout d'un thème
	$projet = $_POST['NomTheme'];
	// ajout d'un attribut
    $requete = "INSERT INTO theme (NomTheme, PonderationTheme, TypeTheme) values (\"$projet\",0,1)";
	//echo $requete;
    $resultat =  mysqli_query($connexionDB,$requete);
	$filtre = 11;
	$anneeTri = '%';
}
$filtreSQL = "";
if($filtre<10) {
	$filtreSQL = " and et.IDEtatProjet = ".$filtre;
}
if($filtre==11) {
	$filtreSQL = " and et.IDEtatProjet is null";
}

if(isset($_POST['theme'])&& !empty($_POST['theme'])) {
	$themeTri = $_POST['theme'];
}
if(isset($_GET['theme'])&& !empty($_GET['theme'])) {
	$themeTri = $_GET['theme'];
}
if(isset($_GET['theme'])&& !empty($_GET['theme'])) {
	$themeTri = $_GET['theme'];
}
if(!empty($themeTri)) {
	// tri sur theme, toutes années et etat confindus
	$filtre = 10;
	$anneeTri = '%';
	$filtreSQL = " and th.IDTheme=". $themeTri;
}


if(isset($_POST['actionProjet'])) {
	if($_POST['actionProjet']=='newAttrib') {
		// ajout d'une attribution
		$theme = $_POST['theme'];
		$eleve = $_POST['IDEleve'.$theme];
		$type = $_POST['IDTypeProjet'.$theme];
		if(in_array($eleve,$configurationATE)) {
			// si classe entière
			$requeteEl = "SELECT * FROM elevesbk ele join eleves el on ele.IDGDN=el.IDGDN where Classe like '".$eleve."%' and IDEntreprise=1 order by Classe desc, Nom, Prenom";
			$resultatEl =  mysqli_query($connexionDB,$requeteEl);
			while ($ligneEl = mysqli_fetch_assoc($resultatEl)) {
				$requete = "insert into projets (IDTheme, IDEleve, IDTypeProjet, IDEtatProjet) values ($theme, $ligneEl[IDGDN], $type, 0)";
				$resultat =  mysqli_query($connexionDB,$requete);
			}
		} else {
			//$idType
			//echo "theme ".$theme.", eleve ".$eleve.", type ".$type;
			$requete = "insert into projets (IDTheme, IDEleve, IDTypeProjet, IDEtatProjet) values ($theme, $eleve, $type, 0)";
			//echo $requete;
    		$resultat =  mysqli_query($connexionDB,$requete);
		}
	} else if($_POST['actionProjet']=='start') {
		$projet = $_POST['projet'];
		$eleve = $_POST['eleve'];
		$theme = $_POST['theme'];
		// mise à jour du projet
		$requete = "update projets set IDEtatProjet=1 where IDProjet=".$projet;
		//echo $requete;
    		$resultat =  mysqli_query($connexionDB,$requete);
		// ajout d'une ligne dans le suivi pour début de projet
		//$requete = "insert into suiviprojet (IDProjet, DateSaisie, RemarqueSuivi) values (".$projet.", \"".date('Y-m-d')."\", \"Début de projet\")";
		$requete = "insert into remarquesuivi (IDEleve, IDTheme, DateSaisie, Remarque, TypeRemarque) values (".$eleve.", ".$theme.",\"".date('Y-m-d')."\", \"Début de projet\",1)";
		//echo $requete;
    		$resultat =  mysqli_query($connexionDB,$requete);
	} else if($_POST['actionProjet']=='stop') {
		$projet = $_POST['projet'];
		$eleve = $_POST['eleve'];
		$theme = $_POST['theme'];
		// mise à jour du projet
		$requete = "update projets set IDEtatProjet=2 where IDProjet=".$projet;
		//echo $requete;
    		$resultat =  mysqli_query($connexionDB,$requete);
		// ajout d'une ligne dans le suivi pour finir de projet
		//$requete = "insert into suiviprojet (IDProjet, DateSaisie, RemarqueSuivi) values (".$projet.", \"".date('Y-m-d')."\", \"Fin de projet\")";
		$requete = "insert into remarquesuivi (IDEleve, IDTheme, DateSaisie, Remarque, TypeRemarque) values (".$eleve.", ".$theme.",\"".date('Y-m-d')."\", \"Fin de projet\",1)";
		//echo $requete;
    		$resultat =  mysqli_query($connexionDB,$requete);
	} else if($_POST['actionProjet']=='repeat') {
		$theme = $_POST['theme'];
		// mise à jour du projet
		$requete = "update projets set IDEtatProjet=4 where IDTheme=".$theme;
		//echo $requete;
    	$resultat =  mysqli_query($connexionDB,$requete);
	} else if($_POST['actionProjet']=='end') {
		$theme = $_POST['theme'];
		// mise à jour du projet
		$requete = "update projets set IDEtatProjet=2 where IDTheme=".$theme;
		//echo $requete;
    	$resultat =  mysqli_query($connexionDB,$requete);
	}
}
if(isset($_GET['IDTheme'])) {
	// suppression d'un theme
	$requete = "DELETE FROM theme where IDTheme=".$_GET['IDTheme'];
	//echo $requete;
    $resultat =  mysqli_query($connexionDB,$requete);
}
if(isset($_GET['IDProjet'])) {
	// suppression d'un projet
	$requete = "DELETE FROM projets where IDProjet=".$_GET['IDProjet'];
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
function submitNewAttrib(theme) {
	//alert(theme);
	document.getElementById('myForm').actionProjet.value='newAttrib';
	document.getElementById('myForm').theme.value=theme;
	//document.getElementById('myForm').annee.value='%';
	document.getElementById('myForm').triProjet.value=0;
	document.getElementById('myForm').submit();
}
function submitUpdateProj(theme,action) {
	//alert(theme);
	document.getElementById('myForm').actionProjet.value=action;
	document.getElementById('myForm').theme.value=theme;
	//if("repeat"==action) {
	//	document.getElementById('myForm').triProjet.value=4;
	//} else {
	//	document.getElementById('myForm').triProjet.value=2;
	//}
	document.getElementById('myForm').submit();
}
function submitUpdateProjID(projet,theme, eleve,action) {
	//alert(theme);
	document.getElementById('myForm').actionProjet.value=action;
	document.getElementById('myForm').projet.value=projet;
	document.getElementById('myForm').theme.value=theme;
	document.getElementById('myForm').eleve.value=eleve;
	//if("start"==action) {
	//	document.getElementById('myForm').triProjet.value=1;
	//} else {
	//	document.getElementById('myForm').triProjet.value=2;
	//}
	document.getElementById('myForm').submit();
}
</script>
<?php
include("../../userInfo.php");
/* en-tête */
echo "<FORM id='myForm' ACTION='listeProjets.php'  METHOD='POST'>";
echo "<input type='hidden' name='actionProjet' value=''>";
echo "<input type='hidden' name='theme' value=''>";
echo "<input type='hidden' name='projet' value=''>";
echo "<input type='hidden' name='eleve' value=''>";
echo "<div class='post'>";
echo "<center> <font color='#088A08'></font>";
echo "<br><font color='#FF0000'><b>Attention: vérifier avec l'apprenti qu'il ait bien fini de saisir ses journaux ainsi que son auto-évaluation avant de clôturer un projet!</b></font>";
echo "</center>";


// construction de la liste des élèves
$listeSCT = "<option value='0'></option>";
// recherche des élèves
$requete = "SELECT * FROM elevesbk ele join eleves el on ele.IDGDN=el.IDGDN where Classe like '".$app_section."%' and IDEntreprise=1 order by Classe desc, Nom, Prenom";
// Classe in ('".$app_section." 3','".$app_section." 4', '".$app_section." 3+1') order by Classe desc, Nom, Prenom";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
$lastClasse = "";
while ($ligne = mysqli_fetch_assoc($resultat)) {
	//if($ligne['IDAttribut']!=13) {
	if($lastClasse!=$ligne['Classe']) {
		// ajout d'une ligne classe
		$listeSCT .= "<option value='".$ligne['Classe']."'>".$ligne['Classe']."</option>";
	}
	$listeSCT .= "<option value='".$ligne['IDGDN']."'>&nbsp;  ".$ligne['Nom']." ".$ligne['Prenom']."</option>";
	//}
	$lastClasse = $ligne['Classe'];
}

// construction de la liste de types de projet
$selectType = "";
$requete = "SELECT * FROM typeprojet";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$selectType .= "<option value='".$ligne['IDTypeProjet']."'>".$ligne['LibelleTypeProjet']."</option>";
}
//$selectType .= "</select>";

// construction de la liste de etats de projet
$selectEtat = "";
$requete = "SELECT * FROM etatprojet";
$resultat =  mysqli_query($connexionDB,$requete);
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$selectEtat .= "<option value='".$ligne['IDEtatProjet']."'";
	if($ligne['IDEtatProjet']==$filtre) {
		$selectEtat .= " selected";
	}
	$selectEtat .= ">".$ligne['LibelleEtatProjet']."</option>";
}
echo "<br><table border=0 width='100%'><tr><td width='33%'></td><td width='33%' align='center'>";
$havingSQL = "";
//if($filtre==10) {
	$optionAnnee = "";
	for($cntA=0;$cntA<5;$cntA++) {
		$optionAnnee .= "<option value='".($annee-$cntA)."'";
		if(($annee-$cntA)==$anneeTri) {
			$optionAnnee .= " selected ";
		}
		$optionAnnee .= ">".($annee-$cntA)."/".($annee-$cntA+1)."</option>";
	}
	if(empty($themeTri)) {
		echo "Année: <select name='annee' onchange='submit()'><option value='%'>Tous</option>".$optionAnnee."</select>";
	}
	if($anneeTri!='%') {
		$havingSQL .= " having DDebut >= '".$anneeTri."-08-01' and DDebut <= '".($anneeTri+1)."-07-31'";
	}
//}
echo "</td><td align='right'>Tri projets: <select name='triProjet' onchange='submit()'>";
if(!empty($themeTri)) {
	echo "<option value='12' selected>Sélection</option>";
}
echo "<option value='10'>Tous</option><option value='11'".($filtre==11?'selected':'').">Nouveau</option>".$selectEtat."</select></td></tr></table>";
echo "<br><div id='corners'>";
if(empty($themeTri)) {
	echo "<div id='legend'>Liste des projets</div>";
} else {
	echo "<div id='legend'>Liste des attributions</div>";
}
echo "<table id='hor-minimalist-b' width='100%' border='0'>\n";
echo "<tr><th width='300'>Nom projet</th><th width='10'></th><th>Attribué à</th><th width='80'>Début</th><th width='80'>Fin</th><th width='80'>Type</th><th width='80'>Etat</th><th width='10'>Suivi</th><th width='10'>Noté</th><th width='10'></th></tr>";
// recherche des attributs généraux (1 à 6)
$requete = "SELECT th.IDTheme, pr.IDProjet, th.NomTheme, el.Nom, el.Prenom, pr.IDEleve, pr.DebutProjet, pr.FinProjet, ty.LibelleTypeProjet, et.LibelleEtatProjet, et.IDEtatProjet";
$requete .= ", min(DateSaisie) as DDebut, max(DateSaisie) as DFin";
$requete .= " FROM theme th left join projets pr on th.IDTheme=pr.IDTheme left join typeprojet ty on pr.IDTypeProjet=ty.IDTypeProjet left join etatprojet et on pr.IDEtatProjet=et.IDEtatProjet left join elevesbk el on pr.IDEleve=el.IDGDN";
$requete .= " left join remarquesuivi suv on th.IDTheme=suv.IDTheme and pr.IDEleve=suv.IDEleve";
if($anneeTri!='%') {
	//$requete .= " and DateSaisie >= '".$anneeTri."-08-01'";// and DateSaisie <= '".($anneeTri+1)."-07-31'";
}
$requete .= " where typetheme = 1 ".$filtreSQL;
$requete .= " group by th.IDTheme, pr.IDEleve".$havingSQL;
$requete .= " order by th.NomTheme, et.IDEtatProjet, ty.IDTypeProjet, DDebut desc";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
$cnt=0;
$lastID = 0;
$lastEtat = 0;
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$idTheme = $ligne['IDTheme'];
	$idProjet = $ligne['IDProjet'];
	$idEleve = $ligne['IDEleve'];
	//if(empty($idEleve)) {
		// pas encore attribuer, on effectue un toggle avec cette ligne et la ligne d'ajout
	//	echo "<tr newattrib$idTheme='1'>";
	//} else {

	//}
	if($lastID!=$ligne['IDTheme']) {
		// si nouveau thème
		echo "<tr bgColor='#DEDEDE'>";
		echo "<td><a href='listeProjets.php?theme=$ligne[IDTheme]'><b>".$ligne['NomTheme']."</b></a></td><td>";
		if($ligne['IDEtatProjet']==0||$ligne['IDEtatProjet']==1||$ligne['IDEtatProjet']==4) {
			// ajout apprenti si attente, en cours ou à reprendre
			echo "<img src='/iconsFam/user_add.png' align='absmiddle' onmouseover=\"Tip('Attribuer à un élève')\" onmouseout='UnTip()' onclick='toggle(\"newattrib$idTheme\");'>";
		}
		echo "</td>";
		if(empty($ligne['Nom'])) {
			// action supprimer le projet si aucune attribution
			echo "<td colspan='4'></td><td><b>Nouveau</b></td><td colspan='2'></td>";
		} else if($ligne['IDEtatProjet']==4 || $ligne['IDEtatProjet']==2) {
			echo "<td colspan='4'></td><td><b>".$ligne['LibelleEtatProjet']."</b></td><td colspan='2'></td>";
		} else {
			echo "<td colspan='7'></td>";
		}
		echo "<td align='right'>";
		if(!isset($ligne['IDEtatProjet'])) {
			echo "<a href='listeProjets.php?IDTheme=$ligne[IDTheme]'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a>";
		} else if($ligne['IDEtatProjet']==2) {
			echo "<img src='/iconsFam/control_repeat.png' align='absmiddle' onmouseover=\"Tip('Reprendre le projet')\" onmouseout='UnTip()' onclick='submitUpdateProj(\"$idTheme\",\"repeat\")'>";
		} else if($ligne['IDEtatProjet']==4) {
			echo "<img src='/iconsFam/control_end.png' align='absmiddle' onmouseover=\"Tip('Marquer comme terminé')\" onmouseout='UnTip()' onclick='submitUpdateProj(\"$idTheme\",\"end\")'>";
		}
		echo "</td></tr>";
		// ligne d'ajout d'attribution
		if($lastID!=$ligne['IDTheme']) {
			echo "<tr newattrib$idTheme='1' style='display:none'><td  colspan='2'></td><td colspan='3'><select name='IDEleve".$idTheme."'>".$listeSCT."</select></td><td><select name='IDTypeProjet".$idTheme."'>".$selectType."</select></td><td colspan='4' align='right'><input type='button' name='ajoutAttribution' value='Attribuer' onclick='submitNewAttrib(\"$idTheme\")'></input></td></tr>";
		}
		if(!empty($ligne['Nom'])) {
			// si au moins une attribution, on liste les noms
			echo "<tr>";
		}
	} else {
		echo "<tr>";
	}



	if(!empty($ligne['Nom'])) {
		// première colonne vide
		echo "<td></td><td>";
		// recherche info suivi et note
		$vide = 1;
		$celSuivi = "";
		$celNote = "";
		//$requeteF = "SELECT count(*) as tot from suiviprojet where IDProjet=".$ligne['IDProjet'];
		$requeteF = "SELECT count(*) as tot from remarquesuivi where IDTheme=".$ligne['IDTheme']." and IDEleve=".$ligne['IDEleve'];
		$resultatF =  mysqli_query($connexionDB,$requeteF);
		$ligneF = mysqli_fetch_assoc($resultatF);
		if($ligneF['tot']>2) {
			$celSuivi = "<td align='center'><img src='/iconsFam/bullet_green.png' align='absmiddle'></td>";
			$vide = 0;
		} else {
			if($ligneF['tot']>0) {
				//$vide = 0;
			}
			$celSuivi = "<td align='center'><img src='/iconsFam/bullet_red.png' align='absmiddle'></td>";
		}
		$requeteF = "SELECT * from notes where IDTheme=".$ligne['IDTheme']." and IDTypeNote<>0 and IDEleve=".$ligne['IDEleve'];
		$resultatF =  mysqli_query($connexionDB,$requeteF);
		if(mysqli_num_rows($resultatF)>0) {
			$celNote = "<td align='center'><img src='/iconsFam/bullet_green.png' align='absmiddle'></td>";
			$vide = 0;
		} else {
			$celNote = "<td align='center'><img src='/iconsFam/bullet_red.png' align='absmiddle'></td>";
		}
		// effacer apprenti si applicable (moins de 3 suivis ou pas de note)
		if($vide) {
			echo "<a href='listeProjets.php?IDProjet=$ligne[IDProjet]&theme=$ligne[IDTheme]'><img src='/iconsFam/user_delete.png' align='absmiddle' onmouseover=\"Tip('Enlever cette attribution')\" onmouseout='UnTip()'></a>";
		}
		// nom+ prénom
		echo "</td><td><a href='../detail/activites.php?nom=".$ligne['Nom']."&prenom=".$ligne['Prenom']."&idEleve=".$ligne['IDEleve']."&IDTheme=".$ligne['IDTheme']."'>".$ligne['Nom']." ".$ligne['Prenom']."</a></td><td>";
		//if($ligne['IDEtatProjet']==1) {
		//	$requeteF = "SELECT min(DateSaisie) min, max(DateSaisie) max from remarquesuivi where IDTheme=".$ligne['IDTheme']." and IDEleve=".$ligne['IDEleve']." and (DateSaisie between '".$annee."-08-01' and '".$anneePlus."-07-31')";
		//} else {
		//	$requeteF = "SELECT min(DateSaisie) min, max(DateSaisie) max from remarquesuivi where IDTheme=".$ligne['IDTheme']." and IDEleve=".$ligne['IDEleve'];
		//}
		//echo $requeteF;
		//$resultatF =  mysqli_query($connexionDB,$requeteF);
		//$ligneF = mysqli_fetch_assoc($resultatF);
		if(!empty($ligne['DebutProjet'])) {
			echo date('d.m.Y', strtotime($ligne['DebutProjet']));
		//} else if($ligneF['min']!=0){
		} else if($ligne['DDebut']){
			//if($ligneF['min']!=$ligne['DDebut']) {
			//	echo "<font color='red'>".date('d.m.Y', strtotime($ligneF['min']))."</font><br>";
			//}
			echo date('d.m.Y', strtotime($ligne['DDebut']));
		}
		echo "</td><td>";
		if(!empty($ligne['FinProjet'])) {
			echo date('d.m.Y', strtotime($ligne['FinProjet']));
		//} else if($ligneF['max']!=0&&$ligne['IDEtatProjet']!=1){
		} else if($ligne['DFin']!=0 && $ligne['IDEtatProjet']!=1) {
			//if($ligneF['max']!=$ligne['DFin']) {
			//	echo "<font color='red'>".date('d.m.Y', strtotime($ligneF['max']))."</font><br>";
			//}
			echo date('d.m.Y', strtotime($ligne['DFin']));
		}
		echo "</td><td>".$ligne['LibelleTypeProjet']."</td><td>";
		if(($ligne['IDEtatProjet']!=4 && $ligne['IDEtatProjet']!=2) || ($lastID==$ligne['IDTheme']&&$lastEtat!=$ligne['IDEtatProjet'])) {
			echo $ligne['LibelleEtatProjet'];
		}
		echo "</td>";
		echo $celSuivi;
		echo $celNote;
		// actions
		echo "<td align='right'>";
		if($ligne['IDEtatProjet']==0) {
			echo "<img src='/iconsFam/control_play.png' align='absmiddle' onmouseover=\"Tip('Démarrer le projet')\" onmouseout='UnTip()' onclick='submitUpdateProjID(\"$idProjet\",\"$idTheme\",\"$idEleve\",\"start\")'>";
		} else if($ligne['IDEtatProjet']==1) {
			echo "<img src='/iconsFam/control_stop.png' align='absmiddle' onmouseover=\"Tip('Terminer le projet')\" onmouseout='UnTip()' onclick='submitUpdateProjID(\"$idProjet\",\"$idTheme\",\"$idEleve\",\"stop\")'>";
		}
		//if($lastID==$ligne['IDTheme']&&$lastEtat!=$ligne['IDEtatProjet']) {
			// état différents sur même thème -> on afficher les options pour terminer
			//if($ligne['IDEtatProjet']==4) {
				//echo "<img src='/iconsFam/control_end.png' align='absmiddle' onmouseover=\"Tip('Marquer comme terminé')\" onmouseout='UnTip()' onclick='submitUpdateProj(\"$idTheme\",\"end\")'>";
			//}
		//}
		echo "</td></tr>";
	}


	if($lastID==$ligne['IDTheme']&&$lastEtat!=$ligne['IDEtatProjet']) {
		// si état différent dans le même thème, on réinitialise le dernier état afin que le prochain soit affiché
		$lastEtat = 0;
	} else {
		$lastEtat = $ligne['IDEtatProjet'];
	}
	$lastID = $ligne['IDTheme'];
	$cnt++;
}
if ($cnt==0) {
	echo "<tr><td colspan='10' align='center'><i>Aucun enregistrement</i></td></tr>";
}
// ligne d'ajout

echo "<tr><td colspan='10' bgColor='#5C5C5C'></td></tr>";
echo "<tr newProjet='1' ><td colspan='9'></td><td align='right'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un projet')\" onmouseout='UnTip()' onclick='toggle(\"newProjet\");' align='absmiddle'></td></tr>";

echo "<tr newProjet='1' style='display:none' ><td colspan='10' valign='bottom' height='30'><b>Nouveau projet:<b></td></tr>";
echo "<tr newProjet='1' style='display:none' >";
echo "<td valign='top' colspan='2'><input name='NomTheme' value='' size='40'></input></td>";
echo "<td valign='top' colspan='8'><input type='submit' name='ajoutProjet' value='Ajouter'></input></td></tr>";
echo "</table></div><br>";
?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
