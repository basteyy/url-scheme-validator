# UrlSchemeValidator

This is a small PHP-Class which can help you to validate a scheme of a url. 

## Installation

````text
composer require basteyy/url-scheme-validator
````

## Usage

### Example 1
```php
<?php
$validator = new \basteyy\UrlSchemeValidator\UrlSchemeValidator('http://www.example.com');
echo $validator->getScheme(); // Returns http
```

### Example 2
```php
<?php
$validator = new \basteyy\UrlSchemeValidator\UrlSchemeValidator('//www.example.com');
echo $validator->getScheme(); // Returns http
echo $validator->getUrl(); // Returns http://www.example.com
```

### Example 3
```php
<?php
$validator = new \basteyy\UrlSchemeValidator\UrlSchemeValidator('//www.example.com:443');
echo $validator->getScheme(); // Returns https
echo $validator->getUrl(); // Returns https://www.example.com
```

### Example 4
```php
<?php
$validator = new \basteyy\UrlSchemeValidator\UrlSchemeValidator;
$validator->setUrl('www.example.com');
$validator->setUrl('example.com:443');
$validator->setUrl('https://de.wikipedia.org');

$validator->validateAll(); // Validates all Urls in a rush

echo $validator->getScheme('https://de.wikipedia.org'); // Returns https
echo $validator->getScheme('example.com:443'); // Returns https
echo $validator->getScheme('www.example.com'); // Returns http

echo $validator->getUrl('https://de.wikipedia.org'); // Returns https://de.wikipedia.org
echo $validator->getUrl('example.com:443'); // Returns https://example.com
echo $validator->getUrl('www.example.com'); // Returns http://www.example.com

var_dump($validator->getUrls()); 
/* Returns an array : 
 [
    'https://de.wikipedia.org' => [
        'scheme' => 'https', 
        'url' => 'https://de.wikipedia.org'
    ],
    'example.com:443' => [
        'scheme' => 'https', 
        'url' => 'https://example.com:443'
    ],
    'www.example.com' => [
        'scheme' => 'http', 
        'url' => 'http://www.example.com'
    ],
]
 */
```

## Options and more features

See the following code for the options.

```php
<?php
$validator = new \basteyy\UrlSchemeValidator\UrlSchemeValidator('//www.example.com');

// Change the Default Scheme
$validator->setDefaultScheme('file');
echo $validator->getUrl(); // Returns file://www.example.com

// Validate direct for web (only http and https)
var_dump($validator->isWebScheme()); // Returns false in this case (case now its file)

```
