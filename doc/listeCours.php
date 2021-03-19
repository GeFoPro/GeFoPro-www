<?php
include("../appHeader.php");

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
/* en-tête */
echo "<FORM id='myForm' ACTION='listeCours.php'  METHOD='POST'>";
echo "<input type='hidden' name='actionCours' value=''>";

echo "<div class='post'>";
echo "<center> <font color='#088A08'></font>";
echo "</center>";


// construction de la liste des cours
echo "<br><table border=0 width='100%'><tr><td><h2>Liste des cours de l'atelier</h2></td><td align='right'></td></tr></table>";
echo "<table id='hor-minimalist-b'>\n";
echo "<tr><th width='300'>Cours</th><th width='50'>Version</th><th width='100'>Date mis à jour</th><th width='200'>Référence</th></tr>";
$requeteH = "SELECT * FROM document where NoIdentite<>'' order by NoIdentite";
//echo $requeteH;
$resultat =  mysqli_query($connexionDB,$requeteH);
while($ligne = mysqli_fetch_assoc($resultat)) {
	echo "<tr onClick='document.location.href=\"docUpload.php?IDDocument=".$ligne['IDDocument']."\"'>";
	echo "<td><b>".$ligne['Libelle']."</b></td>";
	echo "<td align='center'>".$ligne['Version']."</td>";
	echo "<td align='center'>".date('d.m.Y', strtotime($ligne['DateUpload']))."</td>";
	echo "<td>".$ligne['NoIdentite']."</td>";
	echo "</tr>";
}
echo "</table><br>";

?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../piedPage.php"); ?>
