<?php
namespace Nickpeirson\Sculpin\Bundle\SitemapBundle;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\AbstractSource;
use Sculpin\Core\Source\MemorySource;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SitemapGenerator implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_AFTER_FORMAT => 'afterFormat',
        );
    }

    /**
     * Before run
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function afterFormat(SourceSetEvent $sourceSetEvent)
    {
        $sitemap = [];
        /** @var AbstractSource $source */
        foreach ($sourceSetEvent->allSources() as $source) {
            $data = $source->data()->export();
            if (empty($data) || !$source->canBeFormatted()) {
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
            if (isset($data['sitemap'])) {
                $url = array_merge($url, $data['sitemap']);
            }
            $sitemap[$url['loc']] = $url;
        }
        $sitemapSource = new MemorySource(
            'Sitemap',
            new Configuration,
            'sitemap',
            print_r($sitemap, true),
            'sitemap.xml',
            'sitemap.xml',
            null,
            false,
            false,
            true
        );
        $sitemapSource->setPermalink(new Permalink('sitemap.xml', 'sitemap.xml'));
        $sourceSetEvent->sourceSet()->mergeSource($sitemapSource);
    }
}