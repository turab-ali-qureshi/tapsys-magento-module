# Mage2 Module Tapsys Checkout

    ``tapsys/module-checkout``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
 - Allows Magento2 merchants to integrate Tapsys Secure Checkout on their payment page.
 - Enables administration configuration options to store credentials and set module operational status.

## Installation
\* = in production please use the `--keep-generated` option

 - Unzip the zip file in `app/code/Tapsys/Checkout`
 - Enable the module by running `php bin/magento module:enable Tapsys_Checkout`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

## Configuration

 - tapsys - payment/tapsys/*


## Specifications

 - Payment Method
	- tapsys


## Attributes

 - Merchant ID
 - Enable SandBox Mode
 - Production (Sandbox) Key
 - Production (Sandbox) Secret

