Using general.yml to store your database config
================================================

The instructions in `documentation/INSTALL.md` tell you to edit the database
configuration values (which will, of course, be specific to your own
installation) in `codeigniter/fms_endpoint/config/database.php` (and other
files in that directory). This is the easiest way to get up and running,
since this is the normal place for CodeIgniter configuration.

However, those files are within the git repository, which means they *may*
get changed in future versions of FMS-endpoint. If you might be pushing any 
code back to us, or you simply prefer to keep your config separate from the
code, like we do, then we provide an alternative mechanism for specifying
configuration values:

---

Put your configuration settings in `conf/general.yml`. 

FMS-endpoint will read those and use them to override anything that is set in 
`codeigniter/fms_endpoint/config/database.php` and
`codeigniter/fms_endpoint/config/config.php`

Note that database settings need to be prefixed with DB_ -- for an example 
of what this file should look like, see: 
`conf/general-example.yml`

Incidentally, it's in YAML format (that is, not a PHP/CodeIgniter config file)
just because that's how our own server config rolls. For details of YAML, see:
http://www.yaml.org/
