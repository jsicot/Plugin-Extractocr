This omeka plugin allows creation of xml files from pdf using pdftohtml. The xml is stored as a new file associated with the item.

This XML file can then be used to : 
- perform full text search (use extract_ocr_form() instead of simple_search() in your theme to use it)
- allow fulltext searching within BookReader plugin (original version there : https://github.com/jsicot/BookReader , to be updated to allow fulltext search)

