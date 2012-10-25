<?php header('Content-Type: text/html; charset=utf-8'); ?>
<?php head(array('title' => 'Résultats dans les documents', 'bodyclass' => 'page')); ?>

<?php
?>

<script type="text/javascript">
var $eo = jQuery.noConflict();

$eo(document).ready(function()
	{
		$eo("#res_inside").html("L'interrogation de la base peut prendre quelques secondes (&lt;1 min), veuillez patienter<br/><img src='<?php echo img("ajax-loader.gif"); ?>' alt='chargement ...' title='chargement ...'/>");
		$eo.get("<?php echo extract_ocr_build_url_inside(); ?>", function(data)
			{
				$eo("#res_inside").html(data);
			}
		);
	});
</script>

<div id="primary">
	<h1>Résultats dans les documents</h1>
	<div id='res_inside'>&nbsp;</div>
</div>
<?php echo foot(); ?>
