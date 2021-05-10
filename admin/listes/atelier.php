<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:68
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: atelier.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 14:03:57
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

/* config attributs */
$attribNormal = 0;
$attribFem = 4;
$attribResp = 2;
$attribMPT = 1;
$attribtroisplusun = 3;
$attribfuturtrois = 5;
$attribMPTtrois = 6;
$attribDual = 8;
$attribCarnet = 100;
$attribTel = 101;
$attribRem = 102;
$attribConge = 106;

$attribPAS = 0;
$attribANG = 9;
$attribALL = 10;
$attribMAT = 11;

$attribHOR = 12;
$attribNum = 14;

// liste des ids des apprentis, triées par classe et par nom
$listeIds = array();

/* mode html ou excel */
$modeHTML = false;
if(isset($_GET['modeHTML'])) {
	$modeHTML = true;
}

/* mode Affichage */
$modeAff = '0';
if(isset($_GET['modeAff'])) {
	$modeAff = $_GET['modeAff'];
}

if(isset($_GET['classe'])) {
	$classe = $_GET['classe'];
	$_SESSION['classe'] = $classe;
} else if(isset($_SESSION['classe'])) {
	$classe = $_SESSION['classe'];
}

if(isset($_GET['idEleve'])) {
	$IDEleve = $_GET['idEleve'];
}

// si APP pas chargé l'effectuer maintenant
//require_once("Session.php");
//if(!application_loaded()) {
	//echo "reload app";
//	application_start();
//}

/* mode GDN ou locla */
$modeLocal = false;
if(isset($_GET['modeLocal'])) {
	$modeLocal = (boolean)$_GET['modeLocal'];
	$_SESSION['APP']['modeGDN'] = !$modeLocal;
	application_save ();
} else {
	if(isset($_SESSION['APP']['modeGDN'])) {
		$modeLocal = !$_SESSION['APP']['modeGDN'];
	}
}

if(!$modeHTML) {
	/* librairies pour Excel */
	require_once 'PHPExcel/IOFactory.php';
	require_once 'PHPExcel/Writer/Excel5.php';
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	//$objReader->setReadDataOnly(true);
	$liste = "../../docBase/liste_".$_SESSION['user_lang'].".xls";
	$objPHPExcel = $objReader->load($liste);
} else {
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
	function updateEleve(id,nom,prenom) {
		select = document.getElementById('modeAff');
		mode = select.options[select.selectedIndex].value;
		select = document.getElementById('classe');
		classe = select.options[select.selectedIndex].value;
		if(mode!=0) {
			document.location.href='atelier.php?modeHTML=&idEleve='+id+'&modeAff='+mode+'&classe='+classe;
		} else {
			document.location.href='../detail/detailEleve.php?idEleve='+id+'&nom='+nom+'&prenom='+prenom;
		}
	}

	</script>
	<?php
	include("../../userInfo.php");

	if(isset($_SESSION['user_attributs'])) {
		print_r($_SESSION['user_attributs']);
	}
}



function writeToCell($stmt, $dataCol, $objPHPExcel, $excelCol, $excelLine, $mode) {
	$cell = ociresult($stmt,$dataCol);
	writeDataToCell($cell, $objPHPExcel, $excelCol, $excelLine, $mode);
}

function writeDataToCell($cell, $objPHPExcel, $excelCol, $excelLine, $mode) {
	$objPHPExcel->getActiveSheet()->setCellValue($excelCol.$excelLine, iconv("ISO-8859-1", "UTF-8", "$cell"));
	if($mode) {
		$objPHPExcel->getActiveSheet()->getStyle($excelCol.$excelLine)->getFont()->setItalic(true);
	}
}

function getValue($connexion, $data, $pos) {
	if($connexion!=null) {
		return ociresult($data,$pos);
	} else {
		return $data[$pos-1];
	}
}

function nextEntry($connexion, $data) {
	if($connexion!=null) {
		if(ocifetch($data)) {
			return $data;
		} else {
			return null;
		}
	} else {
		return mysqli_fetch_row($data);
	}
}

//if(!hasAdminRigth() && $modeHTML) {
//	echo "<br><center><b>Contenu non autorisé.</b></center>";
//} else {
/* année en cours */
$annee = date('Y');
$mois = date('m');

/* en-tête */
if($mois<8) {
	$anneePlus = $annee;
	$annee = $annee-1;
} else {
	$anneePlus= $annee+1;
}
if(!$modeHTML) {
	$objPHPExcel->getActiveSheet()->setCellValue('A1', "$annee - $anneePlus");
} else {
	echo "<center> <font color='#088A08'></font></center>";
	// form
	echo "<FORM id='myForm' ACTION='atelier.php'  METHOD='GET'>";
	echo "<input type='hidden' name='modeHTML'>";
	echo "<br><table width='100%'><tr><td><!-- h2>&nbsp;Liste élèves $app_section $annee - $anneePlus</h2 --></td><td align='right'>".libelleTradUpd('modeaffichage')." <select id='modeAff' name='modeAff' onChange='submit();'>";
	if($modeAff==0) {
		echo "<option value='".$attribNormal."' selected='selected'>".libelleTrad('normal')."</option><option value='".$attribCarnet."'>".libelleTrad('carnets')."</option>";
	} else {
		echo "<option value='".$attribNormal."'>".libelleTrad('normal')."</option><option value='".$attribCarnet."' selected='selected'>".libelleTrad('carnets')."</option>";
	}
	echo "</select><select id='classe' name='classe' onChange='submit();'>";
	if(empty($classe) || $classe==0) {
		echo "<option value='0' selected='selected'>".libelleTrad('atelier')."</option>";
	} else {
		echo "<option value='0'>Atelier</option>";
	}
	// ajout d'une ligne Tous
	if(isset($classe) && $classe==101) {
		echo "<option value='101' selected='selected'>$app_section</option>";
	} else {
		echo "<option value='101'>$app_section</option>";
	}
	// construction de la liste des classes
	foreach ($configurationATE as $pos => $value) {
		if(!empty($classe) && $classe==$pos) {
			echo "<option value='$pos' selected='selected'>$value</option>";
		} else {
			echo "<option value='$pos'>$value</option>";
		}
	}
	// ajout d'une ligne pour interrogation des anciennes années (id=100)
	if(isset($classe) && $classe==100) {
		echo "<option value='100' selected='selected'>Anciens</option>";
	} else {
		echo "<option value='100'>".libelleTrad('anciens')."</option>";
	}
	echo "</select></td></tr></table><br>\n";
	/*
	$linkDB = "";
	if($connexion!=null) {
		$linkDB = "<a href='atelier.php?modeHTML&modeLocal=1'><img src='/iconsFam/database_refresh.png' onmouseover=\"Tip('Désactiver GDN')\" onmouseout='UnTip()'></a>";
	} else {
		$linkDB = "<a href='atelier.php?modeHTML&modeLocal=0'><img src='/iconsFam/database_refresh.png' onmouseover=\"Tip('Activer GDN')\" onmouseout='UnTip()'></a>";
	} */
	echo "<div class='post'><div id='corners'><div id='legend'>".libelleTradUpd('liste_eleve')." ".$app_section." $annee - $anneePlus</div><table id='hor-minimalist-b' width='100%'><tr><th colspan='2'><a href='atelier.php'><img src='/iconsFam/page_excel.png' onmouseover=\"Tip('".libelleTrad('listexcel')."')\" onmouseout='UnTip()'></a>";
	//echo "<a href='syncGDN.php'><img src='/iconsFam/arrow_refresh.png' onmouseover=\"Tip('Synchro local-GDN')\" onmouseout='UnTip()'></a> $linkDB ";
	echo "<a href='../detail/detailEleve.php'><img src='/iconsFam/add.png' onmouseover=\"Tip('".libelleTrad('ajout')."')\" onmouseout='UnTip()'></a></th><th></th><th align='center'>".libelleTradUpd('vestiaire')."</th><th align='center'>".libelleTradUpd('jeton')."</th><th align='center' width='100'>".libelleTradUpd('cle')."</th><th width='100'>".libelleTradUpd('utilisateur')."/<br>".libelleTradUpd('nompc')."</th><!-- th align='center' width='100'>Chaise/<br>Banc</th --><th>".libelleTradUpd('adresse')."</th><th align='center' width='120'>".libelleTradUpd('telparent')."/<br>".libelleTradUpd('telpers')."</th><th></th><th width='100' align='center'>".libelleTradUpd('datenaissance')."</th><!-- <th>ID GDN</th> --></tr>\n";
}

// gestion des modification sur élève
if(!empty($IDEleve) && $modeAff!=0) {
	$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $IDEleve and IDAttribut = $modeAff";
	//echo $requete."<br>";
	$resultat =  mysqli_query($connexionDB,$requete);
	$content = mysqli_fetch_assoc($resultat);
	if($content!=null) {
		// un enregistrement existe -> on le supprime
		$requete = "DELETE from $tableAttribEleves where IDEleve=$IDEleve and IDAttribut=$modeAff";
		$resultat =  mysqli_query($connexionDB,$requete);
	} else {
		// aucun enregistrement -> on l'ajoute
		$requete = "INSERT INTO $tableAttribEleves (IDEleve, IDAttribut) values ($IDEleve, $modeAff)";
		$resultat =  mysqli_query($connexionDB,$requete);
	}
	//echo $requete."<br>";

}
if(!empty($_GET['reset']) && $modeAff!=0) {
	// effacer l'attribut pour tous les élèves
	$requete = "DELETE from $tableAttribEleves where IDAttribut=$modeAff";
	$resultat =  mysqli_query($connexionDB,$requete);
}

if(!empty($_GET['resetAndSet']) && $modeAff!=0) {
	// ajouter une remarque de carnet non signés
	$requete = "SELECT distinct IDGDN as IDEleve from $tableEleves where IDGDN not in (SELECT IDEleve from $tableAttribEleves where IDAttribut=100)";
	$requeteNew = "SELECT distinct el.IDGDN as IDEleve from $tableEleves as el join elevesbk as elbk on el.IDGDN=elbk.IDGDN where el.IDEntreprise=1 and elbk.Classe in ('ZZZ'";
	foreach ($configurationATE as $pos => $value) {
		$requeteNew .= ",'".$value."'";
	}
	$requeteNew .= ") and el.IDGDN not in (SELECT IDEleve from $tableAttribEleves where IDAttribut=100)";
	//echo $requeteNew."<br>";
	$resultat = mysqli_query($connexionDB,$requeteNew);
	$cntModif=0;
	while ($ligne = mysqli_fetch_assoc($resultat)) {
		$requeteUpd = "INSERT INTO $tableAttribEleves (IDEleve, IDAttribut, Remarque, Date) values ($ligne[IDEleve], 103, \"Ajout automatique\", \"".date("Y-m-d")."\")";
		mysqli_query($connexionDB,$requeteUpd);
		$cntModif++;
	}
	// effacer l'attribut pour tous les élèves
	$requete = "DELETE from $tableAttribEleves where IDAttribut=$modeAff";
	$resultat =  mysqli_query($connexionDB,$requete);
}

// construction de la liste des classe si 'ancien' à été choisi
if(isset($classe)&&$classe==100) {
	$requeteCl = "select distinct Classe from elevesbk where Classe not like '".$app_section."%'";
	//foreach ($configurationATE as $pos => $value) {
	//	$requeteCl .= ",'".$value."'";
	//}
	// ajout des sections autres
	if(isset($configurationAPP)) {
		foreach ($configurationAPP as $pos => $value) {
			$requeteCl .= " and Classe not like '".$value."%'";
		}
	}
	//$requeteCl .= ") order by Classe";
		//echo $requeteCl."<br>";
	$resultat =  mysqli_query($connexionDB,$requeteCl);
	// construction de la nouvelle configuration
	$cnt=1;
	unset($configurationATE);
	while ($ligne = mysqli_fetch_assoc($resultat)) {
		$configurationATE[] = $ligne['Classe'];
		$cnt++;
	}

}

// construction de la liste des classe qui commencent par l'abbreviation de la section (ELT, AUT, etc.)
if(isset($classe)&&$classe==101) {
	$requeteCl = "select distinct Classe from elevesbk where Classe like '".$app_section."%' order by Classe desc";
	//$requeteCl = "select distinct Classe from elevesbk where Classe like 'ELT%' order by Classe desc";
	//echo $requeteCl;

	$resultat =  mysqli_query($connexionDB,$requeteCl);
	if(mysqli_num_rows ($resultat)!=0) {
		// construction de la nouvelle configuration
		$cnt=1;
		unset($configurationATE);
		while ($ligne = mysqli_fetch_assoc($resultat)) {
			$configurationATE[] = $ligne['Classe'];
			$cnt++;
		}
	}

}


/* tableau des élèves par jour et construction de l'entête*/
$cntCell = 3;
if(!empty($configurationATE)) {
	foreach ($configurationATE as $pos => $value) {
		if(empty($classe) || $classe==$pos || $classe==100 || ($classe==101 && strpos($value, $app_section)===0)) {
			// Récupérer la liste des adresses e-mail des profs (seulement si la DB de la GDN est accessible)
			$liste_emails_profs = "" ;
			/*
			if($connexion!=null)
			{
				// pas accessible pour l'instant
				//$stmt = ociparse($connexion,"SELECT DISTINCT EMAIL_PROF FROM VU_PROF, TB_SUIVRE, TB_COURS, TB_SOUS_CLASSE WHERE FK_COURS_PROF = PK_PROF AND PK_FK_SUIVRE_COURS = PK_COURS AND PK_FK_SUIVRE_SOUS_CLASSE = PK_SOUS_CLASSE AND NOM_SOUS_CLASSE like '$value%'");
				//ociexecute($stmt,OCI_DEFAULT);
				//while (($data = nextEntry($connexion,$stmt))!=null)
				//{
				//	$liste_emails_profs .= getValue($connexion,$data,1).';' ;
				//}
			} */

			// associer la liste d'élève
			//if($connexion!=null) {
				//$stmt = ociparse($connexion,"$GDN_eleves WHERE $GDN_tri like '$value%' $GDN_orderby");
				//ociexecute($stmt,OCI_DEFAULT);
			//} else {
				$requete = "SELECT * FROM $tableElevesBK where Classe like '$value' order by Nom";
					//echo $requete."<br>";
				$stmt =  mysqli_query($connexionDB,$requete);
			//}
			if(!$modeHTML) {
				$objPHPExcel->getActiveSheet()->setCellValue("A".$cntCell, iconv("ISO-8859-1", "UTF-8", "$value"));
				$objPHPExcel->getActiveSheet()->getStyle("A".$cntCell.":S".$cntCell)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$objPHPExcel->getActiveSheet()->getStyle("A".$cntCell++)->getFont()->setBold(true);
			} else {
				echo "<tr><td colspan='10'><b><a href='#' onClick='toggle(\"block$pos\");'>$value</a></b></td><td colspan='3'>";
				//echo "<a href='' id='email_eleve_$value'>  <img src='/iconsFam/iconStudent.gif' height='25' align='absmiddle' onmouseover=\"Tip('Email à la classe')\" onmouseout='UnTip()'></a>";
				//echo "<a href='mailto:$liste_emails_profs' id='email_profs_$value'>  <img src='/iconsFam/iconProfessor.gif' height='25' align='absmiddle' onmouseover=\"Tip('Email aux enseigants')\" onmouseout='UnTip()'></a>";
				echo "</td></tr>\n";
			}
			$emailGroup = "";
			$emailGroupOutlook = "";
			while ($data = mysqli_fetch_assoc($stmt)) {
			//while (($data = nextEntry($connexion,$stmt))!=null){
				$troisplusun = false;

				//if(strstr(getValue($connexion,$data,9),"+1")) {
				//	$troisplusun = true;
				//}
				// recherche des données complémentaires

				//$idGDN = getValue($connexion,$data,1);
				$idGDN = $data['IDGDN'];
				$nom = $data['Nom'];
				$prenom = $data['Prenom'];
				//echo $idGDN."-".$nom."-".$prenom;


				$requete = "SELECT * FROM $tableEleves el left join cleatelier ca on el.IDCle=ca.IDCle where el.IDGDN = $idGDN";
		//echo $requete."<br>";
				$resultat =  mysqli_query($connexionDB,$requete);
				if($resultat!=null) {
					$ligne = mysqli_fetch_assoc($resultat);
				}
				// ajout dans liste éleves si internes
				//listeIds[] = array($idGDN,getValue($connexion,$data,2),getValue($connexion,$data,3),$value);
				if($ligne['IDEntreprise']==1) {
					$listeIds[] = array($idGDN,htmlentities($nom, ENT_QUOTES),$prenom,$value);
				}
				if((!empty($classe)&&$classe!=0)||$ligne['IDEntreprise']==1) {
					if(!$modeHTML) {

						writeDataToCell($nom,$objPHPExcel,"A",$cntCell,$troisplusun);
						//writeToCell($stmt,2,$objPHPExcel,"A",$cntCell,$troisplusun);
						writeDataToCell($prenom,$objPHPExcel,"B",$cntCell,$troisplusun);
						//writeToCell($stmt,3,$objPHPExcel,"B",$cntCell,$troisplusun);
						writeDataToCell($data['Adresse'],$objPHPExcel,"I",$cntCell,$troisplusun);
						//writeToCell($stmt,4,$objPHPExcel,"I",$cntCell,$troisplusun);
						writeDataToCell($data['NPA'],$objPHPExcel,"J",$cntCell,$troisplusun);
						//writeToCell($stmt,5,$objPHPExcel,"J",$cntCell,$troisplusun);
						writeDataToCell($data['Localite'],$objPHPExcel,"K",$cntCell,$troisplusun);
						//writeToCell($stmt,6,$objPHPExcel,"K",$cntCell,$troisplusun);
						writeDataToCell($data['Email'],$objPHPExcel,"N",$cntCell,$troisplusun);
						//writeToCell($stmt,7,$objPHPExcel,"N",$cntCell,$troisplusun);
						// données uniquement de srv-electro
						writeDataToCell($ligne['NoVestiaire'],$objPHPExcel,"C",$cntCell,$troisplusun);
						writeDataToCell($ligne['NoJeton'],$objPHPExcel,"D",$cntCell,$troisplusun);
						writeDataToCell($ligne['NoBadge'],$objPHPExcel,"E",$cntCell,$troisplusun);
						writeDataToCell($ligne['NumeroCle'],$objPHPExcel,"F",$cntCell,$troisplusun);

						//writeDataToCell($ligne['noChaise'],$objPHPExcel,"G",$cntCell,$troisplusun);
						//writeDataToCell($ligne['noBanc'],$objPHPExcel,"H",$cntCell,$troisplusun);

						writeDataToCell($ligne['NoTel'],$objPHPExcel,"L",$cntCell,$troisplusun);
						writeDataToCell($ligne['NoMobile'],$objPHPExcel,"M",$cntCell,$troisplusun);
						$dateNaissance = explode("-", $ligne['DateNaissance']);
						if(count($dateNaissance)==3) {
							$dateList = date("d.m.Y",mktime(0,0,0, $dateNaissance[1], $dateNaissance[2], $dateNaissance[0]));
							writeDataToCell($dateList,$objPHPExcel,"O",$cntCell,$troisplusun);
						}
						writeDataToCell($ligne['NoSeriePC'],$objPHPExcel,"P",$cntCell,$troisplusun);
						writeDataToCell($ligne['NomPC'],$objPHPExcel,"Q",$cntCell,$troisplusun);
						writeDataToCell($ligne['MacAdresseWifi'],$objPHPExcel,"R",$cntCell,$troisplusun);
						writeDataToCell($ligne['MacAdresseEthernet'],$objPHPExcel,"S",$cntCell,$troisplusun);
					} else {
						//$nomDB = getValue($connexion,$data,2);
						//$prenomDB = getValue($connexion,$data,3);
						echo "<tr block$pos='$idGDN' id='hidethis' onclick='updateEleve($idGDN,\"".htmlentities($nom, ENT_QUOTES)."\",\"$prenom\");'";

						$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut in ($attribConge,$attribTel) and Date = \"".date("Y-m-d")."\"";
			//echo $requete."<br>";
						$resultat =  mysqli_query($connexionDB,$requete);

						$ico = mysqli_fetch_assoc($resultat);
						if($ico!=null) {
							echo " bgcolor='#F8E0E0' onmouseover=\"Tip('";
							if($ico['IDAttribut']==$attribTel) {
								echo "Malade";
							} else {
								echo "Congé aujourd\'hui";
							}
							echo ": ".addslashes($ico['Remarque'])."')\" onmouseout='UnTip()'";
						}
						$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribConge and Date > \"".date("Y-m-d")."\"";
			//echo $requete."<br>";
						$resultat =  mysqli_query($connexionDB,$requete);
						$ico = mysqli_fetch_assoc($resultat);
						if($ico!=null) {
							echo " bgcolor='#F8ECE0' onmouseover=\"Tip('Congé le ".date('d.m.Y', strtotime($ico['Date'])).": ".addslashes($ico['Remarque'])."')\" onmouseout='UnTip()'";
						}
						if($ligne['IDEntreprise']!=1) {
							echo " bgcolor='#F0F0F0' style='color:#C0C0C0' onmouseover=\"Tip('Formation en dual')\" onmouseout='UnTip()'";
						}
						echo "><td><nobr>";
						if($modeAff==0) {
							$cntIcon = 1;

							// mode Normal
							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribFem";
			//echo $requete."<br>";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null) {
								echo "<img src='/iconsFam/user_female.png'>";
							} else {
								echo "<img src='/iconsFam/user.png'>";
							}

							if($ligne['IDEntreprise']!=1) {
								echo "<img src='/iconsFam/building.png' onmouseover=\"Tip('".libelleTrad('dual')."')\" onmouseout='UnTip()'>";
								$cntIcon++;
							} else {
								$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = 13";
								$resultat =  mysqli_query($connexionDB,$requete);
								$ico = mysqli_fetch_assoc($resultat);
								if($ico!=null) {
									echo "<img src='/iconsFam/building_go.png' onmouseover=\"Tip('".libelleTrad('stage')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								}
							}

							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut in ($attribtroisplusun, $attribMPT, $attribMPTtrois, $attribfuturtrois)";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null || $troisplusun) {
								if($ico['IDAttribut']==$attribtroisplusun || $troisplusun) {
									echo "<img src='/iconsFam/award_star_gold_1.png' onmouseover=\"Tip('".libelleTrad('form31')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								} else if($ico['IDAttribut']==$attribfuturtrois) {
									echo "<img src='/iconsFam/award_star_bronze_2.png' onmouseover=\"Tip('".libelleTrad('insc31')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								} else if($ico['IDAttribut']==$attribMPTtrois) {
									echo "<img src='/iconsFam/award_star_silver_3.png' onmouseover=\"Tip('".libelleTrad('insc31mpt')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								} else if($ico['IDAttribut']==$attribMPT) {
									echo "<img src='/iconsFam/medal_silver_3.png' onmouseover=\"Tip('".libelleTrad('prepmpt')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								} else if($ico['IDAttribut']==$attribPAS) {
									echo "<img src='/iconsFam/arrow_switch.png' onmouseover=\"Tip('".libelleTrad('passerelle')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								}
							}
							if($cntIcon==3) {
								echo "<br>";
								$cntIcon = 0;
							}

							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribResp";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null) {
								echo "<img src='/iconsFam/rosette.png' onmouseover=\"Tip('".libelleTrad('delclasse')."')\" onmouseout='UnTip()'>";
								$cntIcon++;
							}
							if($cntIcon==3) {
								echo "<br>";
								$cntIcon = 0;
							}
							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribNum";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null) {
								echo "<img src='/iconsFam/camera_small.png' onmouseover=\"Tip('".libelleTrad('delnum')."')\" onmouseout='UnTip()'>";
								$cntIcon++;
							}
							if($cntIcon==3) {
								echo "<br>";
								$cntIcon = 0;
							}

							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut in ($attribANG, $attribALL,$attribMAT)";
							$resultat =  mysqli_query($connexionDB,$requete);
							while(($ico = mysqli_fetch_assoc($resultat))!=null) {
								//if($ico!=null) {
								if($ico['IDAttribut']==$attribANG) {
									echo "<img src='/iconsFam/flag-english.png' onmouseover=\"Tip('".libelleTrad('anglais')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								} else if($ico['IDAttribut']==$attribALL) {
									echo "<img src='/iconsFam/flag-german.png' onmouseover=\"Tip('".libelleTrad('allemand')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								} else if($ico['IDAttribut']==$attribMAT) {
									echo "<img src='/iconsFam/calculator.png' onmouseover=\"Tip('".libelleTrad('math')."')\" onmouseout='UnTip()'>";
									$cntIcon++;
								}
								if($cntIcon==3) {
									echo "<br>";
									$cntIcon = 0;
								}
							}

							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribHOR";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null) {
								echo "<img src='/iconsFam/clock_error.png' onmouseover=\"Tip('".libelleTrad('blochoraire')."')\" onmouseout='UnTip()'>";
								$cntIcon++;
							}
							if($cntIcon==3) {
								echo "<br>";
								$cntIcon = 0;
							}

							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribDual";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null) {
								echo "<img src='/iconsFam/cog_delete.png' onmouseover=\"Tip('".libelleTrad('horstaches')."')\" onmouseout='UnTip()'>";
							}

						} else {
							// mode carnet
							$requete = "SELECT * FROM $tableAttribEleves where IDEleve = $idGDN and IDAttribut = $attribCarnet";
			//echo $requete."<br>";
							$resultat =  mysqli_query($connexionDB,$requete);
							$ico = mysqli_fetch_assoc($resultat);
							if($ico!=null) {
								echo "<img src='/iconsFam/tick.png'>";
							} else {
								echo "<img src='/iconsFam/empty.png'>";
							}
						}
						echo "</nobr></td>";
						//writeToHTMLCell($stmt,2,$troisplusun);
						//$cell = getValue($connexion,$data,2);
						echo "<td>$nom</td>";
						//writeToHTMLCell($stmt,3,$troisplusun);
						//$cell = getValue($connexion,$data,3);
						echo "<td><nobr>$prenom</nobr></td>";
						echo "<td align='center'>$ligne[NoVestiaire]</td>";
						echo "<td align='center'>$ligne[NoJeton]</td>";
						//echo "<td align='center'>$ligne[NoBadge]</td>";
						echo "<td align='center'><nobr>$ligne[NumeroCle]</nobr></td>";
						echo "<td>$ligne[Userid]<br>$ligne[NomPC]</td>";
						//echo "<td align='center'>$ligne[noChaise]<br>$ligne[noBanc]</td>";
						//$cell = getValue($connexion,$data,4);
						echo "<td><nobr>".$data['Adresse']."<nobr><br>";
						//$cell = getValue($connexion,$data,5);
						echo $data['NPA']." ";
						//$cell = getValue($connexion,$data,6);
						echo $data['Localite']."</td>";
						echo "<td align='center' >$ligne[NoTel]<br>";
						echo "$ligne[NoMobile]</td>";
						//$cell = getValue($connexion,$data,7);
						//echo "<td><a href='mailto:$cell'>$cell</a></td>";
						echo "<td></td>";
						/*
						if(!empty($emailGroup)) {
							$emailGroup = $emailGroup . ', ';
							$emailGroupOutlook = $emailGroupOutlook . '; ';
						}
						$emailGroup = $emailGroup . "$cell";
						$emailGroupOutlook = $emailGroupOutlook . $data['Email'];
						*/
						$dateNaissance = explode("-", $ligne['DateNaissance']);
						if(count($dateNaissance)==3) {
							$birth = mktime(0,0,0, $dateNaissance[1], $dateNaissance[2], $dateNaissance[0]);
							$dateList = date("d.m.Y",$birth);
							$age = (date("md", date("U", $birth)) > date("md") ? ((date("Y") - $dateNaissance[0]) - 1) : (date("Y") - $dateNaissance[0]));
							echo "<td align='center'>$dateList";
							if($age>=18) {
								echo " <img src='/iconsFam/tag_green.png' onmouseover=\"Tip('".libelleTrad('majeur')." (".$age.")')\" onmouseout='UnTip()'>";
							} else {
								echo " <img src='/iconsFam/tag_yellow.png' onmouseover=\"Tip('".libelleTrad('mineur')." (".$age.")')\" onmouseout='UnTip()'>";
							}
							echo "</td>";
						} else {
							echo "<td align='center'>-</td>";
						}
						//$cell = getValue($connexion,$data,1);
						//echo "<td>$cell</td>";
						//writeToHTMLCell($stmt,1,$troisplusun);
						echo "</tr>\n";

					}
				}
				$cntCell++;
			}
			$cntCell++;
			if($modeHTML) {
				// echo "<tr><td colspan='9'></td><td colspan='3'><a href='mailto:$emailGroup'><img src='/iconsFam/email.png' align='absmiddle'> Email $value</a></td></tr>";
				// echo "<script>document.getElementById('email_eleve_$value').href = \"mailto:$emailGroup\";document.getElementById('email_eleve_$value').href = \"mailto:$emailGroupOutlook\";</script>";
				echo "<tr height='20'></tr>";
			}
		}
	}
}

//mémorisation de la liste d'éleves en session
$_SESSION['listeId'] = $listeIds;

if(!$modeHTML) {
	// générer la feuille excel
	$writer = new PHPExcel_Writer_Excel5($objPHPExcel);
	header('Content-type: application/vnd.ms-excel');
	header("Content-Disposition: attachment;Filename=liste.xls");
	$writer->save('php://output');
} else {
?>
</table></div><br><br>
<div id='corners'><div id='legend'><?=libelleTradUpd('legende')?></div>
<br><table border='0'>
<!-- tr><td colspan><b>".libelleTradUpd('liste_eleve').":</b></td></tr -->
<tr><td width='5'></td><td><?=libelleTradUpd('form31')?></td><td><img src='/iconsFam/award_star_gold_1.png'></td><td width='100'></td><td><?=libelleTradUpd('delclasse')?></td><td><img src='/iconsFam/rosette.png'></td><td width='100'></td><td><?=libelleTradUpd('blochoraire')?></td><td><img src='/iconsFam/clock_error.png'></td><td width='100'></td><td><?=libelleTradUpd('stage')?></td><td><img src='/iconsFam/building_go.png'></td></tr>
<tr><td width='5'></td><td><?=libelleTradUpd('insc31')?></td><td><img src='/iconsFam/award_star_bronze_2.png'></td><td width='100'></td><td><?=libelleTradUpd('appui')?></td><td><img src='/iconsFam/flag-english.png' onmouseover="Tip('<?=libelleTrad('anglais')?>')" onmouseout='UnTip()'> <img src='/iconsFam/flag-german.png' onmouseover="Tip('<?=libelleTrad('allemand')?>')" onmouseout='UnTip()'> <img src='/iconsFam/calculator.png' onmouseover="Tip('<?=libelleTrad('math')?>')" onmouseout='UnTip()'></td><td width='100'></td><td><?=libelleTradUpd('horstaches')?></td><td><img src='/iconsFam/cog_delete.png'></td><td width='100'></td><td><?=libelleTradUpd('delnum')?></td><td><img src='/iconsFam/camera_small.png'></td></tr>
<tr><td width='5'></td><td><?=libelleTradUpd('insc31mpt')?></td><td><img src='/iconsFam/award_star_silver_3.png'></td><td width='100'></td><td><?=libelleTradUpd('prepmpt')?></td><td><img src='/iconsFam/medal_silver_3.png'></td><td width='100'></td><td><?=libelleTradUpd('dual')?></td><td><img src='/iconsFam/building.png'></td></tr>
</table></div>
</div> <!-- post -->
</form>
<?php } // has admin right?>

</div> <!-- page -->
<?php include("../../piedPage.php"); ?>
<!-- end page -->
<?php  // fin modeHTML?>
