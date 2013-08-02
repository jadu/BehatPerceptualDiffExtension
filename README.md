# BehatPerceptualDiffExtension

## Getting started

### Running everything locally (Mac OS X specific)

1. [Download Selenium2 standalone server package](https://code.google.com/p/selenium/downloads/detail?name=selenium-server-standalone-2.33.0.jar) and run the JAR

```sh
java -jar selenium-server-standalone-2.33.0.jar
```

1. Install [ImageMagick](http://www.imagemagick.org/script/binary-releases.php)

```sh
brew install imagemagick
```

1. Install Composer dependencies

```sh
composer install --dev
```

1. Run the example tests

```sh
cd example && ../vendor/bin/behat --format=pretty,html --out=,report.html
```
