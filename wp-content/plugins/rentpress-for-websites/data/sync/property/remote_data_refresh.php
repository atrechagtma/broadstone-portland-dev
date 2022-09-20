<?php

function rentpress_updateRefreshTable()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'resync.php';
    require_once RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php';

    $rentpress_options = get_option('rentpress_options');
    $total_pages = 1;
    $refresh_ts = rentpress_getOldestRefreshTS();
    $hasCredentials = isset($rentpress_options['rentpress_api_credentials_section_api_token']) && isset($rentpress_options['rentpress_api_credentials_section_username']);
    $last_resync_data_call = $hasCredentials ? rentpress_getLastTimePropertiesWereUpdatedInTLC($rentpress_options) : null;
    $results = array(
        "message" => "",
        "errorMessage" => "",
        "error" => false,
        "synced" => false,
        "propertyCodes" => []
    );

    // if the tlc call errors and there are credentials, then show it, otherwise only attempt to do the resync if a timestamp was returned from tlc
    if ($hasCredentials && is_object($last_resync_data_call)) {
        if (isset($last_resync_data_call->errors) && $last_resync_data_call->errors['http_request_failed']) {
            $results["message"] = 'Issue with connection: ' . $last_resync_data_call->errors['http_request_failed'][0];
            $results["error"] = true;
        } else {
            $results["message"] = 'Error Occurred with API endpoint: Check Console for more information. Contact 30 Lines if problem persists.';
            $results["errorMessage"] = $last_resync_data_call;
            $results["error"] = true;
        }
    } elseif ($hasCredentials) {
        $last_resync_ts = json_decode($last_resync_data_call['body']);
        if (is_int($last_resync_ts)) {
            // If the refresh time is older than the last time tlc was updated, then call topline for all new data
            if (empty($refresh_ts) || $last_resync_ts > (int) $refresh_ts) {
                // start fresh
                rentpress_deleteAllRefreshData();
                // Get all Rentpress Data from server
                for ($i = 1; $i <= $total_pages; $i++) {
                    $response = rentpress_getAllDataForPropertiesResponse($i, $rentpress_options);
                    if (!isset($response->ResponseData->data)) {
                        // TODO: @Ryan Log this event
                        $results["message"] = 'Error Occurred with API response: Check Console for more information. Contact 30 Lines if problem persists.';
                        $results["errorMessage"] = $response;
                        $results["error"] = true;
                        continue;
                    }

                    foreach ($response->ResponseData->data as $property) {
                        rentpress_saveRefreshData($property->Identification->PropertyCode, $property);
                        array_push($results["propertyCodes"], $property->Identification->PropertyCode);
                    }
                    $total_pages = $response->ResponseMeta->total_pages;
                }
                $results["synced"] = true;
            } else {
                $results["propertyCodes"] = rentpress_getRefreshPropertyCodes();
            }

        } elseif (isset($last_resync_ts->error->message)) {
            $results["message"] = 'Issue with connection: ' . $last_resync_ts->error->message;
            $results["error"] = true;
        } else {
            $results["message"] = 'Error Occurred: Check Console for more information. Contact 30 Lines if problem persists.';
            $results["errorMessage"] = $last_resync_ts;
            $results["error"] = true;
        }
    }
    return $results;
}