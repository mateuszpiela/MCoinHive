<?php
namespace Concrete\Package\MCoinHive;
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Logging\Logger;
use Package;
use Concrete\Core\Captcha\Library as CaptchaLibrary;

/**
 * Mateusz CoinHive package for Concrete5
 * @author Mateusz P
 * @package Concrete\Package\MCoinHive
 */
class Controller extends Package
{
    protected $pkgHandle = 'mcoinhive';
    protected $appVersionRequired = '5.7.0.4';
    protected $pkgVersion = '0.0.1';

    protected $logger;

    public function getPackageName()
    {
        return t('MCoinHive');
    }

    public function getPackageDescription()
    {
        return t('Provides a CoinHive powered captcha field.');
    }

    public function install()
    {
        $pkg = parent::install();
        CaptchaLibrary::add('mcoinhive', t('MCoinHive'), $pkg);
        return $pkg;
    }

    /**
     * @return Logger;
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger('mcoinhive', $this->getConfig()->get('debug.log_level', Logger::WARNING));
        }

        return $this->logger;
    }
}