PHP WebScraper
==============

Web Scraper to find email addresses of companies within a csv file.
The CSV contains a list of company names, each name is Googled in order to
find the best possible URL.
The script then scraps the company URL to find possible 'contact' pages.
The script then scraps the comanies homepage and contact page for any email addresses.

The email addresses are stored in an array and can either be saved into a database or a text file.
