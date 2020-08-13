# xRefCore
Simple OOP framework for OOP coding
You come from C# or Java and want to create PHP-apps for CLI?
This simple framework will simplify a creation of PHP-applicaions with OOP using

<h4>Features:</h4>
* Class autoloading. Just create a new class and application will load it automatically
* PHAR support
* Windows CMD and Linux Console encoding support (cyrillic)
* Startup arguments will be sent on initializing main class

<h4>How to use</h4>
1. Download
2. Extract somewhere
3. Setup app.json
4. Start coding in <code>/Program/Main.php</code>
5. Run <code>autoload.php</code>

<h4>app.json</h4>
This file almost is useless. There are two useful things:
1. <code>php_version</code>. Set minimal PHP version for app. If your PHP version too old than app's required version, the application won't start
2. <code>namespaces</code>. If you have a some library and don't want extract to <code>/Program</code>, you can extract it to main dir and add folder name into <code>namespaces</code>

<h4>Classes:</h4>
All methods in these classes are static
<b>Application\Application</b>
<code>GetRequiredPhpVersion()</code> returns a required PHP version for application (don't forget to set it at app.json)
<code>GetName()</code> returns an application name
<code>GetDescription()</code> returns a description
<code>GetVersion()</code> returns a version of application
<code>GetAuthor()</code> I think you really know what it does
<code>GetExecutableFileName()</code> returns a full path of executable file. If your application packed to PHAR-archive, it will return a full path to PHAR-archive. If not, it will return a full path to file where this method was called
<code>GetExecutableDirectory()</code> returns a path only of executable file. If your application packed to PHAR-archive, it will return a path where PHAR-archive is set. If not, it will return a path to file where this method was called

<b>IO\Console</b>
<code>ReadLine()</code> reads and returns the input string from the standard input stream
<code>WriteLine(string $text)</code> writes the string to screen and set pointer to the next line
<code>Write(string $text)</code> writes the string to screen