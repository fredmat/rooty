<?php

use Symfony\Component\HttpFoundation\Request;

/*
|--------------------------------------------------------------
| Bootstrap Rooty.
|--------------------------------------------------------------
*/

(require_once __DIR__.'/../../../bootstrap/app.php')->handleRequest(Request::createFromGlobals());
