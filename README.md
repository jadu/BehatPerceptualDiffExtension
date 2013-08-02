# BehatPerceptualDiffExtension

## Getting started

### Running everything locally (Mac OS X specific)

[Download Selenium2 standalone server package](https://code.google.com/p/selenium/downloads/detail?name=selenium-server-standalone-2.33.0.jar) and run the JAR

```sh
java -jar selenium-server-standalone-2.33.0.jar
```

Install [ImageMagick](http://www.imagemagick.org/script/binary-releases.php) with [Homebrew](http://brew.sh/)

```sh
brew install imagemagick
```

Install Composer dependencies

```sh
composer install --dev
```

Run the example tests

```sh
cd example && ../vendor/bin/behat --format=pretty,html --out=,report.html
```
