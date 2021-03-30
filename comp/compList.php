<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:88
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: compList.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:79
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");

/* LIMIT */
$limit = 0;
if(isset($_GET['limit'])) {
	$limit = $_GET['limit'];
}

/* requete */
$critere = "";
if(isset($_GET['Rechercher']) && isset($_GET['recherche'])) {
	$critere = $_GET['recherche'];
	$_SESSION['recherche'] = $critere;
} else {
	if(isset($_SESSION['recherche'])) {
		$critere = $_SESSION['recherche'];
	}
}

// genre et type (filtre)
$IDGenre = "";
if(isset($_GET['IDGenre'])) {
	$IDGenre = $_GET['IDGenre'];
	$_SESSION['IDGenreTri'] = $IDGenre;
} else {
	if(isset($_SESSION['IDGenreTri'])) {
		$IDGenre = $_SESSION['IDGenreTri'];
	}
}

$IDType = "";
if(isset($_GET['IDType'])) {
	$IDType = $_GET['IDType'];
	$_SESSION['IDTypeTri'] = $IDType;
} else {
	if(isset($_SESSION['IDTypeTri'])) {
		$IDType = $_SESSION['IDTypeTri'];
	}
}

$IDBoitier = "";
if(isset($_GET['IDBoitier'])) {
	$IDBoitier = $_GET['IDBoitier'];
	$_SESSION['IDBoitierTri'] = $IDBoitier;
} else {
	if(isset($_SESSION['IDBoitierTri'])) {
		$IDBoitier = $_SESSION['IDBoitierTri'];
	}
}

// tri et ordre
if(isset($_GET['tri']) && !empty($_GET['tri'])) {
	$tri = $_GET['tri'];
} else {
	$tri = "Description";
}
if(isset($_GET['sens']) && !empty($_GET['sens'])) {
	$sens = $_GET['sens'];
} else {
  	$sens = "ASC";
}


if($sens=="ASC") {
  $newSens="DESC";
  //$affSens="<b>&darr;</b>";
  $affSens="<img src='/iconsFam/arrow_down.png' align='ABSBOTTOM'>";
} else {
  $newSens="ASC";
  //$affSens="<b>&uarr;</b>";
  $affSens="<img src='/iconsFam/arrow_up.png' align='ABSBOTTOM'>";
}

$affSensDir = "";
$affSensVal = "";
$affSensCar = "";
$affSensGen = "";
$affSensTy = "";
if($tri=="Description")
  $affSensDir = $affSens;
if($tri=="Valeur")
  $affSensVal = $affSens;
if($tri=="Caracteristiques")
  $affSensCar = $affSens;
if($tri=="LibelleGenre")
  $affSensGen = $affSens;
if($tri=="LibelleType")
  $affSensTy = $affSens;

/* Impression */
$countImp = 0;
if(isset($_GET['imp']) && !empty($_GET['imp'])) {
	// recherche si composant cochés
	$requete = "select * from composant where Imprimer <> 0";
	$resultat =  mysqli_query($connexionDB,$requete);
	$countImp = mysqli_num_rows($resultat);
	if($countImp>0) {
		$impTxt = "<input type='checkbox' name='impQ' value='on' checked onClick='resetImp(this.form);'>";
		$impTri=1;
	} else {
		$impTxt = "<input type='checkbox' name='impQ' value='off' onClick='submit();'>";
		$impTri=0;
	}
} else {
	$impTxt = "<a href='compList.php?imp=1'>Imp</a>";
}

if(isset($_GET['Reset']) && !empty($_GET['Reset'])) {
	$requete = "UPDATE $tableComp set Imprimer=0";
	$resultat =  mysqli_query($connexionDB,$requete);
}

if(isset($_GET['Modifier']) && !empty($_GET['Modifier'])) {
	$requete = "UPDATE $tableComp set Imprimer=0";
	$resultat =  mysqli_query($connexionDB,$requete);
	if(isset($_GET['imprimer'])) {
		$imprimer = $_GET['imprimer'];
		for($i=0;$i<sizeof($imprimer);$i++) {
			$requete = "UPDATE $tableComp set Imprimer=1 where IDComposant=$imprimer[$i]";
			$resultat =  mysqli_query($connexionDB,$requete);
		}
	}
}

// fonction truncate
function myTruncate($string, $limit, $break=".", $pad="...") {
	// return with no change if string is shorter than $limit
	if(strlen($string) <= $limit)
		return $string;
	// is $break present between $limit and the end of the string?
	if(false !== ($breakpoint = strpos($string, $break, $limit))) {
		if($breakpoint < strlen($string) - 1) {
			$string = substr($string, 0, $breakpoint) . $pad;
		}
	}
	return $string;
}
// output de la page
$pageOut = "";

/* liste composants */
$requete = "SELECT comp.IDComposant, Imprimer, Caracteristiques, Description, Valeur, LibelleGenre, LibelleType, LibelleBoitier, datasheet, image, comp.IDGenre, comp.IDType FROM $tableComp comp
left outer join $tableCommande com on comp.IDComposant=com.IDComposant
left outer join $tableGenre ge on comp.IDGenre=ge.IDGenre
left outer join $tableType ty on comp.IDType=ty.IDType
left outer join $tableBoitier bo on comp.IDBoitier=bo.IDBoitier";
//left outer join $tableCommande co on comp.IDComposant=co.IDComposant";

$whereClause = "";
if(isset($critere) && !empty($critere)) {
	$whereClause =  " where (description like '%$critere%' OR valeur like '%$critere%' OR caracteristiques like '%$critere%' OR replace(NoArticle, ' ','') like '%".str_replace(' ', '', $critere)."%')";
	//echo $whereClause;
}
if(isset($IDGenre) && !empty($IDGenre)) {
	if(!empty($whereClause)) {
		$whereClause = $whereClause . " AND ";
	} else {
		$whereClause = $whereClause . " WHERE ";
	}
	$whereClause = $whereClause . "comp.IDGenre = " . $IDGenre;
}
if(isset($IDType) && !empty($IDType)) {
	if(!empty($whereClause)) {
		$whereClause = $whereClause . " AND ";
	} else {
		$whereClause = $whereClause . " WHERE ";
	}
	$whereClause = $whereClause . "comp.IDType = " . $IDType;
}
if(isset($IDBoitier) && !empty($IDBoitier)) {
	if(!empty($whereClause)) {
		$whereClause = $whereClause . " AND ";
	} else {
		$whereClause = $whereClause . " WHERE ";
	}
	$whereClause = $whereClause . "comp.IDBoitier = " . $IDBoitier;
}
if(isset($impTri) && !empty($impTri)) {
	if(!empty($whereClause)) {
		$whereClause = $whereClause . " AND ";
	} else {
		$whereClause = $whereClause . " WHERE ";
	}
	$whereClause = $whereClause . "comp.Imprimer = " . $impTri;
}
$requete = $requete . $whereClause . " GROUP BY comp.IDComposant ORDER BY $tri $sens";
$requete .= " LIMIT ".$limit.",50";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);

// construction de la table
$rowcnt = 0;
$lastIDComp="";
$colorLine = "";
while ($ligne = mysqli_fetch_assoc($resultat) ) {
	// présent en stock?
	$requeteStock = "SELECT count(*) FROM $tableStockage stg
	join $tableStock st on stg.IDStock=st.IDStock
	where IDComposant=$ligne[IDComposant]";
	$resultatStock =  mysqli_query($connexionDB,$requeteStock );
	$stockImg = "accept.png";
	if($resultatStock!=null) {
		$resultCount = mysqli_fetch_array($resultatStock);
		if($resultCount[0]==0) {
			$stockImg = "cross.png";
		}
	}
	if($ligne['Imprimer']!=0)
		$checked = "CHECKED";
	else
		$checked = "";
	if($app_section=='ELT') {
		$caracteristiquesTrunc = myTruncate($ligne['Caracteristiques'], 20, ' ');
	} else {
		$caracteristiquesTrunc = myTruncate($ligne['Caracteristiques'], 40, ' ');
	}
	$imgpath = "".$_SESSION['home']."images/articles/";
	$pageOut = $pageOut . "<tr id='comp".$ligne['IDComposant']."' onclick='callDetail(".$ligne['IDComposant'].");' ".$colorLine." onmouseover='imageAppear(\"".$imgpath."\",\"".$ligne['image']."\",\"".preg_replace('/\W+/', '_', $ligne['Description'])."\",\"".$ligne['LibelleBoitier']."\",\"".$ligne['IDGenre']."_".$ligne['IDType']."_0\")' onmouseout='imageDisappear()'><td height='16' width='45' onclick='event.returnValue=false;'><input type='checkbox' name='imprimer[]' value='".$ligne['IDComposant']."' ".$checked." onClick='submitPage(this.form,".$ligne['IDComposant'].");'></td>";
	$pageOut = $pageOut . "<td width='140'>".$ligne['Description']."</td>";
	//$pageOut = $pageOut . "<td ><img src='".$imgsrc."' height='150'></td>";
	if($app_section=='ELT') {
		$pageOut = $pageOut ."<td width='250'>";
	} else {
		$pageOut = $pageOut ."<td width='440'>";
	}
	$pageOut = $pageOut . $ligne['Valeur']."<br><nobr>".$caracteristiquesTrunc."</nobr></td><td width='135'>".$ligne['LibelleGenre']."</td><td width='160'>".$ligne['LibelleType']."</td>";
	if($app_section=='ELT') {
			$pageOut = $pageOut . "<td width='125'>".$ligne['LibelleBoitier']."</td>";
	}
	$pageOut = $pageOut . "<td align='center' width='63'><img src='/iconsFam/".$stockImg."'></td>";
	if($app_section=='ELT') {
			$pageOut = $pageOut . "<td align='center' width='63'>";
			$requete ="select comp.IDComposant from $tableComp comp join $tableFootprint foot on comp.IDComposant=foot.IDComposant where comp.IDComposant=$ligne[IDComposant] AND IDSchema<>''";
			$resultatLigne =  mysqli_query($connexionDB,$requete);
			$row = mysqli_fetch_row($resultatLigne);
			if(!empty($row)) {
				$pageOut = $pageOut . "<img src='/iconsFam/flag_green.png' align='absmiddle'></td>";
				//width='20' height='20' border='0' hspace='0' vspace='0'>";
			} else {
				$pageOut = $pageOut . "<img src='/iconsFam/flag_red.png' align='absmiddle'></td>";
			}
	}
	$pageOut = $pageOut . "<td align='center' width='63'>";
	if(!empty($ligne['datasheet']))  {
		$pageOut = $pageOut . "<a href='lirePDF.php?IDComposant=$ligne[IDComposant]' target='pdf'><img src='/iconsFam/pdf.jpg' width='16' height='16' onmouseover=\"Tip('Datasheet atelier')\" onmouseout='UnTip()' align='absmiddle'></a>&nbsp;<br>";
	} else {
		//$pageOut = $pageOut . "<img src='/iconsFam/empty.png' align='absmiddle'>&nbsp";
	}
		// on essaie avec le fournisseur Distrelec
		$requeteF = <<<FOURN
SELECT * FROM $tableCommande com
join $tableFournisseur four on com.IDFournisseur=four.IDFournisseur
where IDComposant=$ligne[IDComposant] and com.IDFournisseur=1
FOURN;
		$resultatF =  mysqli_query($connexionDB,$requeteF);
		$pdfexists = false;
		if($resultatF!=null) {
			while ($fournLigne = mysqli_fetch_assoc($resultatF)) {
				$link = $fournLigne['LienArticle'];
				$dataSheet = $fournLigne['LienDatasheet'];
				$noArticle= str_replace(" ","",$fournLigne['NoArticle']);
				$noArticle= str_replace(".","",$noArticle);
				$noArticle= str_replace("-","",$noArticle);
				eval( "\$link = \"$link\";" );
				eval( "\$dataSheet = \"$dataSheet\";" );
				if(!empty($link)) {
					//$pageOut = $pageOut . "<a href='$link' target='_fournisseur'><img src='/iconsFam/world_link.png' align='absmiddle' onmouseover=\"Tip('Lien sur article du fournisseur')\" onmouseout='UnTip()'></a>&nbsp";
				}
				if(!empty($dataSheet)) {
					$pageOut = $pageOut . "<a href='$dataSheet' target='_datasheet'><img src='/iconsFam/distrelec.png' align='absmiddle' onmouseover=\"Tip('Datasheet du fournisseur')\" onmouseout='UnTip()' align='absmiddle'></a>&nbsp";
					$pdfexists = true;
				}
			}
		}
		if(!$pdfexists) {
			//$pageOut = $pageOut . "<br><img src='/iconsFam/empty.png'>&nbsp";
		}
	//}
	$pageOut = $pageOut . "</td></tr>\n";
	$lastIDComp = $ligne['IDComposant'];
	$rowcnt++;

}
if($rowcnt==1 && isset($_GET['Rechercher'])) {
	$_SESSION['recherche'] = "";
	$_SESSION['IDGenreTri'] = "";
	$_SESSION['IDTypeTri'] = "";
	$_SESSION['IDBoitierTri'] = "";
	$setLocation = "Location: comp.php?IDComposant=".$lastIDComp;
	header($setLocation);
}
include("entete.php");
?>
<div id="page">
<SCRIPT language="Javascript">
function submitPage(form, idComp) {
	form.Modifier.value="Modifier";
	form.action="compList.php#comp"+idComp;
	form.submit();
}
function resetImp(form) {
	form.Reset.value="Reset";
	form.submit();
}
function callDetail(id) {
	if(document.getElementById('myForm').Modifier.value!="Modifier") {
		document.location.href='comp.php?IDComposant='+id;
	}
}
function resetType() {
	document.getElementById('myForm').IDType.value = "";
}
function openImage(image) {
	var xhr = new XMLHttpRequest();
	xhr.open('HEAD', image, false);
	xhr.send();
	return (xhr.status != "404");
}
function imageAppear(path, img1, img2, img3, img4) {

	var image = null;
	//alert(img1);
	if(img1!='') {
		image = path+img1+".png";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image == null && img2!='') {
		image = path+img2+".png";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image == null && img3!='') {
		image = path+img3+".png";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image == null && img4!='') {
		image = path+img4+".png";
		//alert(image);
		if(!openImage(image)) {
			image = null;
		}
	}
	if(image!=null) {
		//alert("OK pour "+image);
		var event = window.event;
		var posX = event.clientX; //- event.currentTarget.getBoundingClientRect().left
		var posY = event.clientY; //- event.currentTarget.getBoundingClientRect().top
		//alert("PosX "+e.clientX);
		document.getElementById('compImg').src = image;
		document.getElementById('compImg').style.maxHeight = "150px";
		document.getElementById('compImg').style.width = "auto";
		document.getElementById('compImg').style.maxWidth = "240px";
		document.getElementById('compImg').style.marginTop = (posY-430)+"px";
		document.getElementById('compImg').style.marginLeft = (posX-430)+"px";
		document.getElementById('compImgDiv').style.display = "";
	}
}

function imageDisappear() {
    document.getElementById('compImgDiv').style.display = "none";
}

</SCRIPT>
<?php
include("../userInfo.php");
?>
<FORM id="myForm" ACTION="compList.php"  METHOD="GET">
<?php
echo "<div class='post'>";
//if(isset($critere) && !empty($critere)) {
//	echo "<br><h2>Résultat trouvé pour '$critere'</h2><br>";
//} else {
//	echo "<br><h2>Liste des composants</h2><br>";
//}
echo "<div id='compImgDiv' style='display:none'><img src='' id='compImg'></div>";
?>


<table border='0' width='100%'><tr><td align='right'>
<input type='text' name='recherche' value='<?=$critere?>'> <input type='submit' name='Rechercher' value='Rechercher'>
<input type="button" onclick="location.href='comp.php?action=Nouveau'" value="Nouveau" /></td></tr></table>

<br><div id='corners'>
<?php
if(isset($critere) && !empty($critere)) {
	echo "<div id='legend'>Résultat trouvé pour '$critere'</div>";
} else {
	echo "<div id='legend'>Liste des articles</div>";
}
?>
<table id="hor-minimalist-b" border='0'><tr>
<th align="left" width="25"><?=$impTxt?></th>
<th align="left" width="130"><a href="compList.php?tri=Description&sens=<?=$newSens?>">Identifiant<?=$affSensDir?></a></th>
<?php if($app_section=='ELT') { ?>
<th align="left" width="250"><a href="compList.php?tri=Valeur&sens=<?=$newSens?>">Valeur<?=$affSensVal?></a> / Caractérisitiques</th>
<?php } else { ?>
<th align="left" width="430"><a href="compList.php?tri=Valeur&sens=<?=$newSens?>">Valeur<?=$affSensVal?></a> / Caractérisitiques</th>
<?php } ?>
<!-- th align="left" width="20"><a href="compList.php?tri=Caracteristiques&sens=<?=$newSens?>">Caractéristiques<?=$affSensCar?></a></th -->
<!-- th align="left"><a href="compList.php?tri=LibelleGenre&sens=<?=$newSens?>">Genre<?=$affSensGen?></a></th -->
<th align="left" width="120"><select name="IDGenre" onChange='resetType();submit();' style="font-size: 9px;"><option value=''>&lt;Genre&gt;</option>

<?php
$requeteTri = "SELECT * FROM $tableGenre order by LibelleGenre";
$resultatTri =  mysqli_query($connexionDB,$requeteTri);
while ($listeLigne = mysqli_fetch_array($resultatTri)) {
	if($IDGenre==$listeLigne[0])
		echo "<option value='$listeLigne[0]' selected='selected'>";
	else
		echo "<option value='$listeLigne[0]'>";
	echo "$listeLigne[1] </option>";
}
?>
</select></th>
<!-- th align="left"><a href="compList.php?tri=LibelleType&sens=<?=$newSens?>">Type<?=$affSensTy?></a></th -->
<th align="left" width='160'><select name="IDType" onChange='submit();' style="font-size: 9px;"><option value=''>&lt;Type&gt;</option>
<?php

if(isset($IDGenre) && !empty($IDGenre)) {
	$requeteTri = "SELECT * FROM $tableType where IDGenre=$IDGenre order by LibelleType";
	$resultatTri =  mysqli_query($connexionDB,$requeteTri);
	while ($listeLigne = mysqli_fetch_array($resultatTri)) {
		if($IDType==$listeLigne[0])
			echo "<option value='$listeLigne[0]' selected='selected'>";
		else
			echo "<option value='$listeLigne[0]'>";
		echo "$listeLigne[2] </option>";
	}
}
?>
</select></th>
<?php if($app_section=='ELT') { ?>
<th align="left" width="120"><select name="IDBoitier" onChange='submit();' style="font-size: 9px;"><option value=''>&lt;Boitier&gt;</option>
<?php
$requeteTri = "SELECT * FROM $tableBoitier order by LibelleBoitier";
$resultatTri =  mysqli_query($connexionDB,$requeteTri);
while ($listeLigne = mysqli_fetch_array($resultatTri)) {
	if($IDBoitier==$listeLigne[0])
		echo "<option value='$listeLigne[0]' selected='selected'>";
	else
		echo "<option value='$listeLigne[0]'>";
	echo "$listeLigne[1] </option>";
}
?>
</select></th>
<?php } ?>
<th align="center" width='50'>Stock</th>
<?php if($app_section=='ELT') { ?>
	<th align="center" width='50'>Altium</th>
<?php } ?>
<th align="center" width='50'>DataS.</th>
</tr>
</table>
<div style="height:500px;overflow:auto;overflow-x:hidden;width:1048px">
<table id="hor-minimalist-b" border='0'>
<?php if($limit!=0) { ?>
<tr>
<?php if($app_section=='ELT') { ?>
<td colspan="9">
<?php } else { ?>
<td colspan="7">
<?php } ?>
<a href="compList.php?limit=<?=$limit-50?>"><< Précédents</a></td></tr>
<?php } ?>
<?php
echo $pageOut;
?>

<tr><td></td>
<?php if($app_section=='ELT') { ?>
<td colspan="5">
<?php } else { ?>
<td colspan="3">
<?php } ?>
<input type="hidden" name="Modifier" value=""><input type="hidden" name="Reset" value=""></td><td colspan="3" align="right">
<?php if($rowcnt==50) { ?>
<a href="compList.php?limit=<?=$limit+50?>">Suivants >></a>
<?php } ?>
</td></tr>
</table>
</div></div>
<br>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../piedPage.php"); ?>
