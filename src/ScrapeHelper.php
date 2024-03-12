<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    /**
     * @throws GuzzleException
     */
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();
        $response = $client->get($url);
        return new Crawler($response->getBody()->getContents(), $url);
    }

    public static function productName($name = null): string
    {
        if(null === $name){
            return 'NA';
        }
        return trim($name);
    }

    public static function productCapacity($capacity = null): string
    {
        if(null === $capacity){
            return 'NA';
        }
        $string = strtolower($capacity);
        if(str_contains($string,'mb')){
            $removedString = (int)str_replace('mb','',$string);
            $removedString /= 1024;
            return round($removedString,2);
        }
        $removedString = (int)str_replace('gb','',$string);
        return $removedString *  1024;
    }

    public static function productImage($imgId = null): string
    {
        if(null === $imgId){
            return 'NA';
        }
        $onlyId = str_replace('../images/','',$imgId);
        return 'https://www.magpiehq.com/developer-challenge/images/'.$onlyId;
    }

    public static function productPrice($priceWithSymbol = null)
    {
        if(null === $priceWithSymbol){
            return 'NA';
        }
        return (float)trim(str_replace('£','',$priceWithSymbol));
    }

    public static function productAvailability($availability = null): array
    {
        if(null === $availability){
            return ['inStock' => false,'text' => 'NA'];
        }
        $string = strtolower($availability);
        if(str_contains($string,'out')){
            return ['inStock' => false,'text' => $availability];
        }
        return ['inStock' => true,'text' => $availability];

    }

    public static function productShipping($shipping = null): array
    {
        if(null === $shipping){
            return ['date' => 'NA','text' => 'NA'];
        }
        $shipText = $shipping;
        $wordsToRemove = array('Available on','Delivery','by','from','Free','Tuesday','th','Delivers','Order within 6 hours and have it','Available on Tuesday');
        $date = trim(str_replace($wordsToRemove,'',$shipping));
        if(str_contains($shipping,'tomorrow')){
            return ['date' => date('Y-m-d',strtotime('tomorrow')),'text' => $shipText];
        }
        if (strtotime($date) !== false){
            return ['date' => date('Y-m-d',strtotime($date)),'text' => $shipText];
        }
        $date = 'NA';
        return ['date' => $date,'text' => $shipText];
    }
}
