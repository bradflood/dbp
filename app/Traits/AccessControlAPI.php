<?php

namespace App\Traits;

use App\Models\User\AccessGroup;
use App\Models\User\AccessType;

trait AccessControlAPI
{

    /**
     *
     * Filters out filesets by the access control tables
     *
     * @param string $api_key - The User's API key
     * @param string $type
     *
     * @return object
     */
    public function accessControl($api_key, $type = 'api')
    {

        $user_location = checkParam('ip_address', null, 'optional');
        $user_location = geoip($user_location);
        if (!isset($user_location->iso_code)) {
            $user_location->iso_code   = 'unset';
        }
        if (!isset($user_location->continent)) {
            $user_location->continent = 'unset';
        }

        $access_type = AccessType::where('name', $type)->first();

        $access = [];
        $accessGroups = AccessGroup::with('filesets')
            ->whereHas('types', function ($query) use ($user_location, $access_type) {
                $query->where(function ($query) use ($user_location) {
                    $query->where('country_id', $user_location->iso_code)->orWhere('country_id', '=', null);
                })->where(function ($query) use ($user_location) {
                    $query->where('country_id', $user_location->continent)->orWhere('continent_id', '=', null);
                })->where('access_type_id', $access_type->id);
            })->whereHas('keys', function ($query) use ($api_key) {
                $query->where('key_id', $api_key);
            })->where('name', '!=', 'RESTRICTED')->get();

        $access['hashes'] = $accessGroups->map(function ($item, $key) use ($user_location) {
            return collect($item->filesets)->pluck('hash_id');
        })->unique()->flatten()->toArray();

        $access['string'] = $accessGroups->pluck('name')->implode('_');
        return (object) $access;
    }
}
