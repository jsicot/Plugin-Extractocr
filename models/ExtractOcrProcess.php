<?php
class ExtractOcrProcess extends ProcessAbstract
{
    public function run($args)
    {
        $db = get_db();
        
        $extractOcr = new ExtractOcrPlugin;
       	print "Test enregistrement log\n";
        // We're goind to fetch only the item_id having at least one PDF
	$sql = "SELECT * FROM {$db->File} where original_filename like '%pdf'";
        $items = $db->fetchAll($sql);
        foreach ($items as $i) {
            // Release an existing item to avoid a memory leak in PHP 5.2.
            if (isset($item)) {
                release_object($item);
            }
            
            $item = $db->getTable('Item')->find($i['item_id']);
            $extractOcr->saveItemPdfText($item);
        }
    }
}
