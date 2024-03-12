<?php

namespace App;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];
    private array $rawProducts = [];
    private int $totalPages = 0;
    private string $baseUrl = 'https://www.magpiehq.com/developer-challenge/smartphones';


    public function __construct(){
        if((!PHP_VERSION_ID) >= 70400){
            echo "Minimum PHP version required is 7.4";
            exit;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function run(): void
    {
        echo 'Process started'.PHP_EOL;
        $document = ScrapeHelper::fetchDocument($this->baseUrl);
        $paginationLinks = $document->filter('#pages');
        if ($paginationLinks->count() && $paginationLinks->children('.justify-center')->count()) {
            $children = $paginationLinks->children('.justify-center')->children()->last();
            $this->totalPages = (int)trim($children->text(0,true));
        }
        echo 'Total pages found: '.$this->totalPages.PHP_EOL;

        for($start = 1;$start <= $this->totalPages;$start++){
            $url = $this->baseUrl.'?page='.$start;
            $document = ScrapeHelper::fetchDocument($url);
            $products = $document->filter('.product');
            if($products->count()){
                echo 'Total products found: '.$products->count().PHP_EOL;
                $products->each(function (Crawler $product){
                    //Get product Name,Capacity,Image and Price
                    $name = $product->filter('.product-name')->first()->text();
                    $capacity = $product->filter('.product-capacity')->first()->text();
                    $image = $product->filter('img')->attr('src');
                    $price = $product->filter('.my-8')->last()->text();

                    //Product specifications are conditional now so based on condition we are extracting the information.
                    $otherAttributes = $product->filter('.my-4');
                    //Can only have 3 or 4 as total nodes
                    $totalCount = $otherAttributes->count();

                    //Setting default value
                    $availability = 'NA';
                    $shipping = 'NA';
                    $colors = array();
                    //We used the switch case in case if there are more or less attributes, so we can modify our condition
                    switch ($totalCount){
                        case 3:
                            $availability = trim(str_replace('Availability:','',$otherAttributes->eq(2)->text()));
                            $colors = $otherAttributes->eq(1)->filter('.flex')->filter('.px-2')->filter('span')->each(function(Crawler $span){
                                return $span->attr('data-colour');
                            });
                            break;
                        case 4:
                            $availability = trim(str_replace('Availability:','',$otherAttributes->eq(2)->text()));
                            $shipping = trim($otherAttributes->eq(3)->text());
                            $colors = $otherAttributes->eq(1)->filter('.flex')->filter('.px-2')->filter('span')->each(function(Crawler $span){
                                return $span->attr('data-colour');
                            });
                            break;
                        default:
                            break;
                    }
                    $singleProductAvailability = ScrapeHelper::productAvailability($availability);
                    $singleProductShipping = ScrapeHelper::productShipping($shipping);

                    $this->rawProducts[] = array(
                        'title' => ScrapeHelper::productName($name),
                        'price' => ScrapeHelper::productPrice($price),
                        'imageUrl' => ScrapeHelper::productImage($image),
                        'capacityMB' => ScrapeHelper::productCapacity($capacity),
                        'colour' => $colors,
                        'availabilityText' => $singleProductAvailability['text'],
                        'isAvailable' => $singleProductAvailability['inStock'],
                        'shippingText' => $singleProductShipping['text'],
                        'shippingDate' => $singleProductShipping['date'],
                    );
                });
            }
        }
        foreach($this->rawProducts as $rawProduct){
            $singleProduct = $rawProduct;
            foreach($rawProduct['colour'] as $color){
                $singleProduct['colour'] = $color;
                $this->products[] = $singleProduct;
            }
        }
        echo 'Process Finished Output File: {output.json}'.PHP_EOL;
        file_put_contents('output.json',json_encode($this->products));
    }
}

$scrape = new Scrape();
try {
    $scrape->run();
} catch (GuzzleException $exception) {
    echo $exception->getMessage();
    exit;
}
