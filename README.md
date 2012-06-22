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

You have to modify *TARGET_FOLDER* and *$core_array* in the script.
*$core_array* is a list of your different concrete5 versions.
*TARGET_FOLDER* is the folder where your websites are stored.

Finally, please modify *concrete/controller/install.php* in the core folder.
Comment those lines : 

    if (PHP_SAPI != 'cli') {
        $this->redirect('/');
    }
    
I hope this problem will be fixed asap.

Put the *install.php* wherever you want and try it. 

Todo 
----

 * Fix the FIXME (-:
 * display the differents steps during installation
