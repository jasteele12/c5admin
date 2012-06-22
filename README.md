README
======

What is c5admin ?
-----------------

Create a new concrete5 website easily and quickly.
Very handy if you regularly create concrete5 websites on your server.

Inspired by [Andrew Embler](http://andrewembler.com/posts/installing-concrete5-from-the-command-line/).


Documentation
-------------
Read those lines before execute the script.

**Be careful**, the generation can be time consuming.

You have to modify *$target_array* and *$core_array* in the script.
*$core_array* is a list of your different concrete5 versions.
*$target_array* is a list of your websites.
 
Put the *install.php* wherever you want.
 
Before execute *install.php*, you have to copy some folders and files
from core to your website folder. 

    cp blocks/ config/ controllers/ css/ elements/ files/ helpers/ jobs/ js/ languages/ libraries/ mail/ models/ packages/ page_types/ single_pages/ themes/ tools/ updates/ index.php robots.txt -Rp /var/www/mywebsites/site1/ 

This step will be do in the script in future.

You have also to change some permissions in your website folder.

    chmod 777 files
    chmod 777 config
    chmod 777 packages
    
This step will be do in the script in future.

Finally, please modify *concrete/controller/install.php* in the core folder.
Comment those lines : 

    if (PHP_SAPI != 'cli') {
        $this->redirect('/');
    }
    
I hope this problem will be fixed asap.
