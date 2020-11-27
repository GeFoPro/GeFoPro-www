<?php 
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
<?
include($app_section."/userInfo.php");
?>

<FORM id="myForm" ACTION="dossiers.php"  METHOD="POST">

<?  

/* détail */
if(!empty($IDDocument)) {
	$requete = "SELECT * FROM Document doc"; 
	$requete .= " join Dossier dos on doc.IDDossier = dos.IDDossier";	 
	$requete .= " where IDDocument = ".$IDDocument; 
	$resultat =  mysql_query($requete);
	$ligne = mysql_fetch_assoc($resultat);
} else {
	$ligne['IDDossier'] = $IDDossier;
	$ligne['Nom'] = $nomDossier;	
	$ligne['Libelle'] = "";
	$ligne['LienLibelle'] = "";
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
<br><h2>Détail du document</h2>
<table border="0">
<tr><td>Dossier</td><td><input type='text' name='NomRD' value='<?=$ligne['Nom']?>' readonly id='readOnly' size='40'><input type='hidden' name='IDDossier' value='<?=$ligne['IDDossier']?>'></input></td></tr>
<tr><td>Description</td><td><input type='text' name='Libelle' value='<?=$ligne['Libelle']?>' size='40'></input> * </td></tr>
<tr><td>Lien</td><td><input type='text' name='LienLibelle' value='<?=$ligne['LienLibelle']?>' size='100'></input> * </td></tr>
<tr><td>Type de document</td><td>
<select name='IDType'>
  <option selected> </option>
  <?
  /* Construction liste type document */
  $requete = "SELECT * FROM type";
  $resultat =  mysql_query($requete);
  while ($listeLigne = mysql_fetch_array($resultat)) {
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
<tr><td>Taille document</td><td><input type='text' name='Taille' value='<?=$ligne['Taille']?>' size='10'></input> kBytes</td></tr>
<tr><td>Auteur</td><td><input type='text' name='Auteur' value='<?=$ligne['Auteur']?>' size='40'></input> *</td></tr>
<tr><td>Lien sur l'auteur</td><td><input type='text' name='LienAuteur' value='<?=$ligne['LienAuteur']?>' size='100'></input></td></tr>

<tr><td></td><td>
<?php
if(!empty($IDDocument)) {
	echo "<input type='hidden' name='IDDocument' value='".$IDDocument."'><input type='submit' name='modifier' value='Modifier'>";
} else {
	echo "<input type='submit' name='ajouter' value='Ajouter'>";
}
?>
</td></tr>
</table>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>