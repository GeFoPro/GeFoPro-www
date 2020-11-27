<?php 
include("../../appHeader.php");

/* PDF */
include("phpToPDF.php");


if(isset($_GET['nom'])) {
	$nom = $_GET['nom'];
	$prenom = $_GET['prenom'];
	$IDEleve = $_GET['idEleve'];
	$IDTheme = $_GET['idTheme'];
	$annee = $_GET['annee'];
} else if(isset($_POST['nom'])) {
	$nom = $_POST['nom'];
	$prenom = $_POST['prenom'];
	$IDEleve = $_POST['IDEleve'];
	$IDTheme = $_POST['IDTheme'];
	$annee = $_POST['annee'];
}

//echo $requete;
$requeteH = "SELECT IDTheme, NomTheme, sum( heures ) AS heures, count( heures ) AS jours
FROM (
SELECT  jo.IDTheme as IDTheme, NomTheme, sum( Heures ) AS heures
FROM elevesbk
JOIN journal jo ON IDGDN = IDEleve
JOIN theme th ON jo.IDTheme=th.IDTheme
WHERE (DateJournal between '".$annee."-08-01' and '".($annee+1)."-07-31') and IDGDN=".$IDEleve." and jo.IDTheme=".$IDTheme."
GROUP BY jo.IDTheme, DateJournal
) AS res
GROUP BY IDTheme";
//echo $requete;
$resultat =  mysql_query($requeteH);
$ligne = mysql_fetch_assoc($resultat);
$strHeures = sprintf("%2.1f",$ligne['heures'])."h / ".sprintf("%d",$ligne['jours'])." jours";
$nomTheme = $ligne['NomTheme'];

class PDF extends FPDF
{
// En-tête
function Header()
{
    global $strHeures,$annee,$nomTheme,$nom,$prenom;
    // Logo
    $this->Image("../../images/logoEMT.jpg",20,11,40);
    // Police Arial gras 15
    $this->SetFont('Arial','B',15);
    // Décalage à droite
    $this->Cell(60,16,'',0,0,'C');
    // Titre
    $this->Cell(60,16,'Journal de travail',0,0,'C');
    $this->Cell(68,16,'',1,0,'C');

    $this->SetFont("Arial","",8);

    $this->SetXY(130,13);
    $this->Write(0,$nom." ".$prenom);
    $this->SetXY(130,16.5);
    $this->Write(0,$nomTheme);
    $this->SetXY(130,20);
    $this->Write(0,"Année ".$annee."/".($annee+1));
    $this->SetXY(130,23.5);
    $this->Write(0,"Heures consacrées: ".$strHeures);

    $this->SetFont("Arial","B",8);
    $this->SetXY(20,35);
    $this->Write(0,"Date");
    $this->SetXY(40,35);
    $this->Write(0,"Sem.");	
    $this->SetXY(50,35);
    $this->Write(0,"Heures");
    $this->SetXY(65,35);
    $this->Write(0,"Commentaires"); 	
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


// recherche des attributs généraux (1 à 6)
$requete = "SELECT * FROM journal where IDEleve = $IDEleve and IDTheme = $IDTheme and (DateJournal between '".$annee."-08-01' and '".($annee+1)."-07-31') order by DateJournal";
//echo $requete;
$resultat =  mysql_query($requete);
$PDF->AddPage();
$PDF->SetFont("Arial","",8);

while ($ligne = mysql_fetch_assoc($resultat)) {
	//$idJournal = $ligne['IDJournal'];
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

	$posLigne += 4;
	if($posLigne>260) {
		$PDF->AddPage();
		$posLigne = 40;
	}
		

}


$PDF->Output('journal.pdf','D');
?>
