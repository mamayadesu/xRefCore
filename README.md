# xRefCore
This framework will simplify a creation of PHP console applicaions with OOP using

<h2>Features:</h2>
* Class autoloading. Just create a new class and application will load it automatically<br>
* PHAR support. For convertation ZIP to PHAR you can use: https://github.com/mamayadesu/zip2phar<br>
* Windows CMD and Linux Console encoding support (cyrillic)<br>
* Startup arguments will be sent on initializing main class<br>
* Emulation of multithreading. Only `socket` extesion required

<h2>How to use</h2>
1. Download<br>
2. Extract somewhere<br>
3. Setup app.json<br>
4. Start coding in <code>/Program/Main.php</code><br>
5. Run <code>autoload.php</code><br>

<h2>app.json</h2>
This file contains application configuration<br>
1. <code>php_version</code> Minimal PHP version required for application<br>
2. <code>app_name</code> Application name<br>
3. <code>app_version</code> Version of application<br>
4. <code>app_author</code> Author of application<br>
5. <code>app_description</code> Description of application<br>
6. <code>namespaces</code> List of roots of namespaces. Do not put 'Program' and 'Core' to this list!<br>
7. <code>priorities</code> Classes which has highest load priority<br>