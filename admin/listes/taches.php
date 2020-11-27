<?php 
include("../../appHeader.php");

/* mode html ou excel */
$modeHTML = false;
if(isset($_GET['modeHTML'])) {
	$modeHTML = true;
}

if(!$modeHTML) {
	/* librairies pour Excel */
	require_once 'PHPExcel/IOFactory.php';
	require_once 'PHPExcel/Writer/Excel5.php';
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	//$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load("../../docBase/taches.xls");
} else {
	include("entete.php");
?>

<div id="page">
<?
include($app_section."/userInfo.php");
}

/* position de départ pour excel */
$configurationColExcel = array(1 => "B","C","D","E","F");
$configurationLiExcel = 7;

/* jour de la semaine */
$tab_jour = array(1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi');

/* numéro de semaine */
$semaine =  date('W');


/* en-tête */
if(!$modeHTML) {
	$objPHPExcel->getActiveSheet()->setCellValue('A'.($configurationLiExcel-1), "Semaine $semaine");
} else {
	echo "<br><table border=0 width='100%'><tr><td><!-- h2>Tâches atelier, semaine $semaine</h2 --></td><td align='right'><a href='taches.php'>A imprimer: <img src='/iconsFam/printer.png'></a></td></tr></table><br>\n";
	echo "<div class='post'>";
	echo "<br><div id='corners'>";
	echo "<div id='legend'>Tâches atelier, semaine $semaine</div>";
	echo "<table id='hor-minimalist-b' width='100%'><tr>";
	echo "<th></th><th></th><th width='120'>Balayages</th><th width='120'>Poubelles</th><th width='120'>Place Machines</th><th width='120'>Papier/Carton</th><th width='120'>PET + Alu</th><th width='120'>PC/Imprimantes</th><th width='120'>Bobines de fils</th></tr><tr>";
}

/* tableau des élèves par jour et construction de l'entête*/
$eleves = array();
$cnt = 1;
foreach ($configurationTCH as $pos => $value) {
	// associer la liste d'élève
	
		$requete = "SELECT * FROM $tableElevesBK el join eleves eli on el.IDGDN=eli.IDGDN and IDEntreprise=1 left outer join $tableAttribEleves at on (el.IDGDN = at.IDEleve and (at.IDAttribut = 8 OR at.IDAttribut = 13)) where Classe like '$value%' order by Nom, Prenom";
		$resultat =  mysql_query($requete);
		$classe = array();
		//echo "classe: ".$value."<br>";
		while ($ligne = mysql_fetch_assoc($resultat) ) {
			//echo $ligne['Nom']." - ".$ligne['IDAttribut'];
			if(empty($ligne['IDAttribut'])) {
				$classe[] = $ligne['Nom'] . ' ' . $ligne['Prenom'];
				//echo " el: ".$ligne['Nom'] . ' ' . $ligne['Prenom']."<br>";
			}
		}
	
	//print_r($classe);
	$eleves[$tab_jour[$cnt]] = $classe;
	$cnt++;
	// ajout en-tête
	if(!$modeHTML) {
		$objPHPExcel->getActiveSheet()->setCellValue($configurationColExcel[$pos].$configurationLiExcel, $configurationTCH[$pos]);
	}
}

$cntJour = 1;
foreach ($eleves as $jour => $classe) {
	if($modeHTML) {
		echo "<tr><td width='100'><b>$tab_jour[$cntJour]</b></td><td width='100'><b>$configurationTCH[$cntJour]</b></td>\n";
	}
	// nombre d'élève à dispo
	$cntEleves = count($classe);
	$debut = ($semaine -1  + $decalageSemaine[$cntJour]) % $cntEleves;
	$posNom = $debut;
	//echo "début: $debut, nom: $classe[$posNom]";
	$posCell = $configurationLiExcel + 2;
	for($cnt=1;$cnt<=$tachesParJour;$cnt++) {
		if($posNom >= $cntEleves) {
			$posNom = 0;
		}
		if($cnt>$cntEleves) {
			//$posNomDec = $decalageEleve[$cnt];
			$posNomModulo = ($posNom + $decalageEleve[$cnt]) % $cntEleves;
			//echo "$posNomModulo, $cntEleves, $classe[$posNomModulo]<br>";
			if(!$modeHTML) {
				$objPHPExcel->getActiveSheet()->setCellValue($configurationColExcel[$cntJour].$posCell, iconv("ISO-8859-1", "UTF-8", "$classe[$posNomModulo]"));
			} else {
				echo "<td>$classe[$posNomModulo]</td>";
			}
		} else {
			//echo "      $classe[$posNom]<br>";
			if(!$modeHTML) {
				$objPHPExcel->getActiveSheet()->setCellValue($configurationColExcel[$cntJour].$posCell, iconv("ISO-8859-1", "UTF-8", "$classe[$posNom]"));
			} else {
				echo "<td>$classe[$posNom]</td>";
			}			
		}
		$posNom++;
		// incrément ligne excel
		$posCell = $posCell + 2;
	}
	//echo "<br>\n";
	$cntJour++;
	if($modeHTML) {
		echo "</tr>\n";
	}
}
	
if(!$modeHTML) {
	// générer la feuille excel
	$writer = new PHPExcel_Writer_Excel5($objPHPExcel);
	header('Content-type: application/vnd.ms-excel');
	header("Content-Disposition: attachment;Filename=taches.xls");
	$writer->save('php://output'); 
	
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
	//header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	//$objWriter->save("php://output");
} else {
?>
</table></div>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
<?php } ?>