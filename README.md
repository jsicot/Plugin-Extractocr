Extract OCR (plugin for Omeka)
=============================


Summary
-----------

Omeka plugin to extract OCR text in XML from PDF files, allowing fulltext searching within BookReader plugin for omeka.

See demo of the in [Bibliothèque numérique de l'université Rennes 2 (France)](http://bibnum.univ-rennes2.fr/viewer/show/572).



Installation
------------
- This plugin needs pdftohtml command-line tool on your server

```
    sudo apt-get install poppler-utils
```

- Upload the Extract OCR plugin folder into your plugins folder on the server;
- you can install the plugin via github

```
    cd omeka/plugins  
    git clone git@github.com:symac/Plugin-ExtractOcr.git "ExtractOcr"
```

- Activate it from the admin → Settings → Plugins page
- Click the Configure link to process or not existing PDF files.


Using the PDF TOC Plugin
---------------------------

- Create an item
- Add PDF file(s) to this item
- Save Item
- To locate extracted OCR xml file, select the item to which the PDF is attached. Normally, you should see an XML file attached to the record with the same filename than the pdf file. 


Optional plugins
----------------

- [BookReader](https://github.com/jsicot/BookReader) : This plugin adds Internet Archive BookReader into Omeka.


Troubleshooting
---------------

See online [PDF TOC issues](https://github.com/symac/Plugin-ExtractOcr/issues).


License
-------

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


Contact
-------

* Syvain Machefert, Université Bordeaux 3 (see [symac](https://github.com/symac))



