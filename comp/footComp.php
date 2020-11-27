<tr>
<?
if(hasStockRight()) {
	if($action=="Nouveau") {
	  echo "<td></td><td colspan='5' align='left'><input type='submit' name='action' value='Ajouter'></td>";
	} else {
	  echo "<td colspan='6'>&nbsp;</td></tr><tr><td></td><td colspan='5' align='left'><input type='submit' name='action' value='Modifier'>";
	  echo " <input type='submit' name='action' value='Supprimer'>";
	  echo " <input type='submit' name='action' value='Copier'>";
	}
}
?></tr>