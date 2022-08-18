<?php

/**
 * Random User Agent Class
 *
 * This class is used to return a randomly-selected user agent string, which may be based on
 * a given set of criteria. See README.md for more details on how to use this class
 *
 * @author Nick Andren
 * @author Joe Campo
 */

namespace Campo;

use Exception;

class UserAgent
{
    /**
     * Agent data stored in file agent_list.json.
     *
     * @var array
     */
    private static array $agentDetails;

    /**
     * Grab a random user agent from the library's agent list
     * @throws Exception
     */
    public static function random($filterBy = []) : string
    {
        $agents = self::loadUserAgents($filterBy);

        if (empty($agents)) {
            throw new Exception('No user agents matched the filter');
        }

        return $agents[random_int(0, count($agents) - 1)];
    }

    /**
     * Get all of the unique values of the device_type field, which can be used for filtering
     * Device types give a general description of the type of hardware that the agent is running,
     * such as "Desktop", "Tablet", or "Mobile"
     */
    public static function getDeviceTypes() : array
    {
        return self::getField('device_type');
    }

    /**
     * Get all of the unique values of the agent_type field, which can be used for filtering
     * Agent types give a general description of the type of software that the agent is running,
     * such as "Crawler" or "Browser"
     */
    public static function getAgentTypes() : array
    {
        return self::getField('agent_type');
    }

    /**
     * Get all of the unique values of the agent_name field, which can be used for filtering
     * Agent names are general identifiers for a given user agent. For example, "Chrome" or "Firefox"
     */
    public static function getAgentNames() : array
    {
        return self::getField('agent_name');
    }

    /**
     * Get all of the unique values of the os_type field, which can be used for filtering
     * OS Types are general names given for an operating system, such as "Windows" or "Linux"
     */
    public static function getOSTypes() : array
    {
        return self::getField('os_type');
    }

    /**
     * Get all of the unique values of the os_name field, which can be used for filtering
     * OS Names are more specific names given to an operating system, such as "Windows Phone OS"
     */
    public static function getOSNames() : array
    {
        return self::getField('os_name');
    }

    /**
     * This is a helper for the publicly-exposed methods named get...()
     * @throws Exception
     */
    private static function getField($fieldName) : array
    {
        $agentDetails = self::getAgentDetails();
        $values       = [];

        foreach ($agentDetails as $agent) {
            if (!isset($agent[$fieldName])) {
                throw new Exception("Field name \"$fieldName\" not found, can't continue");
            }

            $values[] = $agent[$fieldName];
        }

        return array_values(array_unique($values));
    }

    /**
     * Validates the filter so that no unexpected values make their way through
     */
    private static function validateFilter($filterBy = []) : array
    {
        // Components of $filterBy that will not be ignored
        $filterParams = [
            'agent_name',
            'agent_type',
            'device_type',
            'os_name',
            'os_type',
        ];

        $outputFilter = [];

        foreach ($filterParams as $field) {
            if (!empty($filterBy[$field])) {
                $outputFilter[$field] = $filterBy[$field];
            }
        }

        return $outputFilter;
    }

    /**
     * Returns an array of user agents that match a filter if one is provided
     */
    private static function loadUserAgents($filterBy = []) : array
    {
        $filterBy = self::validateFilter($filterBy);

        $agentDetails = self::getAgentDetails();
        $agentStrings = [];

        foreach($agentDetails as $agentDetail){
            foreach ($filterBy as $key => $value) {
                if (!isset($agentDetail[$key]) || !self::inFilter($agentDetail[$key], $value)) {
                    continue 2;
                }
            }
            $agentStrings[] = $agentDetail['agent_string'];
        }

        return array_values($agentStrings);
    }

    /**
     * return if key exist in array of filters
     */
    private static function inFilter($key, $array) : bool
    {
        return in_array(strtolower($key), array_map('strtolower', (array) $array));
    }

    private static function getAgentDetails() : array
    {
        if (!isset(self::$agentDetails)) {
            self::$agentDetails = json_decode(file_get_contents(__DIR__ . '/agents/agent_list.json'), true);
        }

        return self::$agentDetails;
    }
}
