<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:51
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: activites.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:03
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");
date_default_timezone_set('Europe/Zurich');
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
} else {
	if(isset($_GET['vue'])&& 'app'==$_GET['vue']) {
		$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where el.IDGDN=".$_GET['idEleve'];
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

$from = "";
if(isset($_GET['from'])) {
	$from = $_GET['from'];
}
if(isset($_POST['from'])) {
	$from = $_POST['from'];
}
if(isset($_GET['triSem'])) {
	$triSemaine = $_GET['triSem'];
	$_SESSION['triSem'] = $triSemaine;
} else {
	$triSemaine = (isset($_SESSION['triSem'])?$_SESSION['triSem']:"");
}
if(isset($_POST['vue'])) {
	$vue = $_POST['vue'];
	$_SESSION['vue'] = $vue;
	if($vue==2) {
		// forcer semaine du jour
		$noSemaine = date('W');
		$anneeCalc = date('Y');
		$_SESSION['noSemaine'] = $noSemaine;
		$_SESSION['anneeCalc'] = $anneeCalc;
	}
} else if(isset($_GET['vue'])) {
	$vue = $_GET['vue'];
	$_SESSION['vue'] = $vue;
	if($vue==2) {
		// forcer semaine du jour
		$noSemaine = date('W');
		$anneeCalc = date('Y');
		$_SESSION['noSemaine'] = $noSemaine;
		$_SESSION['anneeCalc'] = $anneeCalc;
	}
} else if(isset($_SESSION['vue'])) {
	$vue=$_SESSION['vue'];
} else {
	$vue=1;
	$_SESSION['vue'] = $vue;
}

$IDQuery="";
if(isset($_GET['IDTheme'])) {
	$IDQuery = $_GET['IDTheme'];
	$IDTheme = $IDQuery;
	$vue = 2;
	$_SESSION['vue'] = $vue;
	// forcer semaine du jour
	$noSemaine = date('W');
	$anneeCalc = date('Y');
	$_SESSION['noSemaine'] = $noSemaine;
	$_SESSION['anneeCalc'] = $anneeCalc;
}
//echo "vue: ".$_SESSION['vue']."/".$_POST['vue']."/".$_GET['vue'];
if(isset($_POST['IDThemeSel'])&&$vue==2) {
	$IDQuery = $_POST['IDThemeSel'];
	$IDTheme = $IDQuery;
	// forcer semaine du jour
	$noSemaine = date('W');
	$anneeCalc = date('Y');
	$_SESSION['noSemaine'] = $noSemaine;
	$_SESSION['anneeCalc'] = $anneeCalc;
}

if(!isset($_POST['noSemaine']) || "" == $_POST['noSemaine']) {
	//echo "pas trouvé en POST: ".$_POST['noSemaine'];
	if(!isset($_SESSION['noSemaine']) || "" == $_SESSION['noSemaine']) {
		//echo "- pas trouvé en session: ".$_SESSION['noSemaine'];
		$noSemaine = date('W');
		$anneeCalc = date('Y');
	} else {
		//echo "- trouvé en session: ".$_SESSION['noSemaine'];
		$noSemaine = $_SESSION['noSemaine'];
		$anneeCalc = $_SESSION['anneeCalc'];
	}
} else {
	//echo "trouvé en POST: ".$_POST['noSemaine'];
	$noSemaine = $_POST['noSemaine'];
	$anneeCalc = $_POST['anneeCalc'];
	$_SESSION['noSemaine'] = $noSemaine;
	$_SESSION['anneeCalc'] = $anneeCalc;

}

if(isset($_GET['anneeAff'])) {
	// affichage forcé des activités de l'année entière (semaine 31 = début année scolaire), spécifiée en paramètre GET
	$noSemaine = 31;
	$anneeCalc = $_GET['anneeAff'];
}

if(isset($_GET['DateJournal'])) {
	$datejrn = strtotime($_GET['DateJournal']);
	$noSemaine = date('W',$datejrn);
	$anneeCalc = date('Y',$datejrn);
}

// calcul lundi et vendredi
$dateCalc=mktime(0,0,0,1,4,$anneeCalc);
$jour_semaine=date("N",$dateCalc);

//$num_semaine=date("W",$dateCalc);
$lundi=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaine-1);
$vendredi = $lundi + 86400*5 - 3600*2; // retrait de 2h -> vendredi soir même si GMT +1 ou +2
//$lundi=$dateCalc*($jour_semaine-1)+604800*($noSemaine-1);
//echo "4 janvier ".$anneeCalc." -> ".$dateCalc;
// calcul du tri sur les dates du semestre en cours
if(isset($_POST['triPeriode'])) {
	$triPeriode = $_POST['triPeriode'];
	$_SESSION['triPeriode'] = $triPeriode;
} else {
	if(isset($_GET['triPeriode'])) {
		$triPeriode = $_GET['triPeriode'];
		$_SESSION['triPeriode'] = $triPeriode;
	} else {
		$triPeriode = (isset($_SESSION['triPeriode'])?$_SESSION['triPeriode']:"");
	}
}


//$triPeriode = 1; // 1 = semster en cours, 2 = année en cours, 0 = tout
$semestreAct = 1;
if($noSemaine<30) {
	// de janvier à juillet
	// calcul du lundi du 2ème semestre
	$lundisem2=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaineSem2-1);
	if($noSemaine>=$noSemaineSem2) {
		// uniquement 2ème semestre
		$betweenSQLSem = "between '".date("Y-m-d",$lundisem2)."' and '".($anneeCalc)."-07-31'";
		$semestreAct = 2;
	} else {
		// fin du premier semestre en début d'année civile
		$betweenSQLSem = "between '".($anneeCalc-1)."-08-01' and '".date("Y-m-d",$lundisem2-86400)."'";
	}
	// requete pour annee en cours
	$betweenSQLAnn = "between '".($anneeCalc-1)."-08-01' and '".$anneeCalc."-07-31'";
} else {
	// semestre 1 - août à décembre
	$dateCalcPlus=mktime(0,0,0,1,4,($anneeCalc+1));
	$jour_semainePlus=date("N",$dateCalcPlus);
	$lundisem2=$dateCalcPlus-86400*($jour_semainePlus-1)+604800*($noSemaineSem2-1);
	// requete pour semestre en cours
	$betweenSQLSem = "between '".($anneeCalc)."-08-01' and '".date("Y-m-d",($lundisem2-86400))."'";
	// requete pour année en cours
	$betweenSQLAnn = "between '".$anneeCalc."-08-01' and '".($anneeCalc+1)."-07-31'";
}
//echo $betweenSQL;

//construction liste des jours de la semaine
//$listeJours = "<select name='DateActivite'>";
$listeJours = "";
$joursem = $lundi;
$listeJours .= "<option value='".date('d.m.Y',$joursem)."'>Lu ".date('d.m',$joursem)."</option>";
$joursem += 86400;
$listeJours .= "<option value='".date('d.m.Y',$joursem)."'>Ma ".date('d.m',$joursem)."</option>";
$joursem += 86400;
$listeJours .= "<option value='".date('d.m.Y',$joursem)."'>Me ".date('d.m',$joursem)."</option>";
$joursem += 86400;
$listeJours .= "<option value='".date('d.m.Y',$joursem)."'>Je ".date('d.m',$joursem)."</option>";
$joursem += 86400;
$listeJours .= "<option value='".date('d.m.Y',$joursem)."'>Ve ".date('d.m',$joursem)."</option>";
//$listeJours .= "</select>";

// heure
$heureAct = date('H:i');

// construction liste des heures disponibles
$listeHeures = "";
$listeHeures .= "<option value='0.0'>0.0h</option>";
$listeHeures .= "<option value='0.5'>0.5h</option>";
$listeHeures .= "<option value='1.0'>1.0h</option>";
$listeHeures .= "<option value='1.5'>1.5h</option>";
$listeHeures .= "<option value='2.0'>2.0h</option>";
$listeHeures .= "<option value='2.5'>2.5h</option>";
$listeHeures .= "<option value='3.0'>3.0h</option>";
$listeHeures .= "<option value='3.5'>3.5h</option>";
$listeHeures .= "<option value='4.0'>4.0h</option>";
$listeHeures .= "<option value='4.5'>4.5h</option>";
$listeHeures .= "<option value='5.0'>5.0h</option>";
$listeHeures .= "<option value='5.5'>5.5h</option>";
$listeHeures .= "<option value='6.0'>6.0h</option>";
$listeHeures .= "<option value='6.5'>6.5h</option>";
$listeHeures .= "<option value='7.0'>7.0h</option>";
$listeHeures .= "<option value='7.5'>7.5h</option>";
$listeHeures .= "<option value='8.0'>8.0h</option>";
$listeHeures .= "<option value='8.5'>8.5h</option>";
$listeHeures .= "<option value='9.0'>9.0h</option>";
$listeHeures .= "<option value='9.5'>9.5h</option>";
$listeHeures .= "<option value='10.0'>10.0h</option>";
$listeHeures .= "<option value='10.5'>10.5h</option>";
$listeHeures .= "<option value='11.0'>11.0h</option>";
$listeHeures .= "<option value='11.5'>11.5h</option>";
$listeHeures .= "<option value='12.0'>12.0h</option>";


$listeId = array();
if(isset($_SESSION['listeId'])) {
	$listeId = $_SESSION['listeId'];
}
$msgErreur = "";
$commentaires = "";
if(isset($_POST['AjoutActivite'])) {
	$IDEl = $_POST['IDEleve'];
	$date = date("Y-m-d",strtotime($_POST['DateActivite']));
	$IDTheme = $_POST['IDTheme'];
	$commentaires = $_POST['RemarqueActivite'];
	$commentaires = addslashes($commentaires);
	$heures = $_POST['HeuresActivite'];
	$heureDebut = $_POST['HeureDebut'];
	$heureFin = $_POST['HeureFin'];

	//echo strtotime($_POST['DateActivite'])+3600;
	if(empty($_POST['DateActivite'])) {
		// date du jour
		$date = date("Y-m-d");
	} else {
		$dateCheck = strtotime($_POST['DateActivite'])+3600; // evite les problème de calcul avec l'heure d'été
		if($dateCheck<$lundi || $dateCheck>$vendredi) {
			$msgErreur = "<font color='#FF0000'>La date saisie (".date("d.m.Y",strtotime($_POST['DateActivite'])).") se trouve en dehors de la semaine concernée!</font>";
		}
	}
	//echo $dateCheck."-".$lundi."-".$vendredi;
	if($heures=="0.0"&&empty($heureDebut)&&empty($heureFin)) {
		// aucune info sur le temps/durée -> on utilise le mode chrono avec l'heure actuelle
		$heureDebut = $heureAct;
	} else {
		if($heures!="0.0") {
			// si durée renseignée, on s'assure que les heures de début et de fin ne sont pas enregistrées
			$heureDebut = "00:00";
			$heureFin = "00:00";
		} else {
			if(!empty($heureDebut)) {
				$time1 = strtotime($heureDebut);
				if(empty($time1)) {
					$msgErreur .= "<font color='#FF0000'>L'heure de début n'est pas correcte!</font>";
				}
				if(!empty($heureFin)) {
					// heures de début et de fin spécifiée -> calcul de la durée
					$time2 = strtotime($heureFin);
					if(empty($time2) || $time2<$time1) {
						$msgErreur .= "<font color='#FF0000'>L'heure de fin n'est pas correcte!</font>";
					} else {
						//echo $time1."-".$time2."->".(round(abs($time2 - $time1) / 3600,1));
						$heures = round(abs($time2 - $time1) / 3600,3);
						if($heures==0) {
							$msgErreur .= "<font color='#FF0000'>Les heures saisies ne sont pas correctes!</font>";
						}
					}
				} else {
					$heureFin = "00:00";
				}
			} else {
				$msgErreur .= "<font color='#FF0000'>L'heure de début est obligatoire!</font>";
			}
		}
	}

	if(empty($msgErreur)) {
		// ajout d'un attribut
   		$requete = "INSERT INTO journal (IDTheme, IDEleve, DateJournal, Heures, Commentaires, HeureDebut, HeureFin) values ($IDTheme, $IDEl, \"$date\", \"$heures\", \"$commentaires\", \"".$heureDebut.":00\", \"".$heureFin.":00\")";
    		//echo "requete avec ".$login.":".$requete."<br>";
		$resultat =  mysqli_query($connexionDB,$requete);
	}
}

// Ajout ou modif remarque
if(isset($_POST['actionRemarque'])&&!empty($_POST['actionRemarque'])) {
	if($_POST['actionRemarque']=='ajout') {
		// mode ajout avec IDTheme
		$IDEl = $_POST['IDEleve'];
		$IDTheme = $_POST['IDRemarque'];
		$dateRema = $_POST['DateRemarque'.$IDTheme];
		$typeRema = $_POST['TypeRemarque'.$IDTheme];
		$rema = addslashes($_POST['Remarque'.$IDTheme]);
		if(empty($dateRema)) {
			// date du dernier jour de la semaine
			$dateRema = date("Y-m-d",$vendredi);
		} else {
			$dateRema = date("Y-m-d",strtotime($dateRema));
		}
		if($typeRema>=10) {
			// remarque associée à un thème
			$IDTheme = $typeRema;
			$typeRema = 1;
		} else {
			$IDTheme = 1;
		}
		$uid = $_SESSION['user_login'];
		// ajout
   		$requete = "INSERT INTO remarquesuivi (IDTheme, IDEleve, DateSaisie, Remarque, TypeRemarque, UserId) values ($IDTheme, $IDEl, \"$dateRema\", \"$rema\", $typeRema, \"$uid\")";
	} else {
		// mode modif
		$idremUpd = $_POST['IDRemarque'];
		$rema = addslashes($_POST['Remarque'.$idremUpd]);
		//echo "date: ".$_POST['DateRemarque'.$idremUpd];
		$dateRema = date("Y-m-d",strtotime($_POST['DateRemarque'.$idremUpd]));
		$requete = "UPDATE remarquesuivi set Remarque=\"$rema\", DateSaisie=\"$dateRema\" where IDRemSuivi=".$idremUpd;
	}
    	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);

}

// effacement d'une ligne
if(isset($_GET['IDJournal'])) {
	$requete = "DELETE FROM journal where IDJournal=$_GET[IDJournal]";
	//echo $requete;
	mysqli_query($connexionDB,$requete);

}
// effacement ou changement de type d'une remarque
if(isset($_GET['IDRemSuivi'])) {
	if($_GET['action']=='delete') {
		$requete = "DELETE FROM remarquesuivi where IDRemSuivi=$_GET[IDRemSuivi]";
	}
	if($_GET['action']=='corrected') {
		// journal corrigé
		$requete = "UPDATE remarquesuivi set TypeRemarque=3 where IDRemSuivi=$_GET[IDRemSuivi]";
	}
	if($_GET['action']=='done') {
		// tâche effectuée
		$requete = "UPDATE remarquesuivi set TypeRemarque=5 where IDRemSuivi=$_GET[IDRemSuivi]";
	}
	if($_GET['action']=='validated') {
		// tâche effectuée
		$requete = "UPDATE remarquesuivi set TypeRemarque=6 where IDRemSuivi=$_GET[IDRemSuivi]";
	}
	//echo $requete;
	mysqli_query($connexionDB,$requete);

}

// modification d'une ligne
if(isset($_POST['activite'])&&!empty($_POST['activite'])) {
	$activite = $_POST['activite'];
	$themeMaj = $_POST['IDTheme'.$activite];
	$dateMaj = date("Y-m-d",strtotime($_POST['DateJournal'.$activite]));
	$commentairesMaj = $_POST['Commentaires'.$activite];
	$commentairesMaj = addslashes($commentairesMaj);
	$heuresMaj = $_POST['Heures'.$activite];
	$dateCheck = strtotime($_POST['DateJournal'.$activite])+3600;
	$msgErreur = "";
	$heureDebutMaj = $_POST['HeureDebut'.$activite];
	$heureFinMaj = $_POST['HeureFin'.$activite];
	//echo $dateCheck."-".$lundi."-".$vendredi;
	if($dateCheck<$lundi || $dateCheck>$vendredi) {
		$msgErreur = "<font color='#FF0000'>La date saisie (".date("d.m.Y",strtotime($_POST['DateJournal'.$activite])).") se trouve en dehors de la semaine concernée!</font>";
	}
	if($heuresMaj=="0.0"||(empty($heuresMaj)&&empty($heureDebutMaj)&&empty($heureFinMaj))) {
		// aucune info sur le temps/durée -> maj impossible
		$msgErreur .= "<font color='#FF0000'>Aucune heure ou durée spécifiée!</font>";
	} else {
		if(!empty($heureDebutMaj)) {
			$time1 = strtotime($heureDebutMaj);
			if(empty($time1)) {
				$msgErreur .= "<font color='#FF0000'>L'heure de début n'est pas correcte!</font>";
			}
			if(!empty($heureFinMaj)) {
				// heures de début et de fin spécifiée -> calcul de la durée
				$time2 = strtotime($heureFinMaj);
				if(empty($time2)|| $time2<$time1) {
					$msgErreur .= "<font color='#FF0000'>L'heure de fin n'est pas correcte!</font>";
				} else {
					$heuresMaj = round(abs($time2 - $time1) / 3600,3);
					if($heuresMaj==0) {
						$msgErreur .= "<font color='#FF0000'>Les heures saisies ne sont pas correctes!</font>";
					}
				}
			} else {
				$heureFinMaj = "00:00";
				$heuresMaj = "0.0";
			}
		} else {
			if(empty($heuresMaj)) {
				// mode chrono sans heure de début
				$msgErreur .= "<font color='#FF0000'>L'heure de début est obligatoire!</font>";
			} else {
				// mode durée
				$heureDebutMaj = "00:00";
				$heureFinMaj = "00:00";
			}
		}
	}

	if(empty($msgErreur)) {
		$requete = "update journal set IDTheme=$themeMaj , DateJournal=\"".$dateMaj."\", Commentaires=\"".$commentairesMaj."\", Heures=$heuresMaj, HeureDebut=\"".$heureDebutMaj."\", HeureFin=\"".$heureFinMaj."\" where IDJournal=".$activite;
		//echo "<br>".$requete;
    	$resultat =  mysqli_query($connexionDB,$requete);
	}

}
if(isset($_POST['validation'])&&!empty($_POST['validation'])) {

	if($_POST['validation']=="lock") {
		//echo "lock";
		$requete = "update journal set DateValidation = \"".date('Y-m-d')."\" where IDEleve = $IDEleve and DateValidation is null and (DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
		//echo $requete;
		mysqli_query($connexionDB,$requete);
	} else if($_POST['validation']=="unlock") {
		//echo "unlock";
		$requete = "update journal set DateValidation = null where IDEleve = $IDEleve and (DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
		//echo $requete;
		mysqli_query($connexionDB,$requete);
	} else if($_POST['validation']=="lockEval") {
		//if($vue==1) {
			$requete = "update evalhebdo set DateValidation = \"".date('Y-m-d')."\" where IDEleve = $IDEleve and NoSemaine = $noSemaine and Annee = $anneeCalc";
		//} else {
		//	$requete = "update evalhebdo set DateValidation = \"".date('Y-m-d')."\" where IDEleve = $IDEleve and DateValidation is NULL";
		//}
		//echo $requete;
		mysqli_query($connexionDB,$requete);
		if($vue==1) {
			// si vue semaine, valider journaux de la semaine également
			$requete = "update journal set DateValidation = \"".date('Y-m-d')."\" where IDEleve = $IDEleve and DateValidation is null and (DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
			//echo $requete;
			mysqli_query($connexionDB,$requete);
		} else {
			// si vue theme, valider tous les journaux jusqu'à la semaine précédente
			$requete = "update journal set DateValidation = \"".date('Y-m-d')."\" where IDEleve = $IDEleve and DateValidation is null and IDTheme=".$IDTheme." and DateJournal < '".date('Y-m-d', $lundi)."'";
			//echo $requete;
			mysqli_query($connexionDB,$requete);
		}

	} else if($_POST['validation']=="unlockEval") {
		//if($vue==1) {
			$requete = "update evalhebdo set DateValidation = null where IDEleve = $IDEleve and NoSemaine = $noSemaine and Annee = $anneeCalc";
		//} else {
		//	$requete = "update evalhebdo set DateValidation = null where IDEleve = $IDEleve and DateValidation = \"".$_POST['validation']."\"";
		//}
		//echo $requete;
		mysqli_query($connexionDB,$requete);
	}
}
if(isset($_POST['evaluation'])&&!empty($_POST['evaluation'])) {
	//echo "<br>tdid: ".$_POST['evaluation'];
	//echo "<br>semaine: ".$noSemaine;
	//echo "<br>année: ".$anneeCalc;
	//echo "<br>idEleve: ".$IDEleve;
	$compSel = substr($_POST['evaluation'],4,2);
	if(99==$compSel) {
		$typeSel = substr($_POST['evaluation'],6,1);
	} else {
		$compSel = substr($_POST['evaluation'],4,1);
		$typeSel = substr($_POST['evaluation'],5,1);
	}
	//echo "<br>compétence: ".$compSel;
	//echo "<br>type: ".$typeSel;
	//echo "<br>évaluation: ".$_POST['eval'.$_POST['evaluation']];
	//echo "<br>observation: ".$_POST['obs'.$_POST['evaluation']];
	$noteEval = (!empty($_POST['eval'.$_POST['evaluation']])?$_POST['eval'.$_POST['evaluation']]:"NULL");

	$obsEval = $_POST['obs'.$_POST['evaluation']];
	if(empty($obsEval)) {
		$obsEval = "NULL";
	} else {
		$obsEval = "\"".$obsEval."\"";
	}
	$nivEval = (!empty($_POST['niv'.$_POST['evaluation']])?$_POST['niv'.$_POST['evaluation']]:"NULL");

	$uid = $_SESSION['user_login'];
	if(empty($IDTheme)) {
		$idth = 10;
	} else {
		$idth = $IDTheme;
		//$noSemaine = date('W'); // on s'assure de prendre le no de semaine actuel
	}
	$requete = "replace into evalhebdo (IDEleve,NoSemaine,Annee,IDTypeEval,IDCompetence,Note,Niveau,Remarque,Responsable, IDTheme) values ($IDEleve,$noSemaine,$anneeCalc,$typeSel,$compSel,$noteEval,$nivEval,$obsEval,\"$uid\", $idth)";
	//echo $requete;
	mysqli_query($connexionDB,$requete);
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

function toggleTable(thisname) {
	tbl=document.getElementsByTagName('table')
	for (i=0;i<tbl.length;i++){
		if (tbl[i].getAttribute(thisname)){
			if ( tbl[i].style.display=='none' ){
				tbl[i].style.display = '';
				document.getElementsByName(thisname)[0].value='';
			} else {
				tbl[i].style.display = 'none';
				document.getElementsByName(thisname)[0].value='none';
			}
		}
	}
}

function toggleNot(thisname) {

}

function selectItemByValue(name, value){

  elmnt = document.getElementById(name);

  for(var i=0; i < elmnt.options.length; i++)
  {
    if(elmnt.options[i].value == value) {
      elmnt.selectedIndex = i;
      break;
    }
  }
}
function submitTheme(activite) {
	document.getElementById('myForm').activite.value=activite;
	document.getElementById('myForm').submit();
}
function submitActivite(activite) {
	document.getElementById('myForm').activite.value=activite;
	document.getElementById('myForm').submit();
}

function stopActivite(activite,heure) {
	document.getElementById('myForm').activite.value=activite;
	document.getElementsByName('HeureFin'+activite)[0].value=heure;
	document.getElementById('myForm').submit();
}
function setHeureFin(activite,heure) {
	document.getElementsByName('HeureFin'+activite)[0].value=heure;
}

function submitSemaineAnnee(nosemaine,annee) {
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').anneeCalc.value=annee;
	document.getElementById('myForm').submit();
}
function submitSemaine(nosemaine) {
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').submit();
}
function submitValidation(action,nosemaine,annee) {
	document.getElementById('myForm').validation.value=action;
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').anneeCalc.value=annee;
	document.getElementById('myForm').submit();
}
function submitRemarque(id,action) {
	document.getElementById('myForm').IDRemarque.value=id;
	document.getElementById('myForm').actionRemarque.value=action;
	document.getElementById('myForm').submit();
}

function submitEvalution(id,nosemaine,annee) {
	document.getElementById('myForm').evaluation.value=id;
	document.getElementById('myForm').noSemaine.value=nosemaine;
	document.getElementById('myForm').anneeCalc.value=annee;
	document.getElementById('myForm').submit();
	unique=false;
}

function limitEvent(e) {
    if (window.event) { //IE
        window.event.cancelBubble = true;
    } else if (e && e.stopPropagation) { //standard
        e.stopPropagation();
    }
}

function selectList(select,date) {
	//alert(select+" "+date);
	var sel = document.getElementById(select);
	for(var i = 0, j = sel.options.length; i < j; ++i) {
		//alert(date+" "+sel.options[i].value);
        	if(sel.options[i].value == date) {
           		sel.selectedIndex = i;
         	  	break;
        	}
    	}
}





</script>
<?php
include("../../userInfo.php");
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
		$text = preg_replace('/--(.*?)--/', '<strike>$1</strike>', $text);
        //$text = preg_replace('/\[\[Image:(.*?)\|(.*?)\]\]/', '<img src="$1" alt="$2" title="$2" />', $text);
        $text = preg_replace('/\[(.*?) (.*?)\]/', '<a href="$1" title="$2" target="ext" onclick="limitEvent(event)"><u>$2</u></a>', $text);
        //$text = preg_replace('/&gt;(.*?)\n/', '<blockquote>$1</blockquote>', $text);

        //$text = preg_replace('/\* (.*?)/', '<ul><li>$1</li></ul>', $text);
        //$text = preg_replace('/<\/ul>\n<ul>/', '', $text);
		
		$text = preg_replace("/\*\*(.*)?/i","<ull><li>$1</li></ull>",$text); // liste 2ème niveau
		$text = preg_replace("/(\<\/ull\>\n(.*)\<ull\>)+/","",$text); // correction entre lignes
		$text = preg_replace("/\*(.*)?/i","<ul><li>$1</li></ul>",$text); // liste premier niveau
		$text = preg_replace("/(\<\/ul\>\n(.*)\<ul\>)+/","",$text); // correction entre lignes
		$text = preg_replace('/(\<\/ul\>\n(.*)\<ull\>)+/', '<li style="list-style-type:none"><ul>', $text); // correction entre niveau 1 et 2
        $text = preg_replace('/(\<\/ull\>\n(.*)\<ul\>)+/', '</ul></li>', $text); // correction entre niveau 2 et 1
		$text = preg_replace('/(\<\/ull\>\n)+/', '</ul></li></ul>', $text); // correction entre niveau 2 et fin
		
        //$text = preg_replace("/\#(.*)?/i", '<ol><li>$1</li></ol>', $text);
        //$text = preg_replace("/(\<\/ol\>\n(.*)\<ol\>)+/","",$text);
		$text = preg_replace("/\#\#(.*)?/i","<oll><li>$1</li></oll>",$text); // liste 2ème niveau
		$text = preg_replace("/(\<\/oll\>\n(.*)\<oll\>)+/","",$text); // correction entre lignes
		$text = preg_replace("/\#(.*)?/i","<ol><li>$1</li></ol>",$text); // liste premier niveau
		$text = preg_replace("/(\<\/ol\>\n(.*)\<ol\>)+/","",$text); // correction entre lignes
		$text = preg_replace('/(\<\/ol\>\n(.*)\<oll\>)+/', '<li style="list-style-type:none"><ol>', $text); // correction entre niveau 1 et 2
        $text = preg_replace('/(\<\/oll\>\n(.*)\<ol\>)+/', '</ol></li>', $text); // correction entre niveau 2 et 1
		$text = preg_replace('/(\<\/oll\>\n)+/', '</ol></li></ol>', $text); // correction entre niveau 2 et fin

        //$text = str_replace("\r\n\r\n", '</p><p>', $text);
        $text = str_replace("\r\n", '<br/>', $text);
        //$text = '<p>'.$text.'</p>';
        return $text;
}

$helptxt = "<b><i>Syntaxe:</i></b><br><br>";
$helptxt .= "== Titre 1 ==<br>";
$helptxt .= "=== Titre 2 ===<br>";
$helptxt .= "==== Titre 3 ====<br><br>";
$helptxt .= "\'\'Italic\'\'<br>";
$helptxt .= "\'\'\'Gras\'\'\'<br>";
$helptxt .= "--Barré--<br><br>";
$helptxt .= "[adresse lien]<br><br>";
$helptxt .= "* liste à puce <br>";
$helptxt .= "** liste à puce indentée <br><br>";
$helptxt .= "# liste à numéro <br>";
$helptxt .= "## liste à numéro indentée";

//function jourSemaine($lundi, $jour) {
	//$jSem = array(0=>"Lu","Ma","Me","Je","Ve");
	//echo "!".($jour%7)."-".($lundi%7)."-".$jSem[(($jour%7)-($lundi%7))/86400]."!";
	//date('w', strtotime($date));
	//return $jSem[($jour-$lundi)/86400];
//}
function jourSemaine($jour) {
	$jSem = array(0=>"Lu","Ma","Me","Je","Ve");
	//echo "!".($jour%7)."-".($lundi%7)."-".$jSem[(($jour%7)-($lundi%7))/86400]."!";
	// return date('w', $jour);
	return $jSem[date('w', strtotime($jour))-1];
}

function niveauMoy($moyenne) {
	return chr(round($moyenne,0,PHP_ROUND_HALF_DOWN)+64);
}
function noteMoy($moyenne) {
	return round($moyenne,1,PHP_ROUND_HALF_UP);
}

echo "<FORM id='myForm' ACTION='activites.php'  METHOD='POST'>";
// transfert info
//echo "<input type='hidden' name='vue' value='".$vue."'>";
echo "<input type='hidden' name='IDEleve' value='$IDEleve'>";
echo "<input type='hidden' name='nom' value='".htmlentities($nom,ENT_QUOTES)."'>";
echo "<input type='hidden' name='prenom' value='".htmlentities($prenom,ENT_QUOTES)."'>";
echo "<input type='hidden' name='activite' value=''>";
echo "<input type='hidden' name='noSemaine' value=''>";
echo "<input type='hidden' name='anneeCalc' value=''>";
echo "<input type='hidden' name='validation' value=''>";
echo "<input type='hidden' name='from' value='".$from."'>";
echo "<input type='hidden' name='Classe' value='".$classe."'>";
echo "<input type='hidden' name='IDRemarque' value=''>";
echo "<input type='hidden' name='actionRemarque' value=''>";
echo "<input type='hidden' name='evaluation' value=''>";
echo "<input type='hidden' name='tblActivite' value='".(isset($_POST['tblActivite'])?$_POST['tblActivite']:"")."'>";
echo "<input type='hidden' name='tblSuivi' value='".(isset($_POST['tblSuivi'])?$_POST['tblSuivi']:"")."'>";
echo "<input type='hidden' name='tblEval' value='".(isset($_POST['tblEval'])?$_POST['tblEval']:"")."'>";

echo "<div class='post'>";
if(!empty($msgErreur)) {
	echo "<center>".$msgErreur."</center>";
}
//echo "<center><font color='#088A08'>Nouveauté: Les erreurs de saisie sont affichées dans la semaine concernée.</font></center>";
$noSemMoinsUn = date('W',$lundi - 86400*7);
if($noSemaine==1) {
	$anneeMoinsUn = $anneeCalc-1;
} else {
	$anneeMoinsUn = $anneeCalc;
}
//$anneeMoinsUn = date('Y',$lundi - 86400*7);
$noSemPlusUn = date('W',$lundi + 86400*7);
if($noSemPlusUn==1) {
	$anneePlusUn = $anneeCalc+1;
} else {
	$anneePlusUn = $anneeCalc;
}
//$anneePlusUn = date('Y',$lundi + 86400*7);



echo "<table border='0' width='100%'><tr><td width='33%'>";
if(hasAdminRigth()) {
	echo "<h2>";
	if(isset($listeId)&&!empty($listeId)) {
		foreach($listeId as $key => $valeur) {
			if($valeur[0]==$IDEleve) {
				if($key!=0) {
					echo "<a href='activites.php?from=".$from."&nom=".$listeId[$key-1][1]."&prenom=".$listeId[$key-1][2]."&idEleve=".$listeId[$key-1][0]."&Classe=".urlencode($listeId[$key-1][3])."'>";
					echo "<img id='prev' src='/iconsFam/resultset_previous.png'></a>";
				}
				echo $nom." ".$prenom;
				if($key<count($listeId)-1) {
					echo "<a href='activites.php?from=".$from."&nom=".$listeId[$key+1][1]."&prenom=".$listeId[$key+1][2]."&idEleve=".$listeId[$key+1][0]."&Classe=".urlencode($listeId[$key+1][3])."'>";
					echo "<img id='next' src='/iconsFam/resultset_next.png'></a>";
				}
				$classe = $listeId[$key][3];
				break;
			}
		}
	} else {
		echo $nom." ".$prenom;
	}
	echo "</h2>";
} else {
	//echo "<h2>".$nom." ".$prenom."</h2>";
}
echo "</td><td align='center' width='33%'>";
if($vue==1) {
	echo "<h2><img id='up' src='/iconsFam/resultset_previous.png' onClick='submitSemaineAnnee(".$noSemMoinsUn.",".$anneeMoinsUn.")'>Semaine ".$noSemaine;
	if($vendredi<mktime(0, 0, 0, date('m'), date('d'), date('y'))) {
		echo "<img id='down' src='/iconsFam/resultset_next.png' onClick='submitSemaineAnnee(".$noSemPlusUn.",".$anneePlusUn.")'>";
	}
	echo "</h2>";
}
echo "</td><td align='right'>\n";
if($vue==1) {
	//echo "Tri: <select name='tri'><option value='1'>Semaine</option><option value='2'>Mois</option><option value='3'>Année</option><option value='4'>Tous</option></select>";
	echo "Vue: <select name='vue' onChange='document.getElementById(\"myForm\").submit();'><option value='1'>Semaine</option><option value='2'>Thème</option></select>";
} else {
	echo "Vue: <select name='vue' onChange='document.getElementById(\"myForm\").submit();'><option value='1'>Semaine</option><option value='2' selected>Thème</option></select>";
}
echo "</td></tr>";

// recherche du theme en cours selon journal de l'apprenti si pas donné en paramètre, pour la vue thème et si pas donné en paramètre
$lastIDTheme;

	if(empty($IDQuery)) {
		$requete = "SELECT jo.IDTheme from journal as jo join theme as th on jo.IDTheme=th.IDTheme  where IDEleve=".$IDEleve." and TypeTheme < 2 order by DateJournal desc limit 1";
		$resultat =  mysqli_query($connexionDB,$requete);
		$ligne = mysqli_fetch_row($resultat);
		if($vue==2) {
			$IDQuery = $ligne[0];
		}
		$lastIDTheme = $ligne[0];
	} else {
		$lastIDTheme = $IDQuery;
	}


// requete pour liste des themes de la personne et contruction du select
//$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (IDEtatProjet=1 and IDEleve = $IDEleve) OR (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (TypeTheme=2) order by TypeTheme, NomTheme";
$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (TypeTheme=1 and IDEleve = $IDEleve) OR (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) order by TypeTheme, NomTheme";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
$option = "";
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$option .= "<option value=".$ligne['IDTheme'];
	if($lastIDTheme==$ligne['IDTheme']) {
		$option .= " selected";
	}
	$option .= ">";
	if($ligne['TypeTheme']==1) {
		$option .= "Projet - ";
	}
	$option .= $ligne['NomTheme']."</option>";
}
$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (IDEtatProjet=1 and IDEleve = $IDEleve) OR (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (TypeTheme=2) order by TypeTheme, NomTheme";
//$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (TypeTheme=1 and IDEleve = $IDEleve) OR (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) order by TypeTheme, NomTheme";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
$optionAPP = "";
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$optionAPP .= "<option value=".$ligne['IDTheme'];
	if($lastIDTheme==$ligne['IDTheme']) {
		$optionAPP .= " selected";
	}
	$optionAPP .= ">";
	if($ligne['TypeTheme']==1) {
		$optionAPP .= "Projet - ";
	}
	$optionAPP .= $ligne['NomTheme']."</option>";
}


if($vue==1) {
	/* calcul du lundi */
	$lundiTxt =  "Semaine du lundi " . date('d.m', $lundi) . " au vendredi ".date('d.m.Y', $vendredi);
	echo "<tr><td></td><td colspan='1' align='center'>".$lundiTxt." <div id='circlesel' onmouseover=\"Tip('Semestre ".$semestreAct."')\" onmouseout='UnTip()'>".$semestreAct." </div></td>";
	echo "<td align='right'>";

	echo "</td></tr>";
} else {
	//$IDQuery = $IDThemeJrn;
	//echo "<tr><td colspan='3' align='right'>";
	echo "<tr><td></td><td align='center'>";
	echo " Période: <select name='triPeriode' onChange='document.getElementById(\"myForm\").submit();'>";
	$txtSelected = " selected";
	echo "<option value='1'".($triPeriode==1?$txtSelected:"").">Semestre en cours</option>";
	echo "<option value='2'".($triPeriode==2?$txtSelected:"").">Année en cours</option>";
	// recherche dernière date d'évaluation pour le thème donné
	$requete = "SELECT noSemaine, annee FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$IDQuery and Datevalidation is not null order by annee desc ,noSemaine desc LIMIT 1";
	$resultat =  mysqli_query($connexionDB,$requete);
	if(!empty($resultat)&&mysqli_num_rows($resultat)>0) {
		$ligne = mysqli_fetch_assoc($resultat);
	}
	if(!empty($ligne['noSemaine'])) {
		$dateCalcEval=mktime(0,0,0,1,4,$ligne['annee']);
		//echo date("d.m.Y",$dateCalcEval);
		$jour_semaineEval=date("N",$dateCalcEval);
		$maxDateEval = $dateCalcEval-86400*($jour_semaineEval-1)+604800*($ligne['noSemaine']);
		echo "<option value='3'".($triPeriode==3?$txtSelected:"").">Non évalué</option>";
	} else {
		if($triPeriode==3) $triPeriode=2;
	}
	echo "<option value='0'".($triPeriode==0?$txtSelected:"").">Tout</option>";
	echo "</select>";
	echo "</td><td align='right'><nobr>";
	echo "Thème: <select name='IDThemeSel' onChange='document.getElementById(\"myForm\").submit();'>";
	if(hasAdminRigth()) {
		echo "<option value='2'";
		if($IDQuery==2) echo " selected";
		echo ">Tout</option>";
		echo "<option value='1'";
		if($IDQuery==1) echo " selected";
		echo ">CPG - Remarque</option>";
	}
	echo $option;
	echo "</select>";
	//echo "</td></tr>";
	// ajout tri sur période
	//echo "<tr><td colspan='3' align='right'>";

	echo "</nobr></td></tr>";


}
echo "</table>";
echo "<br><div id='corners'>";
echo "<div id='legend' onClick='toggleTable(\"tblActivite\")'>Activités</div>";
if($vue==1) {
	if(!$triSemaine) {
		echo "<div id='criteres' width='100%' align='right'><i>Tri par thème &nbsp;<a href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&triSem=1'><img src='/iconsFam/table_refresh.png' style='vertical-align: middle;' onmouseover=\"Tip('Changer de tri par jour')\" onmouseout='UnTip()'></a></i></div>";
	} else {
		echo "<div id='criteres' width='100%' align='right'><i>Tri par jour &nbsp;<a href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&triSem=0'><img src='/iconsFam/table_refresh.png' style='vertical-align: middle;' onmouseover=\"Tip('Changer de tri par thème')\" onmouseout='UnTip()'></a></i></div>";
	}
} else {
	// si pas vue semaine, on n'active pas le tri par jour
	$triSemaine = 0;
}

//$triSemaine = 0; // 0 = thème, 1 = jour
// requete principale
if(empty($IDQuery)) {
	// requete pour la semaine donnée
	$requete = "SELECT * FROM journal jou join theme th on jou.IDTheme=th.IDTheme where IDEleve = $IDEleve and (DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
	if(!$triSemaine) {
		$requete .= " order by jou.IDTheme, DateJournal, HeureDebut";
	} else {
		$requete .= " order by DateJournal, HeureDebut";
	}
} else {
	if($IDQuery==2) {
		// tout
		$requete = "SELECT * FROM journal jou join theme th on jou.IDTheme=th.IDTheme where IDEleve = $IDEleve";
	} else {
		// requete pour le theme donné
		$requete = "SELECT * FROM journal jou join theme th on jou.IDTheme=th.IDTheme where IDEleve = $IDEleve and jou.IDTheme = $IDQuery";
	}
	//if($noSemaine>30) {
	//	$requete .= "and (DateJournal between '".$anneeCalc."-08-01' and '".($anneeCalc+1)."-07-31') order by DateJournal";
	//} else {
	//	$requete .= "and (DateJournal between '".($anneeCalc-1)."-08-01' and '".$anneeCalc."-07-31') order by DateJournal";
	//}
	switch($triPeriode) {
		case 1: $requete .= " and (DateJournal ".$betweenSQLSem.")"; echo "<div id='criteres' width='100%' align='right'><i>Semestre ".$semestreAct."</i></div>"; break;
		case 2: $requete .= " and (DateJournal ".$betweenSQLAnn.")"; break;
		case 3: $requete .= " and (DateJournal >= '".date("Y-m-d",$maxDateEval)."')"; echo "<div id='criteres' width='100%' align='right'><i>Période dès le ".date("d.m.Y",$maxDateEval).", semaine ".date("W",$maxDateEval)."</i></div>"; break;
	}
	if($IDQuery==2) {
		$requete .= " order by jou.IDTheme, DateJournal";
	} else {
		$requete .= " order by DateJournal";
	}
}
//echo $requete;
echo "<table id='hor-minimalist-b' width='100%' tblActivite='1'>\n";
if(!$triSemaine) {
	echo "<tr><th width='105'>Thème</th><th width='90' align='center' onClick=\"location.href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&triSem=1'\">Date</th><th width='85' align='center'>Heures</th><th>Activités</th><th width='25' align='right'>";
} else {
	echo "<tr><th width='35'>Date</th><th width='250' onClick=\"location.href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&triSem=0'\">Thème</th><th width='85' align='center'>Heures</th><th>Activités</th><th width='25' align='right'>";
}
if(empty($IDQuery)) {
	echo "<a href='impressionJournalPDF.php?IDEleve=".$IDEleve."&noSemaine=".$noSemaine."&annee=".$anneeCalc."&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&triSemaine=".$triSemaine."' target='pdf'><img src='/iconsFam/page_white_acrobat.png' onmouseover=\"Tip('Imprimer le journal')\" onmouseout='UnTip()'></a>";
} else {
	if($IDQuery!=1 && $IDQuery!=2) {
		// ne pas proposer d'imprimmer si CPG selectionné
		echo "<a href='impressionJournalPDF.php?IDEleve=".$IDEleve."&IDTheme=".$IDQuery."&annee=".$anneeCalc."&noSemaine=".$noSemaine."&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&tri=".$triPeriode."' target='pdf'><img src='/iconsFam/page_white_acrobat.png' onmouseover=\"Tip('Imprimer le journal')\" onmouseout='UnTip()'></a>";
	}
}
echo "</th></tr>";
$resultat =  mysqli_query($connexionDB,$requete);
$cnt=0;
$last = 0;
$total = 0;
$totalPage = 0;
$cntlock = 0;
$cntunlock = 0;
$nbrJour = array();
$lastJour = 0;
$cntErreurSaisie=0;
$ligneHeures = array();
//$objectif = 0;
while ($ligne = mysqli_fetch_assoc($resultat)) {
	$idJournal = $ligne['IDJournal'];
	//$idTh = $ligne['IDTheme'];
	$lastJour = $ligne['DateJournal'];
	$lastIDTheme = $ligne['TypeTheme'];
	if (isset($nbrJour[$lastJour])) {
		$nbrJour[$lastJour] = $nbrJour[$lastJour] + $ligne['Heures'];
	} else {
		$nbrJour[$lastJour] = $ligne['Heures'];
	}
	if(!$triSemaine) {
		if($last!=$ligne['IDTheme']) {
			if($last!=0) {
				echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
				echo "<tr><td><i>Total thème</i></td><td></td><td align='center'>".round($total,2)."h</td><td>";
				if($total!=$ligneHeures['heures']) {
					echo "(cumul sur l'année scolaire: ".round($ligneHeures['heures'],2)."h";
					//if($objectif!=0) {
					//	echo "/".$objectif."h";
					//}
					echo ")";
				}
				echo "</td><td align='center'>";
				/*
				if(hasAdminRigth()&&empty($IDQuery)) {
					echo "<img src='/iconsFam/comment_add.png'  onclick='toggle(\"newremarque$last\")' onmouseover=\"Tip('Ajouter remarque')\" onmouseout='UnTip()'>";
				} */
				echo "</td></tr>";

				$total = 0;
				/*
				$total = 0;
					if(empty($IDQuery)) {
						$requeteTri = "(DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
					} else {
						if($noSemaine>30) {
							$requeteTri = "(DateSaisie between '".$anneeCalc."-08-01' and '".($anneeCalc+1)."-07-31')";
						} else {
							$requeteTri = "(DateSaisie between '".($anneeCalc-1)."-08-01' and '".$anneeCalc."-07-31')";
						}
					}
					if($lastIDTheme==1) {
						//$requeteRem = "SELECT IDSuivi as IDRemSuivi, RemarqueSuivi as Remarque, DateSaisie, 0 as TypeRemarque FROM suiviprojet as suivi join projets as proj on suivi.IDProjet=proj.IDProjet where IDEleve=".$IDEleve." and IDTheme=".$last." and (DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') UNION SELECT IDRemSuivi,Remarque,DateSaisie,TypeRemarque FROM remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$last." and (DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') order by DateSaisie";
						$requeteRem = "SELECT IDSuivi as IDRemSuivi, RemarqueSuivi as Remarque, DateSaisie, 0 as TypeRemarque FROM suiviprojet as suivi join projets as proj on suivi.IDProjet=proj.IDProjet where IDEleve=".$IDEleve." and IDTheme=".$last." and ".$requeteTri." UNION SELECT IDRemSuivi,Remarque,DateSaisie,TypeRemarque FROM remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$last." and ".$requeteTri." order by DateSaisie";
					} else {
						$requeteRem = "SELECT * FROM remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$last." and ".$requeteTri." order by DateSaisie";
					}
					//echo "requ ".$requeteRem;
					$resultatRem =  mysqli_query($connexionDB,$requeteRem);
					if(!empty($resultatRem)) {
						while ($ligneRem = mysqli_fetch_assoc($resultatRem)) {
							$idRem = $ligneRem['IDRemSuivi'];
							$txtrem = $ligneRem['Remarque'];
							$typerem = $ligneRem['TypeRemarque'];
							//$daterem = date('d.m.Y', strtotime($ligneRem['DateSaisie']));
							if(!empty($txtrem)) {
								if($typerem<=1&&hasAdminRigth()) {
									echo "<tr remarque$idRem='1' onclick='toggle(\"remarque$idRem\")'><td valign='top'><i>Remarque suivi</i></td><td valign='top'></td><td align='center'></td><td><i>".wiki2html($txtrem)."</i></td><td align='center' valign='top'>";
									if(empty($IDQuery)&&$lastIDTheme!=1) {
										echo "<a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette remarque')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a>";
									}
									echo "</td></tr>";
									echo "<tr remarque$idRem='1' style='display:none' onclick='toggle(\"remarque$idRem\")'><td valign='top'>Remarque suivi</td><td valign='top'></td><td align='center'></td><td><textarea name='Remarque$idRem' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$txtrem."</textarea></td><td valign='top'><input type='button' name='ModifRem' value='Modifier' onclick='submitRemarque(\"$idRem\",\"modif\")'></input></td></tr>";
								} else if($typerem==2&&empty($IDQuery)) {
									if(hasAdminRigth()) {
										echo "<tr><td valign='top'><b><font color='#FF0000'>A corriger</font></b></td><td valign='top'></td><td align='center'></td><td><b><font color='#FF0000'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=corrected'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Marquer l\'erreur comme corrigée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
										//echo "<tr remarque$idRem='1' style='display:none' onclick='toggle(\"remarque$idRem\")'><td valign='top'>A corriger</td><td valign='top'></td><td align='center'></td><td colspan='2'><textarea name='RemarqueJournal' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$txtrem."</textarea></td></tr>";
									} else {
										echo "<tr><td valign='top'><b><font color='#FF0000'>A corriger</font></b></td><td valign='top'></td><td align='center'></td><td><b><font color='#FF0000'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=corrected'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Marquer l\'erreur comme corrigée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
									}
									$cntErreurSaisie++;
								} else if($typerem==3&&hasAdminRigth()&&empty($IDQuery)) {
									echo "<tr><td valign='top'><font color='#FF7F00'>A vérifier</font></td><td valign='top'></td><td align='center'></td><td><font color='#FF7F00'>".wiki2html($txtrem)."</font></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Clôturer l\'erreur')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
								}
							}
						}
					}
					$txtrem = "";
					echo "<tr newremarque$last='1' style='display:none' onclick='toggle(\"newremarque$last\")'><td valign='top'><select name='TypeRemarque$last' onclick='limitEvent(event)'><option value='1'>Remarque suivi</option><option value='2'>A corriger</option></select></td><td valign='top'><input type='hidden' name='DateRemarque$last' value='".date('Y-m-d',$vendredi)."'></input></td><td align='center'></td><td><textarea name='Remarque$last' COLS=60 ROWS=20 onclick='limitEvent(event)'></textarea></td><td valign='top'><input type='button' name='AjoutRem' value='Ajouter' onclick='submitRemarque(\"$last\",\"ajout\")'></input></td></tr>";
				echo "<tr><td colspan='5' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
				*/
			}
			echo "<tr><td valign='top' colspan='2' bgColor='#DEDEDE'><b>";
			if($lastIDTheme==1) {
				echo "Projet - ";
			}
			// recherche du nombre d'heures déjà effectuées pour le thème sur l'année scolaire entière
			$requeteTot = "SELECT IDTheme, sum( heures ) AS heures, count( heures ) AS jours FROM (SELECT  jo.IDTheme as IDTheme, sum( Heures ) AS heures FROM elevesbk JOIN journal jo ON IDGDN = IDEleve JOIN theme th ON jo.IDTheme=th.IDTheme";
			if($noSemaine>30) {
				$requeteTot .= " where (DateJournal between '".$anneeCalc."-08-01' and '".date('Y-m-d', $vendredi)."') and IDGDN=".$IDEleve." and jo.IDTheme=".$ligne['IDTheme'];
			} else {
				$requeteTot .= " where (DateJournal between '".($anneeCalc-1)."-08-01' and '".date('Y-m-d', $vendredi)."') and IDGDN=".$IDEleve." and jo.IDTheme=".$ligne['IDTheme'];
			}
			$requeteTot .= " GROUP BY jo.IDTheme, DateJournal) AS res GROUP BY IDTheme";
			//echo "Heures: ".$requeteTot."<br>";
			$resultatTot =  mysqli_query($connexionDB,$requeteTot);
			$ligneHeures = mysqli_fetch_assoc($resultatTot);
			//$objectif = $ligne['Objectif'];
			echo $ligne['NomTheme']."</b></td><td align='center' bgColor='#DEDEDE'>";
			if($ligne['Objectif']!=0) {
				echo "<b onmouseover=\"Tip('Objectif du thème')\" onmouseout='UnTip()'>".$ligne['Objectif']."h</b>";
			}
			echo "</td><td colspan='2' bgColor='#DEDEDE'></td></tr>";
			//echo "<tr><td colspan='5' valign='bottom' valign='bottom' bgColor='#DEDEDE'></td></tr>";
			//echo "<tr journal$idJournal='1' onclick='toggle(\"journal$idJournal\")'><td></td>";
			$last = $ligne['IDTheme'];
		}
	} else {
		// tri par jour -> total par jour si nouveau jour
		if($last != $ligne['DateJournal']) {
			if($last != 0) {
				echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
				echo "<tr><td><i>Total jour</i></td><td></td><td align='center'>".round($nbrJour[$last], 2)."h</td><td>";
				echo "</td><td></td></tr>";
			}
			echo "<tr><td valign='top' colspan='2' bgColor='#DEDEDE'><b>";
			echo jourSemaine($ligne['DateJournal'])." ";
			echo date('d.m.Y', strtotime($ligne['DateJournal']))."</b>";
			echo "</td><td colspan='3' bgColor='#DEDEDE'></td></tr>";
			$last = $ligne['DateJournal'];
		}
	}
	//} else {
	//	echo "<tr journal$idJournal='1' onclick='toggle(\"journal$idJournal\")'><td></td>";
	//}
	if(hasAdminRigth()) {
		echo "<tr onClick=\"location.href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&DateJournal=$ligne[DateJournal]&vue=1'\">";
	} else {
		if($ligne['DateValidation']!=0) {
			echo "<tr journal$idJournal='1'>";
		} else {
			if(empty($IDQuery)) {
				echo "<tr journal$idJournal='1' onclick='toggle(\"journal$idJournal\")'>";
			} else {
				echo "<tr onClick=\"location.href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&DateJournal=$ligne[DateJournal]&vue=1'\">";
			}
		}
	}
	if(!$triSemaine) {
		// on inscrit rien sous thème (donnée dans le groupe)
		echo "<td></td>";
	} else {
		// vue par jour, on inscrit le thème sur chaque ligne
		echo "<td></td><td valign='top'>";
		if($lastIDTheme==1) {
			echo "Projet - ";
		}
		echo $ligne['NomTheme'];
		echo "</td>";
	}
	if(!$triSemaine) {
		echo "<td valign='top' align='center'>";
		//if(empty($IDQuery)) {
			//echo jourSemaine($lundi,strtotime($ligne['DateJournal']))." ";
			echo jourSemaine($ligne['DateJournal'])." ";
		//}
		echo date('d.m.Y', strtotime($ligne['DateJournal']))."</td>";
	}
	if($ligne['HeureDebut']!='00:00:00') {
		echo "<td valign='top' align='center'>";
		echo substr($ligne['HeureDebut'],0,5)."-";
		if($ligne['HeureFin']!='00:00:00') {
			echo substr($ligne['HeureFin'],0,5);
		} else {
			echo " ... ";
		}
	} else {
		echo "<td valign='top' align='center'>";
		if($nbrJour[$lastJour]>9) {
			echo "<font color='#FF0000'>".round($ligne['Heures'], 2)."h</font>";
			$msgErreur .= "Heures journalières > 9h<br>";
			echo "<img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('".$msgErreur."')\" onmouseout='UnTip()'>";
		} else {
			echo round($ligne['Heures'], 2)."h";
		}
	}

	echo "</td><td>".wiki2html($ligne['Commentaires'])."</td><td align='right' valign='top'>";
	if($ligne['DateValidation']!=0) {
		$cntlock++;
		//echo strtotime($ligne['DateValidation']);
		if(!hasAdminRigth()) {
			echo "<img src='/iconsFam/lock.png' align='absmiddle' onmouseover=\"Tip('Saisie validée le ".date('d.m.Y', strtotime($ligne['DateValidation']))."')\" onmouseout='UnTip()'>";
		} else {
			echo "<img src='/iconsFam/bullet_green.png' align='absmiddle' onmouseover=\"Tip('Saisie validée le ".date('d.m.Y', strtotime($ligne['DateValidation']))."')\" onmouseout='UnTip()'>";
		}
	} else {
		$cntunlock++;
		if(!hasAdminRigth()) {
			if(empty($IDQuery)) {
				if($ligne['Heures']=='0.0') {
					echo "<img src='/iconsFam/clock_stop.png' align='absmiddle' onmouseover=\"Tip('Terminer l\'activité')\" onmouseout='UnTip()' onclick='limitEvent(event);stopActivite(\"$idJournal\",\"$heureAct\")'> ";
				}
				echo "<a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDJournal=$ligne[IDJournal]'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a>";

			}
		} else {
			if($ligne['Heures']=='0.0') {
				echo "<img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('Activité non terminée')\" onmouseout='UnTip()'> ";
			}
			echo "<a href='activites.php?from=journaux&idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&Classe=$classe&DateJournal=$ligne[DateJournal]'><img src='/iconsFam/bullet_red.png' align='absmiddle' onmouseover=\"Tip('Saisie à valider')\" onmouseout='UnTip()'></a>";

		}
	}
	echo "</td></tr>";
	$total += $ligne['Heures'];
	$totalPage += $ligne['Heures'];
	// seconde ligne pour modification
	echo "<tr journal$idJournal='1' style='display:none' onclick='toggle(\"journal$idJournal\")'><td valign='top' colspan='5'><select id='IDTheme".$idJournal."' name='IDTheme".$idJournal."' onclick='limitEvent(event)' style='width: 900px'>".$optionAPP."</select></td>";
	//echo "<td valign='top'><input type='texte' name='DateJournal".$idJournal."' size='8' maxlength='10' value='".date('d.m.Y', strtotime($ligne['DateJournal']))."' onclick='limitEvent(event)'></input></td>";
	echo "<tr journal$idJournal='1' style='display:none' onclick='toggle(\"journal$idJournal\")'><td></td>";
	if(!$triSemaine) {
		echo "<td valign='top'>";
	} else {
		echo "<td valign='top' align='right'>";
	}
	echo "<select id='DateJournal".$idJournal."' name='DateJournal".$idJournal."' onclick='limitEvent(event)'>".$listeJours."</select><script>selectList('DateJournal".$idJournal."','".date('d.m.Y', strtotime($ligne['DateJournal']))."')</script></td>";
	//echo "<td valign='top'><select id='Heures".$idJournal."' name='Heures".$idJournal."' onclick='limitEvent(event)'>".$listeHeures."</select><input type='texte' name='Heures".$idJournal."' size='3' maxlength='5' value='".$ligne['Heures']."' onclick='limitEvent(event)'></input></td>";
	if($ligne['HeureDebut']!='00:00:00') {
		$heureDebutStr = substr($ligne['HeureDebut'],0,5);
		if($heureDebutStr=='00:00') $heureDebutStr = "";
		echo "<td valign='top' align='center'><input type='texte' size='2' id='HeureDebut".$idJournal."' name='HeureDebut".$idJournal."' value='".$heureDebutStr."' onclick='limitEvent(event)'></input><img src='/iconsFam/empty.png'><br><img src='/iconsFam/arrow_down.png'><img src='/iconsFam/empty.png'><br>";
		$heureFinStr = substr($ligne['HeureFin'],0,5);
		if($heureFinStr=='00:00') $heureFinStr = "";
		echo "<input type='texte' size='2' id='HeureFin".$idJournal."' name='HeureFin".$idJournal."' value='".$heureFinStr."' onclick='limitEvent(event)'></input>";
		if(empty($heureFinStr)) {
			echo "<img src='/iconsFam/clock_stop.png' align='absmiddle' onmouseover=\"Tip('Terminer l'activité')\" onmouseout='UnTip()' onclick='limitEvent(event);setHeureFin(\"$idJournal\",\"$heureAct\")'>";
		} else {
			echo "<img src='/iconsFam/empty.png'>";
		}
	} else {
		echo "<td valign='top'><select id='Heures".$idJournal."' name='Heures".$idJournal."' onclick='limitEvent(event)'>".$listeHeures."</select><script>selectList('Heures".$idJournal."','".$ligne['Heures']."')</script></td>";
	}
	echo "<td><textarea name='Commentaires".$idJournal."' COLS=65 ROWS=20 onclick='limitEvent(event)'>".$ligne['Commentaires']."</textarea></td>";
	echo "<td valign='top' align='right'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitActivite(\"$idJournal\")'></td></tr>";
	echo "<script>selectItemByValue('IDTheme".$idJournal."',".$ligne['IDTheme'].");</script>";
	//echo "<script>alert('".$ligne['IDTheme']."')</script>";
	// nl2br
	$cnt++;
}
if ($cnt==0) {
	echo "<tr><td colspan='5' align='center'><i>Aucun enregistrement</i></td></tr>";
} else {
	if(!$triSemaine) {
		if(empty($IDQuery) || $IDQuery==2) {
			echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
			echo "<tr><td><i>Total thème</i></td><td></td><td align='center'>".round($total,2)."h</td><td>";
			if($total!=$ligneHeures['heures']) {
				echo "(cumul sur l'année scolaire: ".round($ligneHeures['heures'],2)."h";
				//if($objectif!=0) {
				//	echo "/".$objectif."h";
				//}
				echo ")";
			}
			echo "</td><td align='center'>";
			//echo "<tr><td><i>Total thème</i></td><td></td><td align='center'>".$total."h</td><td></td><td  align='center'>";
			/*
			if(hasAdminRigth()&&empty($IDQuery)) {
				echo "<img src='/iconsFam/comment_add.png'  onclick='toggle(\"newremarque$last\")' onmouseover=\"Tip('Ajouter un suivi')\" onmouseout='UnTip()'>";
			} */
			echo "</td></tr>";
		}
	} else {
		echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
		echo "<tr><td><i>Total jour</i></td><td></td><td align='center'>".round($nbrJour[$last], 2)."h</td><td>";
		echo "</td><td></td></tr>";

	}
	/*
		if(empty($IDQuery)) {
			$requeteTri = "(DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
		} else {
			if($noSemaine>30) {
				$requeteTri = "(DateSaisie between '".$anneeCalc."-08-01' and '".($anneeCalc+1)."-07-31')";
			} else {
				$requeteTri = "(DateSaisie between '".($anneeCalc-1)."-08-01' and '".$anneeCalc."-07-31')";
			}
		}
		//if($lastIDTheme==1) {
			//$requeteRem = "SELECT IDSuivi as IDRemSuivi, RemarqueSuivi as Remarque, DateSaisie, 0 as TypeRemarque FROM suiviprojet as suivi join projets as proj on suivi.IDProjet=proj.IDProjet where IDEleve=".$IDEleve." and IDTheme=".$last." and (DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') UNION SELECT IDRemSuivi,Remarque,DateSaisie,TypeRemarque FROM remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$last." and (DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') order by DateSaisie";
		//	$requeteRem = "SELECT IDSuivi as IDRemSuivi, RemarqueSuivi as Remarque, DateSaisie, 0 as TypeRemarque FROM suiviprojet as suivi join projets as proj on suivi.IDProjet=proj.IDProjet where IDEleve=".$IDEleve." and IDTheme=".$last." and ".$requeteTri." UNION SELECT IDRemSuivi,Remarque,DateSaisie,TypeRemarque FROM remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$last." and ".$requeteTri." order by DateSaisie";
		//} else {
			$requeteRem = "SELECT * FROM remarquesuivi where IDEleve=".$IDEleve." and IDTheme=".$last." and ".$requeteTri." order by DateSaisie";
		//}
		//echo "requ ".$requeteRem;
		$resultatRem =  mysqli_query($connexionDB,$requeteRem);
		while ($ligneRem = mysqli_fetch_assoc($resultatRem)) {
			$idRem = $ligneRem['IDRemSuivi'];
			$txtrem = $ligneRem['Remarque'];
			$typerem = $ligneRem['TypeRemarque'];
			//$daterem = date('d.m.Y', strtotime($ligneRem['DateSaisie']));
			if(!empty($txtrem)) {
				if($typerem<=1&&hasAdminRigth()) {
					echo "<tr remarque$idRem='1' onclick='toggle(\"remarque$idRem\")'><td valign='top'><i>Suivi thème</i></td><td valign='top'></td><td align='center'></td><td><i>".wiki2html($txtrem)."</i></td><td align='center' valign='top'>";
					if(empty($IDQuery)) {
						echo "<a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette remarque')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a>";
					}
					echo "</td></tr>";
					echo "<tr remarque$idRem='1' style='display:none' onclick='toggle(\"remarque$idRem\")'><td valign='top'>Remarque suivi</td><td valign='top'></td><td align='center'></td><td><textarea name='Remarque$idRem' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$txtrem."</textarea></td><td valign='top'><input type='button' name='ModifRem' value='Modifier' onclick='submitRemarque(\"$idRem\",\"modif\")'></input></td></tr>";
				} else if($typerem==2&&empty($IDQuery)) {
					if(hasAdminRigth()) {
						echo "<tr><td valign='top'><b><font color='#FF0000'>A corriger</font></b></td><td valign='top'></td><td align='center'></td><td><b><font color='#FF0000'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=corrected'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Marquer l\'erreur comme corrigée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
						//echo "<tr remarque$idRem='1' style='display:none' onclick='toggle(\"remarque$idRem\")'><td valign='top'>A corriger</td><td valign='top'></td><td align='center'></td><td colspan='2'><textarea name='RemarqueJournal' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$txtrem."</textarea></td></tr>";
					} else {
						echo "<tr><td valign='top'><b><font color='#FF0000'>A corriger</font></b></td><td valign='top'></td><td align='center'></td><td><b><font color='#FF0000'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=corrected'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Marquer l\'erreur comme corrigée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
					}
					$cntErreurSaisie++;
				} else if($typerem==3&&hasAdminRigth()&&empty($IDQuery)) {
					echo "<tr><td valign='top'><font color='#FF7F00'>A vérifier</font></td><td valign='top'></td><td align='center'></td><td><font color='#FF7F00'>".wiki2html($txtrem)."</font></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=$nom&prenom=$prenom&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Clôturer l\'erreur')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
				}
			}
		}
		echo "<tr newremarque$last='1' style='display:none' onclick='toggle(\"newremarque$last\")'><td valign='top'><select name='TypeRemarque$last' onclick='limitEvent(event)'><option value='1'>Remarque suivi</option><option value='2'>A corriger</option></select></td><td valign='top'><input type='hidden' name='DateRemarque$last' value='".date('Y-m-d',$vendredi)."'></input></td><td align='center'></td><td><textarea name='Remarque$last' COLS=60 ROWS=20 onclick='limitEvent(event)'></textarea></td><td valign='top'><input type='button' name='AjoutRem' value='Ajouter' onclick='submitRemarque(\"$last\",\"ajout\")'></input></td></tr>";
	echo "<tr><td colspan='5' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
	*/
	$msgErreur = "";
	if(empty($IDQuery)) {
			// vue une semaine
			if($joursATE[$classe] != count($nbrJour)) {
				$msgErreur .= "Semaine incomplète<br>";
			}
	}
	if(empty($classe)) {
		$msgErreur = "";
	}
	echo "<tr><td colspan='5' valign='bottom' bgColor='#DEDEDE'></td></tr>";
	echo "<tr><td><b><i>Total</i></b></td><td align='center'>";

	if(!empty($msgErreur) && $vendredi<mktime(0, 0, 0, date('m'), date('d'), date('y')) ) {
		echo "<font color='#FF0000'>".count($nbrJour)." jours </font>";
		echo "<img src='/iconsFam/error.png' align='absmiddle' onmouseover=\"Tip('".$msgErreur."')\" onmouseout='UnTip()'>";
	} else {
		echo count($nbrJour)." jours ";
	}
	echo "</td><td align='center'>".round($totalPage,2)."h</td><td colspan='2'></td></tr>";


}

// ligne d'ajout


echo "<tr><td colspan='5' bgColor='#5C5C5C'></td></tr>";
echo "<tr newActivite='1' ><td></td><td></td><td></td><td align='right'>";
/*
if(empty($IDQuery)) {
	echo "<a href='impressionJournalPDF.php?IDEleve=".$IDEleve."&noSemaine=".$noSemaine."&annee=".$anneeCalc."&nom=".$nom."&prenom=".$prenom."' target='pdf'><img src='/iconsFam/page_white_acrobat.png'></a> ";
} else {
	echo "<a href='impressionJournalPDF.php?IDEleve=".$IDEleve."&IDTheme=".$IDQuery."&annee=".$anneeCalc."&noSemaine=".$noSemaine."&nom=".$nom."&prenom=".$prenom."&tri=".$triPeriode."' target='pdf'><img src='/iconsFam/page_white_acrobat.png'></a> ";
}*/
echo "</td><td align='right'>";
if(hasAdminRigth()) {
	if(empty($IDQuery)) {
		if($cntunlock!=0) {
			echo "<img src='/iconsFam/page_go.png' onmouseover=\"Tip('Valider le journal')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"lock\",".$noSemaine.",".$anneeCalc.")'>";
		}
		if($cntlock!=0) {
			echo "<img src='/iconsFam/page_delete.png' onmouseover=\"Tip('Dévalider le journal')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"unlock\",".$noSemaine.",".$anneeCalc.")'> ";
		}
	}
} else {
	if(($cntlock==0 || $cntunlock!=0) && empty($IDQuery)) {
		echo "<img src='/iconsFam/add.png' onmouseover=\"Tip('Nouvelle entrée')\" onmouseout='UnTip()' onclick='toggle(\"newActivite\");document.getElementById(\"myForm\").HeuresActivite.select();' align='absmiddle'>";
	}
}
echo "</td></tr>";
echo "<tr newActivite='1' style='display:none'><td colspan='5' valign='bottom' height='30'><b>Nouvelle entrée dans le journal:<b></td></tr>";
echo "<tr newActivite='1' style='display:none'><td valign='top' colspan='5' height='30'><select name='IDTheme' style='width: 900px'>".$optionAPP."</select></td></tr>";
//echo "<td valign='top'><input name='DateActivite' size='8' maxlength='10' value='".date('d.m.Y')."'></input></td>";
echo "<tr newActivite='1' style='display:none'><td valign='center' align='center'>";

echo "</td>";
if(!$triSemaine) {
	echo "<td valign='top' align='center'>";
} else {
	echo "<td valign='top' align='right'>";
}

echo "<select id='DateActivite' name='DateActivite'>".$listeJours."</select><script>selectList('DateActivite','".date('d.m.Y')."')</script></td>";
if(empty($heures)) {
	$heures = "0.0";
}
echo "<td valign='top' align='center' modeClock='1' style='display:none'><select name='HeuresActivite'>".$listeHeures."</select><img src='/iconsFam/clock_play.png' align='absmiddle' onmouseover=\"Tip('Changer de mode de saisie en chrono')\" onmouseout='UnTip()' onclick='toggleTD(\"modeClock\")'></td><td valign='top' align='center' modeClock='1'><input type='texte' size='3' name='HeureDebut' value='".$heureAct."'></input><img src='/iconsFam/clock.png' align='absmiddle' onmouseover=\"Tip('Changer le mode de saisie en durée')\" onmouseout='UnTip()' onclick='toggleTD(\"modeClock\")'><br><img src='/iconsFam/arrow_down.png'><img src='/iconsFam/empty.png'><br><input type='texte' size='3' name='HeureFin' value=''></input><img src='/iconsFam/empty.png'></td>";
echo "<td valign='top'><textarea name='RemarqueActivite' COLS=66 ROWS=20>".$commentaires."</textarea><img src='/iconsFam/help.png' onmouseover=\"Tip('".$helptxt."')\" onmouseout='UnTip()'></td>";
echo "<td valign='top'><input type='submit' name='AjoutActivite' value='Ajouter'></input></td></tr>";
echo "</tr>";
echo "</table></div><br>";
//if(!hasAdminRigth()) {
//	echo "<center> <b>Respectez les <u><a href='/wiki/index.php/Saisie_du_journal_de_travail'>consignes</a></u> pour la saisie de vos activités!</b></center>";
//}
echo "<br>";

// suivi
$requeteTri = "";
	if(empty($IDQuery)) {
		$requeteTri = "(DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
	} else {
		// recherche dates min max dans les journaux du thème si vue theme
		if($vue==2) {
			if($IDQuery!=2) {
				// tous les thèmes
				//$requete = "SELECT min(DateJournal) as min, max(DateJournal) as max FROM journal jou where IDEleve = $IDEleve";
			//} else {
				$requete = "SELECT min(DateJournal) as min, max(DateJournal) as max FROM journal jou where IDEleve = $IDEleve and jou.IDTheme = $IDQuery";
			//}
				switch($triPeriode) {
					case 1: $requete .= " and (DateJournal ".$betweenSQLSem.")"; break;
					case 2: $requete .= " and (DateJournal ".$betweenSQLAnn.")"; break;
					case 3: $requete .= " and (DateJournal >= '".date("Y-m-d",$maxDateEval)."')"; break;
				}
				$resultat =  mysqli_query($connexionDB,$requete);
				//echo $requete;
				if(!empty($resultat)&&mysqli_num_rows($resultat)>0) {
					// des activités existent dans les journaux
					$ligne = mysqli_fetch_assoc($resultat);
					$max = $ligne['max'];
					$min = $ligne['min'];
					// -> calcul du premier lundi au dernier vendredi pour effectuer une recherche sur des semaines complètes
					if(!empty($min)) {
						$lundiMin = date('Y-m-d',strtotime($min)-(date("N",strtotime($min))-1)*86400);
					}
					if(!Empty($max)) {
						$vendrediMax = date('Y-m-d',strtotime($max)+(5-date("N",strtotime($max)))*86400);
					}
				}
			}
		} else {
			// vue semaine, on utilise lund et vendredi comme date max de recherche
			$lundiMin = $lundi;
			$vendrediMax = $vendredi;
		}
		$requeteTri .= "((";
		if($IDQuery!=2) {
			$requeteTri .=  "th.IDTheme=".$IDQuery;
		} else {
			$requeteTri .=  "th.IDTheme<>2"; // workaround pour que la requete fonction avec le AND de la période ci-après
		}
		switch($triPeriode) {
			case 1: $requeteTri .= " and (DateSaisie ".$betweenSQLSem.")"; break;
			case 2: $requeteTri .= " and (DateSaisie ".$betweenSQLAnn.")"; break;
			case 3: $requeteTri .= " and (DateSaisie >= '".date("Y-m-d",$maxDateEval)."')"; break;
		}
		$requeteTri .= ")";
		//if($noSemaine>30) {
			// début d'année scolaire
		//	$requeteTri .= "((th.IDTheme=".$IDQuery." and (DateSaisie between '".$anneeCalc."-08-01' and '".($anneeCalc+1)."-07-31'))";
		//} else {
			// fin d'année scolaire
		//	$requeteTri .= "((th.IDTheme=".$IDQuery." and (DateSaisie between '".($anneeCalc-1)."-08-01' and '".$anneeCalc."-07-31'))";
		//}

		if(!empty($lundiMin)) {
			// si activités trouvés dans journaux, on utilise la période correspondante pour rechercher les CPG
			$requeteTri .= " or (th.IDTheme=1 and (DateSaisie between '".$lundiMin."' and '".$vendrediMax."'))";
		}
		$requeteTri .= ")";
	}
	$showtableSuivi = false;
	if(hasAdminRigth()) {
		//$requeteTri = "(DateSaisie between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
		echo "<div id='corners'>";
		echo "<div id='legend' onClick='toggleTable(\"tblSuivi\")'>Suivi</div>";
		if(!empty($IDQuery)) {
			if(!empty($lundiMin)) {
				echo "<div id='criteres' width='100%' align='right'><i>Période CPG/ADM du lundi ".date('d.m.y', strtotime($lundiMin))." au vendredi ".date('d.m.y',strtotime($vendrediMax))."</i></div>";
			} else {
				if($IDQuery!=2) {
					echo "<div id='criteres' width='100%' align='right'><i>Suivi sans ADM";

					if($IDQuery!=1) {
						echo " ni CPG";
					}
					echo "</i></div>";
				}
			}
		} else {
			//echo "<div id='criteres' width='100%' align='right'><i>Période du lundi ".date('d.m.y', $lundi)." au vendredi ".date('d.m.y',$vendredi)."</i></div>";
		}
		echo "<table id='hor-minimalist-b' border='0' width='100%' tblSuivi='1'>\n";
		$showtableSuivi = true;
		echo "<tr><th width='175'>Concerne</th><th width='90'>Date</th><th width='40'>Auteur</th><th>Remarques</th><th width='10'></th></tr>";
	} else {
		$requeteTri .= " and (TypeRemarque=2 or TypeRemarque=4)";
	}
	$requeteRem = "(SELECT IDRemSuivi, NomTheme, DateSaisie, Remarque, TypeRemarque, abbr, th.IDTheme FROM remarquesuivi rem join theme th on rem.IDTheme=th.IDTheme left outer join prof as pr on rem.UserId=pr.userid where IDEleve=".$IDEleve." and ".$requeteTri.")";
	if(hasAdminRigth()) {
		$requeteRem .= " union (SELECT 0, Nom, Date, Remarque, 0, 'ALL', 0 FROM $tableAttribEleves el join $tableAttribut att on el.IDAttribut=att.IDAttribut where IDEleve = ".$IDEleve." and el.IDAttribut > 100 ";
		if(empty($IDQuery)) {
			$requeteRem .= "and (Date between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."')";
		} else {
			if(!empty($lundiMin)) {
				$requeteRem .= "and (Date between '".$lundiMin."' and '".$vendrediMax."')";
			} else {
				if($IDQuery!=2) {
					// force aucun résultat -> todo
					$requeteRem .= "and Date is null";
				} else {
					// filtre tout thème
					switch($triPeriode) {
						case 1: $requeteRem .= "and (Date ".$betweenSQLSem.")"; break;
						case 2: $requeteRem .= "and (Date ".$betweenSQLAnn.")"; break;
						case 3: $requeteRem .= "and (Date >= '".date("Y-m-d",$maxDateEval)."')"; break;
					}
				}

			}
		}
		$requeteRem .= ")";
	}
	//$requeteRem .= " order by DateSaisie";
	//echo $requeteRem;
	$noEntrySuivi = false;

	$resultatRem =  mysqli_query($connexionDB,$requeteRem." order by DateSaisie");
	if(!empty($resultatRem)&&mysqli_num_rows($resultatRem)>0) {
		if(!hasAdminRigth()) {
			echo "<div id='corners'>";
			echo "<div id='legend'>Suivi</div>";
			echo "<table id='hor-minimalist-b' border='0' width='100%'>\n";
			$showtableSuivi = true;
			echo "<tr><th width='175'>Concerne</th><th width='90'>Date</th><th width='40'></th><th>Remarques</th><th width='10'></th></tr>";
		}
		while ($ligneRem = mysqli_fetch_assoc($resultatRem)) {
			$idRem = $ligneRem['IDRemSuivi'];
			$txtrem = $ligneRem['Remarque'];
			$typerem = $ligneRem['TypeRemarque'];
			if($typerem>=4 && is_numeric(substr($txtrem,0,1))) {
				// on enlève le premier caractère s'il est numérique (type de tâche encodé dans ce champ)
				$txtrem = $configurationTJ[substr($txtrem,0,1)]." ".substr($txtrem,1);
			}
			$daterem = jourSemaine($ligneRem['DateSaisie'])." ".date('d.m.Y', strtotime($ligneRem['DateSaisie']));
			$auteur = $ligneRem['abbr'];
			//if(!empty($txtrem)) {
				switch($typerem) {
					case 1:
						$nomTheme = $ligneRem['NomTheme'];
						if($ligneRem['IDTheme']==1) {
							$nomTheme = "<i>CPG</i> - Remarque";
						} else {
							$nomTheme = "<i>SUV</i> - ".$nomTheme;
						}
						echo "<tr remarque$idRem='1' onclick='toggle(\"remarque$idRem\")'><td valign='top'>".$nomTheme."</td><td valign='top'>".$daterem."</td><td align='center' valign='top'>".$auteur."</td><td valign='top'>".wiki2html($txtrem)."</td><td align='center' valign='top'>";
						echo "<a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette remarque')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a>";
						echo "</td></tr>";
						echo "<tr remarque$idRem='1' style='display:none' onclick='toggle(\"remarque$idRem\")'><td valign='top'>".$nomTheme."</td><td valign='top'><input name='DateRemarque$idRem' value='".date('d.m.Y', strtotime($ligneRem['DateSaisie']))."' size='8' maxlength='10' onclick='limitEvent(event)'></input></td><td></td><td valign='top'><textarea name='Remarque$idRem' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$txtrem."</textarea></td><td valign='top'><input type='button' name='ModifRem' value='Modifier' onclick='submitRemarque(\"$idRem\",\"modif\")'></input></td></tr>";
						break;
					case 2:
						if(hasAdminRigth()) {
							echo "<tr><td valign='top'><b><font color='#FF0000'><i>JRN</i> - A corriger</font></b></td><td valign='top'><font color='#FF0000'>".$daterem."</font></td><td align='center'><font color='#FF0000'>".$auteur."</font></td><td><b><font color='#FF0000'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=corrected'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Marquer l\'erreur comme corrigée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
							//echo "<tr remarque$idRem='1' style='display:none' onclick='toggle(\"remarque$idRem\")'><td valign='top'>A corriger</td><td valign='top'></td><td align='center'></td><td colspan='2'><textarea name='RemarqueJournal' COLS=60 ROWS=20 onclick='limitEvent(event)'>".$txtrem."</textarea></td></tr>";
						} else {
							echo "<tr><td valign='top'><b><font color='#FF0000'>Journal de travail à corriger</font></b></td><td><b><font color='#FF0000'>".$daterem."</font></b></td><td></td><td><b><font color='#FF0000'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=corrected'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Marquer l\'erreur comme corrigée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
						}
						$cntErreurSaisie++;
						break;
					case 3: 
						echo "<tr><td valign='top'><font color='#FF7F00'><i>JRN</i> - A vérifier</font></td><td><font color='#FF7F00'>".$daterem."</font></td><td align='center' valign='top'><font color='#FF7F00'> ".$auteur." </font></td><td><font color='#FF7F00'>".wiki2html($txtrem)."</font></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Clôturer l\'erreur')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
						break;
					case 4: // tâche à faire
						if(hasAdminRigth()) {
							echo "<tr><td valign='top'><b><font color='#9900cc'><i>TCH</i> - A faire</font></b></td><td valign='top'><font color='#9900cc'>".$daterem."</font></td><td align='center'><font color='#9900cc'> ".$auteur." </font></td><td><b><font color='#9900cc'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'>";
							if((time()-strtotime($ligneRem['DateSaisie']))>(86400*3)) {
								echo "<a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=done'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('La tâche a été effectuée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a>";
							}
							echo "</td></tr>";
						} else {
							echo "<tr><td valign='top'><b><font color='#9900cc'>Tâche à effectuer</font></b></td><td><b><font color='#9900cc'>Toute la semaine</font></b></td><td></td><td><b><font color='#9900cc'>".wiki2html($txtrem)."</font></b></td><td align='center' valign='top'>";
							if((time()-strtotime($ligneRem['DateSaisie']))>(86400*3)) {
								echo "<a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=done'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('J\'ai effectué la tâche')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a>";
							}
							echo "</td></tr>";
						}
						//$cntErreurSaisie++;
						break;
					case 5: // tâche effectuée
						echo "<tr><td valign='top'><font color='#FF7F00'><i>TCH</i> - A valider</font></td><td><font color='#FF7F00'>".$daterem."</font></td><td align='center' valign='top'><font color='#FF7F00'> ".$auteur." </font></td><td><font color='#FF7F00'>".wiki2html($txtrem)."</font></td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=validated'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Valider la tâche comme effectuée')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
						break;
					case 6: // tâche effectuée et validée
						//echo "<tr><td valign='top'><font color='#007F00'><i>TCH</i> - Vérifiée</font></td><td><font color='#FF7F00'>".$daterem."</font></td><td align='center' valign='top'><font color='#FF7F00'> ".$auteur." </font></td><td><font color='#FF7F00'>".wiki2html($txtrem)."</font></td><td align='center' valign='top'></td></tr>";
						break;
					case 7: // tâche non effectuée
						echo "<tr><td valign='top'><i>TCH</i> - Non effectuée</td><td>".$daterem."</td><td align='center' valign='top'>".$auteur."</td><td>".wiki2html($txtrem)."</td><td align='center' valign='top'><a href='activites.php?idEleve=$IDEleve&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&IDRemSuivi=$ligneRem[IDRemSuivi]&action=delete'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer l\'attribution')\" onmouseout='UnTip()' onclick='limitEvent(event)'></a></td></tr>";
						break;	
					case 0:
						if(hasAdminRigth()) {
							echo "<tr><td valign='top'><i>ADM</i> - ".$ligneRem['NomTheme']."</td><td>".$daterem."</td><td></td><td>".$txtrem."</td><td align='center' valign='top'></td></tr>";
						}
						break;
				}
			//}
		}
	} else {
		if(empty($IDQuery)) {
			if(hasAdminRigth()) {
				//echo "<tr><td colspan='3' align='center'><i>Aucune remarque</i></td></tr>";
				$noEntrySuivi = true;
			} else {
				$requeteRem = "SELECT * FROM remarquesuivi where IDEleve=".$IDEleve." and TypeRemarque=2";
				if($noSemaine>30) {
					$requeteRem .= " and (DateSaisie between '".$anneeCalc."-08-01' and '".($anneeCalc+1)."-07-31')";
				} else {
					$requeteRem .= " and (DateSaisie between '".($anneeCalc-1)."-08-01' and '".$anneeCalc."-07-31')";
				}
				$resultatRem =  mysqli_query($connexionDB,$requeteRem);
				if (mysqli_num_rows($resultatRem) != 0) {
					echo "<div id='corners'>";
					echo "<div id='legend'>Suivi</div>";
					echo "<table id='hor-minimalist-b' border='0' width='100%'>\n";
					$showtableSuivi = true;
					//echo "<tr><th width='175'>Concerne</th><th width='90'>Date</th><th width='40'></th><th>Remarques</th><th width='10'></th></tr>";
					//echo "<tr><td></td><td></td><td></td><td><b><font color='#FF0000'>Des erreurs de saisies sur d'autres semaines doivent encore être corrigées!</font></b></td><td></td></tr>";
					echo "<tr><td align='center'><b><font color='#FF0000'>Des erreurs de saisies sur d'autres semaines doivent encore être corrigées!</font></b></td></tr>";
				}
			}
		}
	}

	// ajout remarques générale de l'élève
	/*
	if(hasAdminRigth()) {
		$requete = "SELECT * FROM $tableAttribEleves el join $tableAttribut att on el.IDAttribut=att.IDAttribut where IDEleve = ".$IDEleve." and el.IDAttribut > 100 and ";
		if(empty($IDQuery)) {
			$requete .= "(Date between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') order by Date";
		} else {
			$requete .= "(Date between '".$min."' and '".$max."')";
		}
		//echo $requete;
		$resultat =  mysqli_query($connexionDB,$requete);
		if(!empty($resultat)&&mysqli_num_rows($resultat)>0) {
			while ($ligne = mysqli_fetch_assoc($resultat)) {
				echo "<tr><td valign='top'><i>ADM</i> - ".$ligne['Nom']."</td><td>".date('d.m.Y', strtotime($ligne['Date']))."</td><td></td><td>".$ligne['Remarque']."</td><td align='center' valign='top'></td></tr>";
			}
		} else {
			$noEntryRem = true;
		}
	}
	*/
	//if($noEntrySuivi && $noEntryRem) {
	if($noEntrySuivi) {
		echo "<tr><td colspan='5' align='center'><i>Aucune remarque</i></td></tr>";
	}
	if(hasAdminRigth()) {
		// liste des activités
		$requete = "SELECT th.IDTheme, th.NomTheme, th.TypeTheme, pr.IDProjet FROM theme th left outer join projets pr on pr.IDTheme=th.IDTheme where (TypeTheme=0 and '".$classe."' LIKE CONCAT(ClasseTheme, '%')) OR (TypeTheme=1 and pr.IDEleve = $IDEleve) group by IDTheme order by TypeTheme, NomTheme";
		//echo $requete;
		//$requete = "SELECT * FROM projets ep join theme th on ep.IDTheme=th.IDTheme where IDEleve = $IDEleve ".$filtreSQL." order by NomTheme";
		// IDEtatProjet=1 and, supprimé le 12.06.2015
		$resultat =  mysqli_query($connexionDB,$requete);
		$option = "";
		//while ($ligne = mysqli_fetch_assoc($resultat)) {
		//	$option .= "<option value=".$ligne['IDProjet'].">".$ligne['NomTheme']."</option>";
		//}
		while ($ligne = mysqli_fetch_assoc($resultat)) {
			$option .= "<option value=".$ligne['IDTheme'];
			//echo $ligne['IDTheme']."/".$IDTheme."/".$ligne['IDProjet']."<br>";
			if($ligne['IDTheme']==$IDQuery) {
				$option .= " selected";
			}
			$option .= ">";
			//if($ligne['TypeTheme']==1) {
			//	$option .= "Projet - ";
			//}
			$option .= "SUV - ".$ligne['NomTheme']."</option>";
		}
		echo "<tr newremarque$last='1' style='display:none' onclick='toggle(\"newremarque$last\")'><td valign='top' colspan='5'><select name='TypeRemarque$last' onclick='limitEvent(event)' style='width: 900px'><option value='1'>CPG - Remarque</option><option value='2'>JRN - Journal à corriger par l'apprenti</option><option value='4'>TCH - Tâche à faire par l'apprenti</option>";
		echo $option;
		$ajd = time();
		if($ajd > $vendredi) {
			$dateajout = $vendredi;
		} else {
			$dateajout = $ajd;
		}
		echo "</select></td></tr>";
		echo "<tr newremarque$last='1' style='display:none' onclick='toggle(\"newremarque$last\")'><td valign='center' align='center'></td>";
		echo "<td valign='top'><input name='DateRemarque$last' value='".date('d.m.Y',$dateajout)."' size='8' maxlength='10' onclick='limitEvent(event)'></input></td><td></td><td><textarea name='Remarque$last' COLS=74 ROWS=20 onclick='limitEvent(event)'></textarea><img src='/iconsFam/help.png' onmouseover=\"Tip('".$helptxt."')\" onmouseout='UnTip()'></td><td valign='top'><input type='button' name='AjoutRem' value='Ajouter' onclick='submitRemarque(\"$last\",\"ajout\")'></input></td></tr>";
		echo "<tr><td colspan='5' valign='bottom' valign='bottom' bgColor='#5C5C5C'></td></tr>";
		echo "<tr newremarque$last='1'><td colspan='5' align='right'>";
		$_SESSION['last_request'] = $requeteRem;
		echo "<a href='impressionSuiviPDF.php?IDEleve=".$IDEleve."&IDTheme=".$IDQuery."&annee=".$anneeCalc."&noSemaine=".$noSemaine."&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&tri=".$triPeriode."' target='pdf'><img src='/iconsFam/page_white_acrobat.png' onmouseover=\"Tip('Imprimer le suivi')\" onmouseout='UnTip()'></a>";
		echo "<img src='/iconsFam/comment_add.png'  onclick='toggle(\"newremarque$last\")' onmouseover=\"Tip('Ajouter une remarque dans le suivi')\" onmouseout='UnTip()'>";
		echo "</td></tr>";
	}
	if($showtableSuivi) {
		echo "</table>";
		echo "</div><br><br>";
	}
//}

// évaluation
if(!empty($typeEvaluation) && $IDQuery!=2) {
	if(($vue==1 && $modeEvaluation=="hebdo") || ($vue==2 && $modeEvaluation=="theme" && $IDQuery!=1)) {
		echo "<div id='corners'>";
		if($vue==1) {
			echo "<div id='legend' onClick='toggleTable(\"tblEval\")'>Evaluation hebdomadaire</div>";
		} else {
			echo "<div id='legend' onClick='toggleTable(\"tblEval\")'>Evaluation du thème</div>";
			//echo "<br>Semaine <a href='#' onclick='submitSemaineAnnee(".date('W').",".$anneeCalc.")'>actuelle</a> | <a href='#' onclick='submitSemaineAnnee(43,".$anneeCalc.")'>43</a> | 41<br>";
			// vue par thème -> recherche initale de la liste des thèmes validés pour l'élève, le thème donné
			$requete = "SELECT distinct NoSemaine, Annee, DateValidation FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$IDQuery";
			if($triPeriode==1 || $triPeriode==2) {
				// tri sur année en cours
				if($noSemaine>30) {
					$requete .= " and (Annee between ".$anneeCalc." and ".($anneeCalc+1).")";
				} else {
					$requete .= " and (Annee between ".($anneeCalc-1)." and ".$anneeCalc.")";
				}
			}
			if($triPeriode==1) {
				// uniquement semestre en cours
				if($semestreAct==1) {
					$requete .= " and (NoSemaine > 30 or NoSemaine < ".$noSemaineSem2.")";
				} else {
					$requete .= " and (NoSemaine between ".$noSemaineSem2." and 30)";
				}
			}
			if($triPeriode==3) {
				// non validée
				$requete .= " and DateValidation is null";
			}
			$requete .=	" order by Annee, NoSemaine LIMIT 20";

			$resultat =  mysqli_query($connexionDB,$requete);
			$evalAct = 0;
			$evalCnt = 0;
			$numEval = 0;
			if(!empty($resultat)) {
				$numEval = mysqli_num_rows($resultat);
			}
			if(!empty($resultat)&&$numEval>0) {
				echo "<div id='criteres' width='100%' align='right'><i>Evaluation(s) présente(s), semaine(s) no: ";
				while ($ligne = mysqli_fetch_assoc($resultat)) {
					$evalCnt++;
					echo "<a id='circle";
					if(empty($ligne['DateValidation'])) {
						echo "red";
					}
					if($noSemaine == $ligne['NoSemaine'] || (!$evalAct&&($numEval==$evalCnt)&&empty($ligne['DateValidation']))) {
						echo "sel";
						$evalAct = 1;
						$noSemaine = $ligne['NoSemaine'];
					}
					echo "' href='#' onclick='submitSemaineAnnee(".$ligne['NoSemaine'].",".$ligne['Annee'].")'>";
					echo "<i>".$ligne['NoSemaine']."</i>";
					echo "</a>";
					//echo $numEval."-".$evalCnt." ";
				}
				// noSemaine pas existante dans la liste ou dernière éval validée, on en ajoute une nouvelle
				if(!$evalAct) {
					$noSemaine = date("W");
					echo "<a id='circlesel' href='#'><i>".$noSemaine."</i></a>";
				}
				echo "</i></div>";
			} else {
				// liste vide
				$noSemaine = date("W");
			}
			//echo "<br>".$requete."<br>";

		}

		if(hasAdminRigth()) {
			$onClickAPP = "toggleNot";
			$onClickMAI = "toggleTD";
			$radioAPP = "disabled";
			$radioMAI = "";
		} else {
			$onClickAPP = "toggleTD";
			$onClickMAI = "toggleNot";
			$radioAPP = "";
			$radioMAI = "disabled";
		}
		echo "<table id='hor-minimalist-b' border='0' width='100%' tblEval='1'>\n";
		if($typeEvaluation=='abcd') {
			echo "<tr><th width='175'>Compétences</th><th width='50'></th><th width='10' align='center'>A</th><th width='10' align='center'>B</th><th width='10' align='center'>C</th><th width='10' align='center'>D</th><th width='10'></th><th>Remarques</th><th width='50' align='center'>Moyenne</th><th width='10'></th></tr>";
		} else {
			echo "<tr><th width='175'>Compétences</th><th width='50'></th><th width='40' align='center'>Note</th><th width='10'></th><th>Remarques</th><th width='50' align='center'>Moyenne</th><th width='10'></th></tr>";
		}

		// rechercher des évaluation
		if($vue==1) {
			// vue par semaine -> recherche par noSemaine/année
			$requete = "SELECT * FROM evalhebdo left outer join prof on Responsable=userid where IDEleve = $IDEleve and NoSemaine = $noSemaine and Annee = $anneeCalc order by IDCompetence, IDTypeEval";
			$requeteMoy = "SELECT IDCompetence, IDTypeEval, AVG(Note) as NoteMoyenne, AVG(Niveau) as NiveauMoyen FROM evalhebdo where IDEleve = $IDEleve";
		} else {

			//if(empty($dateEvalSel)) {
				$requete = "SELECT * FROM evalhebdo left outer join prof on Responsable=userid where IDEleve = $IDEleve and NoSemaine = $noSemaine and Annee = $anneeCalc and IDTheme=$IDQuery order by IDCompetence, IDTypeEval";
				$requeteMoy = "SELECT IDCompetence, IDTypeEval, AVG(Note) as NoteMoyenne, AVG(Niveau) as NiveauMoyen FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$IDQuery";
			//} else {
				//$requete = "SELECT * FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$IDQuery and DateValidation = $dateEvalSel order by IDCompetence, IDTypeEval";
			//}
		}
		//if($noSemaine>30) {
		//	$requeteMoy .= " and (Annee between ".$anneeCalc." and ".($anneeCalc+1).")";
		//} else {
		//	$requeteMoy .= " and (Annee between ".($anneeCalc-1)." and ".$anneeCalc.")";
		//}
		if($semestreAct==1) {
			if($noSemaine>30) {
				$requeteMoy .= " and ((NoSemaine between 30 and ".$noSemaine.") and Annee = ".$anneeCalc.")";
			} else {
				$requeteMoy .= " and ((NoSemaine > 30 and Annee = ".($anneeCalc-1).") or (NoSemaine <= ".$noSemaine." and Annee = ".$anneeCalc."))";
			}
		} else {
			$requeteMoy .= " and ((NoSemaine between ".$noSemaineSem2." and ".$noSemaine.") and Annee = ".$anneeCalc.")";
		}
		//$requeteMoy .= " and Annee = $anneeCalc and NoSemaine <= $noSemaine";
		$requeteMoy .= " and DateValidation is not null group by IDCompetence,IDTypeEval order by IDCompetence, IDTypeEval";
		//echo $requete;
		//echo "<br>".$requeteMoy;
		$resultat =  mysqli_query($connexionDB,$requete);
		$resultatMoy =  mysqli_query($connexionDB,$requeteMoy);
		$ligne = null;
		$ligneMoy = null;
		$validation = null;
		$resp = null;
		$abbrResp = null;
		$moyAPP = 0;
		$moyMAI = 0;
		$pondTotAPP = 0;
		$pondTotMAI = 0;
		$nbrNotes = 0;
		$evalCompleteAPP = true;
		$evalCompleteMAI = true;

		$compEval = $competencesEvaluation;
		$compPond = $competencesPonderation;
		if(!empty($lastAnneeEvalOld) && !empty($lastSemestreEvalOld)) {
			if($anneeCalc<$lastAnneeEvalOld || ($anneeCalc==$lastAnneeEvalOld && $semestreAct <= $lastSemestreEvalOld)) {
				$compEval = $competencesEvaluationOld;
				$compPond = $competencesPonderationOld;
			}
		}

		foreach ($compEval as $key => $value) {
			$noteAPP="";
			$noteMAI="";
			$nivAPP="";
			$nivMAI="";
			$obsAPP="";
			$obsMAI="";
			if($ligne==null&&!empty($resultat)&&mysqli_num_rows($resultat)>0) {
				$ligne = mysqli_fetch_assoc($resultat);
				if($validation==null&&!empty($ligne['DateValidation'])) {
					//echo "v:".$ligne['DateValidation']."<br>";
					$validation = $ligne['DateValidation'];
				}
			}

			if($key==$ligne['IDCompetence']) {
				// competence trouvée dans les données DB
				if(1==$ligne['IDTypeEval']) {
					// competence pour APP trouvée dans DB
					$noteAPP = $ligne['Note'];
					if(!empty($noteAPP)) {
						$nbrNotes++;
					}
					$nivAPP = $ligne['Niveau'];
					if(!empty($nivAPP)) {
						$nbrNotes++;
					}
					$obsAPP = $ligne['Remarque'];
					//if($validation==null) {
					//	$validation = $ligne['DateValidation'];
					//}
					// test si competence MAI également présente
					$ligne = mysqli_fetch_assoc($resultat);
					if($key==$ligne['IDCompetence'] && 2==$ligne['IDTypeEval']) {
						// competence MAI également présente
						$noteMAI = $ligne['Note'];
						if(!empty($noteMAI)) {
							$nbrNotes++;
						}
						$nivMAI = $ligne['Niveau'];
						if(!empty($nivMAI)) {
							$nbrNotes++;
						}
						$obsMAI = $ligne['Remarque'];
						//if($validation==null) {
						//	$validation = $ligne['DateValidation'];
						//}
						if($resp==null) {
							$resp = $ligne['Responsable'];
							$abbrResp = $ligne['abbr'];
						}
						// prochain enregistrement à récupérer
						$ligne = null;
					} else {
						// aucune compétence trouvée ni pour APP, ni pour MAI -> on garde la ligne actuelle pour le prochain passage
					}
				} else {
					// pas de competence APP dans la DB -> competence MAI
					$noteMAI = $ligne['Note'];
					if(!empty($noteMAI)) {
						$nbrNotes++;
					}
					$nivMAI = $ligne['Niveau'];
					if(!empty($nivMAI)) {
						$nbrNotes++;
					}
					$obsMAI = $ligne['Remarque'];

					if($resp==null) {
						$resp = $ligne['Responsable'];
						$abbrResp = $ligne['abbr'];
					}
					// prochain enregistrement à récupérer
					$ligne = null;
				}
			} else {
				// aucune compétence trouvée ni pour APP, ni pour MAI -> on garde la ligne actuelle pour le prochain passage
			}

			if(!empty($resultatMoy)&&mysqli_num_rows($resultatMoy)>0 && $ligneMoy==null) {
				$ligneMoy = mysqli_fetch_assoc($resultatMoy);
				//echo $ligneMoy['NiveauMoyen'];
			}
			// évaluation abcd
			if($typeEvaluation=='abcd') {
				$tdid = "tdid".$key."1";
				echo "<tr";
				if(empty($validation)) {
					echo " onclick='".$onClickAPP."(\"".$tdid."\")'";
				}
				echo "><td valign='top'>".$value."</td><td valign='top' align='center'><i>";
				if(hasAdminRigth() || !empty($validation)) {
				 echo "APP";
				}
				echo "</i></td>";
				for($i=1;$i<5;$i++) {
					echo "<td ".$tdid."='1' valign='top' style='display:none'><input type='radio' name='niv".$tdid."' value='".$i."' ".$radioAPP." onclick='limitEvent(event)'";
					if($i == $nivAPP) {
						echo " checked ";
					}
					echo "></td>";
					echo "<td ".$tdid."='1' valign='top' align='center'>";
					if($i == $nivAPP) {
						echo "X";
					}
					echo "</td>";
				}
				echo "<td></td><td ".$tdid."='1' valign='top' style='display:none' colspan='2'><textarea name='obs".$tdid."' COLS=40 ROWS=4 onclick='limitEvent(event)'>".$obsAPP."</textarea></td>";
				echo "<td ".$tdid."='1' valign='top' style='display:none'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitEvalution(\"".$tdid."\",".$noSemaine.",".$anneeCalc.")'></td>";
				echo "<td ".$tdid."='1'>".wiki2html($obsAPP)."</td>";
				echo "<td ".$tdid."='1' align='center' valign='top'>";
				//if(!empty($validation)) {
				if($ligneMoy['IDCompetence']==$key && $ligneMoy['IDTypeEval']==1) {
					if(!empty($validation) && !empty($ligneMoy['NiveauMoyen'])) {
						echo niveauMoy($ligneMoy['NiveauMoyen']);
						$moyAPP += $ligneMoy['NiveauMoyen'] * $compPond[$key];
						$pondTotAPP += $compPond[$key];
					} else {
						$evalCompleteAPP = false;
					}
					// on lit la moyenne suivante
					if(!empty($resultatMoy)&&mysqli_num_rows($resultatMoy)>0) {
						$ligneMoy = mysqli_fetch_assoc($resultatMoy);
					}
				} else {
					$evalCompleteAPP = false;
				}
				echo "</td><td ".$tdid."='1'></td>";
				echo "</tr>";

				if(hasAdminRigth() || !empty($validation)) {
					$tdid = "tdid".$key."2";
					echo "<tr";
					if(empty($validation)) {
						echo " onclick='".$onClickMAI."(\"".$tdid."\")'";
					}
					echo "><td></td><td valign='top' align='center'><i>MAI</i></td>";
					for($i=1;$i<5;$i++) {
						echo "<td ".$tdid."='1' valign='top' style='display:none'><input type='radio' name='niv".$tdid."' value='".$i."' ".$radioMAI." onclick='limitEvent(event)'";
						if($i == $nivMAI) {
							echo " checked ";
						}
						echo "></td>";
						echo "<td ".$tdid."='1' valign='top' align='center'>";
						if($i == $nivMAI) {
							echo "X";
						}
						echo "</td>";
					}
					echo "<td></td><td ".$tdid."='1' valign='top' style='display:none' colspan='2'><textarea name='obs".$tdid."' COLS=40 ROWS=4 onclick='limitEvent(event)'>".$obsMAI."</textarea></td>";
					echo "<td ".$tdid."='1' valign='top' style='display:none'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitEvalution(\"".$tdid."\",".$noSemaine.",".$anneeCalc.")'></td>";
					echo "<td ".$tdid."='1'>".wiki2html($obsMAI)."</td>";
					echo "<td ".$tdid."='1' align='center' valign='top'>";
					//if(!empty($validation)) {
					//echo $ligneMoy['IDCompetence']."-".$key."-".$ligneMoy['IDTypeEval']."-1";
					if($ligneMoy['IDCompetence']==$key && $ligneMoy['IDTypeEval']==2) {
						if(!empty($validation) &&!empty($ligneMoy['NiveauMoyen'])) {
							echo niveauMoy($ligneMoy['NiveauMoyen']);
							$moyMAI += $ligneMoy['NiveauMoyen'] * $compPond[$key];
							$pondTotMAI += $compPond[$key];
						} else {
								$evalCompleteMAI = false;
						}
						$ligneMoy = null;
					} else {
						$evalCompleteMAI = false;
					}
					echo "</td><td ".$tdid."='1'></td>";
					echo "</tr>";
				}
			} else {
				// evaluation notes
				$tdid = "tdid".$key."1";
				echo "<tr";
				if(empty($validation)) {
					echo " onclick='".$onClickAPP."(\"".$tdid."\")'";
				}
				echo "><td valign='top'>".$value."</td><td valign='top' align='center'><i>";
				if(hasAdminRigth() || !empty($validation)) {
				 echo "APP";
				}
				echo "</i></td><td valign='top' ".$tdid."='1' style='display:none'><input type='input' name='eval".$tdid."' value='".$noteAPP."' size='3' onclick='limitEvent(event)'></td><td valign='top' ".$tdid."='1' align='center'>".$noteAPP."</td>";
				echo "<td></td><td ".$tdid."='1' valign='top' style='display:none' colspan='2'><textarea name='obs".$tdid."' COLS=40 ROWS=4 onclick='limitEvent(event)'>".$obsAPP."</textarea></td>";
				echo "<td ".$tdid."='1' valign='top' style='display:none'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitEvalution(\"".$tdid."\",".$noSemaine.",".$anneeCalc.")'></td>";
				echo "<td ".$tdid."='1'>".wiki2html($obsAPP)."</td>";
				echo "<td ".$tdid."='1' align='center' valign='top'>";
				if($ligneMoy['IDCompetence']==$key && $ligneMoy['IDTypeEval']==1) {
					if(!empty($validation) && !empty($ligneMoy['NoteMoyenne'])) {
						echo noteMoy($ligneMoy['NoteMoyenne']);
						$moyAPP += $ligneMoy['NoteMoyenne'] * $compPond[$key];
						$pondTotAPP += $compPond[$key];
					} else {
							$evalCompleteAPP = false;
					}
					// on lit la moyenne suivante
					if(!empty($resultatMoy)&&mysqli_num_rows($resultatMoy)>0) {
						$ligneMoy = mysqli_fetch_assoc($resultatMoy);
					}
				} else {
					$evalCompleteAPP = false;
				}
				echo "</td><td ".$tdid."='1'></td>";
				echo "</tr>";

				if(hasAdminRigth() || !empty($validation)) {
					$tdid = "tdid".$key."2";
					echo "<tr";
					if(empty($validation)) {
						echo " onclick='".$onClickMAI."(\"".$tdid."\")'";
					}
					echo "><td></td><td valign='top' align='center'><i>MAI</i></td><td valign='top' ".$tdid."='1' style='display:none'><input type='input' name='eval".$tdid."' value='".$noteMAI."' size='3' onclick='limitEvent(event)'></td><td valign='top' ".$tdid."='1' align='center'>".$noteMAI."</td>";
					echo "<td></td><td ".$tdid."='1' valign='top' style='display:none' colspan='2'><textarea name='obs".$tdid."' COLS=40 ROWS=4 onclick='limitEvent(event)'>".$obsMAI."</textarea></td>";
					echo "<td ".$tdid."='1' valign='top' style='display:none'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitEvalution(\"".$tdid."\",".$noSemaine.",".$anneeCalc.")'></td>";
					echo "<td ".$tdid."='1'>".wiki2html($obsMAI)."</td>";
					echo "<td ".$tdid."='1' align='center' valign='top'>";
					if($ligneMoy['IDCompetence']==$key && $ligneMoy['IDTypeEval']==2) {
						if(!empty($validation) && !empty($ligneMoy['NoteMoyenne'])) {
							echo noteMoy($ligneMoy['NoteMoyenne']);
							$moyMAI += $ligneMoy['NoteMoyenne'] * $compPond[$key];
							$pondTotMAI += $compPond[$key];
						} else {
								$evalCompleteMAI = false;
						}
						$ligneMoy = null;
					} else {
						$evalCompleteMAI = false;
					}
					echo "</td><td ".$tdid."='1'></td>";
					echo "</tr>";
				}
			}
			//echo "<tr onclick='".$onClickAPP."(\"tdid3\")'><td valign='top'>Compétences méthodologiques</td><td valign='top'><i>APP</i></td><td valign='top'><input type='radio' name='Aam' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td></td><td  tdid3='1' valign='top' style='display:none'><textarea name='compProfAPP' COLS=40 ROWS=4></textarea></td><td tdid3='1'></td></tr>";
			//echo "<tr onclick='".$onClickMAI."(\"tdid4\")'><td></td><td valign='top'><i>MAI</i></td><td valign='top'><input type='radio' name='Ammm' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td></td><td  tdid4='1' valign='top' style='display:none'><textarea name='compProfAPP' COLS=40 ROWS=4></textarea></td><td tdid4='1'></td></tr>";
			//echo "<tr onclick='".$onClickAPP."(\"tdid5\")'><td valign='top'>Compétences sociales</td><td valign='top'><i>APP</i></td><td valign='top'><input type='radio' name='Aas' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioAPP." onclick='limitEvent(event)'></td><td></td><td  tdid5='1' valign='top' style='display:none'><textarea name='compProfAPP' COLS=40 ROWS=4></textarea></td><td tdid5='1'></td></tr>";
			//echo "<tr onclick='".$onClickMAI."(\"tdid6\")'><td></td><td valign='top'><i>MAI</i></td><td valign='top'><input type='radio' name='Ams' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td valign='top'><input type='radio' name='A' value='' ".$radioMAI." onclick='limitEvent(event)'></td><td></td><td tdid6='1' valign='top' style='display:none'><textarea name='compProfAPP' COLS=40 ROWS=4></textarea></td><td tdid6='1'></td></tr>";
		}
		if($typeEvaluation=='abcd') {
			echo "<tr><td colspan='10' bgColor='#5C5C5C'></td></tr>";
		} else {
			echo "<tr><td colspan='7' bgColor='#5C5C5C'></td></tr>";
		}
		$obsAPP="";
		$obsMAI="";
		if($ligne==null&&!empty($resultat)&&mysqli_num_rows($resultat)>0) {
			$ligne = mysqli_fetch_assoc($resultat);
		}
		if(99==$ligne['IDCompetence']) {
			// observation trouvée dans les données DB
			if(1==$ligne['IDTypeEval']) {
				// observation pour APP trouvée dans DB
				$obsAPP = $ligne['Remarque'];
				// test si observation MAI également présente
				$ligne = mysqli_fetch_assoc($resultat);
				if(99==$ligne['IDCompetence'] && 2==$ligne['IDTypeEval']) {
					// observation MAI également présente
					$obsMAI = $ligne['Remarque'];
					if($resp==null) {
						$resp = $ligne['Responsable'];
						$abbrResp = $ligne['abbr'];
					}
				}
			} else {
				// seule observation MAI présente
				$obsMAI = $ligne['Remarque'];
				if($resp==null) {
					$resp = $ligne['Responsable'];
					$abbrResp = $ligne['abbr'];
				}
			}
		}

		echo "<tr";
		if(empty($validation)) {
			echo " onclick='".$onClickAPP."(\"tdid991\")'";
		}
		echo "><td valign='top'>Observations</td><td valign='top' align='center'><i>";
		if(hasAdminRigth() || !empty($validation)) {
			echo "APP";
		}
		echo "</i></td><td valign='top'></td><td valign='top'></td>";
		if($typeEvaluation=='abcd') {
			echo "<td valign='top'></td><td valign='top'></td><td valign='top'></td>";
		}
		echo "<td tdid991='1' valign='top' style='display:none' colspan='2'><textarea name='obstdid991' COLS=40 ROWS=4 onclick='limitEvent(event)'>".$obsAPP."</textarea></td>";
		echo "<td tdid991='1' valign='top' style='display:none'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitEvalution(\"tdid991\",".$noSemaine.",".$anneeCalc.")'></td>";
		echo "<td tdid991='1'>".wiki2html($obsAPP)."</td>";
		echo "<td tdid991='1' align='center' valign='top'>";
		if(!empty($validation) && $pondTotAPP!=0 && $evalCompleteAPP) {
			if($typeEvaluation=='abcd') {
				echo niveauMoy($moyAPP / $pondTotAPP);
			} else {
				echo noteMoy($moyAPP / $pondTotAPP);
			}
		}
		echo "</td><td tdid991='1'></td>";
		echo "</tr>";
		if(hasAdminRigth() || !empty($validation)) {
			echo "<tr";
			if(empty($validation)) {
				echo " onclick='".$onClickMAI."(\"tdid992\")'";
			}
			echo "><td></td><td valign='top' align='center'><i>MAI</i></td><td valign='top'></td><td valign='top'></td>";
			if($typeEvaluation=='abcd') {
				echo "<td valign='top'></td><td valign='top'></td><td valign='top'></td>";
			}
			echo "<td tdid992='1' valign='top' style='display:none' colspan='2'><textarea name='obstdid992' COLS=40 ROWS=4 onclick='limitEvent(event)'>".$obsMAI."</textarea></td>";
			echo "<td tdid992='1' valign='top' style='display:none'><img src='/iconsFam/tick.png' align='absmiddle' onmouseover=\"Tip('Enregister')\" onmouseout='UnTip()' onclick='submitEvalution(\"tdid992\",".$noSemaine.",".$anneeCalc.")'></td>";
			echo "<td tdid992='1'>".wiki2html($obsMAI)."</td>";
			echo "<td tdid992='1' align='center' valign='top'>";
			if(!empty($validation) && $pondTotMAI!=0 && $evalCompleteMAI) {
				if($typeEvaluation=='abcd') {
					echo niveauMoy($moyMAI / $pondTotMAI);
				} else {
					echo noteMoy($moyMAI / $pondTotMAI);
				}
			}
			echo "</td><td tdid992='1'></td>";
			echo "</tr>";

			if($typeEvaluation=='abcd') {
				echo "<tr><td colspan='10' bgColor='#5C5C5C'></td></tr><tr><td colspan='10' align='right'>";
			} else {
				echo "<tr><td colspan='7' bgColor='#5C5C5C'></td></tr><tr><td colspan='7' align='right'>";
			}
			//echo "val:".$validation." resp:".$resp;
			if(!empty($validation)) {
				echo "<i>Evaluation validée le ".date('d.m.Y', strtotime($validation))." par ".$abbrResp."</i> ";
				if(hasAdminRigth() && $resp==$_SESSION['user_login']) {
					echo "<img src='/iconsFam/page_delete.png' onmouseover=\"Tip('Dévalider l\'évaluation')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"unlockEval\",".$noSemaine.",".$anneeCalc.")'> ";
				}
				echo "<a href='impressionJournalPDF.php?IDEleve=".$IDEleve."&IDTheme=".$IDQuery."&annee=".$anneeCalc."&noSemaine=".$noSemaine."&nom=".urlencode($nom)."&prenom=".urlencode($prenom)."&tri=".$triPeriode."&only=eval' target='pdf'><img src='/iconsFam/page_white_acrobat.png' align='absmiddle' onmouseover=\"Tip('Imprimer l\'évaluation')\" onmouseout='UnTip()'></a>";
			} else {
				//echo (count($compEval)*2)."/".$nbrNotes;
				//
				if(hasAdminRigth() && $resp==$_SESSION['user_login']) { // && $nbrNotes==count($compEval)*2
					//echo $resp."/".$_SESSION['user_login'];
					echo "<img src='/iconsFam/page_go.png' onmouseover=\"Tip('Valider l\'évaluation')\" onmouseout='UnTip()' align='absmiddle' onClick='submitValidation(\"lockEval\",".$noSemaine.",".$anneeCalc.")'>";
				}
			}
			echo "</td></tr>";
		}

		echo "</table><br><br>";

		echo "<table border='0' tblEval='1'><tr><td width='550' valign='top'>";

		echo "<table border=0>";
		foreach ($compEval as $key => $value) {
			echo "<tr><td width='100' colspan='2'><b>".$value.":</b></td></tr>";
			foreach ($competancesCriteres[$key] as $keyCr => $valueCr) {
				echo "<tr><td width='20'></td><td>".$valueCr."</td></tr>";
			}
		}
		/*
		echo "<tr><td width='20'></td><td>Techniquement parlant, est-ce que je maitrise le sujet?</td></tr>";
		echo "<tr><td></td><td>Ai-je été capable de mettre en application ce que j'ai appris?</td></tr>";
		echo "<tr><td width='100' colspan='2'><b>Compétences méthodologiques:</b></td></tr>";
		echo "<tr><td></td><td>Ai-je mis tout en oeuvre pour parvenir à terminer les exercices, les montages ou le projet?</td></tr>";
		echo "<tr><td></td><td>Est-ce complet et de qualité? Suis-je allé au bout des choses?</td></tr>";
		echo "<tr><td></td><td>Ai-je utilisé le temps à ma disposition de manière efficace?</td></tr>";
		echo "<tr><td></td><td>Est-ce que j'ai pu terminer mon travail dans le temps imparti?</td></tr>";
		echo "<tr><td></td><td>Me suis-je organisé correctement?</td></tr>";
		echo "<tr><td width='100' colspan='2'><b>Compétences sociales:</b></td></tr>";
		echo "<tr><td></td><td>Ai-je accepté et appliqué les décisions prises?</td></tr>";
		echo "<tr><td></td><td>Me suis-je comporté en professionnel?</td></tr>";
		echo "<tr><td></td><td>Suis-je capable de rester concentré sur mon travail?</td></tr>";
		echo "<tr><td></td><td>Peut-on compter sur moi pour la réalisation de ce qui m'est demandé de faire?</td></tr>";
		echo "<tr><td></td><td>Suis-je en ordre avec mes horaires, journaux, etc.?</td></tr>";
		*/
		echo "</table>";

		echo "</td><td width='20'></td><td valign='top'>";
		echo "<b>Observations: </b><br><table border=0>";
			foreach ($observationsGenerales as $ke => $value) {
				echo "<tr><td width='20'></td><td>".$value."</td></tr>";
			}
		echo "</table><br>";
		if($typeEvaluation=='abcd') {
			echo "<b>Echelle d'évaluation:</b><br><table border=0>";
			echo "<tr><td width='20'><b>A</b></td><td>Exigences dépassées / très bon, quantitativement et qualitativement</td></tr>";
			echo "<tr><td width='20'><b>B</b></td><td>Exigences atteintes / bon, répondant bien aux objectifs</td></tr>";
			echo "<tr><td width='20'><b>C</b></td><td>Exigences juste atteintes / satisfaisant aux exigences minimales</td></tr>";
			echo "<tr><td width='20'><b>D</b></td><td>Exigences pas atteintes / faible, incomplet</td></tr>";
			echo "</table>";
		}


		echo "</td></tr></table></div>";
	}
}


?>
<script>
//alert("hidden"+document.getElementsByName('tblActivite')[0].value);
if(document.getElementsByName('tblActivite')[0].value=='none') {
	toggleTable('tblActivite');
}
if(document.getElementsByName('tblSuivi')[0].value=='none') {
	toggleTable('tblSuivi');
}
if(document.getElementsByName('tblEval')[0].value=='none') {
	toggleTable('tblEval');
}
</script>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
