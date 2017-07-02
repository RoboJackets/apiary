# Apiary Contributors Guide

Welcome to the Apiary project, and thanks for your interest in contributing! This document is intended to help you get started developing new functionality and bug fixes for the project.

## Reporting issues or proposing features
Please submit bug reports or feature requests to this repository as GitHub issues. Provide as much detail as possible.

## Motivation
This project grew out of frustration with the limitations imposed by Georgia Tech's student organization management system, OrgSync. We found that while it may be an excellent tool for managing small groups, it does not scale very well. To that end, we've tried to design an application that can better support our student organization at its current size, and grow and develop along with our group.

While this is very directly a RoboJackets project, and designed for our specific use case, we've endeavored to make it generic enough for use by any student group at Georgia Tech facing similar challenges at our scale.

## How to get started
If you've never worked with [Laravel](https://laravel.com) before, we recommend watching [the Laravel from Scratch webcast series](https://laracasts.com/series/laravel-from-scratch-2017) to get you up to speed quickly.

This is a pretty conventional Laravel project, so we recommend following [the official guide](https://laravel.com/docs/5.4#installation) to get your workspace set up. At minimum, you will need PHP, `npm`, and a MySQL-compatible database available on your machine.

`npm` is used to compile browser assets - you should run `npm run dev` the first time you clone the repo and any time the assets change.

Most of the backend code lives under [`app/Http`](/app/Http), with templates under [`resources/views`](/resources/views), but you're encouraged to browse through the project tree to get a better feel of where different components live. The `php artisan` command can generate new classes for you in the correct locations automatically - run it with no parameters to see all the options.

As mentioned above, we use GitHub issue tracking to manage the project. Check out the Issues tab to see outstanding work, and feel free to ask questions on any existing issues if they're not clear. You can also discuss your own ideas here with other contributors!
