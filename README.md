# PostCode

Please require the phpmailer using Composer in the terminal:

**MAKE SURE YOUR CURRENT DIRECTORY IS LOCATED IN PostCode-main/PostCode-main/**
_**composer require phpmailer/phpmailer**_

_**-- MAKE SURE TO MOVE THE .ZIP FOLDER TO htdocs/ or www/ or any of the main folder according to what you use for local development env. --**_ 

If the verification/forget password process failed, make sure that the path/location of this downloaded and extracted folder is located in the correct path as the one inside 

**PostCode-main\PostCode-main**\include\send_password_mail.php as well **PostCode-main\PostCode-main**\include\verification_mail.php

When you extract this folder, make sure that there is a **PostCode-main/** folder inside an outer **PostCode-main/** folder, and it contains all the files inside.

If you have registered successfully/the verification e-mail has been sent, but when you tried to verify it when clicking the link in the mail sent, it results in a 404 page, it means that there is probably something wrong with your folder path (still incorrect). If you do not wish to move folders, just change both links/paths of the phpmailers to be the current path of your localhost, please make your own adjustments!

_-- THE .sql FILE PROVIDED IS GIVEN FOR THAT WHO IS HAVING TROUBLE WITH THE INITIALIZATION --_
