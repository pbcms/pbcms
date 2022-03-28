## PBCMS

PBCMS is a basic content management system built on PHP. 

I've started this project since I've noticed that lately I have been building a lot of smaller projects with PHP instead of going all the way with a language like Node. I have been strongly against the use of PHP but there are certain usecases where it honestly provides the better solution.

**Why don't you just use wordpress?**

This project tries to focus itself towards developers but perhaps it will have the potential to spread to end consumers aswell. I don't like certain aspects of wordpress and find myself to install so many plugins that it radically slows it down and bloats the whole interface. I don't want.

I like to build my own and custom solutions to problems I encounter. PBCMS is just the foundation of a bigger project that I'm planning ahead of me.

## Warning for developers

This project is still in alpha and under heavy development. Expect API changes to be made before the initial v1.0.0 release will be made.

## Documentation

Documentation can be accessed via [docs.pbcms.io](https://docs.pbcms.io) or generated with ``phpDocumentor -d /dir/of/pbcms -t /output/dir/of/docs --ignore dynamic/``.

## Webserver configuration

PBCMS is designed in a way that all requests will be executed from the ``public/`` folder instead of the root. This ensures that attackers cannot directly access anything in the ``app/``, ``dynamic/`` or root folder of the instance. As a result of this they will only be able to access public resources and execute the ``public/index.php`` file which will then initiate the ``Loader`` class within the ``app/Loader.php`` file. Then, the request will be processed.

Beside this, the webserver should also respond to a special file called ``execute-update``. If this file exists, the webserver should execute the ``updater.php`` file instead of the ``public/index.php`` file. The updater will then remove the marker, thus the ``public/index.php`` file will be executed again. This fill then proceed to cleanup the updater.

### Apache

To achieve the above described behaviour, two basic .htaccess files are delivered with the source code. For these to work, they need to be allowed by the webserver. You can do so with the following VHOST configuration:

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/DOMAIN.TLD

    ServerName DOMAIN.TLD
    ServerAlias www.DOMAIN.TLD

    <Directory "/var/www/DOMAIN.TLD">
        allow from all
        Options +FollowSymLinks
        Require all granted
        AllowOverride All
    </Directory>
</VirtualHost>
```
