<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Initial Page Rendering
    |--------------------------------------------------------------------------
    |
    | Inertia can render the initial page payload using a script element.
    | This is required for the current Inertia Vue client runtime, which
    | reads the initial page from `script[data-page="app"]`.
    |
    */

    'use_script_element_for_initial_page' => true,

    'page_paths' => [
        resource_path('js/pages'),
    ],
];
