<?php  
defined('C5_EXECUTE') or die('Access denied.');

/** @var \Concrete\Core\Form\Service\Form $form */
$form = Core::make('helper/form');
$config = Package::getByHandle('mcoinhive')->getConfig();
?>

<p><?php  echo  t('A site key and secret key must be provided. They can be obtained from the <a href="%s" target="_blank">CoinHive website</a>.', 'https://coinhive.com/settings/sites') ?></p>

<div class="form-group">
    <?php  echo  $form->label('site', t('Site Key')) ?>
    <?php  echo  $form->text('site', $config->get('captcha.site_key', '')) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('secret', t('Secret Key')) ?>
    <?php  echo  $form->text('secret', $config->get('captcha.secret_key', '')) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('hashes', t('Hashes your hashes goal should be a multiple of 256')) ?>
    <?php  echo  $form->text('hashes', $config->get('captcha.hashes', '256')) ?>
</div>

<div class="form-group">
    <?php  echo  $form->label('whitelabel', t('Hide the Coinhive logo and the _What is this_ link.')) ?>
    <?php  echo  $form->select(
        'whitelabel',
        array(
		   'true' => t('On'),
		   'false' => t('Off'),
		),
		$config->get('captcha.whitelabel', 'true')) ?>
</div>