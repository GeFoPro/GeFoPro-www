<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:01
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: docUpload.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:79
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../appHeader.php");
$IDDocument = "";
if(isset($_GET['IDDocument'])) {
	$IDDocument = $_GET['IDDocument'];
}
$IDDossier = "";
if(isset($_GET['IDDossier'])) {
	$IDDossier = $_GET['IDDossier'];
}
$nomDossier = "";
if(isset($_GET['nomDossier'])) {
	$nomDossier = $_GET['nomDossier'];
}


// catégorie
define ("CatElectro", 56);
define ("CatSoft", 59);
define ("CatInfo", 64);
define ("CatAtelier", 100);
include("entete.php");
?>

<div id="page">
<?php
include("../userInfo.php");
?>

<?php

/* détail */
if(!empty($IDDocument)) {
	$requete = "SELECT * FROM document doc";
	$requete .= " join dossier dos on doc.IDDossier = dos.IDDossier";
	$requete .= " where IDDocument = ".$IDDocument;
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
} else {
	$ligne['IDDossier'] = $IDDossier;
	$ligne['Nom'] = $nomDossier;
	$ligne['Libelle'] = "";
	$ligne['IDType'] = "";
	$ligne['Taille'] = "";
	$ligne['Auteur'] = "";
	$ligne['LienAuteur'] = "";

	$ligne['Version'] = "";
	$ligne['NoIdentite'] = "";
}
//echo $requete;
?>


<div class="post">
<FORM id="myForm" ACTION="dossiers.php"  METHOD="POST" enctype="multipart/form-data">
<h2>Détail du cours</h2>
<table border="0">
<tr><td>Dossier</td><td><input type='text' name='NomRD' value="<?=$ligne['Nom']?>" readonly id='readOnly' size='40'><input type='hidden' name='IDDossier' value='<?=$ligne['IDDossier']?>'></input></td></tr>
<tr><td>Description</td><td><input type='text' name='Libelle' value="<?=$ligne['Libelle']?>" size='40'></input> * </td></tr>
<tr><td>Version</td><td><input type='text' name='Version' value='<?=$ligne['Version']?>' size='4'></input></td></tr>
<tr><td>Identité</td><td><input type='text' name='NoIdentite' value='<?=$ligne['NoIdentite']?>' size='40'></input></td></tr>
<tr><td>Document</td><td><input type="hidden" name="MAX_FILE_SIZE" value="5000000"><input type="file" name="cours">
<?php if(!empty($ligne['document']))  {
	echo " (<a href='lireCours.php?IDDocument=".$IDDocument."' target='pdf'>document actuel</a>)";
} else {
	echo " *";
} ?>
</td></tr>
<tr><td>Type de document</td><td>
<select name='IDType'>
  <option selected> </option>
  <?php
  /* Construction liste type document */
  $requete = "SELECT * FROM type";
  $resultat =  mysqli_query($connexionDB,$requete);
  while ($listeLigne = mysqli_fetch_array($resultat)) {
	if($ligne['IDType']==$listeLigne[0]) {
		echo "<option value='$listeLigne[0]' selected='selected'>";
	} else {
		echo "<option value='$listeLigne[0]'>";
	}
	echo "$listeLigne[1] </option>";
    }
  ?>
  </select> *
</td></tr>
<tr><td>Taille document</td><td><input type='text' name='Taille' value='<?=$ligne['Taille']?>' readonly id='readOnly'></input> kBytes</td></tr>
<tr><td>Auteur</td><td><input type='text' name='Auteur' value='<?=$ligne['Auteur']?>' size='40'></input> *</td></tr>
<tr><td>Lien sur l'auteur</td><td><input type='text' name='LienAuteur' value='<?=$ligne['LienAuteur']?>' size='100'></input></td></tr>

<tr><td></td><td>
<?php
if(!empty($IDDocument)) {
	echo "<input type='hidden' name='IDDocument' value='".$IDDocument."'><input type='hidden' name='LienLibelle' value='".$ligne['LienLibelle']."'><input type='submit' name='modifier' value='Modifier Cours'>";
} else {
	echo "<input type='submit' name='ajouter' value='Ajouter Cours'>";
}
?>
</td></tr>
</table>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../piedPage.php"); ?>
