<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise as GuzzlePromise;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

class Pu12Scraper
{
	private $guzzleConfig;
	
	private $guzzleClient;
	
	/*
	* Function construct
	* Class initialization
	*/
	public function __construct()
	{
		$this->guzzleConfig = array(
			'timeout' => 60,
            'verify' => false
		);
		$CI =& get_instance();
		if($CI->config->item('debug_proxy')){
			$this->guzzleConfig['proxy'] = 'http://172.17.0.1:8888';
		}
		$this->guzzleClient = new GuzzleClient($this->guzzleConfig);
	}
	
	/*
	* Function simultanousRequest
	* Perform Simultanouse request
	*/
	public function simultanousRequest(array $promises = []){
		// Usage: $promises = [ $this->guzzleClient->requestAsync('GET', 'http://domain.com/page.php', ['headers' => $headers]), ]
        $results = GuzzlePromise\settle($promises)->wait();
		$response = array();
		foreach($results as $key=>$result){
			if ($result['state'] === 'fulfilled'){
				$response[$key] = new Response((string) $result['value']->getBody(), $result['value']->getStatusCode(), $result['value']->getHeaders());
			}
			else{
				$response[$key] = new Response((string) 'error', 404, array());
			}
		}
		return $response;
	}
	
	/*
	* Function requestAsync
	* Perform Async request
	*/
	public function requestAsync($method, $uri = '', array $options = [])
	{
		$results = $this->guzzleClient->requestAsync($method, $uri, $options);
		return new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
	}
	
	/*
	* Function request
	* Send Request
	*/
	public function request($method, $uri = '', array $options = [])
	{
		$results = $this->guzzleClient->request($method, $uri, $options);
		return new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
	}
	
	/*
	* Function test
	* test requests
	*/
	public function test(){
		$headers = array(
			'Upgrade-Insecure-Requests' => 1, 
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36', 
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 
			'Referer' => 'http://speedtest.cbn.net.id/'
		);
		$crawler = $this->request('GET', 'http://speedtest.cbn.net.id/', ['headers' => $headers]);
		print_r(htmlentities($crawler));exit;
	}
	
}
