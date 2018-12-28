<?php
 
require_once('_include.php');

\SimpleSAML\Utils\HTTP::redirectTrustedURL(SimpleSAML\Module::getModuleURL('core/frontpage_welcome.php'));



/*
// saml login
if(!isset($_GET['user']))
{    
    if (isset($_COOKIE['samlout'])) {
        setcookie('samlout', '', EXPIRE, '/ui/login/', '', false, true);    
    }
    elseif(!defined('LOGGED_OUT'))
    {
		require_once '../lib/_autoload.php';
		$as = new SimpleSAML_Auth_Simple('default-sp');
 
        $as->requireAuth();
        $attributes = $as->getAttributes();
 
        if(!empty($attributes['uid'][0]))
        {
            $user = $attributes['uid'][0];
            $pass = '<AUTH_TOKEN>';
 
            header('Location: ' . uri::authority() . '/ui/login/?user='.$user.'&pass='.$pass);
            die();
        }
    }
    else
    {
        header('Location: ' . uri::authority() . '/saml_logout.php');
        die;
    }
}
*/