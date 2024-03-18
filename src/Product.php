<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

class Product
{
    public static function extractProducts(Crawler $crawler, string $baseUrl): array
    {
        $products = [];

        $crawler->filter('.product')->each(function (Crawler $node) use ($baseUrl, &$products) {
            $productName = $node->filter('.product-name')->text();
            $productCapacity = $node->filter('.product-capacity')->text();
            $productIdentifier = $productName . ' ' . $productCapacity;
             // Convert capacity to MB if it's specified in GB
            if (stripos($productCapacity, 'GB') !== false) {
                $capacityMB = intval($productCapacity) * 1024; // Convert GB to MB
            } elseif (stripos($productCapacity, 'MB') !== false) {
                // If it's already in MB, just extract the numeric part
                $capacityMB = intval(preg_replace('/[^0-9]/', '', $productCapacity));
            } else {
                // Handle unknown units or invalid capacity formats
                $capacityMB = null;
            }
            $productPrice = $node->filter('.block.text-center.text-lg')->text();
            $productPrice = preg_replace('/[^0-9.]/', '', $productPrice); // Remove non-numeric characters
            $availabilityNode = $node->filter('.text-sm.block.text-center')->eq(0);
            $availabilityText = $availabilityNode->text();
            $availability = trim(str_replace('Availability:', '', $availabilityText)); // Extract value after "Availability:"
             // Determine if the product is available
            $isAvailable = $availability !== "Out of Stock";
            $imageUrl = $node->filter('img')->attr('src');
            // Check if the image URL is absolute
            if (parse_url($imageUrl, PHP_URL_HOST)) {
                // Already absolute URL, use it directly
                $fullImageUrl = $imageUrl;
            } else {
                // Remove trailing slash from base URL if it exists
                $baseUrl = rtrim($baseUrl, '/');
            
                // Prepend base URL and remove leading ../ (if any)
                $fullImageUrl = str_replace('../', '', $baseUrl . '/' . $imageUrl);
            }
                        

            // Extract color information
            $colorNodes = $node->filter('.flex.flex-wrap.justify-center.-mx-2 span[data-colour]');
            $colors = $colorNodes->each(function (Crawler $colorNode) {
                return $colorNode->attr('data-colour');
            });
           // Extract shipping information if available
            $shippingText = null;
            $shippingDate = null;
            $shippingNode = $node->filter('.my-4.text-sm.block.text-center')->eq(1);
            if ($shippingNode->count() > 0) {
                $shippingText = $shippingNode->text();

                // Check for various formats of shipping text and extract the date
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $shippingText, $matches)) {
                    $shippingDate = $matches[0];
                } elseif (preg_match('/(\d{1,2}\s[A-Za-z]+\s\d{4})/', $shippingText, $matches)) {
                    $shippingDate = date('Y-m-d', strtotime($matches[0]));
                } elseif (preg_match('/\d{1,2}th [A-Za-z]+\s\d{4}/', $shippingText, $matches)) {
                    $shippingDate = date('Y-m-d', strtotime(str_replace('th', '', $matches[0])));
                } elseif (preg_match('/\d{1,2} [A-Za-z]+\s\d{4}/', $shippingText, $matches)) {
                    $shippingDate = date('Y-m-d', strtotime($matches[0]));
                } elseif (preg_match('/Delivers (\d{1,2} [A-Za-z]+\s\d{4})/', $shippingText, $matches)) {
                    $shippingDate = date('Y-m-d', strtotime($matches[1]));
                } elseif (preg_match('/(\d{4}-\d{1,2}-\d{1,2})/', $shippingText, $matches)) {
                    $shippingDate = $matches[0];
                } elseif (strpos($shippingText, 'tomorrow') !== false) {
                    $shippingDate = date('Y-m-d', strtotime('+1 day'));
                } elseif (strpos($shippingText, 'today') !== false) {
                    $shippingDate = date('Y-m-d');
                }
            }

            // Treat each color variant as a separate product
            foreach ($colors as $color) {
                    $product = [
                        'title' => $productIdentifier,
                        'capacityMB' => $capacityMB,
                        'price' => $productPrice,
                        'availabilityText' => $availability,
                        'isAvailable' => $isAvailable,
                        'color' => $color,
                        'imageUrl' => $fullImageUrl,
                        'shippingText' => $shippingText,
                        'shippingDate' => $shippingDate,
                    ];

                    $products[] = $product;
            }
        });

        return $products;
    }
}
