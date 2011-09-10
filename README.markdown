napcin
======

napcin [read: napkin] - nginx and PHP configuration is neat

A little about the project
------

The aim of the project is to create a simple command line tool, that would make it easier to manage and configure nginx with PHP, PHP-FPM and MySQL. It is designed so that it would be easy to edit its configuration files (universally stored in the human readable YAML format) by anyone, very safe and robust.

Unlike massive web control panels (such as CPanel, ISPConfig, Webmin, Plesk, DirectAdmin, etc) the idea behind *hypoconf* is to have very little "auto-magic" behaviour, in order to retain full control over the configuration files and enable customisation and manual editing capabilities.

The underlying core - parsing and config generation classes have been built, so that they can be very universal and so that it would be very easy to add support for any other applications that stores its configuration file in plain text. Possible future implementation can include many more applications.

The official 1.0 version has not been released yet, but you may fork the source code and test it (if you feel like debugging). There is very little, to no documentation at this point in time.

Similar project, albeit with a much simpler approach (also - no longer active) was [nginx_config_generator](https://github.com/defunkt/nginx_config_generator).


Licensing
------

MIT License


This used to be on Google Code and Mercurial
------

This project moved and renamed from here: http://code.google.com/p/hypoconf/
