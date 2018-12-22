<?php  
namespace Concrete\Package\MCoinHive\Src\Captcha;
defined('C5_EXECUTE') or die("Access Denied.");


use AssetList;
use Concrete\Core\Captcha\Controller as CaptchaController;
use Concrete\Core\Http\ResponseAssetGroup;
use Package;
use Concrete\Core\Utility\IPAddress;
use Config;
use Core;
use Log;

/**
 * Mateusz CoinHive package for Concrete5
 * @author Mateusz P
 * @package Concrete\Package\MCoinHive 
 */
class McoinhiveController  extends CaptchaController
{
    public function saveOptions($data)
    {
        $config = Package::getByHandle('mcoinhive')->getConfig();
        $config->save('captcha.site_key', $data['site']);
        $config->save('captcha.secret_key', $data['secret']);
		$config->save('captcha.hashes', $data['hashes']);
		$config->save('captcha.whitelabel', $data['whitelabel']);
    }

    /**
     * Shows an input for a particular captcha library
     */
    function showInput()
    {
        $config = Package::getByHandle('mcoinhive')->getConfig();

        $assetList = AssetList::getInstance();

        $assetUrl = 'https://authedmine.com/lib/captcha.min.js';

        $assetList->register('javascript', 'mcoinhive_api', $assetUrl, array('local' => false));

        $assetList->registerGroup(
            'mcoinhive',
            array(
                array('javascript', 'mcoinhive_api'),
            )
        );

        $responseAssets = ResponseAssetGroup::get();
        $responseAssets->requireAsset('mcoinhive');

		echo '<div class="coinhive-captcha" data-hashes="' . $config->get('captcha.hashes') . '" data-key="' . $config->get('captcha.site_key') . '">';		
		echo '<em>Loading Captcha...<br>';
		echo "If it doesn't load, please disable Adblock!</em>";
		echo '</div>';
    }

    /**
     * Displays the graphical portion of the captcha
     */
    function display()
    {
        return '';
    }

    /**
     * Displays the label for this captcha library
     */
    function label()
    {
        return '';
    }

    /**
     * Verifies the captcha submission
     * @return bool
     */
    public function check()
    {
        $pkg = Package::getByHandle('mcoinhive');
        $config = $pkg->getConfig();

        $qsa = [
                'secret' => $config->get('captcha.secret_key'),
                'token' => $_REQUEST['coinhive-captcha-token'],
                'hashes' => $config->get('captcha.hashes')
	         ];
	    $data = http_build_query($qsa);
		
        $ch = curl_init('https://api.coinhive.com/token/verify');
        if (Config::get('concrete.proxy.host') != null) {
            curl_setopt($ch, CURLOPT_PROXY, Config::get('concrete.proxy.host'));
            curl_setopt($ch, CURLOPT_PROXYPORT, Config::get('concrete.proxy.port'));

            // Check if there is a username/password to access the proxy
            if (Config::get('concrete.proxy.user') != null) {
                curl_setopt(
                    $ch,
                    CURLOPT_PROXYUSERPWD,
                    Config::get('concrete.proxy.user') . ':' . Config::get('concrete.proxy.password')
                );
            }
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, Config::get('app.curl.verifyPeer'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_POST, 1);

		
        $response = curl_exec($ch);
		$response = json_decode($response, true);
		
		if ($response['success']) {
			return true;
		}
		else
		{
			if (isset($response['error']) && $response['error'] == "missing_input"){
				$pkg->getLogger()->addError(t('No token or hashes provided as POST parameters.'));
			}
			else if (isset($response['error']) && $response['error'] == "invalid_token"){
				$pkg->getLogger()->addError(t("The token could not be verified. Either the token name was not found, or the token hasn't reached the requested number of hashes."));
			}
			else if (isset($response['error']) && $response['error'] == "invalid_secret"){
				$pkg->getLogger()->addError(t("The secret provided as GET or POST parameter is invalid."));
			}
			else if (isset($response['error']) && $response['error'] == "bad_request"){
				$pkg->getLogger()->addError(t("A malformed request was received."));
			}		
			else if (isset($response['error']) && $response['error'] == "internal_error"){
				$pkg->getLogger()->addError(t("Something bad happened on our side. Contact us if the issue persists."));
			}				
			return false;
		}
    }
}