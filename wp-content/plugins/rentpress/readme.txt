=== RentPress ===
Contributors: 30lines, chancebcobb, foster30lines, mumbles12, nicknieman, ryanmarch, szyam, w3human, wfaiz, zachfarrar
Plugin URI: https://rentpress.io/
Tags: apartments, floor plans, multifamily, property management, rentals, configurable, entrata, rentcafe, realpage, vaultware
Requires at least: 5.3
Tested up to: 5.8
Requires PHP: 5.3
Stable tag: 6.6.5
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

RentPress for Apartments is a powerful, extendable plugin that helps you market your apartments and rental properties.

== Description ==

### Meet RentPress for Apartments
Transform your WordPress website into an incredible apartment marketing tool. Delivering enterprise-level quality and features, all backed by industry veterans with a deep understanding of digital marketing best practices and the online rental market. 

Supercharge your RentPress experience by connecting your data feeds from RentCafe, Entrata, RealPage, MRI MarketConnect/Vaultware, and ResMan. [Contact 30 Lines](https://via.30lines.com/U1qnf-Pd) for a subscription.

Get started quickly by enabling our out-of-the-box page templates. Or code your own templates using RentPress to import property data. A variety of shortcodes are also available.

RentPress is built by the award-winning team at 30 Lines who also offer apartment websites, email marketing tools, and targeted content to further enhance your property’s online presence.


Don't see your preferred property management system? Looking for a new integration in the plugin? [Let us know](https://via.30lines.com/wbh68-O2) you're interested!

### New with RentPress 6
Multi-property websites can now quickly take advantage of real-time pricing and availability with RentPress. With one click, add property listing pages to your site with all the information from your data feed, showcase apartment layouts, and show the property location on a map (Google API required). Add in a photo gallery using a shortcode. Showcase property resident reviews from Modern Message, Kingsley or Google Places with a shortcode (RentPress: Reviews Add-on required).
 
Easily enable a properties search page on your site with the Search Page template. Display all properties across your portfolio, search and filter by beds, price, and pets. 
 
Shoppers can now easily find properties by location with a “Cities” taxonomy. Automatically organize your properties by their city, add in romance copy and a picture to represent your city, and you’re ready for neighborhood leasing.

All RentPress page templates report full Schema Markup, helping search engines get a better understanding of your website and your properties. All RentPress page templates report Google Analytics Events for all shopper actions.

## Latest Release - RentPress: Dusk
This latest release brings Office Hours to properties from most import sources and introduces many new shortcodes. Office Hours are supported on properties from these import sources:

- RentCafe
- MRI MarketConnect / Vaultware
- ResMan

You'll also find shortcodes for:

- Property Address
- Property Phone Number
- Property Social Links
- Property Office Hours
- Nearby Properties
- Equal Housing logos

Most shortcodes offer customization through parameters. For example, the Property Address shortcode can display a "Get Directions" link or not. The Equal Housing shortcode allows you to choose white or black variants. For more information, check out our support article about [RentPress Shortcodes](https://via.30lines.com/3Zkeo-9T).

Finally, we've updated our Single Property template page to display Office Hours when they have been provided. 

### RentPress Add-ons
RentPress: Yardi Toolkit Add-on extends the RentPress experience for properties using Yardi CRM. By connecting into the Yardi Marketing API, you can display in realtime your currently available dates and times visitors can schedule a time to visit. 

Grab RentPress: Reviews Add-on to display reviews on your site from Modern Message, Kingsley or Google Places with a shortcode (a Google API key is required to use Google Maps and Reviews features).

RentPress Add-ons are available as separate downloads.

== Installation ==
RentPress can be installed using one of the following methods:
1. Install and Activate the plugin through the 'Plugins' screen in WordPress (recommended).
2. Upload the plugin files to a `/wp-content/plugins/rentpress` directory via FTP, or install the plugin through the WordPress plugins screen directly.

Once activated, use the RentPress -> RentPress Settings page to start setting up your site. More information can be found in the [Getting Started Guide](https://via.30lines.com/ONg6mVRH).

== Frequently Asked Questions ==

= Does RentPress support my property management system's data feed? =

With a subscription to our RentPress service to parse through property data feeds, RentPress plugin can pull in real-time pricing and availability from the following popular systems:

- RentCafe
- Entrata
- RealPage
- MRI MarketConnect / Vaultware
- ResMan

And yes, you can mix multiple data sources on one website.

= How can I access the property information imported by RentPress? =

Since RentPress imports your property pricing and availability information and stores data as custom post meta, you can access any meta field using the typical WordPress `get_post_meta()` method or new-up a `WP_Query()` class.

We also have shortcodes available to pull in listing data, as well as decorator classes and global singletons that give you a variety of methods to call all pricing and availability information in a clean, template-friendly way.

= Can I manually enter property information? =

Yes, once activated, a custom post type is registered and displayed in the wp-admin area for 'Properties,' 'Floor Plans,' 'Neighborhoods,' and a utility post type for 'Units.'

= Are there log files that I can reference if something is breaking with the plugin? =

Yes, there is a /Log directory in the root of the RentPress plugin. There will be separate, automatically generated logs for warnings, errors, info, and event triggers.

= Can I customize the styling of the RentPress included templates? =

The RentPress templates support choosing one accent color. It will pick up the rest of its styling (typeface, font colors, etc) from your currently active theme. Learn more about [customizing RentPress templates here](https://via.30lines.com/IL87ta22).

= Where can I learn more about RentPress? =

We have an extended FAQ page that's regularly updated at [https://rentpress.io/frequently-asked-questions/](https://via.30lines.com/9-f2fTNt)

You can also check our [Getting Started Guide](https://via.30lines.com/ONg6mVRH).

== Screenshots ==
1. Automatically create a Search Apartments page
2. Automatically create a property listing page for each property
3. Templates included for /floorplans/ overview grid page
4. Templates included for floor plan "product" pages
5. Templates included for floor plan "product" pages
6. RentPress adopts your website's theme and features property information on floor plan pages
7. Responsive design scales to mobile devices

== Changelog ==
### 6.6.5 Dusk
Release Date: 9/7/21

### Bugfixes
* "Contact Leasing" button on single property template to now also looks at the option for Request More Info
* Updates floor plans query to be more compatible with WordPress feeds
* Resolves pricing and availabiliy display issues with featured floor plan shortcode
* Resolves issue with lease term pricing in floor plan editor


### 6.6.4 Dusk
Release Date: 6/1/21

### Bugfixes
* Resolves issue where unit prices may not display when floor plan grid is used
* Adds additional link= parameter to [floorplan_grid] shortcode
  * When left blank or "popup" is used, floor plans will display a popup when clicked
  * When "post" is used, floor plans will link to their post
* Fixes issue that could occur if a unit has a non-numeric name
* Single Property Page will show "About" as section link if no amenities are found
* Advanced Search Page handles property price ranges better
* Fixes for Apply links on single floor plans to deep-link to a shopper's chosen unit
* General refactoring throughout for better website performance



### 6.6.3 Dusk
Release Date: 5/4/21

### Bugfixes
* Fixes links for "Other" properties on Advanced Search Template
* Fixes names for "Other" properties on Advanced Search Template
* Filters "Custom Amenity" out of lists of Amenities & Features
* Updates "Apply Now" CTA in floor plan modals to be more reliable
* Updates CTAs to include more parameters
* Fixes display of office hours to be more consistent
* Better logic for handling property website links
* Adds or improves image alt text throughout templates


### 6.6.2 Dusk
Release Date: 4/14/21

### Bugfixes
* Resolves issue with Equal Housing shortcode


### 6.6.1 Dusk
Release Date: 3/16/21
Bugfix bonanza!

### Updates
* Property listing page will now only show other properties if there are at least three others in the same city
* Updates settings pages for compatibility with new WordPress updates
* Updates logic for auto-creating city taxonomy 
* Adds property code style classes to shortcodes
* Refactors [units_table] shortcode for improved experience
* Updates metaboxes on property and floor plan posts to show placeholder text
* Updates metaboxes to indicate if a field is required 
* Updates floor plan unit metaboxes to show proper name
* Adds fields to input data ranges for properties when not using sync
* Updates filters on search page to show if something to filter exists
* Updates logic on property pages to better hide office hours section when office hours don't exist

### Bugfixes
* Resolves issue that could occur with terms on Floor Plan Features and Property Type taxonomies on install
* Resolves issues that could occur on settings pages
* Addresses featured cities on taxonomies to show only when enough cities exist
* Updates logic on Equal Housing shortcode
* Removes unused logic for metaboxes
* Better handling of cities terms to ensure same city will not show
* Updates logic throughout plugin to show lease terms only when lease terms exist


### 6.6 - Dusk
Release Date: January 13. 2021

### New Features
* Office Hours now supported:
  * Properties will sync office hours from compatible import sources.
  * This release brings support for property office hours from RentCafe, Vaultware, and ResMan. 
  * Single Property Page now displays office hours.
  * Adds an option in the property editor to hide office hours if you choose.
* Adds new shortcodes:
  * Property Hours
    * Will display hours for a given property
    * Requires a property code
  * Property Phone
    * Will display phone number for a given property
    * Supports phone number synced from data feed, or manually-entered tracking phone number
    * Shows phone icon by default, can be hidden
    * Phone number will be linked by default, can be made static
  * Property Address
    * Will display name and address for a given property
    * Shows property name by default, can be hidden
    * Shows map icon by default, can be hidden
    * Shows a "Get Directions" link to Google Maps by default, can be hidden
  * Property Socials
    * Will display links to social networks for given property
    * Shows icons representing social networks by default, can also display names
  * Equal Housing
    * Will display an Equal Housing logo
    * Default is a white image, can choose to display black image
    * Displays plain image by default, can choose to show Equal Housing text
  * Nearby Properties
    * Will display properties in the same city as a given property
    * Shows one nearby property by default, can set a minimum number of properties to return
* For more information about shortcodes including use cases, screenshots, and examples, see our article about [RentPress Shortcodes](https://via.30lines.com/3Zkeo-9T).

### Updates
* Single Property page sub-nav now displays links for Pet Policy and Office Hours (when available).

---

### 6.5.2 - Comet
Release Date: December 2, 2020

### Bugfixes
* Resolves issues with floor plan grid shortcodes

---

### 6.5.1 - Comet
Release Date: September 30, 2020

### Updates
* Property listing page will now only show other properties if there are at least three others in the same city

### Bugfixes
* Resolves issue where property grid shortcodes would always display at the top of a page

---

### 6.5 - Comet
Release Date: August 25, 2020

### New Features
* Floor Plans now support specials:
  * Add a note detailing a special discount or sale to any individual floor plan.
  * Add a link (optional) to provide a destination for shoppers to learn more about the special.
  * Add an expiration date (optional) after which the special will no longer display.
  * Floor plans show a "Special" banner to highlight which floor plans are running a special.
  * Floor plan specials are supported on all templates and shortcodes.
* Properties gain a new "Community Type" taxonomy:
  * Create property types of any kind to fit your portfolio.
  * Use the property type to note if your property is built for Student Housing, Senior Living, Furnished Apartments, and so much more.
  * Each Community Type is available to be searched on in Basic and Advanced search templates.
  * Landing pages will be automatically created for each Community Type. Each page can be built out with images, shortcode, and text.
  * Each Community Type is listed in options on the Advanced Search template.
  * Community Types are also available on the property_grid shortcode to combine with other parameters to create a truly customized page.
* Adds new options to manage property search map experience:
  * New Minimum Cluster Count option allows you to set the minimum amount of properties needed to collide to form a cluster.
  * New Map Marker Grid Size allows you to determine how frequently pins will be brought into clusters.
* Adds URL parameters to Advanced Search page.
  * Quickly build a customized landing page on your existing search page for queries like price, bedrooms, city, state, and any arbitrary keyword.

### Updates
* Map Pins and Cluster markers can be customized to match your chosen accent color.
* Map Pins have greater location accuracy.
* Updates database tables for greater compatibility with ResMan property import. 

### Bugfixes
* Resolves issue with property grid min-beds parameter.
* Resolves issue with property grid image placement.
* Addresses issue where property links could show as empty on Advanced Search Template.

---

### 6.4.1 - Beach
Release Date: August 5, 2020

### Bugfixes
* Addresses issue floor plan popups on mobile devices.
* Addresses issue with single property template CTAs.
* Addresses issue with taxonomy template CTAs.
* Addresses issue with property images on search pages.

---

### 6.4 - Beach
Release Date: July 28, 2020

### New Features
* Adds search Schema Markup for all included templates.
  * Your site will see an immediate boost in SEO performance.
* Adds Google Analytics Event Tracking for all included templates.
  * Gain a greater understanding of shopper interactions on your website.
  * Automatically track interactions like Phone Calls, Get Directions, Schedule Tour, Apply, and many more.
* Adds parameters to property /search/ page.
  * Build a customized landing page on your existing /search/ page for queries like price, bedrooms, city, state, and any arbitrary keyword.
* Renames floor plan "3D Tour" to "Explore Floor Plan" to provide greater consistency with video tours.
* Adds new property sorting options for search.
  * New options include: Soonest Available, Specials, Rent: Low to High, Rent: High to Low, Property: A to Z, City: A to Z.
  * New option in search page settings to select a default option to show on /search/ page first load.
  * New sort options are available on both Basic and Advanced Search page templates.
* New option in the property editor for more granular control of application links.
  * Choose if link will affect units or not.

### Updates
* Adds "Special" banner to "Explore Other Options" section on property page.
* Website managers will see improved handling of pricing and availability for units using RentCafe and Entrata imports.

### Bugfixes
* Addresses issue with image optimization in search.
* Resolves issue where empty cities could show on taxonomy term pages.
* Resolves issue with Gallery and Reviews shortcodes on property pages and property editor.
* Fixes debug warning that could show on initial load while `wp_debug` is enabled.

---

### 6.3 - Aurora
Release date: June 23, 2020
### New Features
* Advanced Property Search page template displays the location of your properties on a map to let shoppers search by location. 
* Advanced Search requires Google Map API key.
* Search for properties by Name, City, State, Zip, Additional Keywords, and see results on a map
* Cities directory page automatically created at /cities/ and displays all cities with one or more properties.
* Cities directory page also supports adding content through the page content area.
* Yardi Toolkit Add-on is now available to connect to the Yardi Marketing API. Your website can now show realtime availability for appointment timeslots and invite shoppers to schedule a tour.
*   More information about RentPress: Yardi Toolkit Add-on is [available here »](https://via.30lines.com/TXsdyum4)
* All templates lazy load images so pages will load quickly.
* All templates will use a best-fit size match for all site-hosted images.

### Updates
* "Schedule Tour" CTAs append more tracking parameters to URLs
* Property search can now search by full state name or state abbreviation

---

### 6.2.1

### Updates
*   Adds p tags around text content in property pages.
*   Adds p tags around text content in taxonomy pages.

### Bugfixes
*   Resolves an issue with property schema.
*   Resolves an issue with invalid foreach on taxonomy creation.

### 6.2
### New Features

*   Adds full support for RealPage as an import source for property data.
*   Property Grid shortcode now available. Use "[property_grid]" to see all properties.
*   Additional parameters can be combined include: city, min_rent, max_rent, min_beds, max_beds, has_special, hide_filters.
*   For more information about shortcodes, [check out our support article](https://via.30lines.com/su_1RYbH).
*   New "Features" taxonomy for floor plans allows floor plans note distinguishing characteristics like "Den," "Townhome," and can be extended to fit your inventory.
*   New content fields on Property > Cities taxonomies: "City Romance" and "Local Favorites" intended to allow extended copy about the city and a list of local favorites and neighborhood hotspots.

### Updates

*   Adds "365 Days" to the units Lookahead option.
*   New parameter for property name on floor plans CTAs on property pages.
*   Updates date format for units available soon on floor plan popup on property pages.
*   Show floor plan bed count on single floor plan template when using the Marketing Name setting


### Bugfixes

*   Floor plan popup on property pages shows "Available Now" if unit's available date is on or before today.
*   Restore click to enlarge image on single floor plan pages.

== Upgrade Notice ==
= 6.6.5 =
This release contains bugfixes and improvements.