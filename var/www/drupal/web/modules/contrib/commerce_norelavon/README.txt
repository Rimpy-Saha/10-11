README file for Commerce Elavon

CONTENTS OF THIS FILE
---------------------
* Introduction
* Requirements
* Configuration
* How It Works
* Note

INTRODUCTION
------------
Integrate Elavon Virtual Merchant payment service Converge payment gateway
with Commerce module.
We use the Offsite payment options, provided by elavon as no credit card information is handled in our website.
* Offsite Redirect
   - Add a new offsite redirect payment. It only supports credit card transaction.
     No credit card number will ever need to pass through the web server.

REQUIREMENTS
------------
This module requires the following:
* Submodules of Drupal Commerce package (https://drupal.org/project/commerce)
  - Commerce core
  - Commerce Payment (and its dependencies)
* Elavon Merchant account (https://www.elavon.com/)

CONFIGURATION
-------------
* Create a new Elavon payment gateway.
  Administration > Commerce > Configuration > Payment gateways > Add payment gateway

HOW IT WORKS
------------
* General considerations:
  - The store owner must have a Elavon merchant account.

NOTE
----
Credit card brands currently configured : "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa"
