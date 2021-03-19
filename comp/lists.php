<?php 
include("../appHeader.php");
include("entete.php");
?>

<div id="page">
<?php
include("../userInfo.php");
?>

<FORM id="myForm" ACTION="listeStock.php"  METHOD="GET">
<div class="post">
<br>
<div id='corners'>
<div id='legend'>Listes par emplacements</div>
<br>
<table border='0'><tr>
<td>Emplacement :</td><td>
  <select name='IDStock'>
  <option selected> </option>
  <?php
  /* Construction listes des emplacements */
  $requete = "SELECT * FROM $tableStock order by Emplacement";
  $resultat =  mysqli_query($connexionDB,$requete);
    while ($listeLigne = mysqli_fetch_array($resultat)) {
	echo "<option value='$listeLigne[0]'>";
	echo "$listeLigne[1] </option>";
    }
  ?>
  </select></td></tr><tr>
<td>Genre :</td><td>
  <select name='IDGenre'>
  <option selected> </option>
  <?php
  /* Construction listes des genre */
  $requete = "SELECT * FROM $tableGenre order by LibelleGenre";
  $resultat =  mysqli_query($connexionDB,$requete);
  while ($listeLigne = mysqli_fetch_array($resultat)) {
	echo "<option value='$listeLigne[0]'>";
	echo "$listeLigne[1] </option>";
  }
  ?>
  </select>
  </td></tr>
  <tr><td></td><td align='left'><input type='submit' name='sub' value='Générer'></td></tr>
  </table>
</div> <!-- post -->
</div>
</form>

</div> <!-- page -->

<?php include("../piedPage.php"); ?>
