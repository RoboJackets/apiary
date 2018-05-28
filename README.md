Apiary
=======

[![GitHub license](https://img.shields.io/github/license/robojackets/apiary.svg?style=flat-square)](https://raw.githubusercontent.com/robojackets/apiary/master/LICENSE) [![StyleCI badge](https://styleci.io/repos/92999743/shield)](https://styleci.io/repos/92999743) [![Documentation generator status](https://img.shields.io/circleci/project/github/RoboJackets/apiary.svg?style=flat-square)](https://circleci.com/gh/RoboJackets/apiary)

Apiary is a tool for managing the membership and operations of RoboJackets, a student organization at Georgia Tech.

## Motivation

This project grew out of frustration with the limitations imposed by Georgia Tech's student organization management system, OrgSync. We found that while it may be an excellent tool for managing small groups, it does not scale very well. To that end, we've tried to design an application that can better support our student organization at its current size, and grow and develop along with our group.

This project has been tailored to support the specific workflow of RoboJackets and is not currently built in a manner that would be easily adaptable to another organization. The decision to limit the scope of this project was made in light of the extensive approvals process to access the amount of student data we currently store. We believe it is unlikely that another org will be able and willing to navigate that process.

## Getting Help
- For development of Apiary, [open a Github issue](https://github.com/RoboJackets/apiary/issues/new) or ask in #apiary on Slack
- For production support of MyRoboJackets, ask in #it-helpdesk on Slack

## Getting Started with Local Development

If you've never worked with [Laravel](https://laravel.com) before, we recommend watching [the Laravel from Scratch webcast series](https://laracasts.com/series/laravel-from-scratch-2017) to get you up to speed quickly.

Apiary is written entirely in languages that can be run from any operating system; however, support is only provided for Linux environments. All instructions below assume that the user is running on a modern, Debian-based Linux distribution.

For an easier setup, you may wish to use [Laravel Homestead](https://laravel.com/docs/5.6/homestead).
Homestead is a pre-packaged [Vagrant](https://www.vagrantup.com/) box maintained by the Laravel creators designed for Laravel development. It takes care of most of the server configuration so that you can get up and running quickly. If you opt to use Homestead, all steps listed below should be performed **inside the Vagrant box**, rather than on the host machine.

Laravel Mix is used to compile browser assets. Currently, we're concatenating and minifying all of our JS and CSS. This step is also where we compile our SCSS into CSS. In your local dev environment, you should run `npm run dev` the first time you clone the repo and any time the assets change. Laravel Mix is a simple wrapper around webpack, which you really don't need to know about at this point. However, the fact that we use Webpack as a module bundler means that the process to reference JavaScript and CSS is a little bit different. It also means that if you add new CSS or JS files into the project, you need to reference them in [`webpack.mix.js`](webpack.mix.js) to be compiled. See [the relevant Laravel documentation](https://laravel.com/docs/5.4/mix#running-mix) for more details.

Most of the backend code lives under [`app/Http`](/app/Http), with templates under [`resources/views`](/resources/views) and [`resources/assets/js`](/resources/assets/js), but you're encouraged to browse through the project tree to get a better feel of where different components live. The `php artisan` command can generate new classes for you in the correct locations automatically - run it with no parameters to see all the options.

### Install dependencies

This is a pretty conventional Laravel project, so we recommend following [the official guide](https://laravel.com/docs/5.6#installation) to get your workspace set up. At minimum, you will need PHP 7.1.3+, [`composer`](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx), `npm`, and a MySQL 5.7+ compatible database available on your machine.

*If you're using Homestead, this section is taken care of for you out of the box.*

You can install all of the required php extensions with:
```
$ sudo apt install php php-common php-cli php-mysql php-mbstring php-json php-opcache php-xml
```

#### Database Encryption

Due to the nature of the data stored in certain tables in Apiary, some tables require encryption. This is implemented with MySQL's [Keyring](https://dev.mysql.com/doc/refman/5.7/en/keyring-installation.html).
For migrations to run successfully, you must also have a proper keyring set up in your development and production environments.

To enable the Keyring functionality, edit your `my.cnf` as follows, then restart MySQL:  

    [mysqld]
    early-plugin-load=keyring_file.so

To check if the Keyring plugin was enabled successfully, run the following command from a MySQL command line.

    mysql> SELECT PLUGIN_NAME, PLUGIN_STATUS
           FROM INFORMATION_SCHEMA.PLUGINS
           WHERE PLUGIN_NAME LIKE 'keyring%';
    +--------------+---------------+
    | PLUGIN_NAME  | PLUGIN_STATUS |
    +--------------+---------------+
    | keyring_file | ACTIVE        |
    +--------------+---------------+

Further documentation about MySQL Keyring can be found in [the MySQL documentation](https://dev.mysql.com/doc/refman/5.7/en/keyring-installation.html).

### Install Apiary
Clone the repository onto your local machine:

```
$ git clone https://github.com/RoboJackets/apiary.git
```

If you a member of RoboJackets, reach out in #apiary on Slack and ask for a copy of a mostly configured `.env` file.

Copy the example environment file to configure Apiary for local development:

```
$ cp .env.example .env
```

For a basic development environment, you'll need to modify the following settings:

| Key                          | Value                                                                                                                                                                          |
|------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| APP_URL                      | Set to the hostname of your local dev environment, ex. `apiary.test`.                                                                                                          |
| DB_*                         | Set appropriately for your database.                                                                                                                                           |
| MAIL_*                       | Mailgun is the suggested mail driver, but you can easily configure Mailtrap or a local mail server referencing the [Laravel documentation](https://laravel.com/docs/5.4/mail). |
| CAS_HOSTNAME                 | FQDN of the CAS server to use, ex. login.gatech.edu                                                                                                                            |
| CAS_REAL_HOSTS               | Should match CAS_HOSTNAME                                                                                                                                                      |
| CAS_LOGOUT_URL               | CAS logout URL, ex. https://login.gatech.edu/cas/logout                                                                                                                |
| CAS_MASQUERADE               | If set, bypasses the CAS authentication flow and authenticates as the specified username.                                                                                      |
| CAS_MASQUERADE_gtGTID        | GTID number for the masquerading user (90xxxxxxx)                                                                                                                              |
| CAS_MASQUERADE_email_primary | Primary email address for the masquerading user                                                                                                                                |
| CAS_MASQUERADE_givenName     | Given Name (First Name) for the masquerading user                                                                                                                              |
| CAS_MASQUERADE_sn            | SN (Second/Last Name) for the masquerading user                                                                                                                                |


#### Installing dependencies

```
$ composer install && npm install
```

You will need to run these commands again in the future if there are any changes to required packages.

### Before Your First Run

Generate an application key (run this only once for initial setup.)

```
$ php artisan key:generate
```

Run database migrations to set up tables (run this for initial setup and when any new migrations are added later.)

```
$ php artisan migrate
```

Seed the database tables with base content (run this only once for initial setup.)

```
$ php artisan db:seed
```

Generate static assets (run this every time Vue or JS files are edited.)

```
$ npm run dev
```

### Starting the Local Development Server

You can use `php`'s built in development web server to easily test your application without needing to configure a production-ready web server, such as `nginx` or `apache`. To start this server:

```
$ php artisan serve
```

This is not necessary if you are using Homestead - you should use the configured hostname from `Homestead.yaml` instead, ex. `apiary.test`.

## Tips for Development

### `npm run watch`
Automatically rebuilds your front-end assets whenever the files change on disk. It's the same as running `npm run dev`. Some platforms will need `npm run watch-poll` to see changes to files, rather than just `watch`.

### `php artisan tinker`

Tinker allows you to interact with Apiary on the command line including the Eloquent ORM, jobs, events, and more. A good introduction to Tinker can be found [here](https://scotch.io/tutorials/tinker-with-the-data-in-your-laravel-apps-with-php-artisan-tinker).

## Moving to Production

### `.env`

There are a few additional changes needed to `.env` when moving to production.

| Key                          | Value                                                                                                                                                                          |
|------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| APP_NAME                     | MyRoboJackets (or other as you see fit)                                                                                                                                        |
| APP_ENV                      | production                                                                                                                                                                     |
| APP_DEBUG                    | false                                                                                                                                                                          |
| APP_LOG_LEVEL                | info (or other as you see fit)                                                                                                                                                 |
| APP_URL                      | DNS hostname for production environment                                                                                                                                        |
| GA_UA                        | Google Analytics identifier, if desired                                                                                                                                        |
| SQUARE_*                     | Square API credentials (Get these from the Square Developer Dashboard)   
