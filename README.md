# Grav Geocoding Plugin

The **Geocoding** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). Converts addresses into geographic coordinates.

## Features
* Geocoding: convert human readable address into coordinates (lat/lon)
* Use Grav builtin caching
* Hide search results from cache via sha256 hashes
* Prevent XSS by validating/encoding given input

## Installation

Installing the Geocoding plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install geocoding

This will install the Geocoding plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/geocoding`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `geocoding`. You can find these files on [GitHub](https://github.com/nico-hood/grav-plugin-geocoding) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/geocoding

> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/nico-hood/grav-plugin-geocoding/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/geocoding/geocoding.yaml` to `user/config/plugins/geocoding.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
nominatim_host: 'https://nominatim.openstreetmap.org/search'
country_code: ''
```

Note that if you use the Admin Plugin, a file with your configuration named geocoding.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

**By using the Openstreetmap Nominatim service you aggree to their [usage policy](https://operations.osmfoundation.org/policies/nominatim/)**

You can directly debug [Nominatim API](https://nominatim.org/release-docs/develop/api/Search/) request using your browser:
```
https://nominatim.openstreetmap.org/search/berlin?format=jsonv2&limit=1
```

## Usage

Go to `www.yourpage.com/index.html?location=berlin` to test the example below.

```html
{% set location_query = uri.query('location') %}
{% set location = geocoding.getLocation(location_query) %}

{% if location != null %}
<a href="https://www.google.com/maps/place/{{ location.lat }},{{ location.lon }}">
  {{ location.name }}
</a>
{% elseif location_query != null %}
<div class="notices red">
  <p>
    Location "{{ location_query }}" not found.
  </p>
</div>
{% endif %}
```
