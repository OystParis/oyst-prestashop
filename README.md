# Oyst PrestaShop module

This module allows to connect a prestaShop store to Oyst.

You will be able to synchronise your catalog and use 2 different payments as:
 - OneClick
 - FreePay
 
## Install

### Requirements

To install this module, you must have a server running a minimal version of PHP in 5.3.
This module use composer and dependencies, you have to install this binary following this website:

https://getcomposer.org/

### Process

First, clone the module inside your PrestaShop `modules` folder. Go inside using your terminal and run

`composer install`

You are now ready to install it on the back-office.

## Code information

Since the OneClick development, we tried to focus on the best guidelines and best practices used by different framework (cf Symfony).

The source code is now put inside the `src/` folder. You will find:
 - Controller
 - Factory
 - Repository
 - Service
 - Transformer

We didn't have the time to install a dependency manager, so we use different Factory to avoid redundant code and centralise
injections. All the requirements for an on object is made inside these Factories.


Then all the logic is put inside the Service folder.

The transformers are used to transform an Entity from PrestaShop to Oyst.

And finally Controller are used to simply control a coming request.

## Oyst library

As I said, the module use composer and we use it to get the Oyst Library.
You can find any information here : 

https://github.com/oystparis/oyst-php
