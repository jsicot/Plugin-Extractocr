<?php
require_once dirname(__FILE__) . "/helpers/ExtractOcrFunctions.php";
require_once 'ExtractOcrPlugin.php';
$plugin = new ExtractOcrPlugin;
$plugin->setUp();
#$filter = array('Form', 'Item', PdfSearchPlugin::ELEMENT_SET_NAME, PdfSearchPlugin::ELEMENT_NAME);
#add_filter($filter, 'PdfSearchPlugin::disableForm');
#$filter = array('Display', 'Item', PdfSearchPlugin::ELEMENT_SET_NAME, PdfSearchPlugin::ELEMENT_NAME);
#add_filter($filter, 'PdfSearchPlugin::disableDisplay');
