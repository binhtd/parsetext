technology
	php5, php-curl, wget, pdftohtml, git

install
	sudo apt-get install wget git php5-curl poppler-utils -y

folder structure
	1) the pdf folder holds all download pdf files (file we need to extract text)
	2) the html folder holds all html files when we convert all pdf files to html files (we need these temp files for extract information)
	3) the downloadpdf.php contains all source code we need to download pdf files
	4) the parse.php contains all source we use to parse the information in the html file (get the valuable information inside source pdf)

running
	in command line switch to user has previlege root by 
	1) sudo su
	access folder /var/www/parsetext
	2) cd /var/www/parsetext
	run file downloadpdf.php file to get all pdf files
	3) php downloadpdf.php
	starting parse information from pdf file
	4) php parse.php

Note: the progress download file and parse data that will consume a lot of the time base on many criterial (ex: network, cpu, ram) so it is better we run these scripts on command line (we don't get timeout issue)
