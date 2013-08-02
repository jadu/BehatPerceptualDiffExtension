# BehatPerceptualDiffExtension

A perceptual diff extension for [Behat](http://behat.org/) to highlight **visual regressions** in web applications.

After each step a screenshot is taken and compared with the screenshot from a previous baseline test run. 
Any differences will be highlighted and output in the HTML report for inspection.

For more on the benefits of perceptual diffs see [@bslatkin](http://github.com/bslatkin)'s great video:

http://www.youtube.com/watch?v=UMnZiTL0tUc

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

Install [Composer](http://getcomposer.org/doc/00-intro.md#installation-nix) dependencies

```sh
composer install --dev
```

Run the example tests

```sh
cd example && ../vendor/bin/behat --format=pretty,html --out=,report.html
```

## Acknowledgements

* [Brett Slatkin](http://github.com/bslatkin) for his brilliant presentation on how they use perceptual diffs at Google.
* [Pete Hunt](http://github.com/petehunt/) for his [Huxley](http://github.com/facebook/huxley) tool that also inspired this project.

## Credits

* [Tom Graham](http://github.com/noginn) - Project lead
