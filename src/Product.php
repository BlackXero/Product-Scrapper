<?php

namespace App;

class Product
{

    private array $dataAttributes = array();
    public function __construct($data = array()){
        $this->dataAttributes = $data;
    }
    /*
     *
     * {"name":"iPhone 11 Pro",
     * "capacity":"64 GB",
     * "image":"..\/images\/iphone-11-pro.png",
     * "price":"\u00a3799.99",
     * "availability":"Out of Stock",
     * "shipping":"NA",
     * "colors":["Green"]}
     * */


    public function toJson($url){
        $attributes = array();
        foreach ($this->dataAttributes as $dataAttribute) {
            if(array_key_exists('',$dataAttribute)){

            }else{

            }
        }
    }

}
