<h1><?php __('Twitter Autorisierung'); ?></h1>

<h3><?php __('Um die Autorisierung zu starten einfach auf Starten klicken'); ?></h3>

<?php 
	echo $this->Form->create('Authorize', array(
		'url' => '/twitter/authorize'
	));
	
	echo $this->Form->input('startAuthorization', array(
		'type' => 'hidden',
		'value' => 1
	));
	
	echo $this->Form->end('Starten');
?>