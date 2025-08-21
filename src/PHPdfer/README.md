# Phpdfer

**Phpdfer** - this library on PHP, for modify metadata in PFD files.

For start work with this library You must create instance of class `PHPdfer`, and use method `changeMetadata()` for
modify metadata in PDF files. This method accepts three arguments:

1. `$pdf` - path to PDF file in which You need change metadata;
2. `$arMetadata` - array with metadata in which need include in PDF file;
3. `$logMode` - enables the mode in which the output of the CLI command is saved to a log file.

`$arMetadata` can contained next elements:

* `TITLE` - title PDF file;
* `AUTHOR` - author of the PDF file;
* `SUBJECT` - short description content PDF file;
* `KEYWORDS` - keywords describing content PDF file;
* `MOD_DATE` - date modification PDF file;
* `CREATION_DATE` - date creation PDF file;
* `CREATOR` - creator PDF file.

After end work library will create new PDF file with prefix `phpdfer_`, when into it will change metadata specified in
`$arMetadata`.

## Installation

```
composer require jasta-fly/phpdfer
```

## Warning!

For work this library need install `Ghostscript` in You operating system. You can check is this program installed,
running this command in CLI:

```
gs -v
```

if in response You get version installed `Ghostscrip` version:

```
GPL Ghostscript 9.55.0 (2021-09-27)
Copyright (C) 2021 Artifex Software, Inc.  All rights reserved.
```

this means that the program necessary for the library to work is installed, and You may continue use it. If you get next
output:

```php
gs: not found
```

this means that You must install `Ghostscrip` in Your operating system, before continue work.