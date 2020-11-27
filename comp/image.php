<?
if($action!="Nouveau" && !empty($IDComp) && hasStockRight()) {
//echo $action;
?>
<br>
<div class='post'>
<form method="POST" action="comp.php" enctype="multipart/form-data">	
<div id='corners'>
<div id='legend'>Image article</div>
<table border=0><tr><td width='150'>Ajouter une image:</td>
<td width="300">
<input type="file" name="imgDownload">
</td><td width="270">230 x 150 env.</td></tr>
<tr><td></td><td><select name='imgGrp'><option value='1'>Pour cet article uniquement</option><option value='2'>Pour ce genre/type</option>
<? if($app_section=='ELT') {  ?>
<option value='3'>Pour ce boitier</option>
<? } ?>
</select></td><td><input type="submit" name="actionDataImg" value="Envoyer">
</td></tr></table>
<input type="hidden" name="IDComposant" value="<?=$IDComp?>">
<input type="hidden" name="IDGenre" value="<?=$IDGenreSel?>">
<input type="hidden" name="IDType" value="<?=$IDTypeSel?>">
<input type="hidden" name="NomBoitier" value="<?=$nomBoitier?>">
<input type="hidden" name="IDUnique" value="<?=$IDUnique?>">
</div>
</form></div>

<? } ?>
