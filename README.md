# Argile Honeypot
---
![Packagist Version](https://img.shields.io/packagist/v/eliepse/argile-honeypot)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/eliepse/argile-honeypot)
![Packagist License](https://img.shields.io/packagist/l/eliepse/argile-honeypot)
![Framework: Argile](https://img.shields.io/badge/framwork-Argile-lightgrey)

*Argile Honeypot* is a simple protection against robot spammers for your public forms.
__It can be used for various project__ but has been made to work with the simple *Argile* framework. 

## How does it work?
The honeypot consist on hashing input names in the html response (handled by `HoneypotResponseMiddleware`), and adding fake inputs (honeypots) with the original
names. Robot spammers will likely fill all fields, espacially the ones that will looks like real fields.

The middleware `HoneypotRequestMiddleware` check the POST request from the client and check if fake fields has been
filled. If so, the request is blocked and an 403 response is sent. It also check if the form has been filled quicker
than a certain delay (default to 5 seconds).

## How to use it?
First install the package by adding it to your composer.json or requiring it through the command line.
```shell script
$ composer require eliepse/argile-honeypot
```
You also have to add the css class "onipat" to hide the fake inputs. A css file is available at 
`/resources/css/honeypot.css`
```css
.onipat {
    opacity: 0;
    position: absolute;
    top: 0;
    left: 0;
    height: 0;
    width: 0;
    z-index: -1;
}
```

### 1. Preparing the form
Then, add the `HoneypotResponseMiddleware` to the route containing the form to protect. As an example:
```php
$router->get('/', WelcomeController::class)
	->addMiddleware(new HoneypotResponseMiddleware());
```
In order for the middlware to work, you have to indicate some common fields copy as honeypot. Simply add the prefix
`honeypot:` to the name of the input. Example:
```html
<input type="email" name="honeypot:email" placeholder="Type your email here...">
```
The middleware will automatically change the name of the real field with a hash, and generate a fake field. 
```html
<!-- The original field with hashed name -->
<input type="email" name="jh87dd4h88rjk8h448dfa" placeholder="Type your email here...">
<!-- The fake field generated -->
<input type="email" name="email" class="onipat" autocomplete="off" tabindex="-1">
```

### 2. Handle the POST request
Now, we have to handle the request to block spams and change the inputs names to the original ones (so the rest of 
the code doesn't have to handle hashed names). Simply add the request middleware to your route as below.
```php
$router->post("/contact", [SendContactMailController::class, 'send'])
	->addMiddleware(new HoneypotRequestMiddleware());
```

### License
This package is under the [MIT license](./LICENSE).

It is maintained by [Élie Meignan](https://github.com/Eliepse).