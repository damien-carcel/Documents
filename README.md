What is Documents?
==================

Documents is a small web application build on top of the [CakePHP framework](http://cakephp.org/).

It is designed to manage files on a PHP/MySQL server in a secure way. Find more on how it work on the dedicated [wiki page]().

# INSTALLATION

## Setup the database

First, you have to create 3 tables in your database. Look to the dedicated [wiki page]() for more explanation about the different table fields.

```
CREATE TABLE documents (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50),
	file int,
	folder int,
	created DATETIME DEFAULT NULL,
	modified DATETIME DEFAULT NULL
);
```

The ```documents``` table will contain all the informations about the files you will upload.

```
CREATE TABLE folders (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50),
	parent int,
	created DATETIME DEFAULT NULL,
	modified DATETIME DEFAULT NULL
);
```

The ```folders``` table will contain the informations about the folders that contain your files.

```
CREATE TABLE users (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50),
	password VARCHAR(50),
	role VARCHAR(50),
	created DATETIME DEFAULT NULL,
	modified DATETIME DEFAULT NULL
);
```

The ```users``` table will contain the different users that can access the application and their role.

Once these tables created, you have to configure the database in the application. See the [official CakePHP documentation](http://book.cakephp.org/2.0/en/development/configuration.html#database-configuration) for more details.

## Configure the app

Once the database is configured, you need to configure the application itself.

First, there is a few things to set up in the ```app/Config/core.php``` file:
* set ```debug``` to ```0```,
* set different ```Security.salt``` and ```Security.cipherSeed```.

See [CakePHP documentation](http://book.cakephp.org/2.0/en/development/configuration.html#core-configuration) for more details.

Then you have to change at your liking the value of ```MAX_FILE_SIZE``` in ```app/View/Document/add_file.php``` to force an limit to the size the uploaded files can be.

Eventually, configure a super administrator for your application. You can simply create it through the ```New user``` page, then you have to set its role to ```superadmin``` in the ```users``` table.

# CONTRIBUTING

The application is fully functional but is still very crude on many aspects. If you find this application useful or are simply curious about it, your contribution is welcome.

There is a few things that need to be done in priority for a 1.0 release:
* download links should be fakes and not refer to the real location of the file on the server,
* when creating a new account ask an email to send a confirmation that the account is active,
* add a captcha system,
* localization of the application (every messages are in french right now, as it is my mother language),
* a global users administration page accessible only from the super administrator, allowing to see every users information and change their role in the application,
* a user administration page: basically the user should be able to change its password, email address…

Other useful functionality can wait for further release:
* capacity to upload multiple files at once,
* refine the CSS stylesheet (right now, it is barely a rework of the generic CakePHP CSS that comes with the framework),
* add an upload progression bar,
* possibility to download an entire folder and its contents at once,
* possibility to stay connected between sessions,
* Installation script to automatize installations and updates.

# License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

The CakePHP framework is licensed under The MIT License. For full copyright and license information, please see the [MIT License](http://www.opensource.org/licenses/mit-license.php).
