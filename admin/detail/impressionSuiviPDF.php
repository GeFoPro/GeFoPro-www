<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:60
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: impressionSuiviPDF.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:88
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

/* PDF */
include("phpToPDF.php");


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
	$semestreAct = 2;
}


if(empty($IDTheme)) {
	$nomThemeTitre = "Semaine: ".$noSemaine.", du ".date("d.m.Y",$lundi)." au ".date("d.m.Y",$vendredi);
} else {
	if($IDTheme==2) {
		$nomThemeTitre = "Suivi complet";
	} else if ($IDTheme==1) {
		$nomThemeTitre = "CPG";
	} else {
		$requeteH = "SELECT NomTheme from theme where IDTheme=".$IDTheme;
		$resultat =  mysqli_query($connexionDB,$requeteH);
		$ligne = mysqli_fetch_assoc($resultat);
		$nomThemeTitre = $ligne['NomTheme'];
	}
}
class PDF extends FPDF
{
// En-tête
function Header()
{
    global $strHeures,$annee,$nomThemeTitre,$nom,$prenom, $noSemaine, $tri, $semestreAct, $IDTheme, $only;
    // Logo
    $this->Image($_SERVER['DOCUMENT_ROOT']."/".$_SESSION['home'].LogoInstitution,20,11,40);
    // Police Arial gras 15
    $this->SetFont('Arial','B',15);
    // Décalage à droite
    $this->Cell(60,16,'',0,0,'C');
    // Titre
	$this->Cell(60,16,'Suivi',0,0,'C');
    $this->Cell(68,16,'',1,0,'C');

    $this->SetFont("Arial","",8);

    $this->SetXY(130,13);
	$this->SetFont("Arial","B",8);
    $this->Write(0,$nom." ".$prenom);
	$this->SetFont("Arial","",8);
    $this->SetXY(130,20);
    $this->Write(0,$nomThemeTitre);
	if(empty($IDTheme) || $tri!=0) {
		$this->SetXY(130,23.5);
		if($tri==1) {
			$txtSem = ", semestre ".$semestreAct;
		}
		if($noSemaine>30) {
			$this->Write(0,"Année: ".$annee."/".($annee+1).$txtSem);
		} else {
			$this->Write(0,"Année: ".($annee-1)."/".$annee.$txtSem);
		}
	} else {
		$this->SetXY(130,23.5);
		$this->Write(0,"Toutes périodes confondues");
	}


    $this->SetFont("Arial","B",8);
	//$this->SetXY(20,30);
    //$this->Write(0,$annee."/".$noSemaine."/".$tri."/".$betweenSQLSem);
	if($only!="eval") {
		$this->SetXY(20,35);
		$this->Write(0,"Date");
		$this->SetXY(40,35);
		$this->Write(0,"Auteur");
		$this->SetXY(60,35);
		$this->Write(0,"Remarques");
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


	//echo $requete;
	//$resultat =  mysqli_query($connexionDB,$requete);
	$requete = $_SESSION['last_request'];
	//echo $requete;
	//if($IDTheme==2) {
		$requete .= " order by IDTheme, DateSaisie";
	//} else {
	//	$requete .= " order by DateSaisie";
	//}
	$resultat =  mysqli_query($connexionDB,$requete);
	$lastTheme = -1;
	while ($ligne = mysqli_fetch_assoc($resultat)) {
		//$idJournal = $ligne['IDJournal'];
		if($ligne['TypeRemarque']>1) continue;
		if($lastTheme!=$ligne['IDTheme']) {
			//$posLigne = $posLigne+2;
			$nomTheme = "";
			$lastTheme = $ligne['IDTheme'];
			if($ligne['IDTheme']==1) {
				$nomTheme = "CPG - Remarques";
			} else {
				if($ligne['TypeRemarque']==0) {
					$nomTheme = "ADM - ";
				} else {
					$nomTheme = "SUV - ";
				}
				$nomTheme .= $ligne['NomTheme'];
			}
			$PDF->SetFont("Arial","I",8);
			$PDF->SetTextColor(0,0,0);
			$PDF->SetXY($posCol,$posLigne-2.5);
			$PDF->Cell(177,4,$nomTheme,1,1,'L',1);
			//$PDF->Write(0,$nomTheme);


			$posLigne += 5;
			$PDF->SetFont("Arial","",8);
		}

		$PDF->SetTextColor(0,0,0);
		$date = strtotime($ligne['DateSaisie']);
		$PDF->SetXY($posCol,$posLigne);
		$PDF->Write(0,date('d.m.Y', $date));
		$PDF->SetXY($posCol+20,$posLigne);
		$PDF->Write(0,$ligne['abbr']);

		$PDF->SetXY($posCol+40,$posLigne);
		// remplacer les caractère wiki
		$rem = $ligne['Remarque'];
		$cntBullet = 1;
		$cntBulletNbr = 1;
		// isoler les différentes lignes du commentaire
		$tok = strtok($rem, "\r\n");
		$found = 0;
		while ($tok !== false) {
			// convertion
			$count = 0;
			$tok = preg_replace("/'''(.*?)'''/", '$1', $tok,1,$count);
			if($count!=0) {
				// titre
				$PDF->SetFont("Arial","B",8);
				if($found) {
					$posLigne += 1;
					$PDF->SetXY($posCol+40,$posLigne);
				}
				$posLigne += 0.5;
			} else {
				$tok = preg_replace("/''(.*?)''/", '$1', $tok,1,$count);
				if($count!=0) {
					// titre
					$PDF->SetFont("Arial","I",8);
					if($found) {
						$posLigne += 1;
						$PDF->SetXY($posCol+40,$posLigne);
					}
					$posLigne += 0.5;
				} else {
					$PDF->SetFont("Arial","",8);
				}
			}

			$tok = preg_replace('/\* (.*?)/', chr(127).' $1', $tok,1,$count);
			if($count!=0) {
				$cntBullet++;
			}
			$tok = preg_replace('/# (.*?)/', $cntBulletNbr.'. $1', $tok,1,$count);
			if($count!=0) {
				$cntBulletNbr++;
			}
			
			$tok = preg_replace('/\[(.*?) (.*?)\]/', '$2', $tok);

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
				if($cntBullet!=1 || $cntBulletNbr!=1) {
					$PDF->SetXY($posCol+42,$posLigne);
				} else {
					$PDF->SetXY($posCol+40,$posLigne);
				}
				$debut += ($fin+1);
			}
			$PDF->SetXY($posCol+40,$posLigne);
			// nouvelle ligne
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

$PDF->Output('suivi.pdf','I');

?>
