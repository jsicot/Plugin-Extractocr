<?php
	foreach ($results as $result)
	{
		$id = $result["id"];
		if ($my_item = get_item_by_id($id))
		{
			set_current_item(get_item_by_id($id));

			if (function_exists(bordeaux3_display_result_line))
			{
				print bordeaux3_display_result_line(get_current_item(), $result["matches_number"], $result["snippet"], $result["uri"]);
			}
			else
			{
				print "<div class='one_res'>";
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
		}
	}
	echo pagination_links();
?>
