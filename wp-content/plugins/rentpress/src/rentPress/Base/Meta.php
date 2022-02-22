<?php 

/**
* Base Meta box class
*/
abstract class rentPress_Base_Meta
{
    public static $propertyExtras = [
        'prop_tagline' => 'Tagline',
        'property_searchterms' => 'Associated Search Terms'
    ];
    public static $propertyOfficeHours = [
        'propOfficeHours' => 'Office Hours'
    ];
    public static $propertyRoomFields = [
        'propAssetsByNumberOfRooms' => 'Assets by number of rooms'
    ];
    public static $propertyImageFields = [
        'propLogo'          => 'Property Logo',
        'propGeneralPhotos' => 'General Property Photos',
        'propMapImage'      => 'Property Map Image'
    ];
    public static $propertyGeneralFields = [
        'propName'        => 'Property Name',
        'propAddress'     => 'Address',
        'propCity'        => 'City',
        'propState'       => 'State',
        'propZip'         => 'Zip',
        'propURL'         => 'Property Website',
        'propAvailUrl'    => 'Availability URL',
        'propDescription' => 'Property Description',
        'propEmail'       => 'Property Email',
        'propLatitude'    => 'Latitude',
        'propLongitude'   => 'Longitude',
        'prop_code'       => 'Property Code',
        'propPhoneNumber' => 'Office Phone',
        'propUnitsAvailable' => '# of Available Units',
        'propUnitsCaptured' => '# of Units Captured',
        'propSource'      => 'Import Source'
    ];
    public static $venterraGeneralInfo = [
        'propName'        => 'Property Name',
        'propAddress'     => 'Address',
        'propCity'        => 'City',
        'propState'       => 'State',
        'propZip'         => 'Zip',
        'propURL'         => 'Property Website',
        'propAvailUrl'    => 'Availability Url',
        'propTourLink'    => 'Tour Url',
        'propDescription' => 'Property Description',
        'propStaffDescription' => 'Property Staff Description',
        'propEmail'       => 'Property Email',
        'propLatitude'    => 'Latitude',
        'propLongitude'   => 'Longitude',
        'prop_code'       => 'Property Code',
        'propPhoneNumber' => 'Office Phone #',
        'propFaxNumber'   => 'Office Fax #',
        // 'propUnits'       => 'Property Units',
        'propUnitsAvailable' => '# Of Available Units',
        'propUnitsCaptured' => '# Of Units Captured',
        'propTimeZone'    => 'Property Timezone',
        'propSpecialsMessage' => 'Property Specials Message',
        'propSource'      => 'Import Source',
        'propMatterportUrl' => 'Matterport Url'
    ];
    public static $propertyIlsTracking = [
        'propTrackingCodes' => 'ILS Tracking Codes'
    ];
    public static $propertyDisable = [
        'propDisablePricing' => 'Disable Pricing',
    ];
    public static $propertyRangeFields = [
        'wpPropMinRent'     => 'Minimum Rent',
        'wpPropMaxRent'     => 'Maximum Rent',
        'wpPropMinBeds'     => 'Minimum Bedrooms',
        'wpPropMaxBeds'     => 'Maximum Bedrooms',
        'wpPropMinBaths'    => 'Minimum Bathrooms',
        'wpPropMaxBaths'    => 'Maximum Bathrooms',
        'wpPropMinSQFT'     => 'Minimum Square Feet',
        'wpPropMaxSQFT'     => 'Maximum Square Feet'
    ];
    public static $floorPlanMeta = [
        'fpID'            => 'Floor Plan ID',
        'fpName'          => 'Floor Plan Name',
        'fpDescription'   => 'Floor Plan Description',
        'fpBeds'          => 'Bedroom Count',
        'fpBaths'         => 'Bathroom Count',
        'fpMinSQFT'       => 'Minimum Square Feet',
        'fpMaxSQFT'       => 'Maximum Square Feet',
        'fpMinRent'       => 'Minimum Rent',
        'fpMaxRent'       => 'Maximum Rent',
        // 'fpMinDeposit'    => 'Minimum Deposit',
        // 'fpMaxDeposit'    => 'Maximum Deposit',
        'fpUnitsCaptured' => 'Total Units Captured',
        'fpAvailUnitCount'=> 'Number of units available for this floor plan',
        'fpAvailableUnitsInThirty' => 'Number of units available in 30 Days',
        'fpAvailableUnitsInSixty' => 'Number of units available in 60 Days',
        'fpAvailURL'      => 'Availability URL',
        'fpImg'           => 'Floor Plan Image URL',
        // 'fpPhone'         => 'Floor plan contact number',
        'parent_property_code' => 'Floor Plan Property Code',
        'fpGalleryImages' => 'Floor Plan Gallery',
        // 'fpMaxRoomates'   => 'Max Roomates',
        'fpUnitMapping'   => 'Unit Type Mapping',
        'fpMatterport'    => 'Virtual Tour URL',
        // 'fpPDF'    => 'Floor Plan PDF'
    ];
    
    public static $floorPlanSpecialMeta = [
        'fp_special_text' => 'Floor Plan Special Text',
        'fp_special_link' => 'Floor Plan Special Link',
        'fp_special_expiration' => 'Floor Plan Special Expiration'
    ];
    
    public static $venterraFloorPlanMeta = [
        'fpID'            => 'Floor Plan ID',
        'fpName'          => 'Floor Plan Name',
        'fpDescription'   => 'Floor Plan Description',
        'fpBeds'          => 'Bedroom Count',
        'fpBaths'         => 'Bathroom Count',
        'fpMinSQFT'       => 'Minimum Square Feet',
        'fpMaxSQFT'       => 'Maximum Square Feet',
        'fpMinRent'       => 'Minimum Rent',
        'fpMaxRent'       => 'Maximum Rent',
        'fpMinDeposit'    => 'Minimum Deposit',
        'fpMaxDeposit'    => 'Maximum Deposit',
        'fpUnitsCaptured' => 'Units Captured',
        'fpAvailUnitCount'=> 'Number of units available with this floor plan',
        'fpAvailableUnitsInThirty' => 'Number of Units Available in 30 Days',
        'fpAvailableUnitsInSixty' => 'Number of Units Available in 60 Days',
        'fpAvailURL'      => 'Availability URL',
        'fpImg'           => 'Floor plan image URL',
        'fpPhone'         => 'Floor plan contact number',
        'parent_property_code' => 'Floor Plan Parent Property',
        'fpGalleryImages' => 'Floor Plan Gallery',
        'fpMaxRoomates'   => 'Max Roomates',
        'fpUnitMapping'   => 'Unit Type Mapping',
        'fpMatterport'    => 'Floor Plan Matterport Video',
        'fpPDF'    => 'Floor Plan PDF'
    ];

    public static $floorPlanListMeta = [ // not sure if still needed... 
        'fpVideos'        => 'Floor Plan Videos',
        'fpAmenities'     => 'Floor Plan Amenities'
    ];
    public static $propertyCoordinateMeta = [
        'prop_coords' => 'Property Coordinates',
        'override_synced_property_coords_data' => "Override Synced Data"
    ];

}