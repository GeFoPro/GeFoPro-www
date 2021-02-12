<?php
include("../../appHeader.php");

/* PDF */
include("phpToPDF.php");

$noSemaine = date('W');
if(isset($_GET['nom'])) {
	$nom = $_GET['nom'];
	$prenom = $_GET['prenom'];
	$IDEleve = $_GET['IDEleve'];
	$IDTheme = $_GET['IDTheme'];
	$noSemaine = (isset($_GET['noSemaine'])?$_GET['noSemaine']:date('W'));
	$annee = $_GET['annee'];
	$tri = (isset($_GET['tri'])?$_GET['tri']:1);
	$only = (isset($_GET['only'])?$_GET['only']:"");
} else if(isset($_POST['nom'])) {
	$nom = $_POST['nom'];
	$prenom = $_POST['prenom'];
	$IDEleve = $_POST['IDEleve'];
	$IDTheme = $_POST['IDTheme'];
	$noSemaine = (isset($_POST['noSemaine'])?$_POST['noSemaine']:date('W'));
	$annee = $_POST['annee'];
	$tri = (isset($_POST['tri'])?$_POST['tri']:1);
	$only = (isset($_POST['only'])?$_POST['only']:"");
}

$dateCalc=mktime(0,0,0,1,4,$annee);
$jour_semaine=date("N",$dateCalc);
$lundi=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaine-1);
$vendredi = $lundi + 86400*4;

$semestreAct = 1;
if($noSemaine<30) {
	// de janvier à juillet
	// calcul du lundi du 2ème semestre
	$lundisem2=$dateCalc-86400*($jour_semaine-1)+604800*($noSemaineSem2-1);
	if($noSemaine>=$noSemaineSem2) {
		// uniquement 2ème semestre
		$betweenSQLSem = "between '".date("Y-m-d",$lundisem2)."' and '".($annee)."-07-31'";
		$semestreAct = 2;
	} else {
		// fin du premier semestre en début d'année civile
		$betweenSQLSem = "between '".($annee-1)."-08-01' and '".date("Y-m-d",$lundisem2-86400)."'";
	}
	// requete pour annee en cours
	$betweenSQLAnn = "between '".($annee-1)."-08-01' and '".$annee."-07-31'";
} else {
	// semestre 1 - août à décembre
	$dateCalcPlus=mktime(0,0,0,1,4,($annee+1));
	$jour_semainePlus=date("N",$dateCalcPlus);
	$lundisem2=$dateCalcPlus-86400*($jour_semainePlus-1)+604800*($noSemaineSem2-1);
	// requete pour semestre en cours
	$betweenSQLSem = "between '".($annee)."-08-01' and '".date("Y-m-d",($lundisem2-86400))."'";
	// requete pour année en cours
	$betweenSQLAnn = "between '".$annee."-08-01' and '".($annee+1)."-07-31'";
}


if(!empty($IDTheme)) {
	// recherche dernière date d'évaluation pour le thème donné
	$requete = "SELECT noSemaine, annee FROM evalhebdo where IDEleve = $IDEleve and IDTheme=$IDTheme and Datevalidation is not null order by annee desc ,noSemaine desc LIMIT 1";
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
	if(!empty($ligne['noSemaine'])) {
		$dateCalcEval=mktime(0,0,0,1,4,$ligne['annee']);
		//echo date("d.m.Y",$dateCalcEval);
		$jour_semaineEval=date("N",$dateCalcEval);
		$maxDateEval = $dateCalcEval-86400*($jour_semaineEval-1)+604800*($ligne['noSemaine']);
	} else {
		if($tri==3) $tri=2;
	}
	// recherche des heures / jour travaillés sur le thème
	$requeteH = "SELECT IDTheme, NomTheme, sum( heures ) AS heures, count( heures ) AS jours
	FROM (
	SELECT  jo.IDTheme as IDTheme, NomTheme, sum( Heures ) AS heures
	FROM elevesbk
	JOIN journal jo ON IDGDN = IDEleve
	JOIN theme th ON jo.IDTheme=th.IDTheme
	WHERE IDGDN=".$IDEleve." and jo.IDTheme=".$IDTheme;
	switch($tri) {
		case 1: $requeteH .= " and (DateJournal ".$betweenSQLSem.")"; break;
		case 2: $requeteH .= " and (DateJournal ".$betweenSQLAnn.")"; break;
		case 3: $requeteH .= " and (DateJournal >= '".date("Y-m-d",$maxDateEval)."')"; break;
	}
	/*
	if($tri!=0) {
		if($noSemaine>30) {
			$requeteH .= " and (DateJournal between '".$annee."-08-01' and '".($annee+1)."-07-31')";
		} else {
			$requeteH .= " and (DateJournal between '".($annee-1)."-08-01' and '".$annee+1."-07-31')";
		}
	}*/
	$requeteH .= " GROUP BY jo.IDTheme, DateJournal
	) AS res
	GROUP BY IDTheme";
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requeteH);
	$ligne = mysqli_fetch_assoc($resultat);
	$strHeures = sprintf("%2.1f",$ligne['heures'])."h / ".sprintf("%d",$ligne['jours'])." jours";
	$nomTheme = $ligne['NomTheme'];
} else {
	$requeteH = "SELECT sum( heures ) AS heures, count( heures ) AS jours
	FROM (
	SELECT sum( Heures ) AS heures
	FROM elevesbk
	JOIN journal jo ON IDGDN = IDEleve
	JOIN theme th ON jo.IDTheme=th.IDTheme
	WHERE IDGDN=".$IDEleve." and (DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') GROUP BY DateJournal
	) AS res";
	$resultat =  mysqli_query($connexionDB,$requeteH);
	$ligne = mysqli_fetch_assoc($resultat);

	$nomTheme = "Semaine: ".$noSemaine.", du ".date("d.m.Y",$lundi)." au ".date("d.m.Y",$vendredi);
	$strHeures = sprintf("%2.1f",$ligne['heures'])."h / ".sprintf("%d",$ligne['jours'])." jours";;
}
//$strHeures = $IDEleve."/".$IDTheme."/".$noSemaine."/".$annee;

class PDF extends FPDF
{
// En-tête
function Header()
{
    global $strHeures,$annee,$nomTheme,$nom,$prenom, $noSemaine, $tri, $semestreAct, $IDTheme, $only;
    // Logo
    $this->Image("../../images/logoEMT.jpg",20,11,40);
    // Police Arial gras 15
    $this->SetFont('Arial','B',15);
    // Décalage à droite
    $this->Cell(60,16,'',0,0,'C');
    // Titre
	if($only=="eval") {
		$this->Cell(60,16,'Evaluation',0,0,'C');
	} else {
		$this->Cell(60,16,'Journal de travail',0,0,'C');
	}
    $this->Cell(68,16,'',1,0,'C');

    $this->SetFont("Arial","",8);

    $this->SetXY(130,13);
	$this->SetFont("Arial","B",8);
    //$this->Write(0,iconv('UTF-8', 'windows-1252', $nom)." ".iconv('UTF-8', 'windows-1252', $prenom));
	$this->Write(0, $nom." ". $prenom);
	$this->SetFont("Arial","",8);
    $this->SetXY(130,16.5);
    $this->Write(0,$nomTheme);
	if(empty($IDTheme) || $tri!=0) {
		$this->SetXY(130,20);
		$txtSem = "";
		if($tri==1) {
			$txtSem = ", semestre ".$semestreAct;
		}
		if($tri==3) {
			$txtSem = ", non évalué";
		}
		if($noSemaine>30) {
			$this->Write(0,"Année: ".$annee."/".($annee+1).$txtSem);
		} else {
			$this->Write(0,"Année: ".($annee-1)."/".$annee.$txtSem);
		}
	}
    $this->SetXY(130,23.5);
	if($only!="eval") {
		$this->Write(0,"Heures consacrées: ".$strHeures);
	} else {
		$this->Write(0,"Evaluation: semaine ".$noSemaine);
	}

    $this->SetFont("Arial","B",8);
	//$this->SetXY(20,30);
    //$this->Write(0,$annee."/".$noSemaine."/".$tri."/".$betweenSQLSem);
	if($only!="eval") {
		$this->SetXY(20,35);
		$this->Write(0,"Date");
		$this->SetXY(40,35);
		$this->Write(0,"Sem.");
		$this->SetXY(50,35);
		$this->Write(0,"Heures");
		$this->SetXY(65,35);
		$this->Write(0,"Commentaires");
		$this->SetDrawColor(183);
		$this->Line(20,37.5,197,37.5);
	}
}

// Pied de page
function Footer()
{
    // Positionnement à 1,5 cm du bas
    $this->SetY(-15);
    // Police Arial italique 8
    $this->SetFont('Arial','I',8);
    // Numéro de page
    $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
}
}

$PDF = new PDF();
/* posistion initial */
$posLigne = 40;
$posCol = 20;
$PDF->AddPage();
	$PDF->SetFont("Arial","",8);
	$nomTheme = "";
	$PDF->SetDrawColor(183); // Couleur du fond
	$PDF->SetFillColor(221); // Couleur des filets
if($only!="eval") {
	if(!empty($IDTheme)) {
		$requete = "SELECT * FROM journal where IDEleve = $IDEleve and IDTheme = $IDTheme";
		/*
		if($tri!=0) {
			if($noSemaine>30) {
				$requete .= " and (DateJournal between '".$annee."-08-01' and '".($annee+1)."-07-31')";
			} else {
				$requete .= " and (DateJournal between '".($annee-1)."-08-01' and '".$annee."-07-31')";
			}
		} */
		switch($tri) {
			case 1: $requete .= " and (DateJournal ".$betweenSQLSem.")"; break;
			case 2: $requete .= " and (DateJournal ".$betweenSQLAnn.")"; break;
			case 3: $requete .= " and (DateJournal >= '".date("Y-m-d",$maxDateEval)."')"; break;
		}
		$requete .= " order by DateJournal";
	} else {
		$requete = "SELECT * FROM journal jou join theme th on jou.IDTheme=th.IDTheme where IDEleve = $IDEleve and (DateJournal between '".date('Y-m-d', $lundi)."' and '".date('Y-m-d', $vendredi)."') order by jou.IDTheme, DateJournal";
	}
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);

	while ($ligne = mysqli_fetch_assoc($resultat)) {

		//$idJournal = $ligne['IDJournal'];
		if(!empty($ligne['NomTheme'])&&$nomTheme!=$ligne['NomTheme']) {
			//$posLigne = $posLigne+2;
			$nomTheme=$ligne['NomTheme'];
			$PDF->SetFont("Arial","I",8);
			$PDF->SetTextColor(0,0,0);
			$PDF->SetXY($posCol,$posLigne-2.5);
			$PDF->Cell(177,4,$nomTheme,1,1,'L',1);
			//$PDF->Write(0,$nomTheme);

			// recherche du nombre d'heures déjà effectuées pour le thème sur l'année scolaire entière
			$requeteTot = "SELECT IDTheme, sum( heures ) AS heures, count( heures ) AS jours FROM (SELECT  jo.IDTheme as IDTheme, sum( Heures ) AS heures FROM elevesbk JOIN journal jo ON IDGDN = IDEleve JOIN theme th ON jo.IDTheme=th.IDTheme";
			if($noSemaine>30) {
				$requeteTot .= " where (DateJournal between '".$annee."-08-01' and '".date('Y-m-d', $vendredi)."') and IDGDN=".$IDEleve." and jo.IDTheme=".$ligne['IDTheme'];
			} else {
				$requeteTot .= " where (DateJournal between '".($annee-1)."-08-01' and '".date('Y-m-d', $vendredi)."') and IDGDN=".$IDEleve." and jo.IDTheme=".$ligne['IDTheme'];
			}
			$requeteTot .= " GROUP BY jo.IDTheme, DateJournal) AS res GROUP BY IDTheme";
			//echo "Heures: ".$requeteTot."<br>";
			$resultatTot =  mysqli_query($connexionDB,$requeteTot);
			$ligneHeures = mysqli_fetch_assoc($resultatTot);
			$PDF->SetXY(130,$posLigne-0.5);
			if($ligne['Objectif']!=0) {
				$PDF->Write(0,"Cumul: ".$ligneHeures['heures']."h, objectif: ".$ligne['Objectif']."h");
			} else {
				$PDF->Write(0,"Cumul: ".$ligneHeures['heures']);
			}

			$posLigne += 5;
			$PDF->SetFont("Arial","",8);
		}
		if(empty($ligne['DateValidation']) || $ligne['DateValidation']=="0000-00-00") {
			$PDF->SetTextColor(100,100,100);
		} else {
			$PDF->SetTextColor(0,0,0);
		}
		$date = strtotime($ligne['DateJournal']);
		$PDF->SetXY($posCol,$posLigne);
		$PDF->Write(0,date('d.m.Y', $date));
		$PDF->SetXY($posCol+22,$posLigne);
		$PDF->Write(0,date('W', $date));
		$PDF->SetXY($posCol+32,$posLigne);
		$PDF->Write(0,$ligne['Heures']."h");
		$PDF->SetXY($posCol+45,$posLigne);
		// remplacer les caractère wiki
		$tok = $ligne['Commentaires'];
		$tok = preg_replace("/'''(.*?)'''/", '$1', $tok);
		$tok = preg_replace('/\* (.*?)\n/', '- $1', $tok);
		$tok = preg_replace('/# (.*?)\n/', '- $1', $tok);
		// isoler les différentes lignes du commentaire
		$tok = strtok($tok, "\r\n");
		$found = 0;
		while ($tok !== false) {
			$debut=0;
			while(strlen($tok) > $debut) {
				$fin = 98;
				$txt = substr($tok,$debut,$fin);
				if(strlen($txt)==$fin) {
					$fin = strrpos($txt," ");
				}
				$PDF->Write(0,substr($tok,$debut,$fin));
				$posLigne += 3.5;
				$found = 1;
				if($posLigne>275) {
					$PDF->AddPage();
					$posLigne = 40;
				}
				$PDF->SetXY($posCol+45,$posLigne);
				$debut += ($fin+1);
			}
			$PDF->SetXY($posCol+45,$posLigne);
			$tok = strtok("\r\n");
		}
		if($found) {
			$posLigne -= 3.5;
		}

		$PDF->Line($posCol,$posLigne+2.5,$posCol+177,$posLigne+2.5);

		$posLigne += 5;
		if($posLigne>260) {
			$PDF->AddPage();
			$posLigne = 40;
		}
	}
}

$compEval = $competencesEvaluation;
$compPond = $competencesPonderation;
if(!empty($lastAnneeEvalOld) && !empty($lastSemestreEvalOld)) {
	if($annee<$lastAnneeEvalOld || ($annee==$lastAnneeEvalOld && $semestreAct <= $lastSemestreEvalOld)) {
		$compEval = $competencesEvaluationOld;
		$compPond = $competencesPonderationOld;
	}
}
// évaluation: si vue semaine et config eval hebdo ou si vue theme et impression eval activée
if((empty($IDTheme)&&$modeEvaluation=="hebdo")||$only=="eval") {
	$posLigne += 10;
	$PDF->SetFont("Arial","B",8);
	$PDF->SetTextColor(0,0,0);
	$PDF->SetXY($posCol,$posLigne);
	$PDF->Write(0,"Evaluation");
	if($typeEvaluation == "note") {
		$PDF->SetXY($posCol+33,$posLigne);
		$PDF->Write(0,"Note");
	} else {
		$PDF->SetXY($posCol+31,$posLigne);
		$PDF->Write(0,"Niveau");
	}
	$PDF->SetXY($posCol+45,$posLigne);
	$PDF->Write(0,"Remarques");
	$PDF->SetFont("Arial","",8);
	$posLigne += 5;

	if(empty($IDTheme)) {
		// vue par semaine -> recherche par noSemaine/année
		$requete = "SELECT * FROM evalhebdo left outer join prof on Responsable=userid where IDEleve = $IDEleve and NoSemaine = $noSemaine and Annee = $annee order by IDCompetence, IDTypeEval";
	} else {
		$requete = "SELECT * FROM evalhebdo left outer join prof on Responsable=userid where IDEleve = $IDEleve and NoSemaine = $noSemaine and Annee = $annee and IDTheme=$IDTheme order by IDCompetence, IDTypeEval";
	}
	$resultat =  mysqli_query($connexionDB,$requete);
	$idcomp = 0;
	$dateValidation = "";
	$respValidation = "";
	if(!empty($resultat)&&mysqli_num_rows($resultat)>0) {
		while ($ligne = mysqli_fetch_assoc($resultat)) {
			// ne pas afficher les entrées non validées pour l'apprenti
			if(hasAdminRigth() || !empty($ligne['DateValidation']) || $ligne['IDTypeEval']==1 ) {
				if($idcomp!=$ligne['IDCompetence']) {
					$idcomp=$ligne['IDCompetence'];
					$PDF->SetXY($posCol,$posLigne-3);
					$PDF->SetFont("Arial","I",8);
					if($ligne['IDCompetence']<99) {
						$PDF->Cell(177,4,$compEval[$ligne['IDCompetence']],1,1,'L',1);
						//$PDF->Write(0,$competencesEvaluation[$ligne['IDCompetence']]);
					} else {
						$posLigne += 10;
						$PDF->SetXY($posCol,$posLigne-3);
						$PDF->Cell(177,4,"Observations",1,1,'L',1);
						//$PDF->Write(0,"Observations");
					}
					$PDF->SetFont("Arial","",8);
					$posLigne += 3.5;
				}
				$PDF->SetXY($posCol+23.5,$posLigne);
				if($ligne['IDTypeEval']==1) {
					$PDF->Write(0,"APP");
				} else {
					$PDF->Write(0,"MAI");
					if(empty($dateValidation)) {
						$dateValidation = $ligne['DateValidation'];
						$respValidation = $ligne['abbr'];
					}
				}
				$PDF->SetXY($posCol+33.5,$posLigne);
				if($typeEvaluation == "note") {
					$PDF->Write(0,$ligne['Note']);
				} else {
					if(!empty($ligne['Niveau'])) $PDF->Write(0,chr($ligne['Niveau']+64));
				}
				$PDF->SetXY($posCol+45,$posLigne);
					// remplacer les caractère wiki
				$tok = $ligne['Remarque'];
				$tok = preg_replace("/'''(.*?)'''/", '$1', $tok);
				$tok = preg_replace('/\* (.*?)\n/', '- $1', $tok);
				$tok = preg_replace('/# (.*?)\n/', '- $1', $tok);
				// isoler les différentes lignes du commentaire
				$tok = strtok($tok, "\r\n");
				$found = 0;
				while ($tok !== false) {
					$debut=0;
					while(strlen($tok) > $debut) {
						$fin = 98;
						$txt = substr($tok,$debut,$fin);
						if(strlen($txt)==$fin) {
							$fin = strrpos($txt," ");
						}
						$PDF->Write(0,substr($tok,$debut,$fin));
						$posLigne += 3.5;
						$found=1;

						if($posLigne>275) {
							$PDF->AddPage();
							$posLigne = 40;
						}
						$PDF->SetXY($posCol+45,$posLigne);
						$debut += ($fin+1);
					}
					$PDF->SetXY($posCol+45,$posLigne);
					$tok = strtok("\r\n");
				}
				if($found) {
					$posLigne -= 3.5;
				}

				$PDF->Line($posCol+23.5,$posLigne+2.5,$posCol+177,$posLigne+2.5);

				$posLigne += 5;
				if($posLigne>260) {
					$PDF->AddPage();
					$posLigne = 40;
				}
			}
		}
	}
	if(!empty($dateValidation)) {
		$PDF->SetXY(130,$posLigne+10);
		$PDF->Write(0,"Evaluation validée le ".date('d.m.Y', strtotime($dateValidation))." par ".$respValidation);
	}
}
$PDF->Output('journal.pdf','I');
?>
