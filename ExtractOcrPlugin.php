<?php
/**
 * Extract OCR from Omeka's PDF. Creates a .xml file with the same name
 * as .pdf. 
 * 
 * This plugin is to be combined with Internet Archive Bookreader plugin
 * to allow fulltext search within the viewer.
 *
 * TODO : Use this plugin to allow fulltext search within the main interface
 * (fulltext search through every PDF at one time). 
 */
class ExtractOcrPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array('install', 'uninstall', 'config_form', 'config', 
                              'after_save_item', 'before_delete_file', 'define_routes', 'public_theme_header', 'public_append_to_items_browse');
    
    protected $_pdfMimeTypes = array('application/pdf', 'application/x-pdf', 
                                     'application/acrobat', 'text/x-pdf', 
                                     'text/pdf', 'applications/vnd.pdf');
    
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Don't install if the pdftohtml command doesn't exist.
        $output = (int) shell_exec('hash pdftohtml 2>&- || echo -1');
        if (-1 == $output) {
            throw new Exception('The pdftohtml command-line utility is not installed. pdftohtml must be installed to install this plugin.');
        }
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
    }
    
    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
?>
<div class="field">
    <label for="save_pdf_texts">Process existing PDF files</label>
    <div class="inputs">
        <?php echo __v()->formCheckbox('extract_pdf_texts'); ?>
    </div>
    <p class="explanation">This plugin enables searching on PDF files by 
    extracting their texts and saving them to their parent items. This normally 
    happens automatically, but there are times when you'll want to extract text 
    from all PDF files that exist in your Omeka archive; for example, when first 
    installing this plugin and when items are being created by other plugins. 
    Check the above box and submit this form to run the text extraction process, 
    which may take some time to finish.</p>
</div>
<?php
    }
    
    /**
     * Process the plugin config form.
     */
    public function hookConfig()
    {
        // Run the text extraction process if directed to do so.
        if ($_POST['extract_pdf_texts']) {
            ProcessDispatcher::startProcess('ExtractOcrProcess');
        }
    }
    
    /**
     * Refresh PDF texts to account for an item save.
     */
    public function hookAfterSaveItem($item)
    {
        $this->saveItemPdfText($item);
    }
    
    /**
     * Refresh PDF texts to account for a file delete.
     */
    public function hookBeforeDeleteFile($file)
    {
    	// We need to find the xml file associated with the PDF
		if (!in_array($file->mime_browser, $this->_pdfMimeTypes))
		{
			return;
		}
	
		$xml_filename = preg_replace("/pdf/i", "xml", $file->original_filename);
		// We've got an xml file, we need to find if the XML file is available
		$db = get_db();
		$query = $db->select("id")->from($db->Files)->where('original_filename = ?', $xml_filename)->where('item_id = ?', $file->item_id);

		$file_id = $db->fetchOne($query);
		if ($file_id)
		{
			$file = $db->getTable("File")->find($file_id);
			$file->delete();
		}
    }
    
	public function hookDefineRoutes($router)
	{
		# TODO : débugger et comprendre pourquoi cela ne fonctionne pas
		# (cd SolrSearch)
/*		$searchResultsRoute = new Zend_Controller_Router_Route('results',
			array(
				'controller' => 'search',
				'action' => 'results',
				'module' => 'extract-ocr'
			)
		); */

//		$router->addRoute('extract_ocr_results_route', $searchResultsRoute);
	}

	public function hookPublicThemeHeader($request)
	{
		if ($request->getModuleName() == "extract-ocr")
		{
			echo '<link rel="stylesheet" href="'.html_escape(css('extract_ocr_public')). '" />';
		}
	}

	public function hookPublicAppendToItemsBrowse()
	{
		$pagination = Zend_Registry::get('pagination');
		$total = $pagination["total_results"];
		$page = $pagination["page"];
		$per_page = $pagination["per_page"];

		$val_test = ceil($total / $per_page);
		if ( ( $val_test == $page) and (isset($_GET["search"])))
		{
			print "<div style='padding:3px; width:700px; margin:auto; color:white; background:#222'>Vous pouvez étendre la recherche au texte contenu dans les ouvrages, cette opération est relativement longue : <a style='font-weight:bold; color:white' href='".__v()->url("extract-ocr/results/v2?q=".$_GET["search"])."'>rechercher dans les documents</a>.</div>";
		}
	}


    /**
     * Extract texts from all PDF files belonging to an item.
     * 
     * @param Item $item
     * @param int $elementId The ID of the "PDF Search::Text" element.
     * @param int $recordTypeId The ID of the Item record type.
     */
    public function saveItemPdfText(Item $item)
    {
        // Iterate all files belonging to this item.
        foreach ($item->Files as $file) {
            $this->saveFilePdfText($file, $item);
        }
    }
    
    /**
     * Extract text from a PDF file and save it to the parent item.
     * 
     * @param File $file
     * @param int $elementId The ID of the "PDF Search::Text" element.
     * @param int $recordTypeId The ID of the Item record type.
     */
    public function saveFilePdfText(File $file, $item)
    {
        // Ignore non-PDF files.
        if (!in_array($file->mime_browser, $this->_pdfMimeTypes)) {
            return;
        }
        
        // Build the XML source
	$original_filename = $file->original_filename;
	$xml_filename = preg_replace("/\.pdf$/i", ".xml", $original_filename);

	// We need to check if the XML already exists
	$pdf_added = $file->added;
	$pdf_modified = $file->modified;

	$db = get_db();
	$query = $db->select()->from($db->Files)->where('original_filename = ?', $xml_filename)->where('item_id = ?', $item->id);
	if (!sizeof($db->fetchAll($query)))
	{
		// We don't have the XML file, we need to build it
		print "Building PDF<br/>";
		$storage = Zend_Registry::get('storage');
		$tmp_dir = $storage->getTempDir();

		$path = escapeshellarg(FILES_DIR . '/' . $file->archive_filename);
		$tmp_file = $tmp_dir . basename($xml_filename, ".xml");
		$tmp_file_escaped = escapeshellarg($tmp_file);

		$cmd = "pdftohtml -i -c -hidden -xml $path  $tmp_file_escaped";
		$res = shell_exec($cmd);
		# TODO : Manage errors for pdftohtml

		// The $tmp_file contains the XML we need to add to our current file
		insert_files_for_item(
			$item,
			'Filesystem',
			$tmp_file.".xml"
		);
	}
    }

}
