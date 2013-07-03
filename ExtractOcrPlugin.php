<?php
/**
 * Extract OCR
 *
 * Adapted from PDF Text plugin by Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * 
 * This plugin is to be combined with Internet Archive Bookreader plugin
 * to allow fulltext search within the viewer.
 *
 * TODO : Use this plugin to allow fulltext search within the main interface
 * (fulltext search through every PDF at one time). 
 */
 
class ExtractOcrPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'before_save_file',
        'before_delete_file',
    );

    protected $_pdfMimeTypes = array(
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'text/x-pdf',
        'text/pdf',
        'applications/vnd.pdf',
    );

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Don't install if the pdftotext command doesn't exist.
        // See: http://stackoverflow.com/questions/592620/check-if-a-program-exists-from-a-bash-script
        if ((int) shell_exec('hash pdftohtml 2>&- || echo 1')) {
            throw new Omeka_Plugin_Installer_Exception(__('The pdftotext command-line utility ' 
            . 'is not installed. pdftohtml must be installed to install this plugin.'));
        }
    }

    /**
     * Uninstall the plugin
     */
    public function hookUninstall()
    {


    }

    /**
     * Display the config form.
     */
    public function hookConfigForm()
    {
        echo get_view()->partial(
            'plugins/extract-ocr-config-form.php', 
            array('valid_storage_adapter' => $this->isValidStorageAdapter())
        );
    }

    /**
     * Handle the config form.
     */
    public function hookConfig()
    {
        // Run the text extraction process if directed to do so.
        if ($_POST['extract_ocr_process'] && $this->isValidStorageAdapter()) {
            Zend_Registry::get('bootstrap')->getResource('jobs')
                ->sendLongRunning('ExtractOcrProcess');
        }
    }

    /**
     * Add the PDF text to the file record.
     * 
     * This has a secondary effect of including the text in the search index.
     */
    public function hookBeforeSaveFile($args)
    {
        if (!$args['insert']) {
            return;
        }
        $file = $args['record'];
        // Ignore non-PDF files.
        if (!in_array($file->mime_type, $this->_pdfMimeTypes)) {
            return;
        }
        $itemid = $file->item_id;
        $item = get_record_by_id('item', $itemid); 
        $this->pdfToHtml($file, $item);
    }

    /**
     * Extract the text from a PDF file.
     * 
     * @param string $path
     * @return string
     */
    public function pdfToHtml($file, $item)
    {
        
        $id = $item->id;
        $original_filename = $file->original_filename;
		$xml_filename = preg_replace("/\.pdf$/i", ".xml", $original_filename);
		$tmp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($xml_filename, ".xml");
		$tmp_file_escaped = escapeshellarg($tmp_file);        
        $path = FILES_DIR . '/original/' . $file->archive_filename;

        if (!(touch($path))) {
			$path = $file->getPath();
        }
        
        $path = escapeshellarg($path);
		$cmd = "pdftohtml -i -c -hidden -xml $path $tmp_file_escaped";
		$res = shell_exec($cmd);
		
         try {
              $file = insert_files_for_item($item,
                                              'Filesystem',
                                              $tmp_file.".xml",
                                              array('ignore_invalid_files' => false));
            } catch (Omeka_File_Ingest_InvalidException $e) {
                $msg = "Error occurred when attempting to ingest the "
                     . "importing file: '$tmp_file.xml': "
                     . $e->getMessage();
                Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage(__('Error occurred when attempting to ingest the importing file: ' . $tmp_file .'.xml ' . $e->getMessage() .'. Command-line : '. $cmd), 'warning');
				return false;
            }
            release_object($file);
            Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage(__('OCR file: '. $tmp_file .'.xml has been sucessfully extracted'), 'success');
    }
    
     /**
     * Refresh PDF texts to account for a file delete.
     */
    public function hookBeforeDeleteFile($args)
    {
        $file = $args['record'];
        	// Ignore non-PDF files.
        	if (!in_array($file->mime_type, $this->_pdfMimeTypes)) {
            		return;
        	}
		$xml_filename = preg_replace("/pdf/i", "xml", $file->original_filename);
		// We've got an xml file, we need to find if the XML file is available
		$db = get_db();
		$query = $db->select("id")
				->from($db->Files)
				->where('original_filename = ?', $xml_filename)
				->where('item_id = ?', $file->item_id);

		$file_id = $db->fetchOne($query);
		if ($file_id)
		{
			$file = $db->getTable("File")->find($file_id);
			$file->delete();
		}
    }
    
    

    /**
     * Determine if the plugin supports the storage adapter.
     * 
     * pdftotext cannot be used on remote files, so only support the default 
     * Filesystem adapter, which stores files locally.
     * 
     * @return bool
     */
    public function isValidStorageAdapter()
    {
        $storageAdapter = Zend_Registry::get('bootstrap')
            ->getResource('storage')->getAdapter();
        if (!($storageAdapter instanceof Omeka_Storage_Adapter_Filesystem)) {
            return false;
        }
        return true;
    }

    /**
     * Get the PDF MIME types.
     * 
     * @return array
     */
    public function getPdfMimeTypes()
    {
        return $this->_pdfMimeTypes;
    }
}
