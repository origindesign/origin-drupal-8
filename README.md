# Origin Composer template for Drupal 8 projects

This project template should provide a kickstart for managing your site
dependencies with [Composer](https://getcomposer.org/).

## Requirements

- [Composer](https://getcomposer.org/download/)
- [Docker](https://docs.docker.com/engine/installation/)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Git](https://git-scm.com/downloads)

## 1. Setting Up Drupal on Docker

### a. Getting Drupal and Docker running

This setup is done on windows, the main projects directory is as close as the C folder to avoid path length limit to 260 characters on Windows. In the following steps, the projects folder is C:\webroot\ and the project name is called "Origin Drop".

The following commands will:

- Navigate to the webroot directory
- Clone the Origin Drupal 8 project into webroot/origindrop/ folder
- Installing a Drupal Project using Composer
- Setup docker containers locally (a LEMP stack working with Nginx, Php 7, MariaDB, PhpMyAdmin and mailhog)
- Remove git tracking so you can set your own
```shell
$ cd .../webroot
$ git clone git@github.com:origindesign/origin-drupal-8.git origindrop
$ cd origindrop
$ composer update
$ docker-compose up -d
$ rm -rf .git
```

### b. Installing Drupal

If everything went well:

- Navigate to <http://localhost:8000> and you should see the Drupal Installation page
- Navigate to <http://localhost:8001> and you should see PhpMyAdmin interface with an empty drupal database
- From the install page, follow the classic Drupal installation step using the following credentials:
```
Database: drupal
Username: drupal
Password: drupal
host: mariadb
port: 3306
```
- After the installation, enter the site information and you should get your fresh Drupal 8 Site.
- At the time of this writing, docker has some permissions issues in the files directory with drupal 8.2.x version in the core/includes/files.inc. This can be solved by using drupal > 8.3.x (alpha at the time of this writing) and applying this [patch](https://www.drupal.org/node/944582)

### c. Architecture
<pre>
webroot/
└── origindrop/
    ├── docker-runtime/
    |   └── ...
    ├── drush/
    |   └── ...
    ├── scripts/
    |   └── ...
    ├── vendor/
    |   └── ...
    ├── web/
    |   ├── core/
    |   ├── modules/
    |   ├── profiles/
    |   ├── sites/
    |   ├── themes/
    |   ├── .htaccess
    |   ├── autoload.php
    |   ├── index.php
    |   ├── robots.txt
    |   ├── update.php
    |   ├── web.config
    |   └── ...
    ├── .gitignore
    ├── .travis.yml
    ├── composer.json
    ├── composer.lock
    ├── docker-compose.yml
    ├── LICENCE
    ├── phpunit.xml.dist
    └── README.md
</pre>

### d. Stopping and Removing containers

When you want to stop working on a project, type `docker-compose stop` from the root of the project, it will stop the containers.
IMPORTANT: Do not use `docker-compose down` command because it will purge MariaDB volume. Instead use `docker-compose stop`. If you restart Docker you WILL NOT lose your MariaDB data. 


## 3. Using Drush to manage Config Manager and Cache Rebuild

- Drush can be accessed normally after sshing into the php container:
```shell
$ docker-compose exec php sh
$ cd var/www/html/web
$ drush status
```
- Drush can aslo be accessed through the docker-compose command and by specifiying the root directory `docker-compose exec php drush -r /var/www/html/web/ status`
- In order to simplify the command, you can create an alias in your .bashrc file like `alias ddrush='docker-compose exec php drush'` (I called mine "ddrush" for docker drush)
- Then you'd need to create your drush aliases and copy it from your local machine to the php container drush directory:
```shell
$ cd ~/.drush
$ docker cp origindrop.aliases.drushrc.php origindrop_php_1:/root/.drush/origindrop.aliases.drushrc.php
```
- Depending on the name you set for your aliases, you should be able to run drush from your local like these:
```shell
$ ddrush @local status 
$ ddrush @pantheon.origindrop.dev status 
```

## 4. Pushing into Host (Pantheon)

If you want to use Pantheon as hosting for your site, you can navigate to web/sites/default, delete the settings.php file and rename :
```shell
settings.local.php.txt to settings.local.php
settings.pantheon.php.txt to settings.pantheon.php
settings.php.txt to settings.php
```

From the Pantheon Dashboard, create a new Drupal 8 site; then, before installing Drupal, set your site to git mode and do the following from the root of your local project:
```shell
$ git init
$ git add -A .
$ git commit -m "Setting up Drupal with web docroot"
$ git remote add origin ssh://ID@ID.drush.in:2222/~/repository.git
$ git push --force origin master
```
Replace ssh://ID@ID.drush.in:2222/~/repository.git with the URL from the middle of the SSH clone URL from the Connection Info popup dialog on your dashboard.


## 5. FAQ

### a. How do I update Drupal Core?

This project will attempt to keep all of your Drupal Core files up-to-date; the 
project [drupal-composer/drupal-scaffold](https://github.com/drupal-composer/drupal-scaffold) 
is used to ensure that your scaffold files are updated every time drupal/core is 
updated. If you customize any of the "scaffolding" files (commonly .htaccess), 
you may need to merge conflicts if any of your modfied files are updated in a 
new release of Drupal core.

Follow the steps below to update your core files.

1. Run `composer update drupal/core --with-dependencies` to update Drupal Core and its dependencies.
1. Run `git diff` to determine if any of the scaffolding files have changed. 
   Review the files for any changes and restore any customizations to 
  `.htaccess` or `robots.txt`.
1. Commit everything all together in a single commit, so `web` will remain in
   sync with the `core` when checking out branches or running `git bisect`.
1. In the event that there are non-trivial conflicts in step 2, you may wish 
   to perform these steps on a branch, and use `git merge` to combine the 
   updated core files with your customized files. This facilitates the use 
   of a [three-way merge tool such as kdiff3](http://www.gitshah.com/2010/12/how-to-setup-kdiff-as-diff-tool-for-git.html). This setup is not necessary if your changes are simple; 
   keeping all of your modifications at the beginning or end of the file is a 
   good strategy to keep merges easy.



### b. Should I commit the scaffolding files?

The [drupal-scaffold](https://github.com/drupal-composer/drupal-scaffold) plugin can download the scaffold files (like
index.php, update.php, …) to the web/ directory of your project. If you have not customized those files you could choose
to not check them into your version control system (e.g. git). If that is the case for your project it might be
convenient to automatically run the drupal-scaffold plugin after every install or update of your project. You can
achieve that by registering `@drupal-scaffold` as post-install and post-update command in your composer.json:

```json
"scripts": {
    "drupal-scaffold": "DrupalComposer\\DrupalScaffold\\Plugin::scaffold",
    "post-install-cmd": [
        "@drupal-scaffold",
        "..."
    ],
    "post-update-cmd": [
        "@drupal-scaffold",
        "..."
    ]
},
```
### c. How can I apply patches to downloaded modules?

If you need to apply patches (depending on the project being modified, a pull 
request is often a better solution), you can do so with the 
[composer-patches](https://github.com/cweagans/composer-patches) plugin.

To add a patch to drupal module foobar insert the patches section in the extra 
section of composer.json:
```json
"extra": {
    "patches": {
        "drupal/foobar": {
            "Patch description": "URL to patch"
        }
    }
}
```


## 6. Troubleshooting
- If you use Mintty as a terminal emulator for Cygwin, you may have some issues when trying to ssh into docker containers. Prefered solution it to use default cmd for Cygwin or git bash if you prefer not to use Cygwin. See the discusion [here](https://github.com/docker/docker/pull/22956)


## 7. Troubleshooting
- Based on [docker4drupal](https://github.com/wodby/docker4drupal)
- Optmized for Origin from [Drupal Composer](https://github.com/drupal-composer/drupal-project)
