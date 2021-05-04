<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:09
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: piedPage.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 16:03:37
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

	$release = "<br><b><i>2.5.1</i></b>";
	$release .= "<br>- Sélection possible de différents métiers dans l\'entête";
	$release .= "<br>- Fonctionnement possible de l\'application sans ActiveDirectory/LDAP";
	$release .= "<br>- Interface utilisateur multilingue";
	$release .= "<br><b><i>2.5</i></b>";
	$release .= "<br>- migration vers php7 (utilisation de mysqli)";
	$release .= "<br>- corrections pour fonctionnement sur systèmes linux (LAMP)";
	$release .= "<br>- paramétrage amélioré pour utilisation de LDAP";
	$release .= "<br>- possibilité d\'installer l\'application dans une sous structure web";
	$release .= "<br><b><i>2.4.3</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Textes pour commandes et étiquettes séparés";
	$release .= "<br>- Possibilité d\'utiliser qu\'un seul champ pour le texte des commandes (sélectionner deux fois la même ligne)";
	$release .= "<br>- Possibilité de valider une évaluation avec seulement un commentaire dans les observations";
	$release .= "<br><b><i>2.4.2</i></b>";
	$release .= "<br>Bugs:";
	$release .= "<br>- Texte de la ligne de commande coupé si présence d\'apostrophes dans les champs de l\'article";
	$release .= "<br><b><i>2.4.1</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Possibilité d\'ajouter ou retourner manuellement un prêt dans la liste des prêts";
	$release .= "<br><b><i>2.4</i></b>";
	$release .= "<br>Fonctionnalités: Gestion des prêts de matériel";
	$release .= "<br>- Gestion de la liste de numéros d\'inventaire et de série par article";
	$release .= "<br>- Attribution d\'un identifiant RFID par article ou pour l\'ensemble d\'un même type d\'article, soit dans l\'inventaire (un par article),";
	$release .= "<br>&nbsp;&nbsp; soit lié au stockage de l\'article (pour l\'ensemble du même type d\'articles)";
	$release .= "<br>- Gestion des prêts d\'articles, avec historique";
	$release .= "<br>- Liste des prêts en cours, par personne ou par emplacement de stockage";
	$release .= "<br>- API REST pour l\'interrogation, le prêt et le retour d\'un article";
	$release .= "<br><b><i>2.3.3</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Possibilité de valider une évaluation hebdomadaire/de thème, même si celle-ci n\'est pas complète";
	$release .= "<br>- Proposition automatique de texte dans la mise des notes, en fonction de ce qui a été saisi dans les évaluations";
	$release .= "<br><b><i>2.3.2</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Intégration d\'un lien sur Teams";
	$release .= "<br><b><i>2.3.1</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Gestion des projets améliorée afin de faciliter les opérations et attributions sur ceux-ci";
	$release .= "<br>- Possibilité d\'attribuer directement une classe entière à un projet";
	$release .= "<br><b><i>2.3</i></b>";
	$release .= "<br>Fonctionnalités (gestion des composants):";
	$release .= "<br>- Possibilité d\'ajouter des images pour un article, un type de boitier ou une catégorie (genre/type)";
	$release .= "<br>- Regroupement des articles en doublons pour une commande et tri selon numéro d\'article";
	$release .= "<br>- Intitulé automatique de l\'article commandé selon la même configuration de l\'impression des étiquettes";
	$release .= "<br>- Pour un article, ajout d\'un nouveau champ de quantité en stock minimum ainsi qu\'un champ de quantité de commande";
	$release .= "<br>- Affichage du montant total par commande avec la TVA";
	$release .= "<br><b><i>2.2.2</i></b>";
	$release .= "<br>Bugs:";
	$release .= "<br>- Saisie des journaux le vendredi impossible, après passage à l\'heure d\'hiver";
	$release .= "<br><b><i>2.2.1</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Affichage des journaux avec tri par thème ou par jour";
	$release .= "<br>- Correctif sur le calcul de la moyenne des évaluations pour la proposition des notes de semestre";
	$release .= "<br><b><i>2.2</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Saisie des journaux de travail par tranche horaire ou durée";
	$release .= "<br>- Impression du suivi en PDF";
	$release .= "<br>- Navigation améliorée avec les vues thèmes/semaine dans les journaux et suivis";
	$release .= "<br><b><i>2.1</i></b>";
	$release .= "<br>Fonctionnalités:";
	$release .= "<br>- Ajout attribut Stage pour apprentis (hors listes tâches et suivis)";
	$release .= "<br>- Apprentis externes hors liste navigation (flèches bleues) ";
	$release .= "<br>- Navigation avec les fèches bleues (apprentis/semaines) possible avec les flèches du clavier (et touche CTRL appuyée)";
	$release .= "<br>- Les projets peuvent être associés à tout apprenti et non plus uniquement que les 3ème et 4ème années";
	$release .= "<br>- Modification possible de la pondération d\'un thème dans la vue des notes de l\'apprenti (clic sur pondération)";
	$release .= "<br>Bugs:";
	$release .= "<br>- Passage à la semaine no 1 n\'incrémente par l\'année correctement";
	$release .= "<br>- Impression en PDF des évaluations uniquement si validées";
	$release .= "<br>- Problème de création globale des périodes pour les notes, pour le système d\'évaluation hebdomadaire";
?>
<div id="footer">
	<p id="legal" onmouseover="Tip('<b>Release notes:</b><?=$release?>')" onmouseout="UnTip()">GeFoPro 2.5.1, 2010 &copy; Designed by DGI</p>

</div>
<!-- end footer -->
</div>

<!-- end wrapper -->
</body>
</html>
