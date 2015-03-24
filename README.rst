###################
NeuroStash
###################

*******
Website
*******
`neurostash.org <http://neurostash.org>`_

*******************
Server Requirements
*******************

PHP version 5.2.4 or newer is recommended.

************
Installation
************

- Download CodeIgniter.
- Drag controllers folder into application/controllers and replace all contents.
- Drag models folder into application/models and replace all contents.
- Drag the routes.php file into application/config and replace.

************
Sample Links
************

-  `GET all entities <http://neurostash.org/sample-app/media-box/index.php/api/v1.0/video>`_
-  `GET entity by id <http://neurostash.org/sample-app/media-box/index.php/api/v1.0/video/1>`_
-  `GET complete results that include one-to-one and one-to-many relationships <http://neurostash.org/sample-app/media-box/index.php/api/v1.0/video/1?complete=true>`_
-  `GET complete results that include one-to-one, one-to-many relationships, and recurse through each entity for more relationships <http://neurostash.org/sample-app/media-box/index.php/api/v1.0/video/1?complete=true&depth=3>`_
-  `GET all the above combined <http://neurostash.org/sample-app/media-box/index.php/api/v1.0/video?complete=true&depth=3>`_
