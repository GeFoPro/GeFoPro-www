<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:88
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: datasheet.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:64
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

if($action!="Nouveau" && !empty($IDComp) && hasStockRight()) {
//echo $action;
?>
<br>
<div class='post'>
<form method="POST" action="comp.php" enctype="multipart/form-data">
<div id='corners'>
<div id='legend'>Fiche technique</div>
<br>
<table border=0><tr>
<?php if(!empty($ligne['datasheet']))  { ?>
<td width='60'>Actuelle: </td>
<td width='200'><a href="lirePDF.php?IDComposant=<?=$IDComp?>" target="pdf"><img src="/iconsFam/pdf.jpg" width="30" height="30"></a></td>
<td width='100'>Remplacer par:</td>
<?php } else { ?>
<td width='60'>Ajouter:</td>
<?php } ?>
<td></td><td width="200">
<input type="file" name="datasheet">
</td>
<td width="270"><input type="submit" name="actionData" value="Envoyer">
</td></tr></table>
<input type="hidden" name="IDComposant" value="<?=$IDComp?>">
</div>
</form></div>

<?php } ?>
