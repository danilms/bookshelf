bookshelf
=========

Book storage for Opensoft

### This is a test project to train our interns
## it is a `kind` of playground

Requirements:
-----------------------

1.  php5-pgsql

2.  composer

3.  mod-rewrite

Installation:
-----------------------

1. Install the dependent libraries by running `composer install` in the project's directory

2. Create a inks to required frontend libraries
 - `ln vendor/twbs/bootstrap/dist/css/bootstrap.min.css web/css/`
 - `ln vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css web/css/`
 - `ln vendor/twbs/bootstrap/dist/js/bootstrap.min.js web/js/`
 - `ln vendor/frameworks/jquery/jquery.min.js web/js/`
 - `ln vendor/pqr/star-rating/css/star-rating.css web/css/`
 - `ln vendor/pqr/star-rating/js/star-rating.js web/js/`
 
3. Create database 'bookshelf' and restore dump (resources/scheme.sql).
