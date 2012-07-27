<?php
	function extract_ocr_get_params($req = null)
	{
		if ($req == null)
		{
			$req = $_REQUEST;
		}

		$params = array();
		$params["q"] = $req["q"];

		// TODO : manage other parameters (page, results / page ?)

		return $params;

	}

	function extract_ocr_build_url_inside()
	{
		$url_out = "";
		$params = extract_ocr_get_params();
		$url_out =  __v()->Url();
		$url_out .= "/?q=".$params["q"];
		$url_out .= "&inside=true";
		return $url_out;
	}
	
	function extract_ocr_results_url()
	{
		return uri("/extract-ocr/results/");
	}
	
	function extract_ocr_form($buttonText = "Search", $formProperties=array('id'=>'simple-search'))
	{
		$uri = extract_ocr_results_url();
		$formProperties['action'] = $uri;
		$formProperties['method'] = 'get';

		$html = "<form "._tag_attributes($formProperties) . ">\n";
		$html .= "<fieldset>\n\n";
		$html .= __v()->formText('q', '', array('name'=>'q', 'class'=>'textinput'));
//		$html .= __v()->formHidden();
		$html .= __v()->formSubmit('submit_search', $buttonText);
		$html .= "</fieldset>\n\n";
		$html .= "</form>";
		return $html;
	}
?>
