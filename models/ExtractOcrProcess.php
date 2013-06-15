<?php

class ExtractOcrProcess extends Omeka_Job_AbstractJob
{
    /**
     * Process all PDF files in Omeka.
     */
    public function perform()
    {
        
        $extractOcrPlugin = new ExtractOcrPlugin;
        $fileTable = $this->_db->getTable('File');

        $select = $this->_db->select()
            ->from($this->_db->File)
            ->where('mime_type IN (?)', $extractOcrPlugin->getPdfMimeTypes());

        // Iterate all PDF file records.
        $pageNumber = 1;
        while ($files = $fileTable->fetchObjects($select->limitPage($pageNumber, 50))) {
            foreach ($files as $file) {
            	 // Build the XML source
		$original_filename = $file->original_filename;
		$xml_filename = preg_replace("/\.pdf$/i", ".xml", $original_filename);
		$itemid = $file->item_id;
        	$item = get_record_by_id('item', $itemid); 
		$query = $this->_db->select()
			->from($this->_db->File)
			->where('original_filename = ?', $xml_filename)
			->where('item_id = ?', $itemid);
			
		if (!sizeof($this->_db->fetchAll($query))) {
			$extractOcrPlugin->pdfToHtml($file, $item);
			

		}
                // Prevent memory leaks.
                release_object($file);
            }
            $pageNumber++;
        }
    }
}
