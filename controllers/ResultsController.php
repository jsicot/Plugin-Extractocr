<?php
/**
* @author	Sylvain Machefert
* @license GPL
* TODO : Gérer les limites / pagination
**/
?>
<?php

require_once 'Omeka/Controller/Action.php';

class ExtractOcr_ResultsController extends Omeka_Controller_Action
{
	public function indexAction()
	{
		$q = $_GET["q"];
		$this->handleHtml($q);
	}

	protected function handleHtml($q)
	{
		if (isset($_GET["inside"]))
		{
			$results = $this->searchInside($q);
			$this->_helper->viewRenderer('inside');
		}
		else
		{
			$results = $this->search(0, 10);
		}

		$this->view->assign(
			array(
				'results' => $results,
				'q' => $q
			)
		);
	}

	private function searchInside($q)
	{
		$out = array();

		if ($q)
		{
			// Then we search just the filenames matching
			$cmd = "grep -l -i '$q' ".FILES_DIR . "/*.xml";
			$res = shell_exec($cmd);

			// For every file we need to build a snippet
			$res = preg_replace("/\n$/", "", $res);
			$tab_files = preg_split("/\n/", $res);
			foreach ($tab_files as $matching_file)
			{


				$res = shell_exec("grep -P -i '<\/?page|$q' $matching_file");
				$res = preg_replace("/<page[^>]*>\n<\/page>\n/", "", $res);
				// We just need to know the first page matching
				$first_page = preg_replace('/^.*<page number="(\d*)".*$/siU', '$1', $res);
			
				// We then need to know how many matches there are
				$matches_number = substr_count($res, $q);

				// And last, extract up to 3 lines width a matching example
				$lines_array = preg_split("/\n/", $res);
				$snippet = "";
				$snippet_lines_number = 0;
				foreach ($lines_array as $matching_line)
				{
					// We also have <page lines, we need to remove them
					if ( (preg_match("/<text/", $matching_line)) and ($snippet_lines_number < 9993) )
					{
						// We remove all tags
						$matching_line = preg_replace("/<\/?[^>]*>/", "", $matching_line);
						$matching_line = preg_replace("/$q/i", "<span class='highlight_results' style='color:red'>$q</span>", $matching_line);

						if ($snippet_lines_number > 0)
						{
							$snippet .= "<br/>";
						}
						$snippet_lines_number++;
						$snippet .= $matching_line;
					}
				}
				$result_out = array();
				$result_out["snippet"] = $snippet;
			
				// From the file, we have to find the item_id
				$db = get_db();
				$query = $db->select()->from(array($db->Files), 'item_id')->where('archive_filename = ?', basename($matching_file));
				$result_out["id"] = $db->fetchOne($query);
				$result_out["first_page"] = $first_page; 
				$result_out["matches_number"] = $matches_number;
				$result_out["uri"] = uri("viewer/show/".$result_out["id"]);
				// We then need to know how many matches there are

				$out[] = $result_out;
			}
		}
		return $out;
	}

	private function search($offset=0, $limit=10)
	{
		$q = $_GET["q"];
		$out = array();
		
		// First, we search using the classical research function from omeka
		$results = get_items(
			array(
				'search'=>$q
			)
		);
		
		foreach ($results as &$value)
		{
			$out[]["id"] = $value->id;
		}
		return $out;
	}
}
?>
