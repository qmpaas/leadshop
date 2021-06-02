Yii Framework 2 imagine extension Change Log
================================================

2.3.0 December 23, 2020
-----------------------

- Enh #28: `Image::thumbnail()` now accepts flag `ImageInterface::THUMBNAIL_FLAG_UPSCALE` to allow thumbnail upscaling. Since this option is only supported in imagine/imagine v1.0.0 or later, support for older version was dropped (yuniorsk)


2.2.0 June 04, 2019
-------------------

- Enh #54: Updated imagine/imagine version constraint to allow using 1.1.0 (samdark)


2.1.1 February 22, 2018
-----------------------

- Bug #35: Fixed incorrect `ceil()` call (Svyatoslav-S, samdark)
- Enh #22: Added method `Image::resize()` to ease resizing images to fit certain dimensions (Renkas)
- Enh #46: Updated Imagine dependency to include versions 0.7.x (klimov-paul)


2.1.0 November 3, 2016
----------------------

- Enh #2: ImageInterface objects are now supported as image files (samdark)
- Enh #11: Resources are now supported as image files (samdark)
- Enh #20: Upgraded the imagine library from 0.5.x to 0.6.x.
      In order to upgrade to 0.6.x the color behavior had to be
      changed. In addition a new `autorotate` method has been implemented
      in order to rotate images based in the EXIF informations provided
      inside the image (nadar)

2.0.4 September 4, 2016
-----------------------

- Enh #3: `Image::thumbnail()` could now automatically calculate thumbnail dimensions based on aspect ratio of original
  image if only width or only height is specified. `Image::$thumbnailBackgroundColor` and
  `Image::$thumbnailBackgroundAlpha` are introduced to be able to configure fill color of thumbnails (HaruAtari, samdark)

2.0.3 March 01, 2015
--------------------

- no changes in this release.


2.0.2 January 11, 2015
----------------------

- no changes in this release.


2.0.1 December 07, 2014
-----------------------

- no changes in this release.


2.0.0 October 12, 2014
----------------------

- no changes in this release.


2.0.0-rc September 27, 2014
---------------------------

- no changes in this release.


2.0.0-beta April 13, 2014
-------------------------

- Initial release.
