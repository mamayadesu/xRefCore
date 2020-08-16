# xRefCore
Simple OOP framework for OOP coding
You come from C# or Java and want to create PHP-apps for CLI?
This simple framework will simplify a creation of PHP-applicaions with OOP using

<h2>Features:</h2>
* Class autoloading. Just create a new class and application will load it automatically<br>
* PHAR support. For convertation ZIP to PHAR you can use: https://github.com/mamayadesu/zip2phar<br>
* Windows CMD and Linux Console encoding support (cyrillic)<br>
* Startup arguments will be sent on initializing main class<br>

<h2>How to use</h2>
1. Download<br>
2. Extract somewhere<br>
3. Setup app.json<br>
4. Start coding in <code>/Program/Main.php</code><br>
5. Run <code>autoload.php</code><br>

<h2>app.json</h2>
This file is almost useless. However, here are two useful things:<br>
1. <code>php_version</code>. Set minimal PHP version for app. If your PHP version too old than app's required version, the application won't start<br>
2. <code>namespaces</code>. If you have a some library and don't want extract to <code>/Program</code>, you can extract it to main dir and add folder name into <code>namespaces</code><br>

<h2>Classes:</h2>
All methods in these classes are static<br>
<b><code>Application\Application</code></b><br>
<code>GetRequiredPhpVersion()</code> returns a required PHP version for application (don't forget to set it at app.json)<br>
<code>GetName()</code> returns an application name<br>
<code>GetDescription()</code> returns a description<br>
<code>GetVersion()</code> returns a version of application<br>
<code>GetAuthor()</code> I think you really know what it does<br>
<code>GetExecutableFileName()</code> returns a full path of executable file. If your application packed to PHAR-archive, it will return a full path to PHAR-archive. If not, it will return a full path to file where this method was called<br>
<code>GetExecutableDirectory()</code> returns a path only of executable file. If your application packed to PHAR-archive, it will return a path where PHAR-archive is set. If not, it will return a path to file where this method was called<br>
<br>
<b><code>IO\Console</code></b><br>
<code>ReadLine()</code> reads and returns the input string from the standard input stream<br>
<code>WriteLine(string $text)</code> writes the string to screen and set pointer to the next line<br>
<code>Write(string $text)</code> writes the string to screen<br>
