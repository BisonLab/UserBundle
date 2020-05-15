<?php

namespace BisonLab\UserBundle\Lib;

/*
 * Idea blatantly nicked from:
 * http://dev4theweb.blogspot.pt/2012/08/how-to-access-configuration-values.html
 * and is it as wrong as people say?
 * It works, well. That makes me happier than "bad pattern"
 * (He even uses Lib/ as I am alot already, so it cannot be wrong!)
 */

class ExternalEntityConfig
{
    protected static $roles = array();

    public static function setRolesConfig($roles)
    {
        self::$roles = $roles;
    }

    public static function getRolesConfig(): array
    {
        return self::$roles;
    }

    public static function getRoles(): array
    {
        return array_keys(self::$roles);
    }

    public static function getRolesAsChoices(): array
    {
        $choices = [];
        foreach (self::$roles as $role => $params) {
            $choices[$params['label']] = $role;
        }
        return $choices;
    }

    public static function getDefaultRole(): string
    {
        foreach (self::$roles as $role => $params) {
            if ($params['default'])
                return $role;
        }
    }

    public static function getEnabledRoles(): array
    {
        $roles = [];
        foreach (self::$roles as $role => $params) {
            if ($params['enabled'])
                $roles[] = $role;
        }
        return $roles;
    }
}
