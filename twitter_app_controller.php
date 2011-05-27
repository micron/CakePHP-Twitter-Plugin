<?php

App::import('Vendor', 'Twitter.HttpSocketOauth');

class TwitterAppController extends AppController {
	
	public $name = 'TwitterApp';
	
	public function index(){
		$Http = new HttpSocketOauth;
		$response = $Http->request($this->appConfig['twitter']['request']);
		parse_str($response, $response);
		$this->redirect('http://api.twitter.com/oauth/authorize?oauth_token=' . $response['oauth_token']);
	}
	
	public function twcallback(){
		$Http = new HttpSocketOauth();

		$request = $this->appConfig['twitter']['access'];
		// the authtoken an verfier got lost wehn redirecting from bit.ly to the localhost domain
		// ?oauth_token=A0UdP3qsYjq6JTjvW69RGarWhaBHEgwWFXVyTvAG2E&oauth_verifier=z5NSfsgn7lfVFKR55sqvMEcA1dvDQu71OQQDKEL4Q
		$request['auth']['oauth_token'] = $this->params['url']['oauth_token'];
		$request['auth']['oauth_verifier'] = $this->params['url']['oauth_verifier'];
		
		$response = $Http->request($request);
		parse_str($response, $response);
		
		$this->Session->write('Twitter', $response);
	}
	
	public function sendtweet($accesskey = NULL){
		var_dump($this->Session->read('Twitter'));
	}
	
}

?>