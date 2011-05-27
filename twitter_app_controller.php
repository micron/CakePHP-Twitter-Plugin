<?php
/**
 * TwitterAppController
 *
 * @filesource
 *
 * @copyright		Copyright 2011, InterCaravaning GmbH & Co. KG, tsitrone medien GmbH & Co. KG 
 * @link			http://www.intercaravaning.de
 * @link			http://www.tsitrone.de
 *
 *
 * @author			Miron Ogrodowicz <ogrodowicz@iconcepts.de>
 *
 * @since			v2.0
 * @todo			create an model where we save the auth tokens
 * 
 */
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
	
	public function sendtweet($content = null){
		$sent = false;
		if($content && strlen($content) <= 140){
			// http://icneuesondermodelle.localhost/Sondermodelle/2-70-Fendt_Saphir_495_TFB_Style.html
			$Http = new HttpSocketOauth();
			
			$this->appConfig['twitter']['access']['uri']['path'] = '1/statuses/update.json';
			$this->appConfig['twitter']['access']['auth']['oauth_token'] = $this->Session->read('Twitter.oauth_token');
			$this->appConfig['twitter']['access']['auth']['oauth_token_secret'] = $this->Session->read('Twitter.oauth_token_secret');
			
			unset($this->appConfig['twitter']['access']['auth']['oauth_verifier']);
			
			$this->appConfig['twitter']['access']['body'] = array(
				'status' => $content
			);
			
			//debug($this->appConfig['twitter']['access']);
			$response = $Http->request($this->appConfig['twitter']['access']);
			$sent = true;
		}
		
		return sent;
	}
	
}

?>