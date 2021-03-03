<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

    'centers' => array(
        "clinic" => "Clinic",
        "hospital" => "Hospital"
    ),

    'Bloodgroup' => array(
        "A+ve" => "A+ve",
        "B+ve" => "B+ve",
        "O+ve" => "O+ve",
        "A-ve" => "A-ve",
        "B-ve" => "B-ve",
        "O-ve" => "O-ve",
        "AB+ve" => "AB+ve",
        "AB-ve" => "AB-ve",
    ),

    'Gender' => array(
        "Male" => "Male",
        "Female" => "Female",
        "Other" => "Other",
    ),

    'Year' => array(
        "2000" => "2000",
        "2001" => "2001",
        "2002" => "2002",
        "2003" => "2003",
        "2004" => "2004",
        "2005" => "2005",
        "2006" => "2006",
        "2007" => "2007",
        "2008" => "2008",
        "2009" => "2009",
        "2010" => "2010",
        "2011" => "2011",
        "2012" => "2012",
        "2013" => "2013",
        "2014" => "2014",
        "2015" => "2015",
        "2016" => "2016",
        "2017" => "2017",
        "2018" => "2018",
        "2019" => "2019",
        "2020" => "2020",
        "2021" => "2021",
    ),

    'Degree' => array(
        "MBBS" => "MBBS – Bachelor of Medicine, Bachelor of Surgery",
        "BDS" => "BDS – Bachelor of Dental Surgery",
        "BAMS" => "BAMS – Bachelor of Ayurvedic Medicine and Surgery",
        "BUMS" => "BUMS – Bachelor of Unani Medicine and Surgery",
        "BHMS" => "BHMS – Bachelor of Homeopathy Medicine and Surgery",
        "BYNS" => "BYNS- Bachelor of Yoga and Naturopathy Sciences",
        "B.V.Sc & AH" => "B.V.Sc & AH- Bachelor of Veterinary Sciences and Animal Husbandry",
    ),

    'Notification_Type' => array(
        "Profile Verification" => "Profile Verification",
        "New Registration" => "New Registration",
        "News Feed" => "News Feed",
    ),

    'Drug_Type' => array(
        "CAPSULE" => "CAPSULE",
        "CREAM" => "CREAM",
        "DROPS" => "DROPS",
        "FOAM" => "FOAM",
        "GEL" => "GEL",
        "INHALER" => "INHALER",
        "LOTION" => "LOTION",
        "MOUTHWASH" => "MOUTHWASH",
        "TABLET" => "TABLET",
        "SYRUP" => "SYRUP",
    ),

    'Dosage_Unit' => array(
        "gm" => "gm",
        "mcg" => "mcg",
        "mg" => "mg",
        "mg SR" => "mg SR",
        "GEL" => "GEL",
        "ml" => "ml",
        "units" => "units"
    ),

    'Frequency' => array(
        "twice daily" => "twice daily",
        "three times a day" => "three times a day",
        "four times a day" => "four times a day",
        "every four hours" => "every four hours",
        "every 2 hours" => "every 2 hours",
        "every other hour" => "every other hour",
        "every day" => "every day",
        "every other day" => "every other day",
        "three times a week" => "three times a week",
        "immediately" => "immediately",
        "as needed" => "as needed",
        "once a week" => "once a week",
        "twice a week" => "twice a week"
    ),
    
    /* use for doctor */
    'Consultant_Duration' => array(
        "15" => "15",
        "30" => "30"
    ),

    /* use for diagnostics */
    'center_Duration' => array(
        "30" => "30",
        "60" => "60"
    ),

];