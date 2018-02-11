Open311 Simple CRM installation instructions
======================================

Overview
--------

You'll need a webserver that's happy to run PHP, and a database. For example,
Apache and mySQL works fine, but if your system is different you should be OK
to change it -- see the configuration details further down in this file.

__Note:__ because of a dependency in GroceryCRUD, it now seems that
Open311 Simple CRM will __only__ work nicely with mySQL and mySQLite databases.
Ouch. We'll try to sort this out in future updates.

Install the files
-----------------

Open311 Simple CRM is a PHP CodeIgniter project.

Place the contents of this git repository somewhere where your webserver will
have access to it. Ideally, only the contents of the `web/` directory (that
is, `index.php` and `assets/`) should be placed under your webserver's server root:

* if you intend to run Open311 Simple CRM as the *only* service for that webserver,
  you can deploy directly into the server root (or simply set your server
  root to be `web/`)

* alternatively, you can put it in a subdirectory so it won't collide with
  other things you might already be running on the webserver

The `web/` directory also contains an `fcgi/` directory -- if you're not using
FastCGI on your webserver you can ignore this, otherwise copy that to be
alongside `assets/` too.


Edit the CodeIgniter $system_folder setting
-------------------------------------------

You can skip this step if your `web/` directory is in the same directory as
the `codeigniter/` directory (that is, if the relative path to `codeigniter/`
from `index.php` hasn't changed).

Otherwise, edit `web/index.php` and change the `$system_folder setting`:

    $system_folder = "../codeigniter";

This must point to the codeigniter directory in this repository. For example,
in the default setup, this is `../codeigniter` because the `codeigniter/`
directory is a sibling of `web/`. If you have moved them apart (which is OK,
of course), make sure this setting has the correct path _from this index.php_
You can use an absolute or relative path here.


Edit the Open311 Simple CRM base_url setting
--------------------------------------

You can skip this step if you have placed `index.php` in the server-root of
your webserver, not in a subdirectory (that is, if `http://YOUR_DOMAIN/`
should hit the `index.php` already).

Otherwise, make sure that Open311 Simple CRM's core CodeIgniter config knows the URL
to the root page:

Edit `codeigniter/Open311 Simple CRM/config/config.php` and set

    $config['base_url']= 'http://example.com/your-path/';


Check the root page
-------------------

You should already be able to see the Open311 Simple CRM root page in your browser
(it will be reporting that you haven't set up the database yet). If you don't 
see such a page, double-check the setting for `$system_folder` -- if you're
not  sure, use an absolute path.

Depending on where you have installed `index.php`, the URL will be:

   * `http:YOUR_DOMAIN/` if you've set `web/` to be your server root, or put
     `index.php` and `assets/` directly in the server root 

   * `http:YOUR_DOMAIN/path_to_web_dir/` otherwise


Set up the database
-------------------

Although CodeIgniter supports mySQL, MySQLi, Postgre SQL, ODBC, and MS SQL,
Open311 Simple CRM probably will __only__ work with mySQL and mySQLi. This is
because of a dependency in the GroceryCRUD plugin. We'll look into removing
this dependency in the future.

Populate that database with the initial values by importing

    db/Open311 Simple CRM-initial.sql

The SQL in that file is in mySQL dialect. You may need to translate it
slightly if you're running with a different flavour of SQL.


Edit the database configuration
-------------------------------

The instructions here assume you are going to edit configuration settings
within `codeigniter/fms_endpoint/config/` which is the usual place for
CodeIgniter application config. This is probably the simplest way to get
Open311 Simple CRM up and running.

> However, be aware that these files are within the git repository.
> This means you *may* run into problems in future releases of the
> Open311 Simple CRM code. We provide an alternative mechanism (because it fits the
> way we deploy our own servers at mySociety) which is described here:
> `documentation/ALTERNATIVE_CONFIG.md`

Edit `codeigniter/fms_endpoint/config/database.php` and set the values
necessary to access your database.

You probably only need to set values for the following:

    $db['default']['hostname'] = "localhost:8889";
    $db['default']['username'] = "dbuser";
    $db['default']['password'] = "the-password";
    $db['default']['database'] = "Open311 Simple CRM";
    $db['default']['dbdriver'] = "mysql";

The values shown here are for a mySQL database running on port 8889 on the
same machine as the webserver. Change the values to match your own situation.


Check the root page again
-------------------------

The root page shows what's not working yet. If Open311 Simple CRM is not complaining
about the database connection, then it's working :-)

   * if you're getting a completely blank (empty) page, check that you didn't
     break the PHP when you edited the configuration files 
     (try `php -l filename` to check the syntax, for example)

   * look in `codeigniter/logs` to see if there is a report in the most
     recently updated log file

   * look in your webserver's error log to see if it's reporting a PHP
     error


Configure URL rewriting
-----------------------

Open311 Simple CRM generates URLs assuming that you've set up the webserver to
rewrite its URLs. The recommended rewrite rules for the Apache webserver can
be found in `/conf/httpd.conf`.

If you're running Open311 Simple CRM under Apache, copy the contents of
`/conf/httpd.conf` into your main `httpd.conf` file.

(Alternatively, you can try use a `.htaccess` file instead. This part of the
configuration is really down to CodeIgniter and your particular webserver,
so if you run into problems -- especially with `.htaccess` -- you can find
several different approaches and solutions on CodeIgniter forums.)

You can't tell if your URL rewriting is working until you try to access a
page that is not the root page -- a good test is to try to log in. If you
cannot log in, but always find yourself at the root page (and perhaps see
the URL includes duplicate items such as `auth/auth/auth`), then CodeIgniter
is failing to extract the segments from the URI. You may be able to 
workaround this by changing the `$config['uri_protocol']` value in
`codeigniter/fms_endpoint/config/config.php`  

By default, `$config['uri_protocol']` is set to `PATH_INFO`, but if that's not
working for you, `AUTO` may fix the problem.


Final Things
------------

When everything is running, you can click on the __Main site__ link on the
Open311 Simple CRM root page that's now running in your webserver.

You'll see from the homepage that there are a couple of things that you *must*
change away from the defaults before your configuration is truly finished.

### Change the administrator username

Go directly into your database and change the email address to a working email
address, for example with: 

    UPDATE `users` SET email='your_email@your_domain.com' 
      WHERE email='admin@example.com';


### Change the administrator password

Log into Open311 Simple CRM by clicking on __Main site__. You can't access the
Open311 Simple CRM (except the root page) unless you're logged in, so you'll be
redirected to the login page. Login as admin:

  * username: `admin@example.com` (unless you've just changed it as above!)
  * password: `password`

You'll be logged in to the root page: click on __Main site__ again, and this
time you'll see an example record in the reports.

Change the administrator's password by clicking on the admin email on the
top-right of the screen.

  * you *must* change the password to a secret one because the default is
    public knowledge!

### Change the organisation name

Now you're logged in as an administrator, you can change any of the
configuration settings that are stored inside the database. Click on
__Settings__ or go to `/admin/settings`

To edit a setting, find it in the list and click on __Edit__.

  * `organisation_name` -- change this to the name of your department. You
    have to navigate to another page, or refresh, to see the heading change
    after you've made the change.

### Optionally redirect the root page when everything is working

As you've seen, the root page shows diagnostics (which, at this stage, should
all be positive). If you prefer not to display this, you can change the
behaviour by clicking on Settings and changing the config setting to whichever
URL you prefer.

  * `redirect_root_page` -- leave blank, or set to any URL (but `/admin` is
    probably best)

### Add some non-admin users

If you want to add users, click on __Users__ then click on __Create new user__.
Users created this way will *not* have admin access.


### Configure Open311

Note that you can control whether or not your server accepts requests through
the Open311 API by clicking on __Settings__ and toggling the config setting:

  * `enable_open311_server` -- set to `yes` or `no` (the setting is shown
    in the footer of all pages, but won't update on the edit page until
    you go to another page or refresh)

There are other settings that affect the behaviour of the Open311 server --
see the entries in __Settings__ for details.

### Delete example data

Your Open311 Simple CRM contains some example records. When you're ready to go live,
you can delete these either from within your database directly, or by
navigating to the pages in Open311 Simple CRM and clicking on the delete icon to the
right of each item's listing.


Working with a FixMyStreet Open311 client
-----------------------------------------

In order to work with clients running FixMyStreet variants, log in as the
admin user,	 click on __Settings__, and use these values:

  * `open311_use_api_keys` set to `yes`
  * `open311_use_external_id` set to `always`
  * `open311_use_external_name` set to `external_id`

This forces your Open311 Simple CRM to only accept FMS reports which provide the ID
or ref from the client system that is reporting them. FixMyStreet clients can
be configured to behave in this way.

Next, you need to add the FixMyStreet client. Still as the admin user, click on __Clients__ and either edit the default one, or else create a new one.

When you've done that, click on __API keys__ and create an API key for your
new client. The key can be any string: ideally make this random or
unpredictable. The API key is not really secure, but it deters speculative
abuse since only requests with the API key will be honoured.

We recommend you run your server over https and where possible enforce IP
restrictions.

This document does not describe how to configure the client, but in general,
you need to be sure that the following behaviour is set up at the FixMyStreet
end:

   * FMS must have some concept of mapping problems to your server
      * this may be by area
      * it may be based on the category of the problem
   * FMS will need to know the specifics of your server, including the
     URL of the endpoint, and the API key it should send
   * FMS must also be configured to send the FMS ID as an `external_id` 
     attribute with every new problem report


Keep in touch
-------------

Finally, if you're running Open311 Simple CRM, do let us know! 

We maintain a site especially for helping people running FixMyStreet and
related projects at [diy.mysociety.org](http://diy.mysociety.org)


--[mySociety](http://www.mysociety.org)




