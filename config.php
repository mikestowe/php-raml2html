<?php

// Source of Your RAML file (local or http)
$RAMLsource = 'raml/twitter.raml';

// Types of Action Verbs Allowed
$RAMLactionVerbs = array('get', 'post', 'put', 'patch', 'delete', 'connect', 'trace');

// APC Cache Time Limit - set to "0" to disable
$cacheTimeLimit = '36000';

// Path to the theme file for the docs
$docsTheme = 'templates/grey/index.phtml';

// Enable Try It (alpha)
$tryIt = false;

// Enable Disqus
$disqus = false;

// Disqus Short Name for Site (see Disqus admin)
$disqus_shortname = '';

?>
