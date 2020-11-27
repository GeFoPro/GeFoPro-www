<?
if($action!="Nouveau") {
?>
<br><br><div id='corners'>
<div id='legend'>Altium</div>
<table border='0'>
<tr><td colspan='6'>&nbsp</td></tr>

<tr><td>Schéma:</td><td colspan="2">
<div id="IDSchNew"><select name='IDSchema'><option></option>
<?
  $requete = "SELECT * FROM $tableRefSchema order by ReferenceSchema";
  $resultat =  mysql_query($requete);
  while ($listeLigne = mysql_fetch_array($resultat)) {
    if($IDSchemaSel==$listeLigne[0])
      echo "<option value='$listeLigne[0]' selected='selected'>";
    else
      echo "<option value='$listeLigne[0]'>";
    echo "$listeLigne[1] </option>";
  }
?>
  </select>
  <a href="#" onClick="toggleForm('IDSchNew','SchNew')"><img src="/iconsFam/table_edit.png" align="absmiddle" onmouseover="Tip('Editer la liste des schémas')" onmouseout="UnTip()"></a></div>
  <div id="SchNew"><input type="texte" name="SchemaNew" value="" size="25"><a href="#" onClick="toggleForm('IDSchNew','SchNew')"><img src="/iconsFam/table.png" align='absmiddle' onmouseover="Tip('Retour à la liste')" onmouseout="UnTip()"></a></div>
</td><td colspan='3'></td></tr>
<tr><td>Footprints <?=$ligne['LibelleBoitier']?>:</td>

<? 
$requete = "SELECT * FROM $tableFootprint foot
join $tableReference ref on foot.IDReference=ref.IDReference
where IDComposant=$IDComp";
  $resultat =  mysql_query($requete);
    while ($footLigne = mysql_fetch_assoc($resultat)) {
	  echo "<td colspan='2'><input type='text' name='reference$footLigne[IDFootprint]' value='$footLigne[Reference]' readonly>";
	  echo "<a href='comp.php?actionFoot=Supprimer&IDFootprint=$footLigne[IDFootprint]&IDComposant=$IDComp'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a></td></tr><tr><td>&nbsp;</td>";	
    }
  ?>
<td colspan="2"><div id="IDRefNew"><select name='IDReferenceNew'><option></option>";

<?
  $requete = "SELECT * FROM $tableReference order by Reference";
  $resultat =  mysql_query($requete);
  while ($listeLigne = mysql_fetch_array($resultat)) {
    echo "<option value='$listeLigne[0]'>";
    echo "$listeLigne[1] </option>";
  }
?>
  </select><a href="#" onClick="toggleForm('IDRefNew','RefNew')"><img src="/iconsFam/table_edit.png" align="absmiddle" onmouseover="Tip('Editer la liste des footprints')" onmouseout="UnTip()"></a></div>
  <div id="RefNew"><input type="texte" name="ReferenceNew" value="" size="25"><a href="#" onClick="toggleForm('IDRefNew','RefNew')"><img src="/iconsFam/table.png" align='absmiddle' onmouseover="Tip('Retour à la liste')" onmouseout="UnTip()"></a></div>
  </td><!-- td><input type="submit" name="actionFoot" value="Ajouter" --><td colspan='3'></td></tr>
<? } ?>
<SCRIPT language="Javascript">
toggleForm('IDRefNew','RefNew');
toggleForm('IDSchNew','SchNew');
</script>
</table></div>