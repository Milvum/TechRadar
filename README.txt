=== Tech Radar ===
Version: 1.1.6
Author: Milvum, Blue Harvest
Author URI: https://milvum.com
Contributors: nwinnubst
Donate link: -
Tags: radar, graph, simple, tech, technology, visuals, visual, tool
Requires: 5.5.1
Tested up to: 5.5.1
Stable tag: 1.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Installation == 
Display the radar on a page by using a shortcode block with [techradar]. =
Settings can be found under Settings => Tech Radar.

== Description ==
This is a simple visual wordpress plugin to showcase technologies in a project or organisation.

The tech radar contains four quadrants.
These are the top-left, top-right, bottom-left, and bottom-right.
Colors for items are assigned based on their location.
Items have 3 values: A name, their x coordinate, and their y coordinate.
To avoid cutoffs, be careful with values close to the midway points between quadrants (values around 50).

Full credits to https://blueharvest.io/ for the original design.
[Explicit permission has been requested and granted by the original author to replicate and publish the design as an open source wordpress plugin]

== Screenshots ==

icon-128x128.png: Base icon-128x128.
screenshot-1.png: Picture of the full radar.
screenshot-2.png: Picture of the radar after selecting a quadrant.

== Changelog ==
V1.1.6
- Fix bug where an edit updated the wrong item
V1.1.5
- Correct alignment of the labels
V1.1.4
- Update on blur instead of on change to help prevent annoying reloading
V1.1.3
- Fix bug with empty array counting as false.
V1.1.2
- Improve data sanitation and escaping.
V1.1.1
- Improve naming of functions as per the wordpress.org guidelines.
V1.1.0
- Improve editing of the radar.
V1.0.0
- Initial version.