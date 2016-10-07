# Sculpin Sitemap Generator Bundle

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

[Sculpin](http://sculpin.io) bundle that adds a data provider for sitemap data.

The sitemap generator will iterate over all sculpin sources that aren't used internally by sculpin via a file reference. This is intended to prevent assets from being included.

## Known limitations
* I'm not using any content types in my sculpin site, so this bundle is untested with content types. You never know, it may work out of the box, but it's untested so be warned.
* As mentioned above, only sources that sculpin doesn't internally use a file reference for will be included. If this skips anythng that you want to include in your sitemap the workaround is to manually add it to your sitemap template.

## Installation

* Add the following to your `sculpin.json` file:

```json
{
  "require": {
    "nickpeirson/sculpin-sitemap-bundle": "~0.1.1"
  }
}
```

* Run `sculpin update`.
* Add the bundle to your kernel `app/SculpinKernel.php`:

```php
<?php
use Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel;

class SculpinKernel extends AbstractKernel
{
    protected function getAdditionalSculpinBundles()
    {
        return [
            'Nickpeirson\Sculpin\Bundle\SitemapBundle\SculpinSitemapBundle'
        ];
    }
}
```

## Usage

The bundle provides a sculpin data provider called `sitemap`. To use it, add the following to your YAML frontmatter:
```
use:
  -sitemap
```
This will make `data.sitemap` available to your template.

`data.sitemap` will be populated with an entry for each page, subject to the limitations above. Each entry will have a `loc` field which is set the the relative URL that sculpin assigns to the source and a `lastmod` field which is set based either on the last modified time of the file or on the time the sitemap was built if the modified time isn't available.

The generator is currently hardcoded to override the `loc` field with `page.canonical` if it exists. This is to ensure only canonical urls are added to the sitemap. In future I intend to make this configurable via maps (see below for more information).
 
It's also possible to add new parameters, and override the default parameters, in `data.sitemap` for a given source by defining a sitemap parameter in the sources YAML frontmatter, e.g. the following will override `lastmod` and add `changefreq` and `priority`:
```yaml
sitemap:
  lastmod: 2016-01-01 
  changefreq: monthly
  priority: 0.8
```

It is also possible to exclude files from the sitemap by adding the `_exclude` key to the pages `sitemap` parameter with any truthy value, e.g.:
```yaml
sitemap:
  _exclude: 1
```
The primary use case for `data.sitemap` is for use in an XML sitemap template, but you could also use this data for generating a HTML sitemap if you anotated the sitemap data appropriately.

It's also worth being aware that the `sitemap` data is keyed by the `loc` field, so if you have multiple sources that result in the same `loc`, the last one will be present in the data.

### Example `sitemap.xml` file

    ---
    permalink: none
    use:
     - sitemap
    ---
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {% for url in data.sitemap %}
      <url>
        <loc>{{ site.url }}{{ url.loc }}</loc>
        <lastmod>{{ url.lastmod }}</lastmod>
    {% if url.changefreq %}
        <changefreq>{{ url.changefreq }}</changefreq>
    {% endif %}
    {% if url.priority %}
        <priority>{{ url.priority }}</priority>
    {% endif %}
      </url>
    {% endfor %}
    </urlset>

## Contributing

PRs greatfully recieved

## Roadmap

* Add configuration to allow adding fields from source data to the sitemap. This would allow for reuse of existing data rather than needing to be duplicated in the source's `sitemap` data, e.g. title.
* Further to using existing source data, allow mapping from source data to `sitemap`. This would allow for overriding sitemap fields with existing data, e.g. mapping `page.canonical_url`  override `url` for entries in `data.sitemap`.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
