<?php
namespace Nickpeirson\Sculpin\Bundle\SitemapBundle;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Event\FormatEvent;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\AbstractSource;
use Sculpin\Core\Source\MemorySource;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SitemapGenerator implements DataProviderInterface, EventSubscriberInterface
{
    protected $sitemap;
    /** @var  SourceSet */
    protected $sources;
    /** @var array */
    protected $sourceDataFields;

    public function __construct(array $fields = [])
    {
        $this->sourceDataFields = $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Sculpin::EVENT_BEFORE_RUN => 'saveSourceSet'
        ];
    }

    /**
     * Before run
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function saveSourceSet(SourceSetEvent $sourceSetEvent)
    {
        $this->sources = $sourceSetEvent->sourceSet();
    }

    protected function buildSitemap()
    {
        if (!empty($this->sitemap)) {
            return $this->sitemap;
        }
        $sitemap = [];
        /** @var \Sculpin\Core\Source\FileSource $source */
        foreach ($this->sources->allSources() as $source) {
            $data = $source->data()->export();
            if (empty($data) || $source->useFileReference()) {
                continue;
            }
            $sitemapData = [];
            if (isset($data['sitemap'])) {
                $sitemapData = $data['sitemap'];
            }
            if (isset($sitemapData['_exclude'])) {
                continue;
            }
            $loc = $data['url'];
            if (isset($data['canonical'])) {
                $loc = $data['canonical'];
            }
            if (is_callable([$source, 'file'])) {
                $lastmod = date(DATE_W3C, $source->file()->getMTime());
            } else {
                $lastmod = date(DATE_W3C);
            }
            $url = [
                'loc' => $loc,
                'lastmod' => $lastmod
            ];
            foreach ($this->sourceDataFields as $field) {
                if (isset($data[ $field ])) {
                    $url[ $field ] = $data[ $field ];
                }
            }
            if (isset($data['sitemap'])) {
                $url = array_merge($url, $data['sitemap']);
            }
            $sitemap[$url['loc']] = $url;
        }
        $this->sitemap = $sitemap;
    }

    /**
     * Provide data.
     *
     * @return array
     */
    public function provideData(): array
    {
        $this->buildSitemap();
        return $this->sitemap;
    }
}
