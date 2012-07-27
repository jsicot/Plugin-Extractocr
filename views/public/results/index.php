<?php header('Content-Type: text/html; charset=utf-8'); ?>
<?php head(array('title' => 'Résultats', 'bodyclass' => 'page')); ?>

<?php
?>

<script type="text/javascript">
var $eo = jQuery.noConflict();

$eo(document).ready(function()
	{
		$eo("#extract_ocr_search_inside").click(
			function()
			{
				$eo("#res_inside").html("L'interrogation de la base peut prendre quelques secondes (&lt;1 min), veuillez patienter<br/><img src='<?php echo img("ajax-loader.gif"); ?>' alt='chargement ...' title='chargement ...'/>");
				// We then query the background file
				$eo.get("<?php echo extract_ocr_build_url_inside(); ?>", function(data)
						{
						$eo("#res_inside").html(data);
					}
				);
				return false;
			}
		)
	}
);
</script>

<div id="primary">
	<h1>Résultats</h1>
	<?php
		if ($results)
		{
			include(dirname(__FILE__)."/items_list.php");
		} # end if $results
		else
		{
			print "Aucun résultat dans les fiches descriptives des documents, pour plus de résultats, relancez une recherche en texte intégral (dans les documents), ci-dessous.";
		}


		// We must allow user to call the inside process (looking inside XML files)
		print "<div id='extract_ocr_div_search_inside'><a href='' id='extract_ocr_search_inside'>Continuez la recherche à l'intérieur des documents (<b>texte intégral</b>)</a></div>";
		print "<div id='res_inside'>&nbsp;</div>";
	?>
</div>
<?php echo foot(); ?>
