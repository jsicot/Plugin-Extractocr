<?php
	foreach ($results as $result)
	{
		$id = $result["id"];
		print "<div class='one_res'>";
		set_current_item(get_item_by_id($id));
		// Printing the thumbnail on the right
		if (item_has_thumbnail())
		{
			print "<div class='item-img'>";
			if ($result["uri"])
			{
				print "<a href='".$result["uri"]."'>".item_square_thumbnail()."</a>";
			}
			else
			{
				print link_to_item(item_square_thumbnail());	
			}
			print "</div> <!-- .item-img -->";
		}
		print link_to_item(item("Dublin Core", "Title"), array('class'=>'permalink'));
		if ($result["snippet"])
		{
			print "<div class='show_snippet'>";
			print $result["matches_number"]." mentions dans cet ouvrage : <br/>";
			print "<div class='snippets'>";
			print $result["snippet"];
			print "</div>";
			print "</div>";
		}
		print "</div>";
	}
?>
