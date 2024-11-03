Apiary
=======

[![GitHub license](https://img.shields.io/github/license/robojackets/apiary.svg?style=flat-square)](https://raw.githubusercontent.com/robojackets/apiary/master/LICENSE) [![Build](https://github.com/RoboJackets/apiary/actions/workflows/build.yml/badge.svg)](https://github.com/RoboJackets/apiary/actions/workflows/build.yml) [![StyleCI](https://github.styleci.io/repos/92999743/shield?branch=main)](https://github.styleci.io/repos/92999743?branch=main)

Apiary is a tool for managing the membership and operations of RoboJackets, a student organization at Georgia Tech.

## Motivation

This project grew out of frustration with the limitations imposed by Georgia Tech's student organization management system, OrgSync. We found that while it may be an excellent tool for managing small groups, it does not scale very well. To that end, we've tried to design an application that can better support our student organization at its current size, and grow and develop along with our group.

This project has been tailored to support the specific workflow of RoboJackets and is not currently built in a manner that would be easily adaptable to another organization. The decision to limit the scope of this project was made in light of the extensive approvals process to access the amount of student data we currently store. We believe it is unlikely that another org will be able and willing to navigate that process.

## Getting Help
- For development of Apiary, [open a Github issue](https://github.com/RoboJackets/apiary/issues/new) or ask in [#apiary](https://robojackets.slack.com/app_redirect?channel=apiary) on Slack
- For production support of MyRoboJackets, ask in [#it-helpdesk](https://robojackets.slack.com/app_redirect?channel=it-helpdesk) on Slack

## Getting Started with Local Development - Docker

> [!WARNING]
> While this repository itself is open-source, we use several **confidential and proprietary** components which are packed into Docker images produced by this process. Images should **never** be pushed to a public registry.

Install Docker and Docker Compose.

Clone the repository, then run

```sh
docker compose up
```

You will need to provide an `auth.json` file that has credentials for downloading Laravel Nova. Ask in Slack and we can provide this file to you.

## Getting Started with Local Development - Hard Way

If you've never worked with [Laravel](https://laravel.com) before, we recommend watching [the Laravel from Scratch webcast series](https://laracasts.com/series/laravel-from-scratch-2017) to get you up to speed quickly.

Apiary is written entirely in languages that can be run from any operating system; however, support is only provided for Linux environments. All instructions below assume that the user is running on a modern, Debian-based Linux distribution.

For an easier setup, you may wish to use [Laravel Homestead](https://laravel.com/docs/5.6/homestead).
Homestead is a pre-packaged [Vagrant](https://www.vagrantup.com/) box maintained by the Laravel creators designed for Laravel development. It takes care of most of the server configuration so that you can get up and running quickly. **If you opt to use Homestead, all steps listed below should be performed inside the Vagrant box, rather than on the host machine.**

Laravel Mix is used to compile browser assets. Currently, we're concatenating and minifying all of our JS and CSS. This step is also where we compile our SCSS into CSS. In your local dev environment, you should run `npm run dev` the first time you clone the repo and any time the assets change. Laravel Mix is a simple wrapper around webpack, which you really don't need to know about at this point. However, the fact that we use Webpack as a module bundler means that the process to reference JavaScript and CSS is a little bit different. It also means that if you add new CSS or JS files into the project, you need to reference them in [`webpack.mix.js`](webpack.mix.js) to be compiled. See [the relevant Laravel documentation](https://laravel.com/docs/5.4/mix#running-mix) for more details.

Most of the backend code lives under [`app/Http`](/app/Http), with templates under [`resources/views`](/resources/views) and [`resources/js`](/resources/js), but you're encouraged to browse through the project tree to get a better feel of where different components live. The `php artisan` command can generate new classes for you in the correct locations automatically - run it with no parameters to see all the options.

### Install dependencies

This is a pretty conventional Laravel project, so we recommend following [the official guide](https://laravel.com/docs/5.6#installation) to get your workspace set up. At minimum, you will need PHP 7.1.3+, [`composer`](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx), `npm`, and a MySQL 5.7+ compatible database available on your machine.

*If you're using Homestead, this section is taken care of for you out of the box.*

You can install most of the required php extensions with:
```
$ sudo apt install php php-common php-cli php-mysql php-mbstring php-json php-opcache php-xml php-bcmath php-curl php-gd php-zip php-ldap php-uuid
```

On certain Linux flavors, you may need to manually install the PHP `sodium` extension, which is used by Laravel Passport's
dependencies.  Sodium is likely not included on RHEL and has to be manually built and enabled.  For RHEL 8, [this third-party](https://gist.github.com/davidalger/c19a53ed293291ec2e93b5227f9e0a2d#file-install-php-sodium-on-el8-sh)
script (reproduced below in case the Gist disappears, but use at your own risk) has worked to enable the `sodium` extension:

```bash
yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm \
  && yum install -y php-cli libsodium \
  && yum install -y php-pear php-devel libsodium-devel make \
  && pecl channel-update pecl.php.net \
  && pecl install libsodium \
  && yum remove -y php-pear php-devel libsodium-devel make \
  && echo 'extension=sodium.so' > /etc/php.d/20-sodium.ini \
  && php -i | grep sodium
```

For the resume book functionality, you'll also need to install `exiftool` and Ghostscript:
```
$ sudo apt install exiftool ghostscript
```

### Install Redis

Apiary uses Redis for queueing jobs, with Laravel Horizon used to manage them. You should be able to just install Redis and the corresponding PHP extension. Once you get Apiary configured below, you can run `php artisan horizon` to process jobs.

### Install Apiary
Clone the repository onto your local machine:

```
$ git clone https://github.com/RoboJackets/apiary.git
```

If you a member of RoboJackets, reach out in [#apiary](https://robojackets.slack.com/app_redirect?channel=apiary) on Slack and ask for a copy of a mostly configured `.env` file.

Copy the example environment file to configure Apiary for local development:

```
$ cp .env.example .env
```

For a basic development environment, you'll need to modify the following settings:

| Key                                    | Value                                                                                                                                                                          |
|----------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| APP_URL                                | Set to the hostname of your local dev environment, ex. `apiary.test`.                                                                                                          |
| DB_*                                   | Set appropriately for your database.                                                                                                                                           |
| MAIL_*                                 | Mailgun is the suggested mail driver, but you can easily configure Mailtrap or a local mail server referencing the [Laravel documentation](https://laravel.com/docs/5.4/mail). |
| CAS_HOSTNAME                           | FQDN of the CAS server to use, ex. login.gatech.edu                                                                                                                            |
| CAS_REAL_HOSTS                         | Should match CAS_HOSTNAME                                                                                                                                                      |
| CAS_LOGOUT_URL                         | CAS logout URL, ex. https://login.gatech.edu/cas/logout                                                                                                                        |
| CAS_CLIENT_SERVICE                     | Base URL for your local instance, e.g., localhost:PORT or possibly something like https://apiary-local.robojackets.org, depending on your local configuration                  |
| CAS_MASQUERADE                         | If set, bypasses the CAS authentication flow and authenticates as the specified username.                                                                                      |
| CAS_MASQUERADE_gtGTID                  | GTID number for the masquerading user (90xxxxxxx)                                                                                                                              |
| CAS_MASQUERADE_email_primary           | Primary email address for the masquerading user                                                                                                                                |
| CAS_MASQUERADE_givenName               | Given Name (First Name) for the masquerading user                                                                                                                              |
| CAS_MASQUERADE_sn                      | SN (Second/Last Name) for the masquerading user                                                                                                                                |
| PASSPORT_PERSONAL_ACCESS_CLIENT_ID     | Client ID from running `php artisan passport:client --personal` used to generate OAuth2 Personal Access Tokens                                                                 |
| PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET | Client secret from running `php artisan passport:client --personal` used to generate OAuth2 Personal Access Tokens                                                             |

#### Installing dependencies

```
$ composer install && npm install
```

Please note that we are using [Laravel Nova](https://nova.laravel.com/) for some admin pages. You will be prompted for credentials when running Composer if an update to Nova is required. Get in touch with us in [#apiary](https://robojackets.slack.com/app_redirect?channel=apiary) when this happens.

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

Setup Laravel Passport:

```
$ php artisan passport:keys
```

*(Optional - Required to create Personal Access Tokens)* Create OAuth2 Personal Access Client: Add the client ID and
secret created to the `PASSPORT_PERSONAL_ACCESS_CLIENT_ID` and `PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET` environment
variables.

```
$ php artisan passport:client --personal
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

### `composer run test`

Use this command to run unit/feature tests locally. You shouldn't need to modify `.env.testing`. If you add migrations,
there's no need to dump the schema again; the migrations will be run as part of the tests. (It's possible to squash the
migrations again if the tests take too long, but simply dumping the schema is insufficient.)

If you try to run PHPUnit directly, you may get various "file not found" errors since the `composer run test` command
runs extra steps before the tests are run.

### Running style checks locally

(If you're using Homestead, run these commands inside the VM in your `apiary` directory.)

`vendor/bin/phpcs <file>`

Run CodeSniffer style checks locally.

`vendor/bin/pint <file>`

Runs Pint style checks locally and applies fixes.

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

### Horizon Configuration

Review the Laravel documentation on deploying Horizon to a production environment.

Also be sure to set up a cron job to run scheduled tasks - Horizon uses this to keep track of statistics.

# Security reporting

Any security issues with the Apiary code or any RoboJackets-managed Apiary deployment (*.robojackets.org) should be reported to [apiary@robojackets.org](mailto:apiary@robojackets.org). This will notify our development and operations teams and you should receive a response within 8 business hours Eastern Time.
