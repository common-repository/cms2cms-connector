<?php

/**
 * Class CmsPluginFunctionsConnector
 */
class CmsPluginFunctionsConnector
{

    const CMS2CMS_OPTION_TABLE = 'cms2cms_connector_options';

    private $jsConfig = '{
        "host"        : "https://app.cms2cms.com",
        "youtube"     : "https://www.youtube.com/watch?feature=player_detailpage&v=DQK01NbrCdw#t=25s",
        "feedback"    : "https://cms2cms.polldaddy.com/s/survey",
        "support"     : "https://app.cms2cms.com?chat=fullscreen",
        "wizard"      : "https://app.cms2cms.com/wizard",
        "facebook"    : "//www.facebook.com/CMS2CMS/",
        "twitter"     : "//twitter.com/Cms2Cms",
        "wp_feedback" : "//wordpress.org/support/plugin/cms2cms-connector/reviews",
        "public_host" : "//www.cms2cms.com",
        "bridge"      : "https://app.cms2cms.com/bridge/download",
        "ticket"      : "//support.magneticone.com/hc/en-us/requests/new?department=cms2cms",
        "partner"     : "https://cms2cms.com/offer-for-partners-and-resellers",
        "logout"      : "https://app.cms2cms.com/auth/logout",
        "auth_check"  : "https://app.cms2cms.com/api/auth-check"
    }';

    private $config = array (
        'host'        => 'https://app.cms2cms.com',
        'youtube'     => 'https://www.youtube.com/watch?feature=player_detailpage&v=DQK01NbrCdw#t=25s',
        'feedback'    => 'https://cms2cms.polldaddy.com/s/survey',
        'support'     => '//support.magneticone.com/visitor/indexp.hp?/Default/LiveChat/Chat/Request/_sessionID=
                    /_promptType=cht/_proactive=0/_filterDepartmentID=55/_randomNumber=bnkpattsb316qulj4o15lvdbkq6qsw53',
        'wizard'      => 'https://app.cms2cms.com/wizard',
        'facebook'    => '//www.facebook.com/CMS2CMS/',
        'twitter'     => '//twitter.com/Cms2Cms',
        'wp_feedback' => '//wordpress.org/support/plugin/cms2cms-connector/reviews',
        'public_host' => '//www.cms2cms.com',
        'bridge'      => 'https://app.cms2cms.com/bridge/download',
        'ticket'      => '//support.magneticone.com/hc/en-us/requests/new?department=cms2cms',
        'partner'     => 'https://cms2cms.com/offer-for-partners-and-resellers',
        'logout'      => 'https://app.cms2cms.com/auth/logout',
        'auth_check'  => 'https://app.cms2cms.com/api/auth-check',
        'cms2cms'     => 'https://cms2cms.com/',  
        'contact'     => 'https://cms2cms.com/contacts/',
        'privacy_policy'    =>  'https://cms2cms.com/privacy-policy/',
        'terms_of_service'  =>  'https://cms2cms.com/terms-of-service/',
        'faqs'        =>  'https://cms2cms.com/faqs/',
        'what-is-migration' => 'https://cms2cms.com/migration-checklist/',
        'how-it-works'      => 'https://cms2cms.com/how-it-works/'
    );

    /**
     * User data
     * @return object
     */
    public function getUser()
    {
        $user_ID = get_current_user_id();
        $user_info = get_userdata($user_ID);

        return $user_info;
    }

    /**
     * User name
     * @return string
     */
    public function getUserName()
    {
        return $this->getUser()->display_name;
    }

    /**
     * User email
     * @return string
     */
    public function getUserEmail()
    {
        return $this->getUser()->user_email;
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return get_site_url();
    }

    /**
     * @param $name
     * @return void
     */
    public function getOption($name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::CMS2CMS_OPTION_TABLE;
        $value = $wpdb->get_var( $wpdb->prepare(
            "
            SELECT option_value
            FROM $table_name
            WHERE option_name = %s
            LIMIT 1
	    ",
            $name
        ));

        return $value;
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function setOption($name, $value)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::CMS2CMS_OPTION_TABLE;
        $wpdb->insert( $table_name, array( 'option_name' => $name, 'option_value' => $value ) );
    }

    public function deleteOption($name)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::CMS2CMS_OPTION_TABLE;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE option_name = %s",
                $name
            )
        );
    }

    /**
     * Get front Url
     * @return string
     */
    public function getFrontUrl()
    {
        return str_replace(array('http:', 'https:'), '', plugin_dir_url( __FILE__ ));
    }

    /**
     * @return array
     */
    public function getAuthData()
    {
        $cms2cms_connector_access_login = $this->getOption('cms2cms-connector-login');
        $cms2cms_connector_access_key = $this->getOption('cms2cms-connector-key');

        return array(
            'email' => $cms2cms_connector_access_login,
            'accessKey' => $cms2cms_connector_access_key
        );
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        $cms2cms_connector_access_key = $this->getOption('cms2cms-connector-key');

        return ($cms2cms_connector_access_key != false);
    }

    /**
     * @return void
     */
    public function install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::CMS2CMS_OPTION_TABLE;
        $sql = sprintf(
            "
                CREATE TABLE IF NOT EXISTS %s (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    option_name VARCHAR(64) DEFAULT '' NOT NULL,
                    option_value VARCHAR(64) DEFAULT '' NOT NULL,
                    UNIQUE KEY id (id)
                )
            ",
            $table_name
        );

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Get Options
     * @return array
     */
    public function getOptions()
    {
        $key = $this->getOption('cms2cms-connector-key');
        $login = $this->getOption('cms2cms-connector-login');

        $response = 0;

        if ( $key && $login ) {
            $response = array(
                'email' => $login,
                'accessKey' => $key,
            );
        }

        return $response;
    }

    /**
     * Save options
     * @return array
     */
    public function saveOptions()
    {
        $key          = substr( $_POST['accessKey'], 0, 64 );
        $login        = sanitize_email( $_POST['login'] );
        $bridge_depth = str_replace(get_site_url(), '', $this->getFrontUrl());
        $bridge_depth = trim($bridge_depth, DIRECTORY_SEPARATOR);
        $bridge_depth = explode(DIRECTORY_SEPARATOR, $bridge_depth);
        $bridge_depth = count( $bridge_depth );
        $response     = array('errors' => _('Provided credentials are not correct: ' . $key . ' = ' . $login ));

        if ( $key && $login ) {
            $this->deleteOption('cms2cms-connector-key');
            $this->setOption('cms2cms-connector-key', $key);

            $this->deleteOption('cms2cms-connector-login');
            $this->setOption('cms2cms-connector-login', $login);

            $this->deleteOption('cms2cms-connector-depth');
            $this->setOption('cms2cms-connector-depth', $bridge_depth);

            $response = array(
                'success' => true
            );
        }

        return $response;
    }

    /**
     * Clear options
     */
    public function clearOptions()
    {
        $this->deleteOption('cms2cms-connector-login');
        $this->deleteOption('cms2cms-connector-key');
        $this->deleteOption('cms2cms-connector-depth');
        $this->deleteOption('cms2cms_notice_enabled');
    }

    /**
     * @param $message
     * @param $domain
     * @inheritdoc
     */
    public function _e($message, $domain)
    {
        return _e($message, $domain);
    }

    /**
     * @param $message
     * @param $domain
     * @return string|void
     */
    public function __($message, $domain)
    {
        return __($message, $domain);
    }

    /**
     * @param $name
     * @return string
     */
    public function getFormTempKey($name)
    {
        return wp_create_nonce($name);
    }

    /**
     * @param $value
     * @param $name
     * @return false|int
     */
    public function verifyFormTempKey($value, $name)
    {
        return wp_verify_nonce($value, $name);
    }

    /**
     * Get app url
     * @param bool $json Json return
     * @return string
     */
    public function getConfig($json = false)
    {
        return  $json ? $this->jsConfig : $this->config;
    }

    /**
     * Get app url
     * @return string
     */
    public function getAppUrl()
    {
        $config = $this->getConfig();
        return $config['host'];
    }

    /**
     * @return string|void
     */
    public function getPluginSourceName()
    {
        return $this->__('CMS2CMS Connector', 'cms2cms-connector');
    }

    /**
     * @return string
     */
    public function getPluginSourceType()
    {
        return 'CMS2CMS_connector';
    }

    /**
     * @return string|void
     */
    public function getPluginTargetName()
    {
        return $this->__('WordPress', 'cms2cms-connector');
    }

    /**
     * @return string
     */
    public function getPluginTargetType()
    {
        return 'WordPress';
    }

    /**
     * @return string
     */
    public function getPluginNameLong()
    {
        return sprintf(
            $this->__('CMS2CMS %s', 'cms2cms-connector'),
            $this->getPluginSourceName()
        );
    }

    /**
     * @return string
     */
    public function getPluginNameShort()
    {
        return sprintf(
            $this->__('%s', 'cms2cms-connector'),
            $this->getPluginSourceName()
        );
    }

    /**
     * @return string
     */
    public function getPluginReferrerId()
    {
        return sprintf(
            'Plugin | %s | %s to %s',
            $this->getPluginTargetType(),
            $this->getPluginSourceType(),
            $this->getPluginTargetType()
        );
    }

    /**
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->getAppUrl() . '/auth/sign-up';
    }

    /**
     * Get bridge Url
     * @return string
     */
    public function getBridgeUrl()
    {
        $config = $this->getConfig();

        return $config['bridge'];
    }

    /**
     * @return string
     */
    public function getBridgeFaqUrl()
    {
        return 'https://www.cms2cms.com/faqs/what-is-the-connection-bridge-and-how-to-install-it';
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getAppUrl() . '/auth/sign-in';
    }

    /**
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->getAppUrl() . '/auth#forgot-password';
    }

    /**
     * @return string
     */
    public function getLogOutUrl()
    {
        return $this->getAppUrl() . '/auth/logout';
    }

    public function logOut()
    {
        if (isset($_REQUEST['_wpnonce'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if ($this->verifyFormTempKey($nonce, 'cms2cms_connector_logout')
                && $_POST['cms2cms_connector_logout'] == 1
            ) {
                $this->clearOptions();
            }
        }
    }

    /**
     * @return string
     */
    public function getWizardUrl()
    {
        return $this->getAppUrl() . '/wizard';
    }

    /**
     * @return string
     */
    public function getDashboardUrl()
    {
        return $this->getAppUrl() . '/dashboard';
    }

}