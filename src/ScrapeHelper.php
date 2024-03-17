<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    public static function fetchDocument(string $url): ?Crawler
    {
        $client = new Client();
    
        try {
            $response = $client->get($url);
            return new Crawler($response->getBody()->getContents(), $url);
        } catch (\Exception $e) {
            // Handle the exception here, log or return null, depending on the requirement
            return null;
        }
    }
    
}
