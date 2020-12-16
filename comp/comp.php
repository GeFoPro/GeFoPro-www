<?php
include("../appHeader.php");

$IDComp = "";
if(isset($_GET['IDComposant'])) {
	$IDComp = $_GET['IDComposant'];
}

include("entete.php");
?>


<div id="page">
<SCRIPT language="Javascript">
function toggleForm(id1,id2) {
	element1 = document.getElementById(id1);
	element2 = document.getElementById(id2);
	if(element2.style.display=='') {
		element1.style.display = '';
		element2.style.display = 'none';
	} else {
		element1.style.display = 'none';
		element2.style.display = '';
	}
}
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
function checkImage(imageSrc, good, bad) {
	alert(imageSrc);
    var img = new Image();
    img.onload = good;
    img.onerror = bad;
    img.src = imageSrc;
}

function setImgBoitier(url, link) {
	ref = document.getElementById('refBoitier');
	imgRef = document.getElementById('imgBoitier');
	imgRef.src = url;
	if(link) {
		ref.href= url;
	} else {
		ref.href= '#';
	}


}




var displayImg = 1;
var valideImg = 2;
function updateImg() {
	//alert("in update: dsp="+displayImg);
	if(valideImg!=0) {
		boitier = document.getElementById('IDBoitier');
		if(displayImg==1) {
			if(valideImg==2) {
				displayImg=2;
			}
			urlImg = '/<?=$app_section?>/images/boitiers/' + boitier.options[boitier.selectedIndex].text + '.jpg';
		} else {
			displayImg=1;
			urlImg = '/<?=$app_section?>/images/boitiers/' + boitier.options[boitier.selectedIndex].text + '.gif';
		}
		//alert(urlImg);
		setImgBoitier(urlImg,true);
		if(valideImg==2) {
			setTimeout("updateImg()",2000);
		}
	}
}

function resetImg() {
	//alert("in reset");
	if(displayImg==2) {
		valideImg = 0;
		setImgBoitier("/<?=$app_section?>/images/spacer.gif", false);
	} else {
		valideImg = 1;
		//displayImg = 1;
		updateImg();
	}

}
function updateFournisseur(id,field,value) {
	//alert("modifier ligne "+id+", champ "+field+", valeur "+value);
	location.href = 'comp.php?actionFourn=Modifier&IDCommande='+id+'&updateField='+field+'&newValue='+value+'&IDComposant=<?=$IDComp?>';
}
function updateInventaire(id,field,value) {
	//alert("modifier ligne "+id+", champ "+field+", valeur "+value);
	location.href = 'comp.php?actionInv=Modifier&IDInventaire='+id+'&updateField='+field+'&newValue='+value+'&IDComposant=<?=$IDComp?>';
}
function updateEmprunt(id,inventaire,uid) {
	//alert("modifier ligne "+id+", inv "+inventaire+", uid "+uid);
	location.href = 'comp.php?actionEmp=Modifier&IDEmprunt='+id+'&IDInventaire='+inventaire+'&Userid='+uid+'&IDComposant=<?=$IDComp?>';
}
function updateStock(id,field,value) {
	//alert("modifier ligne "+id+", champ "+field+", valeur "+value);
	location.href = 'comp.php?actionStock=Modifier&IDStockage='+id+'&updateField='+field+'&newValue='+value+'&IDComposant=<?=$IDComp?>';
}
function openImage(image) {
	var xhr = new XMLHttpRequest();
	xhr.open('HEAD', image, false);
	xhr.send();
	return (xhr.status != "404");
}
function imageAppear(path, img1, img2, img3) {

	var image = null;
	//alert(path);
	if(img1!='') {
		image = path+img1+".PNG";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image == null && img2!='') {
		image = path+img2+".PNG";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image == null && img3!='') {
		image = path+img3+".PNG";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image!=null) {
		//alert("OK pour "+image);
		//var event = window.event;
		//var posX = event.clientX; //- event.currentTarget.getBoundingClientRect().left
		//var posY = event.clientY; //- event.currentTarget.getBoundingClientRect().top
		//alert("PosX "+e.clientX);
		document.getElementById('compImg').src = image;
		document.getElementById('compImg').style.maxHeight = "150px";
		document.getElementById('compImg').style.width = "auto";
		document.getElementById('compImg').style.maxWidth = "240px";
		//document.getElementById('compImg').style.marginTop = (posY-430)+"px";
		//document.getElementById('compImg').style.marginLeft = (posX-430)+"px";
		document.getElementById('compImgDiv').style.display = "";
		//alert("ok");
	}
}
</SCRIPT>
<?
include($app_section."/userInfo.php");
function addMessage($newMessage) {
  $mess =  $_SESSION['message'];
  if(!empty($mess))
	$mess = $mess . ", ";
  $_SESSION['message'] = $mess . $newMessage;
}

function getMessage() {
	$mess =  "";
	if(isset($_SESSION['message'])) {
		$mess = $_SESSION['message'];
	}
	 $_SESSION['message'] = "";
	 return $mess;
}

/* VARIABLES */

/* actions */
$action = "";
if(isset($_GET['action'])) {
	$action = $_GET['action'];
}
if(isset($_GET['actionFourn'])) {
	$actionFourn = $_GET['actionFourn'];
}
if(isset($_GET['actionFoot'])) {
	$actionFoot = $_GET['actionFoot'];
}
if(isset($_GET['actionStock'])) {
	$actionStock = $_GET['actionStock'];
}
if(isset($_GET['actionInv'])) {
	$actionInv = $_GET['actionInv'];
}
if(isset($_GET['actionEmp'])) {
	$actionEmp = $_GET['actionEmp'];
}
/* Formulaire */
if(isset($_GET['IDGenre'])) {
	$IDGenre = $_GET['IDGenre'];
}
if(isset($_GET['Description'])) {
	$Description = $_GET['Description'];
}
if(isset($_GET['Valeur'])) {
	$Valeur = $_GET['Valeur'];
}
if(isset($_GET['Caracteristiques'])) {
	$Caracteristiques = $_GET['Caracteristiques'];
}
$IDType = "";
if(isset($_GET['IDType'])) {
	$IDType = $_GET['IDType'];
}
if(isset($_GET['IDBoitier'])) {
	$IDBoitier = getDBValueDefault($_GET['IDBoitier'],99);
}
if(isset($_GET['IDFournisseurNew'])) {
	$IDFournisseurNew = $_GET['IDFournisseurNew'];
}
if(isset($_GET['IDFAbriquantNew'])) {
	$IDFAbriquantNew = getDBValue($_GET['IDFAbriquantNew']);
}

if(isset($_GET['prixNew'])) {
	if(empty($_GET['prixNew'])) {
		$prixNew = 0;
	} else {
		$prixNew = getDBValue($_GET['prixNew']);
	}

}
if(isset($_GET['noArticleNew'])) {
	$noArticleNew = $_GET['noArticleNew'];
}
if(isset($_GET['IDCommande'])) {
	$IDCommande = $_GET['IDCommande'];
}
if(isset($_GET['IDSchema'])) {
	$IDSchema = getDBValue($_GET['IDSchema']);
} else {
	$IDSchema = "null";
}
if(isset($_GET['IDFootprint'])) {
	$IDFootprint = $_GET['IDFootprint'];
}
if(isset($_GET['IDGenreFPNew'])) {
	$IDGenreFPNew = $_GET['IDGenreFPNew'];
}
if(isset($_GET['IDReferenceNew'])) {
	$IDReferenceNew = $_GET['IDReferenceNew'];
}
if(isset($_GET['ReferenceNew'])) {
	$ReferenceNew = $_GET['ReferenceNew'];
}
if(isset($_GET['IDInventaire'])) {
	$IDInventaire = $_GET['IDInventaire'];
}
if(isset($_GET['IDEmprunt'])) {
	$IDEmprunt = $_GET['IDEmprunt'];
}
if(isset($_GET['IDStockage'])) {
	$IDStockage = $_GET['IDStockage'];
}
if(isset($_GET['IDStockNew'])) {
	$IDStockNew = $_GET['IDStockNew'];
}
if(isset($_GET['EmplacementNew'])) {
	$EmplacementNew = $_GET['EmplacementNew'];
}
if(isset($_GET['NoInventaireNew'])) {
	$NoInventaireNew = $_GET['NoInventaireNew'];
}
if(isset($_GET['NoSerieNew'])) {
	$NoSerieNew = $_GET['NoSerieNew'];
}
if(isset($_GET['AnneeNew'])) {
	$AnneeNew = $_GET['AnneeNew'];
}
if(isset($_GET['IDTagInvNew'])) {
	$IDTageInvNew = $_GET['IDTagInvNew'];
}
if(isset($_GET['RemarqueInvNew'])) {
	$RemarqueInvNew = $_GET['RemarqueInvNew'];
}

if(isset($_GET['SchemaNew'])) {
	$SchemaNew = $_GET['SchemaNew'];
}
if(isset($_GET['QuantiteNew'])) {
	$Quantite = getDBValue($_GET['QuantiteNew']);
}
if(isset($_GET['QuantiteMinNew'])) {
	$QuantiteMin = getDBValue($_GET['QuantiteMinNew']);
}
if(isset($_GET['QuantiteCommNew'])) {
	$QuantiteComm = getDBValue($_GET['QuantiteCommNew']);
}
if(isset($_GET['IDTagNew'])) {
	$IDTag = getDBValue($_GET['IDTagNew']);
}
$posPrint1 = "1";
if(isset($_GET['print1'])) {
	$posPrint1 = getDBValue($_GET['print1']);
}
$posPrint2 = "3";
if(isset($_GET['print2'])) {
	$posPrint2 = getDBValue($_GET['print2']);
}
$posPrintC1 = "6";
if(isset($_GET['printC1'])) {
	$posPrintC1 = getDBValue($_GET['printC1']);
}
$posPrintC2 = "1";
if(isset($_GET['printC2'])) {
	$posPrintC2 = getDBValue($_GET['printC2']);
}

/* ACTIONS */

/* Action datasheet */
if(isset($_POST['actionData'])) {
	$data = file_get_contents($_FILES['datasheet']['tmp_name']);
	$IDComp = $_POST['IDComposant'];
	$requete = "UPDATE $tableComp set datasheet = '".mysql_real_escape_string($data)."' where IDComposant=$IDComp";
	$resultat = mysql_query($requete);
	if($resultat) {
		addMEssage("Datasheet ajoutée");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible d'ajouter la datasheet</font>");
	}
}
/* Action image */
if(isset($_POST['actionDataImg'])) {
	//$data = file_get_contents($_FILES['imgDownload']['tmp_name']);
	$info = pathinfo($_FILES['imgDownload']['name']);
	$ext = $info['extension'];
	if($ext!="png") {
		addMEssage("<font color=red>Images uniquement au format PNG</font>");
	} else {
		$IDComp = $_POST['IDComposant'];
		$imgGrp = $_POST['imgGrp'];
		$IDGenreImg = $_POST['IDGenre'];
		$IDTypeImg = $_POST['IDType'];
		$nomBoitierImg = $_POST['NomBoitier'];
		$IDUnique = preg_replace('/\W+/', '_', $_POST['IDUnique']);
		//echo $IDComp."/".$imgGrp."/".$IDGenreImg."/".$IDTypeImg."/".$IDBoitierImg."/".$IDUnique;
		if($imgGrp==1) {
			// uniquement pour cet article -> enregistrer l'image avec son identifiant
			$target = "../images/articles/".$IDUnique.".".$ext;
			move_uploaded_file( $_FILES['imgDownload']['tmp_name'], $target);
			addMEssage("Image ajoutée pour cet article uniquement");
		} else if($imgGrp==2) {
			// image pour genre/type
			$target = "../images/articles/".$IDGenreImg."_".$IDTypeImg."_0.".$ext;
			if(!is_file($target)) {
				// pas de fichier 0
				// vérifier si fichiers 1 ou suivants existent
				$cntImg = 1;
				$target1 = "../images/articles/".$IDGenreImg."_".$IDTypeImg."_1.".$ext;
				while(is_file($target1)) {
					$cntImg++;
					$target1 = "../images/articles/".$IDGenreImg."_".$IDTypeImg."_".$cntImg.".".$ext;
				}
				if($cntImg==1) {
					// encore aucune image pour ce genre/type -> fichier 0
					move_uploaded_file( $_FILES['imgDownload']['tmp_name'], $target);
					addMEssage("Première image ajoutée pour cette catégorie");
				} else {
					// au moins un fichier déjà existant -> on ajoute avec un nouveau numéro
					move_uploaded_file( $_FILES['imgDownload']['tmp_name'], $target1);
					addMEssage("Image no ".$cntImg." ajoutée pour cette catégorie");
				}
			} else {
				// fichier 0 existe déjà -> renommer en fichier 1
				$target1 = "../images/articles/".$IDGenreImg."_".$IDTypeImg."_1.".$ext;
				rename($target,$target1);
				// ajouter le nouveau en 2
				$target2 = "../images/articles/".$IDGenreImg."_".$IDTypeImg."_2.".$ext;
				move_uploaded_file( $_FILES['imgDownload']['tmp_name'], $target2);
				addMEssage("2ème image ajoutée pour cette catégorie");
			}
		} else if($imgGrp==3) {
			// image pour un boitier
			$target = "../images/articles/".$nomBoitierImg.".".$ext;
			move_uploaded_file( $_FILES['imgDownload']['tmp_name'], $target);
			addMEssage("Image ajoutée pour ce boitier");
		}
	}
	//$requete = "UPDATE $tableComp set image = '".."' where IDComposant=$IDComp";
	//$resultat = mysql_query($requete);
	//if($resultat) {
	//	addMEssage("Image ajoutée");
	//	mysql_query('COMMIT');
	//} else {
	//	addMEssage("<font color=red>Impossible d'ajouter l'image</font>");
	//}
}
/* Action nouveau schema */
if(!empty($SchemaNew)) {
    $requete = "select max(IDSchema) from $tableRefSchema";
    $resultat =  mysql_query($requete);
    $line = mysql_fetch_row($resultat);
    $IDSchema = $line[0]+1;
    $requete = <<<REQ
INSERT INTO $tableRefSchema
(IDSchema, ReferenceSchema) values
($IDSchema, "$SchemaNew")
REQ;
    $resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Nouveau schéma ajouté");
	} else {
		addMEssage("<font color=red>Impossible d'ajouter le nouveau le schema</font>");
	}
}

/* Action composant */
if(isset($action)) {
  if($action=="Nouveau" || $action=="Copier") {
    $requete = "select max(IDComposant) from $tableComp";
    $resultat =  mysql_query($requete);
    $line = mysql_fetch_row($resultat);
    $IDComp = $line[0]+1;
  }
  if($action=="Ajouter" || $action=="Copier") {
	if($action=="Copier") {
		$Description = "Copie_".$Description;
	}
    $requete = <<<REQ
INSERT INTO $tableComp
(IDComposant, Description, Valeur, Caracteristiques) values
($IDComp, "$Description", "$Valeur", "$Caracteristiques")
REQ;
	//echo $requete;
    $resultat =  mysql_query($requete);
    if($resultat) {
	addMEssage("Composant ajouté");
	mysql_query('COMMIT');
    } else {
	addMEssage("<font color=red>Impossible d'ajouter le composant </font>");
    }
  }
  if($action=="Supprimer") {
    $requete = "DELETE FROM $tableComp where IDComposant=$IDComp";
    $resultat =  mysql_query($requete);
    if($resultat) {
	addMEssage("Composant supprimé");
	mysql_query('COMMIT');
	 $IDComp = "";
    } else {
	addMEssage("<font color=red>Impossible de supprimer le composant</font>");
    }

  }
  if($action=="Modifier") {
    $requete = <<<REQ
UPDATE $tableComp set
Description = "$Description",
Valeur = "$Valeur",
Caracteristiques = "$Caracteristiques",
IDGenre = $IDGenre,
IDType = $IDType,
IDSchema = $IDSchema,
IDBoitier = $IDBoitier,
PosLigne1 = $posPrint1,
PosLigne2 = $posPrint2,
PosLigneC1 = $posPrintC1,
PosLigneC2 = $posPrintC2
where IDComposant=$IDComp
REQ;
  $resultat =  mysql_query($requete);
    if($resultat) {
	addMessage("Composant mis à jour");
	mysql_query('COMMIT');
    } else {
	addMEssage("<font color=red>Impossible de mettre à jour le composant </font>");
    }
  // test pour action supplémentaires
  if(!empty($IDReferenceNew)) {
	// nouveau footprint sur les référence existantes
	$actionFoot="Ajouter";
  }
    if(!empty($ReferenceNew)) {
	// nouveau footprint avec nouvelle référence
	$actionFoot="AjouterNouveau";
  }
  if(!empty($IDStockNew)) {
	$actionStock="Ajouter";
  }
  if(!empty($noArticleNew)) {
	$actionFourn="Ajouter";
  }
  }
  if($action=="SetImage") {
	$imgnom = $_GET['ImgNom'];
	$requete = "update $tableComp set image='".$imgnom."' where IDComposant=".$IDComp;
	//echo $requete;
	$resultat =  mysql_query($requete);
    mysql_query('COMMIT');
	addMEssage("Image associée");
  }
  if($action=="RemImage") {
	$requete = "update $tableComp set image='' where IDComposant=".$IDComp;
	//echo $requete;
	$resultat =  mysql_query($requete);
    mysql_query('COMMIT');
	// tenter d'effacer le fichier propre

	addMEssage("Image désassociée");
  }
  if($action=="RemImageProp") {
	// tenter d'effacer le fichier propre
	$target = "../images/articles/".$_GET['file'].".png";
	unlink($target);
	addMEssage("Image effacée");
  }
}
/* Fin action composant */

if(isset($actionFourn)) {
/* Action fournisseurs */

  if($actionFourn=="Ajouter") {
    /* recherche max */
    //echo "Ajouter Fournisseur";
    $requete = "select max(IDCommande) from $tableCommande";
    $resultat =  mysql_query($requete);
    $line = mysql_fetch_row($resultat);
    $newId = $line[0]+1;
    //echo "$newId";
    $requete = <<<REQ
INSERT INTO $tableCommande
(IDCommande, IDComposant, IDFournisseur, IDFabriquant, noArticle, PrixPce) values
($newId, $IDComp, $IDFournisseurNew, $IDFAbriquantNew, "$noArticleNew", $prixNew)
REQ;
//echo "$requete";
    $resultat =  mysql_query($requete);
    mysql_query('COMMIT');
	addMEssage("Fournisseur ajouté");

  }
  if($actionFourn=="Modifier") {
	$noArticleUpd = $_GET['newValue'];
	$field = $_GET['updateField'];
	$requete = "UPDATE $tableCommande set ".$field."=\"".$noArticleUpd."\" where IDCommande=$IDCommande";
	//echo $requete;
	$resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Fournisseur modifié");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible de supprimer le fournisseur</font>");
	}
  }
  if($actionFourn=="Supprimer") {
	$requete = "DELETE FROM $tableCommande where IDCommande=$IDCommande";
	$resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Fournisseur supprimé");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible de supprimer le fournisseur</font>");
	}

  }
}
/* Fin action fournisseurs */

if(isset($actionFoot)) {
/* Action footprints */
  if($actionFoot=="AjouterNouveau") {
    /* recherche max */
    $requete = "select max(IDReference) from $tableReference";
    $resultat =  mysql_query($requete);
    $line = mysql_fetch_row($resultat);
    $newId = $line[0]+1;
    $requete = <<<REQ
INSERT INTO $tableReference
(IDReference, Reference) values
($newId, "$ReferenceNew")
REQ;
//echo "$requete";
    $resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Nouveau Footprint ajouté");
		$actionFoot="Ajouter";
		$IDReferenceNew = $newId;
	} else {
		addMEssage("<font color=red>Impossible d'ajouter le nouveau le footprint </font>");
	}
  }
  if($actionFoot=="Ajouter") {
    /* recherche max */
    $requete = "select max(IDFootprint) from $tableFootprint";
    $resultat =  mysql_query($requete);
    $line = mysql_fetch_row($resultat);
    $newId = $line[0]+1;
    $requete = <<<REQ
INSERT INTO $tableFootprint
(IDFootprint, IDComposant, IDGenreFP, IDReference) values
($newId, $IDComp, 1, $IDReferenceNew)
REQ;
//echo "$requete";
    $resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Footprint ajouté");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible d'ajouter le footprint </font>");
	}
  }

  if($actionFoot=="Supprimer") {
	$requete = "DELETE FROM $tableFootprint where IDFootprint=$IDFootprint";
	$resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Footprint supprimé");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible de supprimer le footprint</font>");
	}
  }
}
/* Fin action footprints */

if(isset($actionInv)) {
/* Action inventaire */
	if($actionInv=="Ajouter") {
		if($IDTagInvNew == 'null' || empty($IDTagInvNew)) {
			$IDTagSQL = "null";
		} else {
			$IDTagSQL = "0x".$IDTagInvNew;
		}
		$requete = "INSERT INTO inventaire (IDComposant, NoInventaire, NoSerie, Annee, IDTag, RemarqueInv) values (".$IDComp.", \"".$NoInventaireNew."\", \"".$NoSerieNew."\", ".$AnneeNew.", ".$IDTagSQL.", \"".$RemarqueInvNew."\")";
		//echo $requete;
		$resultat =  mysql_query($requete);
		if($resultat) {
			addMEssage("Appareil ajouté");
			mysql_query('COMMIT');
		} else {
			addMEssage("<font color=red>Impossible d'ajouter l'appareil</font>");
		}
	}
	if($actionInv=="Modifier") {
		$valueUpd = $_GET['newValue'];
		$field = $_GET['updateField'];
		if($field=="IDTag" && !empty($valueUpd)) {
			$requete = "UPDATE inventaire set ".$field."=0x".$valueUpd." where IDInventaire=$IDInventaire";
		} else {
			if(empty($valueUpd)) {
				$requete = "UPDATE inventaire set ".$field."=null where IDInventaire=$IDInventaire";
			} else {
				$requete = "UPDATE inventaire set ".$field."=\"".$valueUpd."\" where IDInventaire=$IDInventaire";
			}
		}
		//echo $requete;
		$resultat =  mysql_query($requete);
		if($resultat) {
			addMEssage("Inventaire modifié");
			mysql_query('COMMIT');
		} else {
			addMEssage("<font color=red>Impossible de modifier l'inventaire</font>");
		}
  }
}
/* Fin action inventaire */

/* action emprunt *) */
if(isset($actionEmp)) {
	if($actionEmp=="Modifier") {
		$idInv = $_GET['IDInventaire'];
		$uid = $_GET['Userid'];
		//echo $uid."-".$idInv."-".$IDEmprunt;
		if(!empty($IDEmprunt)) {
			if(empty($uid)) {
				//$requete = "DELETE from emprunt where IDEmprunt=$IDEmprunt";
				$requete = "UPDATE emprunt set DateRetour = \"".date('Y-m-d')."\" where IDEmprunt=$IDEmprunt";
			} else {
				$requete = "UPDATE emprunt set Userid=\"".$uid."\" where IDEmprunt=$IDEmprunt";
			}
		} else {
			$requete = "INSERT into emprunt (Userid, IDInventaire, DateEmprunt) values (\"".$uid."\",".$idInv.",\"".date('Y-m-d')."\")";
		}

		//echo $requete;
		$resultat =  mysql_query($requete);
		if($resultat) {
			addMEssage("Emprunt modifié");
			mysql_query('COMMIT');
		} else {
			addMEssage("<font color=red>Impossible de modifier l'emprunt</font>");
		}
	}
}
/* fin action emprunt */

if(isset($actionStock)) {
/* Action stock */
  if($actionStock=="Ajouter") {
    /* recherche max */
    $requete = "select max(IDStockage) from $tableStockage";
    $resultat =  mysql_query($requete);
    $line = mysql_fetch_row($resultat);
		if($IDTag == 'null' || empty($IDTag)) {
			$IDTagSQL = "null";
		} else {
			$IDTagSQL = "0x".$IDTag;
		}
    $newId = $line[0]+1;
    $requete = <<<REQ
INSERT INTO $tableStockage
(IDStockage, IDComposant, IDStock, Tirroir, Quantite, QuantiteMin, QuantiteComm, IDTag) values
($newId, $IDComp, $IDStockNew, "$EmplacementNew", $Quantite, $QuantiteMin, $QuantiteComm, $IDTagSQL)
REQ;
	//echo $requete;
    $resultat =  mysql_query($requete);
    if($resultat) {
	addMEssage("Emplacement ajouté");
	mysql_query('COMMIT');
    } else {
	addMEssage("<font color=red>Impossible d'ajouter l'emplacement</font>");
    }

  }

  if($actionStock=="Supprimer") {
	$requete = "DELETE FROM $tableStockage where IDStockage=$IDStockage";
	$resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Emplacement supprimé");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible de supprimer l'emplacement</font>");
	}
  }

  if($actionStock=="Modifier") {
	$valueUpd = $_GET['newValue'];
	$field = $_GET['updateField'];
	if($field=="IDTag" && !empty($valueUpd)) {
		$requete = "UPDATE $tableStockage set ".$field."=0x".$valueUpd." where IDStockage=$IDStockage";
	} else {
		if(empty($valueUpd)) {
			$requete = "UPDATE $tableStockage set ".$field."=null where IDStockage=$IDStockage";
		} else {
			$requete = "UPDATE $tableStockage set ".$field."=\"".$valueUpd."\" where IDStockage=$IDStockage";
		}
	}
	//echo $requete;
	$resultat =  mysql_query($requete);
	if($resultat) {
		addMEssage("Stock modifié");
		mysql_query('COMMIT');
	} else {
		addMEssage("<font color=red>Impossible de modifier le stock</font>");
	}
  }
}
/* Fin action stock */

function getFieldToPrint($value, $ligne, $pos) {
	switch ($value) {
		case 0: if($pos==2) return $ligne['Valeur'];
		case 1: return substr($ligne['Description'],0,18);
		case 2: return $ligne['Valeur'];
		case 3: return substr($ligne['Caracteristiques'],0,25);
		case 4: return $ligne['LibelleBoitier'];
		case 5: return $ligne['LibelleGenre'];
		case 6: return $ligne['LibelleType'];
	}
}

/* Affichage du composant */

if (isset($IDComp) && !empty($IDComp)) {
  $requete = <<<REQ
SELECT * FROM $tableComp comp
left outer join $tableGenre ge on comp.IDGenre=ge.IDGenre
left outer join $tableType ty on comp.IDType=ty.IDType
left outer join $tableBoitier bo on comp.IDBoitier=bo.IDBoitier
left outer join $tableRefSchema sch on comp.IDSchema=sch.IDSchema
where IDComposant=$IDComp
REQ;
  $resultat =  mysql_query($requete);
  $ligne = mysql_fetch_assoc($resultat);
	if(isset($IDGenre)) {
		$IDGenreSel = $IDGenre;
		$IDTypeSel = $IDType;
		$IDBoitierSel = $IDBoitier;
		$IDSchemaSel = $IDSchema;
	} else {
		$IDGenreSel = $ligne['IDGenre'];
		$IDTypeSel = $ligne['IDType'];
		$IDBoitierSel = $ligne['IDBoitier'];
		$IDSchemaSel = $ligne['IDSchema'];
	}
	/* selection des lignes d'impression pour étiquettes */
	$selected11 = "";
	$selected21 = "";
	$selected31 = "";
	$selected41 = "";
	$selected51 = "";
	$selected61 = "";
	$selected12 = "";
	$selected22 = "";
	$selected32 = "";
	$selected42 = "";
	$selected52 = "";
	$selected62 = "";
	$LabelCHK = "checked='checked'";
	switch ($ligne['PosLigne1']) {
		case 1: $selected11 = $LabelCHK;
		break;
		case 2: $selected21 = $LabelCHK;
		break;
		case 3: $selected31 = $LabelCHK;
		break;
		case 4: $selected41 = $LabelCHK;
		break;
		case 5: $selected51 = $LabelCHK;
		break;
		case 6: $selected61 = $LabelCHK;
	}
	switch ($ligne['PosLigne2']) {
		case 1: $selected12 = $LabelCHK;
		break;
		case 2: $selected22 = $LabelCHK;
		break;
		case 3: $selected32 = $LabelCHK;
		break;
		case 4: $selected42 = $LabelCHK;
		break;
		case 5: $selected52 = $LabelCHK;
		break;
		case 6: $selected62 = $LabelCHK;
	}
	/* selection des lignes pour commandes */
	$selectedC11 = "";
	$selectedC21 = "";
	$selectedC31 = "";
	$selectedC41 = "";
	$selectedC51 = "";
	$selectedC61 = "";
	$selectedC12 = "";
	$selectedC22 = "";
	$selectedC32 = "";
	$selectedC42 = "";
	$selectedC52 = "";
	$selectedC62 = "";

	switch ($ligne['PosLigneC1']) {
		case 1: $selectedC11 = $LabelCHK;
		break;
		case 2: $selectedC21 = $LabelCHK;
		break;
		case 3: $selectedC31 = $LabelCHK;
		break;
		case 4: $selectedC41 = $LabelCHK;
		break;
		case 5: $selectedC51 = $LabelCHK;
		break;
		case 6: $selectedC61 = $LabelCHK;
	}
	switch ($ligne['PosLigneC2']) {
		case 1: $selectedC12 = $LabelCHK;
		break;
		case 2: $selectedC22 = $LabelCHK;
		break;
		case 3: $selectedC32 = $LabelCHK;
		break;
		case 4: $selectedC42 = $LabelCHK;
		break;
		case 5: $selectedC52 = $LabelCHK;
		break;
		case 6: $selectedC62 = $LabelCHK;
	}
}
?>
<FORM ACTION="<? echo $_SERVER['PHP_SELF'] ?>"  METHOD="GET">
<div class='post'>
<div align='center' width='100%' id='hideMe'><font size="2" color="green" align='center'><i><?=getMessage()?></i></font></div>
<!--br><h2>Détail de l'article</h2-->
<br><div id='corners'>
<div id='legend'>Détail de l'article</div>


<table border='0' align="center" width='100%'>
<tr>
<td colspan="2"></td>
<? if($action!="Nouveau" && !empty($IDComp)) echo "<td colspan='2' align='center'><font size='2'>Commandes</font></td><td colspan='2' align='center'><font size='2'>Etiquettes</font></td>"; ?>
<?
$imgpathURL = "/".$app_section."/images/articles/";
$imgpath = "../images/articles/";
$imgFound = false;
$descFound = false;
$tryImg = $ligne['image'].'.PNG';
if (!empty($ligne['image'])&&is_file($imgpath.$tryImg)) {
	$imgpathURL .= $tryImg;
	$imgFound = true;
}
$tryImg = preg_replace('/\W+/', '_', $ligne['Description']).'.PNG';
if(!$imgFound&&is_file($imgpath.$tryImg)) {
	$imgpathURL .= $tryImg;
	$imgFound = true;
	$descFound = true;
}
$tryImg = $ligne['LibelleBoitier'].'.PNG';
if(!$imgFound&&is_file($imgpath.$tryImg)) {
	$imgpathURL .= $tryImg;
	$imgFound = true;
}
$tryImg = $ligne['IDGenre'].'_'.$ligne['IDType'].'_'.'0.PNG';
if(!$imgFound&&is_file($imgpath.$tryImg)) {
	$imgpathURL .= $tryImg;
	$imgFound = true;
}
if($action=="Nouveau") {
	echo "<td rowspan='4 valign='top'>&nbsp;";
} else {
	echo "<td rowspan='7' valign='top'>&nbsp;";
}
if($imgFound) {
	//echo "<img src='".$imgpathURL."' style='width:auto; max-height:150px; max-width:240px;'>";
	echo "<img src='".$imgpathURL."' id='compImg'>";
	if(!empty($ligne['image'])&&hasStockRight()) {
		echo "<a href='comp.php?action=RemImage&IDComposant=$IDComp'><img src='/iconsFam/cross.png' id='compImg'></a>";
	}
	if($descFound&&hasStockRight()) {
		echo "<a href='comp.php?action=RemImageProp&IDComposant=$IDComp&file=".preg_replace('/\W+/', '_', $ligne['Description'])."'><img src='/iconsFam/cross.png' id='compImg'></a>";
	}
} else {
	// affichage des variantes si existantes
	if(hasStockRight()) {
		$cntImg = 1;
		$tryImg = $ligne['IDGenre'].'_'.$ligne['IDType'].'_'.'1.PNG';
		while(is_file($imgpath.$tryImg)) {
			echo "<a href='comp.php?action=SetImage&ImgNom=".$ligne['IDGenre'].'_'.$ligne['IDType'].'_'.$cntImg."&IDComposant=$IDComp'><img src='".$imgpathURL.$tryImg."' id='compImgList'></a><br>";
			$cntImg++;
			$tryImg = $ligne['IDGenre'].'_'.$ligne['IDType'].'_'.$cntImg.'.PNG';
		}
	}

}
echo "</td></tr>";
$IDUnique = htmlspecialchars($ligne['Description']);
?>
<? if(!empty($IDComp)) { ?>
<tr><td width='100'>Identifiant:</td><td width='490'><input type="texte" name="Description" value="<?= htmlspecialchars($ligne['Description']) ?>" size="30"></td>
<? if($action!="Nouveau") echo "<td align='right' width='40'><input type='radio' name='printC1' value='1' $selectedC11></td><td align='left' width='40'><input type='radio' name='printC2' value='1' $selectedC12></td><td align='right' width='40'><input type='radio' name='print1' value='1' $selected11></td><td align='left' width='40'><input type='radio' name='print2' value='1' $selected12></td>"; ?>
</tr>
<tr><td>Valeur:</td><td><input type="texte" name="Valeur" value="<?= htmlspecialchars($ligne['Valeur']) ?>" size="30"></td>
<? if($action!="Nouveau") echo "<td align='right'><input type='radio' name='printC1' value='2' $selectedC21></td><td align='left'><input type='radio' name='printC2' value='2' $selectedC22></td><td align='right'><input type='radio' name='print1' value='2' $selected21></td><td align='left'><input type='radio' name='print2' value='2' $selected22></td>"; ?>
</tr>
<tr><td>Caractéristiques:</td><td><input type="texte" name="Caracteristiques" value="<?= htmlspecialchars($ligne['Caracteristiques']) ?>" size="60"></td>
<? if($action!="Nouveau") echo "<td align='right'><input type='radio' name='printC1' value='3' $selectedC31></td><td align='left'><input type='radio' name='printC2' value='3' $selectedC32></td><td align='right'><input type='radio' name='print1' value='3' $selected31></td><td align='left'><input type='radio' name='print2' value='3' $selected32></td>"; ?>
</tr>
<?
if($action!="Nouveau") {
	if($app_section=='ELT') {
	  /* Boitier */
	  echo "<tr><td>Boîtier:</td><td ><select id='IDBoitier' name='IDBoitier' onChange='updateImg()'><option></option>";

	  $requete = "SELECT * FROM $tableBoitier order by LibelleBoitier";
	  $resultat =  mysql_query($requete);
	  while ($listeLigne = mysql_fetch_array($resultat)) {
		if($IDBoitierSel==$listeLigne[0]) {
		  echo "<option value='$listeLigne[0]' selected='selected'>";
		  $nomBoitier = $listeLigne[1];
		} else
		  echo "<option value='$listeLigne[0]'>";
		echo "$listeLigne[1] </option>";
	  }
	  echo "</select></td>";
	  //echo "<td colspan='2' rowspan='3'><a id='refBoitier' href='#'><img id='imgBoitier' src='/".$app_section."/images/spacer.gif' onerror='resetImg();' width='100' height='80' ></a><script>updateImg();</script></td>";
	  //echo "<td colspan='2' rowspan='3'><a id='refBoitier' href='#'><img id='imgBoitier' src='images/spacer.gif' width='100' height='80'></a></td>";
	  echo "<td align='right'><input type='radio' name='printC1' value='4' $selectedC41></td><td align='left'><input type='radio' name='printC2' value='4' $selectedC42></td><td align='right'><input type='radio' name='print1' value='4' $selected41></td><td align='left'><input type='radio' name='print2' value='4' $selected42></td></tr>";
	} else {
		echo "<tr><td colspan='6'><input type='hidden' name='IDBoitier' value='".$IDBoitierSel."'></td></tr>";
	}
  /* Genre */
  echo "<tr><td>Genre:</td>";
  if($app_section=='ELT') {
	echo "<td>";
  } else {
	echo "<td>";
  }
  echo "<select name='IDGenre' onChange='submit();'>";
  if(!isset($IDGenreSel) || empty($IDGenreSel)) {
  	echo "<option></option>";
  }
  $requete = "SELECT * FROM $tableGenre order by LibelleGenre";
  $resultat =  mysql_query($requete);
  while ($listeLigne = mysql_fetch_array($resultat)) {
    if($IDGenreSel==$listeLigne[0])
      echo "<option value='$listeLigne[0]' selected='selected'>";
    else
      echo "<option value='$listeLigne[0]'>";
    echo "$listeLigne[1] </option>";
  }
  ?>
  </select></td>
	<td align='right'><input type='radio' name='printC1' value='5' <?=$selectedC51?>></td><td align='left'><input type='radio' name='printC2' value='5' <?=$selectedC52?>></td>
  <td align='right'><input type='radio' name='print1' value='5' <?=$selected51?>></td><td align='left'><input type='radio' name='print2' value='5' <?=$selected52?>></td>
  </tr>
  <tr><td>Type:</td>
  <?
  if($app_section=='ELT') {
	echo "<td>";
  } else {
	echo "<td>";
  }
  echo "<select name='IDType'>";
  if(isset($IDGenreSel) && !empty($IDGenreSel)) {
  	$requete = "SELECT * FROM $tableType where IDGenre=$IDGenreSel order by LibelleType";
  	$resultat =  mysql_query($requete);
  	while ($listeLigne = mysql_fetch_array($resultat)) {
  	  	if($IDTypeSel==$listeLigne[0])
      			echo "<option value='$listeLigne[0]' selected='selected'>";
    		else
      			echo "<option value='$listeLigne[0]'>";
    		echo "$listeLigne[2] </option>";
	}
  }

  echo "</td><td align='right'><input type='radio' name='printC1' value='6' $selectedC61></td><td align='left'><input type='radio' name='printC2' value='6' $selectedC62></td><td align='right'><input type='radio' name='print1' value='6' $selected61></td><td align='left'><input type='radio' name='print2' value='6' $selected62></td></tr>";
}
include("footComp.php");
echo "</table></div>";

if($action!="Nouveau") {
  echo "<br><br><div id='corners'>";
  echo "<div id='legend'>Fournisseurs</div>";
  echo "<table border='0' width='100%' id='hor-minimalist-b'>";
  //echo "<tr><td colspan='6'>&nbsp;</td></tr>";
  echo "<tr><th>Référence fournisseur</th><th>Fournisseur</th><th>Fabriquant</th><th>Prix/pce</th><th align='center'>Dernière commande</th><th align='center'>Quantité</th><th></th></tr>";
  $requete = <<<FOURN
SELECT com.IDCommande, four.LienArticle, four.LienDatasheet, com.NoArticle, com.PrixPce, four.NomFournisseur, fab.NomFabriquant, cext.DateReception, cext.Nombre, com.IDFournisseur FROM $tableCommande com
join $tableFournisseur four on com.IDFournisseur=four.IDFournisseur
left outer join $tableFabriquant fab on com.IDFabriquant=fab.IDFabriquant
left outer join commandeext cext on com.noArticle=cext.numArticle and com.IDFournisseur=cext.IDFournisseur
where IDComposant=$IDComp order by com.IDCommande,DateReception desc
FOURN;
$lastIDCom = 0;
//echo $requete;
  $resultat =  mysql_query($requete);
    while ($fournLigne = mysql_fetch_assoc($resultat)) {
		if($fournLigne['IDCommande']!=$lastIDCom) {
			$lastIDCom = $fournLigne['IDCommande'];
			$link = $fournLigne['LienArticle'];
			$dataSheet = $fournLigne['LienDatasheet'];
			$noArticle= str_replace(" ","",$fournLigne['NoArticle']);
			$noArticle= str_replace(".","",$noArticle);
			$noArticle= str_replace("-","",$noArticle);

			$str = number_format($fournLigne[PrixPce], 2, '.', '');
			//echo $str;
			eval( "\$link = \"$link\";" );
			eval( "\$dataSheet = \"$dataSheet\";" );
			echo "<tr><td>";
			if(hasStockRight()) {
				echo "<input type='text' name='noArticle$fournLigne[IDCommande]' value='$fournLigne[NoArticle]' size='15' onChange='updateFournisseur($fournLigne[IDCommande],\"noArticle\",this.value)'>";
			} else {
				echo $fournLigne[NoArticle];
			}
			echo "</td>";
			echo "<td>$fournLigne[NomFournisseur]</td>";
			echo "<td>$fournLigne[NomFabriquant]</td>";
			echo "<td>";
			if(hasStockRight()) {
				echo "<input type='text' name='prix$fournLigne[IDCommande]' style='text-align: right' value='".$str."' size='4' onChange='updateFournisseur($fournLigne[IDCommande],\"PrixPce\",this.value)'>";
			} else {
				echo $str;
			}
			echo "</td>";
			echo "<td align='center'>";
			if(!empty($fournLigne[DateReception])) {
				echo date("d.m.Y",strtotime($fournLigne[DateReception]));
			}
			echo "</td><td align='center'>$fournLigne[Nombre]</td>";
			echo "<td colspan='2' align='right'>";
			if(hasStockRight()) {
				echo "<a href='comp.php?actionFourn=Supprimer&IDCommande=$fournLigne[IDCommande]&IDComposant=$IDComp'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a>";
			}
			if(!empty($dataSheet)) {
				echo "<a href='$dataSheet' target='_datasheet'><img src='/".$app_section."/images/pdf.jpg' width='16' height='16' align='absmiddle' onmouseover=\"Tip('Lien sur datasheet du fournisseur')\" onmouseout='UnTip()'></a>&nbsp";
			}
			if(hasStockRight() || !empty($dataSheet)) {
				echo "<br>";
			}
			if(!empty($link)) {
				echo "<a href='$link' target='_fournisseur'><img src='/iconsFam/world_link.png' align='absmiddle' onmouseover=\"Tip('Lien sur article du fournisseur')\" onmouseout='UnTip()'></a>&nbsp";
			}
			$libelleComNo = getFieldToPrint($ligne['PosLigneC1'],$ligne,1)." ".getFieldToPrint($ligne['PosLigneC2'],$ligne,2);
			$libelleCom = urlencode($libelleComNo);
			echo "<a href='commande.php?action=Ajouter&IDCommande=$fournLigne[IDCommande]&PrixUnite=$str&Libelle=$libelleCom&IDFournisseur=$fournLigne[IDFournisseur]'><img src='/iconsFam/basket_add.png' align='absmiddle' onmouseover=\"Tip('Commander &laquo;$libelleComNo&raquo;')\" onmouseout='UnTip()'></a></td></tr>";
		}
    }
	if(hasStockRight()) {
		echo "<tr newFourni='1'><td colspan='7' bgColor='#5C5C5C'></td></tr>";
		echo "<tr newFourni='1'><td></td><td></td><td></td><td></td><td></td><td></td><td align='right'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter un fournisseur')\" onmouseout='UnTip()' onclick='toggle(\"newFourni\");' align='absmiddle'></td></tr>";

  ?>
		<tr newFourni='1' style='display:none'><td><input type='text' name='noArticleNew' value='' size='15'></td>
		<td><select name='IDFournisseurNew'>
		<option selected> </option>
  <?
		/* Construction listes fournisseurs et fabriquants */
		$requete = "SELECT * FROM $tableFournisseur  order by NomFournisseur";
		$resultat =  mysql_query($requete);
		while ($listeLigne = mysql_fetch_array($resultat)) {
			echo "<option value='$listeLigne[0]'>";
			echo "$listeLigne[1] </option>";
		}
  ?>
		</select></td>
		<td><select name='IDFAbriquantNew'>
		<option selected> </option>
  <?
		/* Construction listes fournisseurs et fabriquants */
		$requete = "SELECT * FROM $tableFabriquant order by NomFabriquant";
		$resultat =  mysql_query($requete);
		while ($listeLigne = mysql_fetch_array($resultat)) {
			echo "<option value='$listeLigne[0]'>";
			echo "$listeLigne[1] </option>";
		}
  ?>
		</select></td><td><input type='text' name='prixNew' style='text-align: right' size='4' value=''></td><td></td><td></td><td colspan='2' align='right'><input type='submit' name='actionFourn' value='Ajouter'></td></tr>
  <? } ?>
  </table></div>
<? } ?>


<? include("stock.php");
include("inventaire.php");
if($app_section=='ELT') {
	include("altium.php");
}

?>
<? } /* si IDComp pas vide*/ ?>
<!-- /table -->

<input type="hidden" name="IDComposant" value="<?=$IDComp?>">

<!-- br>
<a href="compList.php#comp<?=$IDComp?>">Retour liste composants</a -->
</div> <!-- post -->
</form>
<? include("datasheet.php") ?>
<? include("image.php") ?>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
