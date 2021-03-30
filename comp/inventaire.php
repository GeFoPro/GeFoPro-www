<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:95
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: inventaire.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:41
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

if($action!="Nouveau") {
?>
<script>
function toggleEditDiv(thisname) {
	//alert(thisname);
	div=document.getElementsByTagName('div')
	for (i=0;i<div.length;i++){
		if (div[i].getAttribute(thisname)){
			if (div[i].style.display=='none' ){
				div[i].style.display = '';
			} else {
				div[i].style.display = 'none';
			}
		}
	}
}
function limitEvent(e) {
    if (window.event) { //IE
        window.event.cancelBubble = true;
    } else if (e && e.stopPropagation) { //standard
        e.stopPropagation();
    }
}
</script>
<br><br><div id='corners'>
<div id='legend'>Inventaire</div>
<table border='0' id="hor-minimalist-b" width='100%'><tr><th width='100'>N° inventaire</th><th width='100' align='left'>N° de serie</th><th width='50' align='center'>Année</th><th width='100' align='center'>RFID</th><th width='300'>Remarque</th><th align='left' width='150'>Utilisé par</th><th width='20'></th></tr>

<?php
$requete = "SELECT inv.IDInventaire, NoInventaire, NoSerie, Annee, IDTag, RemarqueInv, Userid, IDEmprunt, DateEmprunt FROM inventaire inv left join emprunt emp on inv.IDInventaire=emp.IDInventaire and DateRetour is null where IDComposant=$IDComp order by NoInventaire";
//echo $requete;
$resultat =  mysqli_query($connexionDB,$requete);
if($resultat!=null && !empty($resultat)) {
	while ($footLigne = mysqli_fetch_assoc($resultat)) {
		$idinvtog = $footLigne['IDInventaire'];
		echo "<tr><td>".$footLigne['NoInventaire']."</td>";
		echo "<td align='left'>";
		//if(hasStockRight()) { echo "<input type='text' name='Tirroir$footLigne[IDStockage]' value='$footLigne[Tirroir]' size='4' onChange='updateStock($footLigne[IDStockage],\"Tirroir\",this.value)' style='text-align: right'>";
		echo $footLigne['NoSerie'];
		echo "</td>";
		echo "<td align='center'>";
		echo $footLigne['Annee'];
		echo "</td>";
		$idcardTxt = "";
		if(bin2hex($footLigne['IDTag'])!=0) {
			$idcardTxt = strtoupper(bin2hex($footLigne['IDTag']));
		}
		echo "<td align='center' onclick='toggleEditDiv(\"newIDTag$idinvtog\")'>";
		if(hasAdminRigth()) {
			echo "<div newIDTag$idinvtog='1'>$idcardTxt</div><div newIDTag$idinvtog='1' style='display:none'><input type='text' name='IDTag$footLigne[IDInventaire]' value='$idcardTxt' onclick='limitEvent(event)' size='8' onChange='updateInventaire($footLigne[IDInventaire],\"IDTag\",this.value)' style='text-align: right'></div>";
		} else {
			echo $idcardTxt;
		}

		echo "<td align='left' onclick='toggleEditDiv(\"newRemarque$idinvtog\")'>";
		if(hasAdminRigth()) {
			echo "<div newRemarque$idinvtog='1' >".$footLigne['RemarqueInv']."</div><div newRemarque$idinvtog='1' style='display:none'><input type='text' onclick='limitEvent(event)' name='RemarqueInv$footLigne[IDInventaire]' value='$footLigne[RemarqueInv]' size='35' onChange='updateInventaire($footLigne[IDInventaire],\"RemarqueInv\",this.value)' style='text-align: left'></div>";
		} else {
			echo $footLigne['RemarqueInv'];
		}
		echo "</td>";
		echo "<td align='left' onclick='toggleEditDiv(\"newUserid$idinvtog\")'>";
		$usebyTxt = "";
		if(!empty($footLigne['Userid'])) {

			mysqli_select_db($connexionDB,DBAdmin);
			$requete = "SELECT * FROM elevesbk bk join eleves el on bk.IDGDN=el.IDGDN where Userid='".$footLigne['Userid']."'";
			//echo $requete;
			$resultatUser =  mysqli_query($connexionDB,$requete);
			if($resultatUser!=null && !empty($resultatUser) && mysqli_num_rows($resultatUser)==1) {
				$user = mysqli_fetch_assoc($resultatUser);
				$usebyTxt = $user['Nom']." ".$user['Prenom'];
			} else {
				// on essai avec la table des profs
				$requete = "SELECT * FROM prof where userid='".$footLigne['Userid']."'";
				//echo $requete;
				$resultatUser =  mysqli_query($connexionDB,$requete);
				if($resultatUser!=null && !empty($resultatUser) && mysqli_num_rows($resultatUser)==1) {
					$user = mysqli_fetch_assoc($resultatUser);
					$usebyTxt = $user['abbr'];
				} else {
					$usebyTxt = $footLigne['Userid'];
				}
			}
			if(hasAdminRigth()) {
				echo "<div newUserid$idinvtog='1' >".$usebyTxt;
				echo " (".date('d.m.Y', strtotime($footLigne['DateEmprunt'])).")</div>";
				echo "<div newUserid$idinvtog='1' style='display:none'><input type='text' onclick='limitEvent(event)' name='Userid$footLigne[IDInventaire]' value='$footLigne[Userid]' size='7' onChange='updateEmprunt($footLigne[IDEmprunt],$footLigne[IDInventaire],this.value)' style='text-align: left'></div>";
			} else {
					echo $usebyTxt;
			}
		} else {
			// pas d'emprunt en cours
			echo "<div newUserid$idinvtog='1' style='display:none'><input type='text' onclick='limitEvent(event)' name='Userid$footLigne[IDInventaire]' value='' size='7' onChange='updateEmprunt(\"\",$footLigne[IDInventaire],this.value)' style='text-align: left'></div>";
		}

		echo "</td><td></td></tr>";
	}
}
mysqli_select_db($connexionDB,DBComp);
if(hasStockRight()) {
  ?>
  <tr newInv='1'><td colspan='7' bgColor='#5C5C5C'></td></tr>
  <tr newInv='1'><td colspan='6' align='right'></td><td align='right'><img src='/iconsFam/add.png' onmouseover="Tip('Ajouter un appareil')" onmouseout='UnTip()' onclick='toggle("newInv");' align='absmiddle'></td></tr>
  <tr newInv='1' style='display:none'>
  <td><input type='text' name='NoInventaireNew' value='' size="10" style='text-align: left'></td>
  <td align='left'><input type='text' name='NoSerieNew' value='' size="10" style='text-align: left'></td>
  <td align='center'><input type='text' name='AnneeNew' value='' size="4" style='text-align: right'></td>
	<td align='left'><input type='text' name='IDTagInvNew' value='' size="8" style='text-align: left'></td>
  <td align='left'><input type='text' name='RemarqueInvNew' value='' size="40" style='text-align: left'></td>
  <td align='right' colspan='2'><input type="submit" name="actionInv" value="Ajouter"></td></tr>
 <?php } ?>
  </table></div>
<?php } ?>
