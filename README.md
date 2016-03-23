# RAML 2 HTML for PHP

RAML 2 HTML for PHP is a simple application that makes use of multiple templates to allow you to build and customize your API Docs using RAML.

![Screenshot](http://www.mikestowe.com/wp-content/uploads/2014/05/raml2html.png?v=1)

#### What all does RAML 2 HTML for PHP do?
RAML 2 HTML for PHP builds out a multi-page documentation site for you based on your RAML spec.  This lets users explore and understand how your API works, while letting you style the documentation (within the RAML spec) using HTML or markdown, and provide your users with automatically generated code samples while also letting you include community and commenting functionality within your documentation via Disqus.

Of course, being templated, you can add additional features/ functionality to your site quickly and easily by setting up your own template (or copying the grey template) and modifying the HTML/CSS/PHP code.

#### What version of PHP does RAML 2 HTML require?
RAML 2 HTML for PHP versions 1.0 or greater require PHP 5.3+

If you are running an older version of PHP, it is highly recommend you upgrade, but if you are unable to do so, you can use RAML version 0.2 which supports PHP 5+.  However, this version is extremely limited and is not being maintained or supported.

No other external libraries, databases, or extensions are required to run RAML to HTML for PHP, although if APC is installed the script will take advantage of it for caching purposes.

#### How do I set it up?
Important setup information is stored in config.php.  You can read setup instructions [here](http://www.mikestowe.com/2014/05/raml-2-html.php).

#### Is there a Demo?
Yes!  You can find the latest stable demo and the latest development version demos [here](http://www.mikestowe.com/2014/05/raml-2-html.php).

#### Does it support all RAML features?
Not yet, although version 1.0 was a complete rewrite supports base, path variables, multi-level includes, resourceTypes, traits, and more.  Other features will be added down the road!

#### How Can I Help?
Easy!  Download and use RAML 2 HTML for PHP, tell your friends, if you find issues report them, or even better - feel free to contribute by forking and making pull requests!

#### License
RAML is covered under the GPL2 license.  However, the included class Spyc falls under the MIT license.
