<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:02
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: dossiers.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:81
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");

//if(!isset($login)) {
//	// utilisateur par défaut -> invité
//	$login = DBUser;
//	$mdp = DBPwd;
//	$_SESSION['user_nom'] = "Invité";
//	$_SESSION['user_type'] = "Anonyme";
//}
include("checkURL.php");
// catégorie
define ("CatElectro", 56);
define ("CatSoft", 59);
define ("CatInfo", 64);
define ("CatAtelier", 100);


if(!isset($_GET['cat']) || empty($_GET['cat'])) {
	if(!isset($_SESSION['cat']) || empty($_SESSION['cat'])) {
		$cat=CatAtelier;
	} else {
		$cat = $_SESSION['cat'];
	}
} else {
	$cat = $_GET['cat'];
	$_SESSION['cat'] = $cat;
}

// tri
$niveau1 = "";
if(isset($_GET['niveau1'])) {
	$niveau1 = $_GET['niveau1'];
}
$niveau2 = "";
if(isset($_GET['niveau2'])) {
	$niveau2 = $_GET['niveau2'];
}

if(isset($_GET['newDossier']) && !empty($_GET['newDossier'])) {
	if(isset($_GET['IDParent'])) {
		addDossier($_GET['newDossier'],$_GET['IDParent']);
	}

}

if(isset($_GET['deleteDossier']) && !empty($_GET['deleteDossier'])) {
	if(isset($_GET['IDDossier'])) {
		removeDossier($_GET['IDDossier']);
	}
}

if(isset($_GET['deleteDocument']) && !empty($_GET['deleteDocument'])) {
	if(isset($_GET['IDDocument'])) {
		removeDocument($_GET['IDDocument']);
	}
}

$message = '';

if(isset($_POST['ajouter']) && !empty($_POST['ajouter'])) {
	$action = $_POST['ajouter'];
	// nouvel ID
	$requete = "select max(IDDocument) from document";
	$resultat =  mysqli_query($connexionDB,$requete);
	$line = mysqli_fetch_row($resultat);
	$newIDDocument = $line[0]+1;

	$statLib = 0;
	$statAut = 0;
	$date = "0000-00-00";
	/* Action upload */
	if($action == "Ajouter Cours") {
		$data = file_get_contents($_FILES['cours']['tmp_name']);
		if(!empty($data)) {
			$_POST['Taille'] = $_FILES['cours']['size']/1000;
			//echo $_FILES['cours']['type'];
			$_POST['LienLibelle'] = "lireCours.php?IDDocument=".$newIDDocument;
			$statLib = 1;
			$date = date("Y-m-d");
		}
	} else {
		$data = "";
	}

	if( empty($_POST['LienLibelle']) || empty($_POST['Libelle']) || empty($_POST['IDType']) || empty($_POST['Auteur'])) {
		$message = "Erreur d'ajout, champ(s) obligatoire(s) manqant(s)";
	} else {

		if(!$statLib && http_check_url($_POST['LienLibelle'],5)) {
			$statLib = 1;
		}
		if(!empty($_POST['LienAuteur']) && http_check_url($_POST['LienAuteur'],5)) {
			$statAut = 1;
		}
		if(empty($_POST['Taille'])) {
			$_POST['Taille'] = "0";
		}

		$requete = "INSERT INTO document (IDDocument,Libelle,LienLibelle,IDType,Taille,Auteur,LienAuteur,IDDossier,StatusLienLibelle,StatusLienAuteur,document,DateUpload,Version,NoIdentite) ";
		$requete .= " values (".$newIDDocument.",'".$_POST['Libelle']."','".$_POST['LienLibelle']."',".$_POST['IDType'].",".$_POST['Taille'].",'".$_POST['Auteur']."','".$_POST['LienAuteur']."',".$_POST['IDDossier'].",".$statLib.",".$statAut.",'".mysqli_real_escape_string($connexionDB,$data)."','".$date."','".$_POST['Version']."','".$_POST['NoIdentite']."')";
		//echo $requete;
		$resultat =  mysqli_query($connexionDB,$requete);
		if($resultat) {
			mysqli_query($connexionDB,"COMMIT");
	    } else {
	    	$message = "Erreur de modification de document avec ";
	    	$message .= $requete;
		}
	}
}

if(isset($_POST['modifier']) && !empty($_POST['modifier'])) {
	$action = $_POST['modifier'];
	$statLib = 0;
	$statAut = 0;
	$date = 0;
	/* Action upload */
	if($action == "Modifier Cours") {
		if(!empty($_FILES['cours']['tmp_name'])) {
			$data = file_get_contents($_FILES['cours']['tmp_name']);
			if(!empty($data)) {
				$_POST['Taille'] = $_FILES['cours']['size']/1000;
				//echo $_FILES['cours']['type'];
				$_POST['LienLibelle'] = "lireCours.php?IDDocument=".$_POST['IDDocument'];
				$statLib = 1;
				$date = date("Y-m-d");
			}
		}
	} else {
		$data = "";
	}

	if( empty($_POST['LienLibelle']) || empty($_POST['Libelle']) || empty($_POST['IDType']) || empty($_POST['Auteur'])) {
		$message = "Erreur de modification, champ(s) obligatoire(s) manqant(s)";
	} else {

		if(!$statLib && http_check_url($_POST['LienLibelle'],5)) {
			$statLib = 1;
		}
		if(!empty($_POST['LienAuteur']) && http_check_url($_POST['LienAuteur'],5)) {
			$statAut = 1;
		}
		if(empty($_POST['Taille'])) {
			$_POST['Taille'] = "0";
		}
		$requete = "UPDATE document set ";
		$requete .= "Libelle='".addslashes($_POST['Libelle'])."', ";
		$requete .= "LienLibelle='".$_POST['LienLibelle']."', ";
		$requete .= "IDType=".$_POST['IDType'].", ";
		$requete .= "Taille=".$_POST['Taille'].", ";
		$requete .= "Auteur='".$_POST['Auteur']."', ";
		$requete .= "LienAuteur='".$_POST['LienAuteur']."', ";
		$requete .= "Version='".$_POST['Version']."', ";
		$requete .= "NoIdentite='".$_POST['NoIdentite']."', ";
		$requete .= "StatusLienAuteur=".$statAut." ";
		if(!empty($data)) {
			$requete .= ",StatusLienLibelle=".$statLib.", ";
			$requete .= "document='".mysqli_real_escape_string($connexionDB,$data)."', ";
			$requete .= "DateUpload='".$date."' ";
		}
		$requete .= " where IDDocument=".$_POST['IDDocument'];
		$resultat =  mysqli_query($connexionDB,$requete);
		if($resultat) {
			mysqli_query($connexionDB,"COMMIT");
	    } else {
	    	$message = "Erreur de modification de document avec ";
	    	$message .= $requete;
		}
	}
}

function addDossier ($nom, $idParent) {
	global $connexionDB;
	$requete = "select max(IDDossier) from dossier";
	$resultat =  mysqli_query($connexionDB,$requete);
	if(!empty($resultat)&&mysqli_num_rows($resultat)>0) {
		$line = mysqli_fetch_row($resultat);
		$IDNewDossier = $line[0]+1;
	} else {
		$IDNewDossier = 1;
	}
	$requete = "INSERT INTO dossier (IDDossier, Nom, IDParent) values (".$IDNewDossier.", \"".$nom."\", ".$idParent.")";
	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	if($resultat) {
		mysqli_query($connexionDB,"COMMIT");
	} else {
		$message = "Erreur d'ajout de dossier avec ";
		$message .= $requete;
	}
}

function removeDossier($IDDossier) {
	global $connexionDB;
	$requete = "DELETE FROM dossier where IDDossier = ".$IDDossier;
    $resultat =  mysqli_query($connexionDB,$requete);
}

function removeDocument($IDDocument) {
	global $connexionDB;
	$requete = "DELETE FROM document where IDDocument = ".$IDDocument;
    $resultat =  mysqli_query($connexionDB,$requete);
}


// $parent is the parent of the children we want to see
// $level is increased when we go deeper into the tree,
//        used to display a nice indented tree
function display_children($parent, $level, $cat, $filtre) {
	global $connexionDB;
	$arrayBgColor = array(0 => "#EEEEEE","#DDDDDD","#CCCCCC","#BBBBBB","#AAAAAA");
   // retrieve all children of $parent
   $query = 'SELECT * FROM dossier '.'WHERE IDParent="'.$parent.'"';
   if(!empty($filtre)) {
	$query .= ' AND IDDossier='.$filtre;
   }
   $query .= ' ORDER BY Nom';

   $result = mysqli_query($connexionDB,$query);
   $returnValue = false;
	 $child = false;
   // display each child
   if(!empty($result)&&mysqli_num_rows($result)>0) {
	   while ($row = mysqli_fetch_array($result) ) {

		   // indent and display the title of this child
		   echo "<div blockH$parent='$row[IDDossier]' id='blockH$row[IDDossier]' style='display:none;border-radius: 5px;background-color: ".$arrayBgColor[$level]."; padding:5px 5px 5px 5px;margin:5px 5px 5px 5px;'>";
		   echo "<a href='#' onClick='toggle2(\"blockH$row[IDDossier]\",\"block$row[IDDossier]\");' class='title'><img src='/iconsFam/bullet_arrow_down.png'> ";
		   //echo "<span style='margin:".($level*10)."; font-size:".(14-($level*2))."px;'>".$row['Nom']."</span></a><p></p></div>";
		   echo "<span style='margin: 5px 0px 0px 0px;display:inline-block; font-size:".(14-($level*1.2))."px;'>".$row['Nom']."</span></a></div>";

		   echo "<div block$parent='$row[IDDossier]' id='block$row[IDDossier]' style='border-radius: 5px;background-color: ".$arrayBgColor[$level]."; padding:5px 5px 5px 5px;margin:5px 5px 5px 5px;'>";
		   echo "<a href='#' onClick='toggle2(\"block$row[IDDossier]\",\"blockH$row[IDDossier]\");' class='title'><img src='/iconsFam/bullet_arrow_up.png'> ";
		   //echo "<span style='margin:".($level*10)."; font-size:".(14-($level*2))."px;'>".$row['Nom']."</span></a> ";
		   echo "<span style='margin: 5px 0px 0px 0px;display:inline-block; font-size:".(14-($level*1.2))."px;'>".$row['Nom']."</span></a> ";
		   if(hasAdminRigth()) {
			echo "<img src='/iconsFam/folder_add.png' align='absmiddle' onmouseover=\"Tip('Ajouter sous-dossier')\" onmouseout='UnTip()' onClick='toggle(\"newDossier$row[IDDossier]\")'>";
			if($cat==CatElectro) {
				echo "&nbsp;<a href='document.php?IDDossier=".$row['IDDossier']."&nomDossier=".$row['Nom']."'><img src='/iconsFam/page_add.png' align='absmiddle' onmouseover=\"Tip('Ajouter document')\" onmouseout='UnTip()' ></a>";
			}
		   if($cat==CatAtelier) {
				echo "&nbsp;<a href='docUpload.php?IDDossier=".$row['IDDossier']."&nomDossier=".$row['Nom']."'><img src='/iconsFam/page_add.png' align='absmiddle' onmouseover=\"Tip('Ajouter document')\" onmouseout='UnTip()' ></a>";
			}
			echo "&nbsp;<a href='dossiers.php?IDDossier=".$row['IDDossier']."&deleteDossier=true'><img id='delDossier$row[IDDossier]' src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer dossier')\" onmouseout='UnTip()'></a>";
			echo "<div id='newDossier$row[IDDossier]' style='margin:0px 0px 0px 20px'><input type='texte' name='newDossierTxt$row[IDDossier]' id='name$row[IDDossier]' value=''><input type='button' name='AjouterDossier' value='Ajouter sous ".$row['Nom']."' onclick='submitNewDossier(\"$row[IDDossier]\")'></div><script>document.getElementById(\"newDossier$row[IDDossier]\").style.display = \"none\"</script>";
		   }
		   //echo "<p style='line-height:10px'>\n";
		   echo "<br>";
		   $existDoc = false;
		   //echo "cat:".$cat;
		   if($cat==CatElectro || $cat==CatAtelier) {
			// afficher les documents
			   $resultDoc = mysqli_query($connexionDB,'SELECT * FROM document, type '.
					  'WHERE IDDossier="'.$row['IDDossier'].'" AND document.IDType=type.IDType ORDER BY Libelle;');
			if(!empty($resultDoc)) {
				while ($doc = mysqli_fetch_array($resultDoc)) {
					//echo "<span style='margin: ".(($level+1)*5)."px'>";
					echo "<span style='margin: 5px 0px 0px 20px;display:inline-block;'>";
					if($doc['StatusLienLibelle']) {
						echo "<a href='$doc[LienLibelle]' target='_extern'>$doc[Libelle]</a>";
					} else {
						//echo $doc[Libelle]." <img src='/iconsFam/link_break.png'>";
						echo "<a href='$doc[LienLibelle]' target='_extern'>$doc[Libelle]</a> <img src='/iconsFam/link_break.png'>";
					}
					if(hasAdminRigth()&&$cat==CatAtelier) {
						if(!empty($doc['NoIdentite'])) {
							echo " (".$doc['NoIdentite'].")";
						}
					}
					echo " - ".$doc['Description'];
					if($doc['Taille']!=0) {
						echo ", $doc[Taille]kB";
					}
					echo " - Auteur: ";
					if(!empty($doc['LienAuteur'])) {
						if($doc['StatusLienAuteur']) {
							echo "<a href='$doc[LienAuteur]' target='_extern'>$doc[Auteur]</a></span>";
						} else {
							//echo "$doc[Auteur] <img src='/iconsFam/link_break.png'></span><br>\n";
							echo "<a href='$doc[LienAuteur]' target='_extern'>$doc[Auteur]</a> <img src='/iconsFam/link_break.png'>";
						}
					} else {
						echo $doc['Auteur'];
						if("0000-00-00"!=$doc['DateUpload']) {
							echo " - version: ".date('d.m.Y', strtotime($doc['DateUpload']));
						}
					}
					if(hasAdminRigth()) {
						if($cat==CatElectro) {
							echo "&nbsp;<a href='document.php?IDDocument=".$doc['IDDocument']."'><img src='/iconsFam/page_edit.png' align='absmiddle' onmouseover=\"Tip('Modifier le document')\" onmouseout='UnTip()'></a>";
						}
						if($cat==CatAtelier) {
							echo "&nbsp;<a href='docUpload.php?IDDocument=".$doc['IDDocument']."'><img src='/iconsFam/page_edit.png' align='absmiddle' onmouseover=\"Tip('Modifier le document')\" onmouseout='UnTip()'></a>";
						}
						echo "&nbsp;<a href='dossiers.php?IDDocument=".$doc['IDDocument']."&deleteDocument=true'><img src='/iconsFam/cross.png' align='absmiddle' onmouseover=\"Tip('Supprimer le document')\" onmouseout='UnTip()'></a>";
					}
					echo "</span><br>\n";
					//echo "<br>\n";
					$returnValue = true;
					$existDoc = true;
				}
			}
		} else if($cat==CatSoft) {
			// afficher les soft
			   $resultSoft = mysqli_query($connexionDB,'SELECT * FROM software '.
					  'WHERE IDDossier="'.$row['IDDossier'].'" ORDER BY Nom;');
			while ($soft = mysqli_fetch_array($resultSoft)) {
				//echo "<span style='margin:".(($level+1)*10)."'>";
				echo "<span style='margin: 20px 0px 0px 20px;display:inline-block;'>";
				if($soft['StatusLienSite']) {
					echo "<a href='$soft[LienSite]' target='_extern'><b>$soft[Nom]</b></a> - <font color='#5C5C5C'>$soft[LienSite]</font> -";
				} else {
					echo "<b>$soft[Nom]</b> <img src='/iconsFam/link_break.png'> - <font color='#5C5C5C'>$soft[LienSite]</font> -";
				}
				if($soft['StatusLienDirect']) {
					echo "<a href='$soft[LienDirect]'>Télécharger</a>";
				} else {
					echo "Téléchargement indisponible <img src='/iconsFam/link_break.png'>";
				}
				echo "</span><p style='margin: 0px 0px 0px 20px;display:inline-block;'>$soft[Description]";
				echo"</p>";
				$returnValue = true;
				$existDoc = true;
			}
		}
		//echo "</p>";

		   // call this function again to display this
		   // child's children
		   $child = display_children($row['IDDossier'], $level+1, $cat,'');
		   echo "</div>\n";

		   //echo "Debug - ".$row['Nom'].": ".$existDoc."/".$child."<br>\n";
		   if(!$existDoc && !$child && !hasAdminRigth()) {
				// aucuns documents, on cache le bloc concerné
				//echo "<script>div = document.getElementById('block$row[IDDossier]');div.style.display='none';</script>\n";
				echo "<script>div = document.getElementById('block$parent'); rem = document.getElementById('block$row[IDDossier]');div.removeChild(rem);</script>\n";

		   }
		   if ($existDoc || $child) {
				// cacher les options de modification du contenu en dessous du dossier si existe
			if(hasAdminRigth()) {
					echo "<script>divOpt = document.getElementById('delDossier$row[IDDossier]');divOpt.style.display='none'</script>\n";
			}
		   }

	   }
   }
   return $returnValue||$child;
}

function getOptionsCat($parent,$niveau) {
	global $connexionDB;
	$result = mysqli_query($connexionDB,'SELECT * FROM dossier '.
                          'WHERE IDParent="'.$parent.'" ORDER BY Nom;');
	$options = "";
	if(!empty($result) ) {
		while ($row = mysqli_fetch_array($result)) {
			$options .= "<option value='".$row['IDDossier']."'";
			if($row['IDDossier']==$niveau) {
				$options .= " selected";
			}
			$options .= ">".$row['Nom']."</option>\n";
		}
	}
	return $options;

}
include("entete.php");

?>

<div id="page">
<script>
function toggle2(hide, show) {
	toHide = document.getElementById(hide);
	toShow = document.getElementById(show);

	toHide.style.display = 'none';
	toShow.style.display = '';

}

function toggle(id) {
	elem = document.getElementById(id);
	if(elem.style.display=='none') {
		elem.style.display='';
	} else {
		elem.style.display='none';
	}
}
function submitNewDossier(IDParent) {
	nameDos = document.getElementById('name'+IDParent);
	document.location.href='dossiers.php?IDParent='+IDParent+'&newDossier='+nameDos.value;
}

</script>
<?php include("../userInfo.php"); ?>
<center><font size="2" color="red"><i><?=$message?></i></font></center>
<FORM id="myForm" ACTION="dossiers.php"  METHOD="GET">
<input type="hidden" name="cat" value="<?=$cat?>">
<div class="post">
	<br>Catégorie: <select name="niveau1" onChange='submit();'><option value="">Tous</option><?=getOptionsCat($cat,$niveau1)?></select>
	<?php
	$root = $cat;
	$filtre = '';
	if(!empty($niveau1)) {
		echo "<select name='niveau2' onChange='submit();'><option value=''>Tous</option>".getOptionsCat($niveau1,$niveau2)."</select>";
		//$root = $niveau1;
		$filtre = $niveau1;
	} else {
		$niveau2 = "";
	}
	if(!empty($niveau2)) {
		$root = $niveau1;
		$filtre = $niveau2;
	}
	if(hasAdminRigth()) {
       	echo "<img src='/iconsFam/folder_add.png' align='absmiddle' onmouseover=\"Tip('Ajouter catégorie')\" onmouseout='UnTip()' onClick='toggle(\"newDossier$cat\")'>";
       	echo "<div style='background-color: #EEEEEE; padding:10px 5px 10px 10px;' id='newDossier$cat'><input type='texte' name='newDossier' id='name$cat' value=''><input type='button' name='AjouterDossier' value='Ajouter catégorie' onclick='submitNewDossier(\"$cat\")'></div><script>document.getElementById(\"newDossier$cat\").style.display = \"none\"</script>";
	}
	 ?>
	<br><br>
	<div id='block<?=$root?>'>
		<?php display_children($root,0,$cat, $filtre); ?>
	</div>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../piedPage.php"); ?>
