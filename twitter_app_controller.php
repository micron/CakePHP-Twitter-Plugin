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
 * 
 */
App::import('Vendor', 'Twitter.HttpSocketOauth');

class TwitterAppController extends AppController {
	
	public $name = 'TwitterApp';
	public $uses = array('TwitterToken');
	
	/**
	 * 
	 * Main action, the controller redirects us to the authorization page to authorize the current application
	 */
	public function index(){
		$Http = new HttpSocketOauth;
		$response = $Http->request($this->appConfig['twitter']['request']);
		parse_str($response, $response);
		$this->redirect('http://api.twitter.com/oauth/authorize?oauth_token=' . $response['oauth_token']);
	}
	
	/**
	 * 
	 * callback action after app authorization, here well be redirected if everything
	 * went ok, requested tokens are saved in the db here, if something went wrong it redirects
	 * to the callbackFail action
	 */
	public function twcallback(){
		$Http = new HttpSocketOauth();

		$request = $this->appConfig['twitter']['access'];
		$request['auth']['oauth_token'] = $this->params['url']['oauth_token'];
		$request['auth']['oauth_verifier'] = $this->params['url']['oauth_verifier'];
		
		$response = $Http->request($request);
		parse_str($response, $response);
		if($this->params['url']['oauth_token'] && $this->params['url']['oauth_verifer']){
			// prepare the data to to be saved
			$data = array(
				'TwitterToken' => array(
					'oauth_token' => $response['oauth_token'],
					'oauth_token_secret' => $response['oauth_token_secret'],
					'user_id' => $response['user_id'],
					'screen_name' => $response['screen_name']
				)
			);
			
			$this->TwitterToken->create();
			$this->TwitterToken->save($data);
			$this->Session->write('Twitter', $response);
		}else{
			$this->redirect('twitter/twitterApp/callbackFail');
		}
	}
	
	/**
	 * 
	 * Sends an tweet to twitter, if the oauth tokens couldnt be found in the session
	 * it grabs an prevoiusly created token from the db, of theres no token it redirects
	 * to the index page of twitterApp to get a fresh oauth tokens
	 * 
	 * @param string $content
	 * @return boolean
	 */
	public function sendtweet($content = null){
		$sent = false;
		$oauthTokens = array();

		if($content && strlen($content) <= 140){
			$Http = new HttpSocketOauth();
			
			if(!$this->Session->read('Twitter.oauth_token')){
				// get an previously saved token from the db
				$result = $this->TwitterToken->find('first', array(
					'limit' => 1,
					'order' => 'id DESC'
				));
				
				if(is_array($result) && count($result)){
					$oauthTokens = $result['TwitterToken'];
					$this->Session->write('Twitter', $result['TwitterToken']);
				}else{
					$this->redirect('twitter/twitterApp');
				}
			}else{
				$oauthTokens = $this->Session->read('Twitter');
			}
			
			$this->appConfig['twitter']['access']['uri']['path'] = '1/statuses/update.json';
			$this->appConfig['twitter']['access']['auth']['oauth_token'] = $oauthTokens['oauth_token'];
			$this->appConfig['twitter']['access']['auth']['oauth_token_secret'] = $oauthTokens['oauth_token_secret'];
			
			unset($this->appConfig['twitter']['access']['auth']['oauth_verifier']);
			
			$this->appConfig['twitter']['access']['body'] = array(
				'status' => $content
			);
			
			$response = $Http->request($this->appConfig['twitter']['access']);
			$sent = true;
		}
		return $sent;
	}
	
	public function callbackFail(){
		
	}
	
}

?>