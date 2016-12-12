# http-diff

http-diff is command line tool for comparing http responses from two servers. 

## Installation

Installing `http-diff` in your project can be done with composer.

`composer require tomgud/http-diff`

You can also clone this repository and run `composer install` to install dependencies.

## Requirements

Version 0.1 runs on PHP 5.6 and will be the only version running on 5.6. The `http-diff` executable needs to be 
placed in a sub, parent or the vendor folder to include the `autoload.php` file. 

## Running

You can define a specification of your suite of diffs in a json or yml file. Take a look into the examples/ folder,
to get inspiration for your specification.
