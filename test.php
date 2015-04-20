<?php
require 'vendor/autoload.php';

use BooBoo\BooBoo;
use BooBoo\MyBooBoos\DatabaseError;

BooBoo::setUp();
throw new BooBoo(new DatabaseError(DatabaseError::NOT_AVAILABLE), 503);
