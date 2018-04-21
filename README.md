Apiary
=======

[![GitHub license](https://img.shields.io/github/license/robojackets/apiary.svg?style=flat-square)](https://raw.githubusercontent.com/robojackets/apiary/master/LICENSE) [![StyleCI badge](https://styleci.io/repos/92999743/shield)](https://styleci.io/repos/92999743) [![Documentation generator status](https://img.shields.io/circleci/project/github/RoboJackets/apiary.svg?style=flat-square)](https://circleci.com/gh/RoboJackets/apiary)

Apiary is a tool for managing the membership and operations of RoboJackets, a student organization at Georgia Tech.

## Motivation

This project grew out of frustration with the limitations imposed by Georgia Tech's student organization management system, OrgSync. We found that while it may be an excellent tool for managing small groups, it does not scale very well. To that end, we've tried to design an application that can better support our student organization at its current size, and grow and develop along with our group.

This project has been tailored to support the specific workflow of RoboJackets and is not currently built in a manner that would be easily adaptable to another organization. The decision to limit the scope of this project was made in light of the requirement that CAS login sends GTIDs, which required an extensive approvals process. It is the belief of the project maintainers that it is exceptionally unlikely that another org will be able to navigate the process of gaining access to the Georgia Tech student data that Apiary requires to be useful.

## Getting Help
- For development of Apiary: Raise a Github issue or asking [#apiary]()
- For production support of MyRoboJackets: Ask in [#it-helpdesk]()

## Getting Started with Local Development

See [CONTRIBUTING.md]()

Apiary is written entirely in languages that can be run from any operating system, however, support is only provided for Linux environments. All instructions below assume that the user is running on a modern, Debian-based linux distribution.

It is also known to run well in Homestead on Macs.

### Install dependencies

This is a pretty conventional Laravel project, so we recommend following [the official guide](https://laravel.com/docs/5.6#installation) to get your workspace set up. At minimum, you will need PHP 7.1.3+, [`composer`](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx), `npm`, and a MySQL 5.7+ compatible database available on your machine.

You can install all of the required php extensions with:
`$ sudo apt install php-common php-mbstring php-xml`

### Install Apiary

Clone the repository onto your local machine:

`$ git clone https://github.com/RoboJackets/apiary.git`

If you a member of RoboJackets, reach out in [#apiary]() and ask for a copy of a mostly configured `.env` file.

Copy the example environment file to configure Apiary for local development:

`$ cp .env.example .env`

and edit the file to add credentials for your database and mail driver. Mailgun is the suggested mail driver, but you can easily configure Mailtrap or a local mail server using the Laravel instructions.

If you are running Apiary locally, you will need to additionally set values for all `CAS_MASQUERADE` keys in the config file.

#### Installing dependencies

Run composer and npm install

`$ composer install && npm install`

### Before Your First Run

Generate an application key (Run this only once)

`$ php artisan key:generate`

Run database migrations to set up tables (Run this after any new migrations are added)

`$ php artisan migrate`

Generate static assets (Run this every time Vue or JS files are edited)

`$ npm run dev`

### Starting the Local Development Server

You can use php's built in development web server to easily test your application without needing to configure a production-ready web server, such as nginx or Apache. To start this server:

`$ php artisan serve`

## Tips for Development

TODO
artisan tinker
npm run watch
cache clearing

## Moving to production

TODO
env file
db encryption
deploy scripts