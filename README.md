About this theme
================

This is a temporary theme, it was build based on the Boost theme. The aim of this project is to upgrade Boost to Bootstrap 4 Stable

How to use:
-----------

1. Install this theme in your Moodle '/themes' folder.

2. Enable theme in your theme selector


Contents:
---------

The SCSS folder contains the BS4 sass. Some sass has been commented out, the Moodle SASS PHP compiler does not support things that start with "@supports".. which is ironic :)

This project contains a Gruntfile.js and a package.json to enable compiling sass to scc. This reduces time for development and gives better error messages. Idealy I would like a better sass compiler in mdl but that is something else to worry about later.

Most of the original Moodle sass has been included. Only tiny fixes were made to make sure grunt can compile this.

Todo's
------

Write AMD modules based on the original Bootstrap JS. Then write a readme on how it was done. BS4 now includes Popper which is also included in the user tours. So it is already in core. It could simply be included in the theme again.

Search and find all uses of ".card" in Moodle. Think of something better or just improve the design.

Check all grids. This all uses Flexbox now and might break some mdl pages, especially when the original grid has addes MDL css

Fix dropdowns, use BS dropdowns instead of the ones triggered by MDL core JS.


Updating Bootstrap JS files
---------------------------

The process outlined in `theme/boost/readme_moodle.txt` requires a couple of tweaks to work with v4.0.0 of Bootstrap. Updated package versions based on the Bootstrap package.json. Run the follwing inside the cloned Bootstrap repository:

```
$ npm install @babel/cli@7.0.0-beta.37 @babel/preset-env@7.0.0-beta.37 babel-plugin-transform-es2015-modules-amd @babel/plugin-proposal-object-rest-spread
$ ./node_modules/@babel/cli/bin/babel.js --presets @babel/preset-env --plugins transform-es2015-modules-amd,@babel/plugin-proposal-object-rest-spread -d out/ /path/to/your/moodle/dirroot/theme/reboost/amd/src
```

Popper JS
---------
Bootstrap 4 has a peer dependency on [Popper](https://popper.js.org). Note that while popper is included in core `admin/tool/usertours/amd/src/popper.js` but an older version.
```
$ git clone https://github.com/FezVrasta/popper.js.git
$ git checkout 1.12.9 # or whatever the latest release tag is
```
Copy `dist/popper.js` to `path/to/your/moodle/dirroot/theme/reboost/amd/src` then run the amd build task.

Finally update the theme `thirdpartylibs.xml` to reflect the version of Popper.