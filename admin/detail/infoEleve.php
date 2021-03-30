<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:62
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: infoEleve.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:54
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

include("../../appHeader.php");

if(hasAdminRigth()) {
	if(isset($_GET['nom'])) {
		$nom = $_GET['nom'];
		$prenom = $_GET['prenom'];
		$IDEleve = $_GET['idEleve'];
	} else if(isset($_POST['nom'])) {
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$IDEleve = $_POST['IDEleve'];
	}
} else {
	// tentative de recherche par userid
	$requete = "select * from eleves el join elevesbk bk on el.IDGDN=bk.IDGDN where Userid='".$_SESSION['user_login']."'";
    	//echo $requete;
	$resultat =  mysqli_query($connexionDB,$requete);
	$ligne = mysqli_fetch_assoc($resultat);
	$nom = $ligne['Nom'];
	//echo "Nom: ".$nom;
	$prenom = $ligne['Prenom'];
	$IDEleve = $ligne['IDGDN'];
	$classe = $ligne['Classe'];
}

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
</script>
<?php
include("../../userInfo.php");
/* en-tête */

echo "<FORM id='myForm' ACTION='infoEleve.php'  METHOD='POST'>";


echo "<div class='post'>";

echo "<center> <font color='#088A08'>Veuillez signaler au responsable de section les éventuelles erreurs ou changements à effectuer</font></center><br>";


$requete = "SELECT * FROM elevesbk bk join eleves el on el.IDGDN=bk.IDGDN left join entreprise ent on el.IDEntreprise=ent.IDEntreprise left join cleatelier ca on el.IDCle=ca.IDCle where bk.IDGDN = $IDEleve";
//ECHO $requete;
$resultat =  mysqli_query($connexionDB,$requete);
$ligne = mysqli_fetch_assoc($resultat);
//echo $ligne['Nom'];
echo "<br><div id='corners'>";
echo "<div id='legend'>Données personnelles</div>";
echo "<table border='0'><tr>";

echo "<td><table border='0'>\n";
echo "<tr><td>NOM</td><td>Prénom</td><td></td></tr>";
echo "<tr><td><input type='texte' name='NomNew' value='".htmlentities($ligne['Nom'], ENT_QUOTES)."'></input></td><td><input type='texte' name='PrenomNew' value='".$ligne['Prenom']."'></input></td><td></td></tr>\n";
echo "<tr><td>Adresse</td><td></td><td></td></tr>";
echo "<tr><td colspan='2'><input type='texte' name='AdresseNew' value=\"".$ligne['Adresse']."\" size='45'></input></td><td></td></tr>\n";
echo "<tr><td>NPA</td><td>Localité</td><td></td></tr>";
echo "<tr><td><input type='texte' name='NPANew' value='".$ligne['NPA']."' size='4'></input></td><td><input type='texte' name='LocaliteNew' value='".$ligne['Localite']."'></input></td><td></td></tr>\n";
echo "<tr><td>Date de naissance</td><td>Lieu d'origine</td><td></td><td></td></tr>";
$date = date('d.m.Y', strtotime($ligne['DateNaissance']));
if(empty($ligne['DateNaissance'])) {
	$date = '';
}
echo "<tr><td><input type='texte' name='DateNaissanceNew' value='".$date."' size='10'></input></td><td><input type='texte' name='OrigineNew' value=\"".$ligne['Origine']."\"></input></td><td></td></tr>\n";
echo "<tr><td>Téléphone</td><td>Portable</td><td></td></tr>";
echo "<tr><td><input type='texte' name='NoTelNew' value='".$ligne['NoTel']."' size='13'></input></td><td><input type='texte' name='NoMobileNew' value='".$ligne['NoMobile']."' size='13'></input></td><td></td></tr>\n";
echo "<tr><td colspan='3'>Email</td></td></tr>";
echo "<tr><td colspan='3'><input type='texte' name='EmailNew' value='".$ligne['Email']."' size='45'></input></td></tr>\n";
echo "</table></td><td width='100'></td>";

echo "<td><table border='0'>\n";
//echo "<tr><td>Banc</td><td>Chaise</td></tr>";
echo "<tr><td colspan='2'>Entreprise</td></tr>";
//echo "<tr><td><input type='texte' name='NoBancNew' value='".$ligne['noBanc']."' size='15'></input></td><td><input type='texte' name='NoChaiseNew' value='".$ligne['noChaise']."' size='15'></input></td></tr>\n";
echo "<tr><td colspan='2'><input type='texte' name='IDEntrepriseNew' value='".$ligne['NomEntreprise']."' size='25'></input></td></tr>";
echo "<tr><td>Vestiaire</td><td>Jeton</td></tr>";
echo "<tr><td><input type='texte' name='NoVestiaireNew' value='".$ligne['NoVestiaire']."' size='3'></input></td><td><input type='texte' name='NoJetonNew' value='".$ligne['NoJeton']."' size='2'></input></td></tr>\n";

echo "<tr><td>Clé layette</td><td>Badge</td></tr>";
echo "<tr><td><input type='texte' name='NoCleNew' value='".$ligne['NumeroCle']."' size='15'></input></td><td><input type='texte' name='NoBadgeNew' value='".$ligne['NoBadge']."' size='5'></input></td></tr>\n";
//echo "<tr><td>PC - No. série</td><td>PC - Nom</td></tr>";
//echo "<tr><td><input type='texte' name='NoSeriePCNew' value='".$ligne['NoSeriePC']."' size='15'></input></td><td><input type='texte' name='NomPCNew' value='".$ligne['NomPC']."' size='15'></input></td></tr>\n";
echo "<tr height='25'><td></td><td></td></tr>";
echo "<tr height='25'><td></td><td></td></tr>\n";
echo "<tr height='25'><td></td><td></td></tr>";
echo "<tr height='25'><td></td><td></td></tr>\n";
echo "<tr><td>Classe</td><td>Userid</td></tr>";
echo "<tr><td><input type='texte' name='ClasseNew' value='".$ligne['Classe']."' size='15'></input></td><td><input type='texte' name='UseridNew' value='".$ligne['Userid']."' size='7'></input></td></tr>\n";
echo "</table></td><td width='100'></td>";
echo "<td valign='top'><img src='".$_SESSION['home']."images/photo/".$ligne['Nom']."_".$ligne['Prenom'].".jpg' id='studentImg' alt='(pas de photo)'></td>";

echo "</tr></table></div>";

?>
</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include("../../piedPage.php"); ?>
