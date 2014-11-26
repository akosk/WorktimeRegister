<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.12.
 * Time: 14:44
 */

namespace app\components;

use yii\base\Exception;

class LdapManager
{
    const LDAP_BASE_DN = "ou=users,dc=uni-miskolc,dc=hu";
    const LDAP_SERVER_ADDRESS = "ldaps://auth.uni-miskolc.hu";
    const LDAP_SERVER_PORT = 636;


    public function authenticate($uid, $password)
    {
        $connection = ldap_connect(self::LDAP_SERVER_ADDRESS, self::LDAP_SERVER_PORT);

        if (!ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            throw new Exception("Az LDAP protokoll verzió nem megfelelő.");
        }

        $entry = $this->getEntryByUID($uid);

        return $entry && @ldap_bind($connection, $entry['dn'], $password);
    }

    public function getEntryByUID($uid)
    {
        $connection = ldap_connect(self::LDAP_SERVER_ADDRESS, self::LDAP_SERVER_PORT);
        $result = ldap_search($connection, self::LDAP_BASE_DN, "(uid={$uid})");
        $entries = ldap_get_entries($connection, $result);
        return $entries['count'] == 1 ? $entries[0] : null;
    }

}