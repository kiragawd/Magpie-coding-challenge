<?php

namespace App;

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

class Scrape
{
    private array $products = [];

    public function run(): void
    {
        $baseUrl = 'https://www.magpiehq.com/developer-challenge/';
        $url = $baseUrl . 'smartphones';
        
        // Initialize the DOM crawler
        $crawler = $this->fetchAndProcessPage($url, $baseUrl);

        // Check for pagination
        $pages = $crawler->filter('#pages a')->count();

        // If there are more than one page, process pagination
        if ($pages > 0) {
            for ($i = 2; $i <= $pages + 1; $i++) {
                // Fetch the next page
                $nextPageUrl = $url . '?page=' . $i;
                $this->fetchAndProcessPage($nextPageUrl, $baseUrl);
            }
        }

        // Deduplicate the products array
        $this->deduplicateProducts();

        // Encoding products array into JSON
        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT));
    }

    private function fetchAndProcessPage(string $url, string $baseUrl): ?Crawler
    {
        $crawler = ScrapeHelper::fetchDocument($url);

        if (!$crawler) {
            echo "Failed to fetch the document.";
            return null;
        }

        // Extract product information
        $this->processPage($crawler, $baseUrl);

        return $crawler;
    }

    private function processPage(Crawler $crawler, string $baseUrl): void
    {
        $this->products = array_merge($this->products, Product::extractProducts($crawler, $baseUrl));
    }
    // Deduplicate the products array logic 
    private function deduplicateProducts(): void
    {
        $uniqueProducts = [];
        foreach ($this->products as $product) {
            $hash = md5(json_encode($product));
            $uniqueProducts[$hash] = $product;
        }
        $this->products = array_values($uniqueProducts);
    }

}

$scrape = new Scrape();
$scrape->run();
