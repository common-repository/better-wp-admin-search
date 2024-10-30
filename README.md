# Better WP-Admin Search

Headline ...

## Requirements

- WordPress
- Node / NPM
- Composer

Tested with PHP 7.0

## Installation

- Install using one our pre-zipped releases
  \- **OR** -
- Clone repo into `wp-content/plugins`
- Run `npm install && composer install && npm run build`
- Activate plugin via WordPress admin

## Plugin Build

Need to build the plugin to install on a WordPress site? run `npm run build:plugin` and follow the prompts

## Development

Follow Installation instructions then run `npm run start`

## Unit Testing

We use PHPUnit and Composer to run our unit tests for PHP. To initialize your environment you'll need to run the following:

    npm install
    npm run test

    composer install
    bin/install-wp-tests.sh bwpas-test root '' 127.0.0.1
    vendor/bin/phpunit

    https://phpunit.de/getting-started/phpunit-7.html