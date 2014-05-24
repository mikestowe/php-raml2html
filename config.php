<?php

// Source of Your RAML file (local or http)
$RAMLsource = 'http://api-portal.anypoint.mulesoft.com/twitter/api/twitter-rest-api/twitter-rest-api.raml';

// Types of Action Verbs Allowed
$RAMLactionVerbs = array('get', 'post', 'put', 'patch', 'delete', 'connect', 'trace');

// APC Cache Time Limit - set to "0" to disable
$cacheTimeLimit = '36000';

// Path to the theme file for the docs
$docsTheme = 'templates/theme.phtml';

?>