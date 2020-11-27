<?
if($action!="Nouveau") {
?>

<br><br><div id='corners'>
<div id='legend'>Inventaire</div>
<table border='0' id="hor-minimalist-b" width='100%'><tr><th>N° inventaire</th><th align='left'>N° de serie</th><th align='center'>Année</th><th>Remarque</th><th align='center'>Utilisé par</th><th width='100'></th></tr>

<? 
$requete = "SELECT * FROM inventaire where IDComposant=$IDComp order by NoInventaire";
//echo $requete;
$resultat =  mysql_query($requete);
while ($footLigne = mysql_fetch_assoc($resultat)) {
	echo "<tr><td>".$footLigne['NoInventaire']."</td>";
	echo "<td align='left'>";
	//if(hasStockRight()) { echo "<input type='text' name='Tirroir$footLigne[IDStockage]' value='$footLigne[Tirroir]' size='4' onChange='updateStock($footLigne[IDStockage],\"Tirroir\",this.value)' style='text-align: right'>";
	echo $footLigne['NoSerie'];
	echo "</td>";
	echo "<td align='center'>";
	echo $footLigne['Annee'];
	echo "</td>";
	echo "<td align='left'>";
	echo $footLigne['RemarqueInv'];
	echo "</td>";
	echo "<td align='center'>";
	echo $footLigne['Userid'];
	echo "</td><td></td></tr>";	
}
if(hasStockRight()) {
  ?>
  <tr newInv='1'><td colspan='6' bgColor='#5C5C5C'></td></tr>
  <tr newInv='1'><td colspan='5' align='right'></td><td align='right'><img src='/iconsFam/add.png' onmouseover="Tip('Ajouter un appareil')" onmouseout='UnTip()' onclick='toggle("newInv");' align='absmiddle'></td></tr>
  <tr newInv='1' style='display:none'>
  <td><input type='text' name='NoInventaireNew' value='' size="10" style='text-align: left'></td>
  <td align='left'><input type='text' name='NoSerieNew' value='' size="10" style='text-align: left'></td>
  <td align='center'><input type='text' name='AnneeNew' value='' size="4" style='text-align: right'></td>
  <td align='left'><input type='text' name='RemarqueInvNew' value='' size="40" style='text-align: left'></td>
  <td align='right' colspan='2'><input type="submit" name="actionInv" value="Ajouter"></td></tr>
 <? } ?>
  </table></div>
<? } ?>