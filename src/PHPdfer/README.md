
# PHPdfer

**PHPdfer** is a PHP library for modifying metadata in PDF files.

To start using this library, create an instance of the `PHPdfer` class and use the `changeMetadata()` method to modify metadata in PDF files. This method accepts three arguments:

1. `$pdf` - the path to the PDF file in which you want to change the metadata
2. `$arMetadata` - an array containing the metadata to include in the PDF file
3. `$logMode` - enables a mode in which the output of the CLI command is saved to a log file

`$arMetadata` can contain the following elements:

* `TITLE` - the title of the PDF file
* `AUTHOR` - the author of the PDF file
* `SUBJECT` - a short description of the PDF file's content
* `KEYWORDS` - keywords describing the PDF file's content
* `MOD_DATE` - the modification date of the PDF file
* `CREATION_DATE` - the creation date of the PDF file
* `CREATOR` - the creator of the PDF file

After processing, the library will create a new PDF file with the prefix `phpdfer_`, in which the metadata specified in `$arMetadata` will be updated.

## Installation

```
composer require jasta-fly/phpdfer
```

## Warning!


For this library to work, you need to have `Ghostscript` installed on your operating system. You can check if it is installed by running this command in your terminal:

```
gs -v
```

If you see a version number in the response, for example:

```
GPL Ghostscript 9.55.0 (2021-09-27)
Copyright (C) 2021 Artifex Software, Inc.  All rights reserved.
```

this means that the program required for the library to work is installed, and you may continue to use it. If you get the following output:

```php
gs: not found
```

this means that you must install `Ghostscript` on your operating system before continuing.