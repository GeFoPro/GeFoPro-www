<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:93
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: footComp.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:20
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010
#
echo "<tr>";
if(hasStockRight()) {
	if($action=="Nouveau") {
	  echo "<td></td><td colspan='2' align='left'><input type='submit' name='action' value='Ajouter'></td>";
	} else {
	  echo "<td colspan='7'>&nbsp;</td></tr><tr><td></td><td colspan='6' align='left'><input type='submit' name='action' value='Modifier'>";
	  echo " <input type='submit' name='action' value='Supprimer'>";
	  echo " <input type='submit' name='action' value='Copier'>";
	}
}
echo "</tr>";
?>
