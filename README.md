BackOffice Path
===============
 
Customize the BackOffice path of your Thelia (URL to access to the administration panel).

How to install
--------------

### Download

#### With composer

Require it by adding the following lines to your `composer.json`

```json
"require": {
    "thelia/backofficepath-module": "~0.1"
}
```

#### As Git Submodule

Get it as a submodule by executing the following command lines :

```bash
$ cd /path-to-thelia
$ git submodule add https://github.com/thelia-modules/Keyword.git local/modules/Keyword
```

#### Manually

-   Download the zip archive and extract it
-   Copy the module into `<path-to-thelia>/local/modules/` directory and be sure that the name of the module is `BackOfficePath`

### Activation

-   Go to the modules's list of your Thelia administration panel (with the default URL)
-   Find the *BackOfficePath* module
-   Activate it.

Why use it ?
------------

For your **security**.

It's recommended to change the default URL to any CMS's administration panel.  
This is a quick way to add an extra layer of security for your website.

Usage
----- 

-   Go to the modules's list of your Thelia administration panel (with the default URL)
-   Find the *BackOfficePath* module
-   Click on *Configure* button
-   Set a new custom prefix to access to your Thelia BackOffice.

### Options

#### Use also the default prefix

This option allow you to still use the default prefix.  
Of course, the new path is also activated.

>   If this option isn't checked : you can access to the BackOffice only with the new path.