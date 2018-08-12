<?php

return [
    // Rerun migrations after changing this property
    'triggered_by_id_type' => 'unsignedInteger',

    /** This is the only option you can change without rerunning the migrations */
    'triggered_by_class' => 'App\User',

    // Rerun migrations after changing these properties
    'max_length' => [
        'event_name' => 100,
        'event_description' => 512,
    ]
];