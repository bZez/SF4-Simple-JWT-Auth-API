<?php

namespace App\Helper;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Api
{
    private $URL;

    /**
     * Api constructor.
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->URL = $params->get('API_URL');
    }

    public function get($datas,$die=null)
    {

        $params = array(
            CURLOPT_URL => $this->URL,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => $datas,
            CURLOPT_SSL_VERIFYPEER => false
        );
        $ch = curl_init();
        curl_setopt_array($ch, $params);
        $output = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);
        if($die){
            die($output);
        }

        try{
            $output = json_decode($output);
        }catch (\Exception $e){
            return false;
        }
        return $output;
    }
}