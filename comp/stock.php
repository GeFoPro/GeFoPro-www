<?
if($action!="Nouveau") {
?>

<br><br><div id='corners'>
<div id='legend'>Stock</div>
<table border='0' id="hor-minimalist-b" width='100%'><tr><th>Emplacement</th><th align='center'>Tirroir/étagère -<br>Boîte/rangement</th><th align='center'>Emplacement<br>RFID</th><th align='center'>Quantité<br>actuelle</th><th align='center'>Quantité<br>prêtée</th><th align='center'>Quantité<br>minimale</th><th align='center'>Quantité<br>commande</th><th></th></tr>
<!--tr><td colspan='6'>&nbsp;</td></tr -->
<!--tr><td colspan='2'><b>Stock</b> (Emplacement)</td><td><b>Quantité</b></td><td colspan='3'></td></tr-->
<?
$requete = "SELECT * FROM $tableStockage stg
join $tableStock st on stg.IDStock=st.IDStock
where IDComposant=$IDComp";
  $resultat =  mysql_query($requete);
  //si une seule ligne, on regarde si un inventaire existe pour compter le nombre et utiliser l'info pour la ligne de stock
  $numbInv = 0;
  if(mysql_num_rows($resultat)==1) {
    $requeteInv = "select * from inventaire where IDComposant=$IDComp";
    $resultatInv =  mysql_query($requeteInv);
    $numbInv = mysql_num_rows($resultatInv);
  }
    while ($footLigne = mysql_fetch_assoc($resultat)) {
    // recherche des prêts
    $requetePre = "select * from emprunt emp left join inventaire inv on emp.IDInventaire=inv.IDInventaire where (IDStockage=$footLigne[IDStockage] OR IDComposant=$IDComp) and DateRetour is null";
    $resultatPre =  mysql_query($requetePre);
    $numbPre = mysql_num_rows($resultatPre);
	  echo "<tr><td onClick='location.href=\"listePrets.php?IDStockage=".$footLigne['IDStockage']."\"'>$footLigne[Emplacement]</td>";
	  echo "<td align='center'>";
	  if(hasStockRight()) {
		  echo "<input type='text' name='Tirroir$footLigne[IDStockage]' value='$footLigne[Tirroir]' size='4' onChange='updateStock($footLigne[IDStockage],\"Tirroir\",this.value)' style='text-align: right'>";
	  } else {
		echo "$footLigne[Tirroir]";
	  }
	  echo "</td>";
    $idcardTxt = "";
    if(bin2hex($footLigne['IDTag'])!=0) {
    	$idcardTxt = strtoupper(bin2hex($footLigne['IDTag']));
    }
    echo "<td align='center'>";
    if(hasAdminRigth()) {
		  echo "<input type='text' name='IDTag$footLigne[IDStockage]' value='$idcardTxt' size='8' onChange='updateStock($footLigne[IDStockage],\"IDTag\",this.value)' style='text-align: right'>";
	  } else {
		  echo $idcardTxt;
	  }
    echo "</td>";
    echo "<td align='center'>";
    if($numbInv==0) {
      echo "<input type='text' name='Quantite$footLigne[IDStockage]' value='$footLigne[Quantite]' size='4' onChange='updateStock($footLigne[IDStockage],\"Quantite\",this.value)' style='text-align: right'>";
    } else {
      echo $numbInv;
    }
	  echo "</td>";
    echo "<td align='center' onClick='location.href=\"listePrets.php?IDStockage=".$footLigne['IDStockage']."\"'>".$numbPre."</td>";
	  echo "<td align='center'>";
	  if(hasStockRight()) {
		  echo "<input type='text' name='QuantiteMin$footLigne[IDStockage]' value='$footLigne[QuantiteMin]' size='4' onChange='updateStock($footLigne[IDStockage],\"QuantiteMin\",this.value)' style='text-align: right'>";
	  } else {
		  echo $footLigne[QuantiteMin];
	  }
	  echo "</td><td align='center'>";
	  if(hasStockRight()) {
		  echo "<input type='text' name='QuantiteComm$footLigne[IDStockage]' value='$footLigne[QuantiteComm]' size='4' onChange='updateStock($footLigne[IDStockage],\"QuantiteComm\",this.value)' style='text-align: right'>";
	  } else {
		  echo $footLigne[QuantiteComm];
	  }
	  echo "</td>";
	  echo "<td align='right'>";
	  if(hasStockRight()) {
		  echo "<a href='comp.php?actionStock=Supprimer&IDStockage=$footLigne[IDStockage]&IDComposant=$IDComp'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a>";
	  }
	  echo "</td></tr>";
    }
  if(hasStockRight()) {
  ?>
  <tr newStock='1'><td colspan='8' bgColor='#5C5C5C'></td></tr>
  <tr newStock='1'><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td align='right'><img src='/iconsFam/add.png' onmouseover="Tip('Ajouter un emplacement')" onmouseout='UnTip()' onclick='toggle("newStock");' align='absmiddle'></td></tr>
  <tr newStock='1' style='display:none'><td><select name='IDStockNew'>
  <option selected> </option>
  <?
  /* Construction listes stock */
  $requete = "SELECT * FROM $tableStock order by Emplacement";
  $resultat =  mysql_query($requete);
    while ($listeLigne = mysql_fetch_array($resultat)) {
      echo "<option value='$listeLigne[0]'>";
      echo "$listeLigne[1] </option>";
    }
  ?>
</select></td><td align='center'><input type='text' name='EmplacementNew' value='' size="4" style='text-align: right'><img src="/iconsFam/help.png" align='absmiddle' onmouseover="TagToTip('tooltipInv')" onmouseout="UnTip()"></td>
  <span id="tooltipInv">
  <dl><dt><b>Syntaxe :</b></dt>
  <dd>[Tirroir/étagère]-[Boîte/rangement]</dd>
  <dd>Exemple: 02-48</dd>
  </dl>
  </span>
  <td align='center'><input type='text' name='IDTagNew' value='' size="8" style='text-align: right'></td>
  <td align='center'>
  <? if($numbInv==0) { ?>
    <input type='text' name='QuantiteNew' value='' size="4" style='text-align: right'>
  <? } ?> </td>
  <td></td>
  <td align='center'><input type='text' name='QuantiteMinNew' value='' size="4" style='text-align: right'></td>
  <td align='center'><input type='text' name='QuantiteCommNew' value='' size="4" style='text-align: right'></td>
  <td align='right'><input type="submit" name="actionStock" value="Ajouter"></tr>
 <? } ?>
  </table></div>
<? } ?>
