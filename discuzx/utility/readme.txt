||==================================||
|| Discuz! X upgrade documentation  ||
||==================================||
|| ENGLISH Version                  ||
|| by Valery Votintsev at sources.ru||
||==================================||

This document is designed to help you to upgrade your
current version of any Comsenz product (not Discuz!X Serie)
to the latest Discuz! X version.

This upgrade program can also be used for upgrading any previous
Discuz!X versions to the latest version.

The last Discuz!X version for now is Discuz!X v.2.0 29-Jun-2011.
http://download.comsenz.com/DiscuzX/2.0/

Before you start the upgrade process,
be sure to read step by step the following instructions:

ATTENTION!!!
If you want to use this ENGLISH package,
you MUST replace all the original /utility/ folder
with this one!


1. Before the upgrade, in order to ensure the correct processing,
   it is strongly recommended to backup all the original data,
   and current database!
   We can not provide any technical support for customers who
   did not make a backup before the upgrade!
   
2. Typically, our upgrade program is update.php,
   it is placed in the ./utility/ directory of the product release.

3. In most cases, after you have upgraded to Discuz! X products,
   most of features at your site may not work properly.
   You have to log in into your admin-center and update all the Cache!

4. After a successful upgrade, please delete the update program
   to avoid possible security problems.

 --------------------------------------
 Upgrade procedure steps
 --------------------------------------

1. Shut down your curent system.
   Backup all the files and the database.

2. Upload the latest version of Discuz!X to your site,
   overwriting old files.

3. Upload all the files and subdirectories from the /utility/
   directory into your site /install/ directory.
   Ensure that the /install/ directory contains the latest installer!

4. Copy the files:
   /instal/convert/language/lang_update.php
   /instal/convert/language/lang_convert.php
   into the /source/language/ directory at your site.

5. Visit http://your_domain/install/update.php

6. Follow to the program prompts until completion of
   all the upgrade processes.

 --------------------------------------
 Upgrade Troubleshooting
 --------------------------------------

 If you encounter any problems during the upgrade process,
 this can be resolved using the following channels:

1. Our official help and support forum
   http://www.discuz.net

2. View detailed update guidance documents
   http://faq.comsenz.com/category-202

3. International support and help forum
   http://msg2me.com

4. Commercial users can purchase a service by phone calls,
   MSN, QQ, forums for technical support and other ways.
