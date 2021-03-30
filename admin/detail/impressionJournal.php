<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:59
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: impressionJournal.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:20
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

$IDTheme=0;
if(isset($_POST['IDTheme'])) {
	$IDTheme = $_POST['IDTheme'];
} else if(isset($_GET['IDTheme'])) {
	$IDTheme = $_GET['IDTheme'];
}


$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}
include("entete.php");
?>

<div id="page">
<?
include($app_section."/userInfo.php");
/* en-t�te */

echo "<FORM id='myForm' ACTION='impressionJournalPDF.php'  METHOD='POST'>";
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='$nom'>";
echo "<input type='hidden' name='prenom' value='$prenom'>";
echo "<div class='post'>";
echo "<br><table border='0' width='1000'><tr><td width='200'><!-- h2>Impression";
echo "</h2 --></td><td align='center' width='500'><h2>";
if(isset($listeId)&&!empty($listeId)) {
	foreach($listeId as $key => $valeur) {
		if($valeur[0]==$IDEleve) {
			if($key!=0) {
				echo "<a href='impressionJournal.php?IDTheme=".$IDTheme."&from=".$from."&nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."'><img src='/iconsFam/resultset_previous.png'></a>";
			}
			echo $nom." ".$prenom;
			if($key<count($listeId)-1) {
				echo "<a href='impressionJournal.php?IDTheme=".$IDTheme."&from=".$from."&nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."'><img src='/iconsFam/resultset_next.png'></a>";
			}
			$classe = $listeId[$key][3];
			break;
		}
	}
}
echo "</h2></td><td width='300'></td></tr></table><br>";
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
$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (pr.IDEleve = $IDEleve) OR (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) group by th.IDTheme order by TypeTheme, NomTheme";
//echo $requete;
$resultat =  mysql_query($requete);
$option = "<option value='0'></option>";
while ($ligne = mysql_fetch_assoc($resultat)) {
	$option .= "<option value=".$ligne['IDTheme'];
	if($ligne['IDTheme']==$IDTheme) {
		$option .= " selected";
	}
	$option .= ">";
	if($ligne['TypeTheme']==1) {
		$option .= "Projet - ";
	}
	$option .= $ligne['NomTheme']."</option>";
}

// Rechercher l'ann�e correspondant au th�me choisi (IDTheme transmis)

$requete = "SELECT min(DateJournal) as min, max(DateJournal) as max FROM journal jou where IDEleve = $IDEleve and jou.IDTheme = $IDTheme";
$resultat =  mysql_query($requete);
if(!empty($resultat)) {
	$ligne = mysql_fetch_assoc($resultat);
	$max = $ligne['max'];
	$min = $ligne['min'];
} else {
	$max=0;
	$min=0;
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



// construction de la liste d'ann�es
$optionAnneeEval = "<select name='annee'>";
//$mois = date('m');
//$annee = date('Y');
//if($mois<8) {
//	$annee = $annee-1;
//}
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

echo "<br><div id='corners'>";
echo "<div id='legend'>Impression d'un journal</div>";
echo "<br><table>";
echo "<tr><td width='150' align='right'><b>Theme/Projet:</b></td><td><select name='IDTheme' onChange='document.getElementById(\"myForm\").action=\"impressionJournal.php\";document.getElementById(\"myForm\").submit();'>".$option."</select></td></tr>";
echo "<tr><td width='150' align='right'><b>Ann�e:</b></td><td>".$optionAnneeEval."</td></tr>";


echo "<tr><td></td><td><input type='submit' name='envoyer' value='G�n�rer'></td></tr>";
echo "</table></div><br>";
?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
