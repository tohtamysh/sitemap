<?php

namespace Tohtamysh\Sitemap;

use Carbon\Carbon;
use SimpleXMLElement;

class Sitemap
{
    private $urls;

    private $limitURL = 50000;

    /**
     * @param string $url
     * @param Carbon $lastMod
     * @return $this
     */
    public function add(string $url, Carbon $lastMod = null)
    {
        $this->urls[] = [
            'loc' => htmlentities($url, ENT_XML1),
            'lastmod' => $lastMod ? $lastMod->toAtomString() : Carbon::now()->toAtomString(),
        ];

        return $this;
    }

    /**
     * @param string $savePath
     * @param string $urlSite
     */
    public function make(string $savePath, string $urlSite)
    {
        if ($this->urls) {
            $this->createIndex($savePath, $urlSite);
        }
    }

    /**
     * @param string $path
     * @param array $urls
     * @return string
     */
    private function createFile(string $path, array $urls)
    {
        $urlSet = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');

        $urlSet->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $u = $urlSet->addChild('url');
            foreach ($url as $key => $value) {
                $u->addChild($key, $value);
            }
        }

        $urlSet->asXML($path);
    }

    private function createIndex(string $path, string $urlSite)
    {
        $out = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex/>');

        $out->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $ost = count($this->urls) % $this->limitURL;

        $div = intdiv(count($this->urls), $this->limitURL);

        $index = $ost ? $div + 1 : $div;

        for ($i = 1; $i <= $index; $i++) {
            $fileName = '/sitemap-' . $i . '.xml';

            $filePath = $path . $fileName;

            $this->createFile($filePath, array_slice($this->urls, ($i - 1) * $this->limitURL, $this->limitURL));

            $sitemap = $out->addChild('sitemap');

            $sitemap->addChild('loc', $urlSite . $fileName);
        }

        $out->asXML($path . '/sitemap.xml');
    }
}
