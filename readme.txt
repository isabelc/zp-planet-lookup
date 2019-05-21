=== ZodiacPress Planet Lookup ===
Contributors: isabel104
Requires at least: 4.3
Tested up to: 5.2
Stable tag: 1.3.2
License: GNU Version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extension for ZodiacPress to lookup the sign of one planet or point.

== Changelog ==

= 1.3.2 =

* Fix - Fixed a bug with the updater. Nothing changed in the code base.

= 1.3.1 =

* Tweak - updated plugin URI.

= 1.3 =

* New - add setting to the Planet Lookup tab to allow unknown birth time.
* API - zppl_remove_report_header() is no longer needed because report header is not included in custom reports by default anymore.

= 1.2 =

* New - Birth time is no longer required for the Moon Lookup. This is because the Moon doesn't change signs every single day, so it's possible to know the Moon on sign some days, even without a birth time. Worry free accuracy: If a person looks up the Moon sign, and checks the No Birth time box, and the Moon changes sign on that day, they will be told that they need to enter their birth time.
* Fix - The {degree} should not display anything if the birth time is unknown. This applies to any planets that don't require a birth time.

= 1.1.1 =

* Tweak - The 'Look Up Another' link will now be displayed on its own line.

= 1.1 =

* New - Added a new template tag to customize the output of the Planet Lookup. The new tag is {degree}, and it displays the exact degree, minute, second, and the sign glyph.

= 1.0 =
* Inital release.
