<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => [
        'queue' => 'meilisearch',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | MeiliSearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your MeiliSearch settings. MeiliSearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own MeiliSearch installation.
    |
    | See: https://docs.meilisearch.com/guides/advanced_guides/configuration.html
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            \App\Models\Airport::class => [
                'displayedAttributes' => [
                    'iata',
                ],
                'searchableAttributes' => [
                    'iata',
                    'name',
                    'city',
                    'state',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\Attendance::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'filterableAttributes' => [
                    'team_id',
                    'event_id',
                    'user_id',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'updated_at_unix:desc',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\DuesTransaction::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'searchableAttributes' => [
                    'user_first_name',
                    'user_preferred_name',
                    'user_legal_middle_name',
                    'user_last_name',
                    'user_uid',
                    'user_gt_email',
                    'user_gmail_address',
                    'user_clickup_email',
                    'user_github_username',
                    'package_name',
                    'package_effective_start',
                    'package_effective_end',
                    'status',
                    'payable_type',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'user_revenue_total:desc',
                    'user_attendance_count:desc',
                    'user_envelopes_count:desc',
                    'user_signatures_count:desc',
                    'updated_at_unix:desc',
                ],
                'filterableAttributes' => [
                    'dues_package_id',
                    'user_id',
                    'merchandise_id',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\Event::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'start_time_unix:desc',
                    'end_time_unix:desc',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\Team::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'filterableAttributes' => [
                    'user_id',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'attendance_count:desc',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\Travel::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'departure_date_unix:desc',
                    'return_date_unix:desc',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\TravelAssignment::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'searchableAttributes' => [
                    'user_first_name',
                    'user_preferred_name',
                    'user_legal_middle_name',
                    'user_last_name',
                    'user_uid',
                    'user_gt_email',
                    'user_gmail_address',
                    'user_clickup_email',
                    'user_github_username',
                    'travel_name',
                    'travel_destination',
                    'travel_departure_date',
                    'travel_return_date',
                    'payable_type',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'user_revenue_total:desc',
                    'user_attendance_count:desc',
                    'user_envelopes_count:desc',
                    'user_signatures_count:desc',
                    'updated_at_unix:desc',
                ],
                'filterableAttributes' => [
                    'user_id',
                    'travel_id',
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
            \App\Models\User::class => [
                'displayedAttributes' => [
                    'id',
                ],
                'searchableAttributes' => [
                    'first_name',
                    'preferred_name',
                    'legal_middle_name',
                    'last_name',
                    'uid',
                    'gt_email',
                    'gmail_address',
                    'clickup_email',
                    'github_username',
                    'gtid',
                    'phone',
                    'gtDirGUID',
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'revenue_total:desc',
                    'attendance_count:desc',
                    'envelopes_count:desc',
                    'signatures_count:desc',
                    'gtid:desc',
                ],
                'filterableAttributes' => [
                    'class_standing_id',
                    'major_id',
                    'team_id',
                    'permission_id',
                    'role_id',
                ],
                'synonyms' => [
                    'abbey' => [
                        'abigail',
                    ],
                    'abbie' => [
                        'abigail',
                    ],
                    'abby' => [
                        'abigail',
                    ],
                    'abe' => [
                        'abraham',
                        'abel',
                    ],
                    'abie' => [
                        'abigail',
                    ],
                    'addie' => [
                        'adeline',
                        'adam',
                        'aidan',
                    ],
                    'addy' => [
                        'adeline',
                        'adam',
                        'aidan',
                    ],
                    'ade' => [
                        'adam',
                        'aidan',
                        'adrian',
                    ],
                    'adi' => [
                        'adrian',
                    ],
                    'adie' => [
                        'aidan',
                    ],
                    'aggie' => [
                        'agatha',
                        'agnes',
                    ],
                    'ala' => [
                        'alastair',
                    ],
                    'alberto' => [
                        'albert',
                    ],
                    'alec' => [
                        'alexander',
                    ],
                    'alex' => [
                        'alexander',
                        'alexandra',
                        'alexis',
                        'alastair',
                        'alexandre',
                    ],
                    'alexie' => [
                        'alexis',
                        'alexander',
                        'alexandra',
                    ],
                    'alf' => [
                        'alfred',
                        'alfredo',
                    ],
                    'alfie' => [
                        'alfred',
                        'alfredo',
                    ],
                    'ali' => [
                        'alison',
                        'alexandra',
                    ],
                    'alick' => [
                        'alexander',
                    ],
                    'alison' => [
                        'alice',
                    ],
                    'allie' => [
                        'alexandra',
                        'alice',
                        'alberta',
                        'alana',
                        'alison',
                    ],
                    'ally' => [
                        'alison',
                    ],
                    'ander' => [
                        'alexander',
                    ],
                    'andy' => [
                        'andrew',
                        'andrea',
                        'armando',
                    ],
                    'angie' => [
                        'angelia',
                        'angela',
                    ],
                    'ann' => [
                        'hannah',
                    ],
                    'anna' => [
                        'hannah',
                    ],
                    'annette' => [
                        'anne',
                    ],
                    'annie' => [
                        'anne',
                        'hannah',
                    ],
                    'ari' => [
                        'adrian',
                    ],
                    'arnie' => [
                        'arnold',
                    ],
                    'ash' => [
                        'ashley',
                    ],
                    'ashy' => [
                        'ashley',
                    ],
                    'auggy' => [
                        'augustus',
                    ],
                    'august' => [
                        'augustus',
                    ],
                    'ava' => [
                        'iva',
                    ],
                    'barb' => [
                        'barbara',
                    ],
                    'barney' => [
                        'barnaby',
                        'bernard',
                    ],
                    'barry' => [
                        'bartholomew',
                    ],
                    'bart' => [
                        'bartholomew',
                        'barton',
                    ],
                    'bartie' => [
                        'bartholomew',
                        'barton',
                    ],
                    'bea' => [
                        'beatrice',
                    ],
                    'beau' => [
                        'beauregard',
                    ],
                    'bec' => [
                        'rebecca',
                    ],
                    'becca' => [
                        'rebecca',
                    ],
                    'becks' => [
                        'rebecca',
                    ],
                    'becky' => [
                        'rebecca',
                    ],
                    'bell' => [
                        'isabella',
                    ],
                    'bella' => [
                        'isabella',
                    ],
                    'belle' => [
                        'isabelle',
                    ],
                    'ben' => [
                        'benjamin',
                        'robinson',
                    ],
                    'benji' => [
                        'benjamin',
                    ],
                    'benny' => [
                        'benjamin',
                        'bonnie',
                    ],
                    'beri' => [
                        'berry',
                    ],
                    'bernie' => [
                        'bernard',
                    ],
                    'berrietta' => [
                        'bernadette',
                    ],
                    'bert' => [
                        'albert',
                        'bertram',
                        'gilbert',
                        'herbert',
                        'norbert',
                        'robert',
                    ],
                    'bertie' => [
                        'albert',
                        'bertram',
                    ],
                    'bess' => [
                        'elizabeth',
                    ],
                    'bessie' => [
                        'elizabeth',
                    ],
                    'bet' => [
                        'elizabeth',
                    ],
                    'beth' => [
                        'elizabeth',
                    ],
                    'betsy' => [
                        'elizabeth',
                    ],
                    'bette' => [
                        'elizabeth',
                    ],
                    'betty' => [
                        'elizabeth',
                    ],
                    'bev' => [
                        'beverly',
                        'beverley',
                    ],
                    'biddy' => [
                        'bridget',
                    ],
                    'bill' => [
                        'william',
                    ],
                    'billie' => [
                        'william',
                    ],
                    'billy' => [
                        'william',
                    ],
                    'bob' => [
                        'robert',
                        'norbert',
                    ],
                    'bobbie' => [
                        'robert',
                        'roberta',
                        'norbert',
                    ],
                    'bobby' => [
                        'robert',
                        'norbert',
                    ],
                    'bon' => [
                        'bonnie',
                    ],
                    'brad' => [
                        'bradley',
                    ],
                    'brady' => [
                        'bradley',
                    ],
                    'bram' => [
                        'abraham',
                    ],
                    'brayden' => [
                        'bradley',
                    ],
                    'brent' => [
                        'brentley',
                    ],
                    'brett' => [
                        'brentley',
                    ],
                    'bridey' => [
                        'bridget',
                    ],
                    'brodie' => [
                        'bradley',
                    ],
                    'burt' => [
                        'albert',
                        'norbert',
                    ],
                    'cal' => [
                        'calvin',
                    ],
                    'cam' => [
                        'cameron',
                    ],
                    'candy' => [
                        'candace',
                    ],
                    'carl' => [
                        'carlos',
                    ],
                    'carly' => [
                        'carlos',
                    ],
                    'carol' => [
                        'carolyn',
                    ],
                    'carrie' => [
                        'caroline',
                    ],
                    'casey' => [
                        'cassandra',
                    ],
                    'cass' => [
                        'cassandra',
                    ],
                    'cassie' => [
                        'cassandra',
                        'catherine',
                    ],
                    'cat' => [
                        'catherine',
                        'karina',
                    ],
                    'cate' => [
                        'catherine',
                    ],
                    'cath' => [
                        'catherine',
                    ],
                    'cathy' => [
                        'catherine',
                    ],
                    'cecie' => [
                        'cecilia',
                    ],
                    'cecil' => [
                        'cecilia',
                    ],
                    'celine' => [
                        'marceline',
                    ],
                    'char' => [
                        'charlotte',
                    ],
                    'charley' => [
                        'charles',
                    ],
                    'charlie' => [
                        'charles',
                        'charlotte',
                    ],
                    'chelle' => [
                        'michelle',
                    ],
                    'chesty' => [
                        'chester',
                    ],
                    'chet' => [
                        'chester',
                    ],
                    'chip' => [
                        'charles',
                    ],
                    'chris' => [
                        'christopher',
                        'christian',
                    ],
                    'chrissy' => [
                        'christian',
                        'christopher',
                    ],
                    'christie' => [
                        'christine',
                        'christopher',
                    ],
                    'christine' => [
                        'christina',
                    ],
                    'christy' => [
                        'christine',
                        'christopher',
                        'christina',
                    ],
                    'chuck' => [
                        'charles',
                    ],
                    'chucky' => [
                        'charles',
                    ],
                    'cilla' => [
                        'priscilla',
                    ],
                    'cin' => [
                        'cynthia',
                    ],
                    'cindy' => [
                        'cynthia',
                        'lucinda',
                    ],
                    'cis' => [
                        'cecilia',
                    ],
                    'cissie' => [
                        'cecilia',
                    ],
                    'cissy' => [
                        'cecilia',
                        'priscilla',
                    ],
                    'clay' => [
                        'clayton',
                    ],
                    'clem' => [
                        'clement',
                    ],
                    'clementine' => [
                        'clement',
                    ],
                    'cliff' => [
                        'clifford',
                    ],
                    'clint' => [
                        'clinton',
                    ],
                    'coby' => [
                        'jacoby',
                    ],
                    'cody' => [
                        'cooper',
                    ],
                    'colette' => [
                        'nicole',
                    ],
                    'colin' => [
                        'nicholas',
                        'colombus',
                    ],
                    'connie' => [
                        'constance',
                    ],
                    'coop' => [
                        'cooper',
                    ],
                    'costin' => [
                        'constantine',
                    ],
                    'curt' => [
                        'curtis',
                    ],
                    'dan' => [
                        'daniel',
                        'jordan',
                    ],
                    'dana' => [
                        'bogdan',
                        'danielle',
                        'daniel',
                        'daria',
                    ],
                    'danielle' => [
                        'daniella',
                    ],
                    'danni' => [
                        'daniella',
                        'danielle',
                    ],
                    'danny' => [
                        'daniel',
                        'donovan',
                        'jordan',
                    ],
                    'dante' => [
                        'durand',
                    ],
                    'dave' => [
                        'david',
                    ],
                    'davey' => [
                        'david',
                    ],
                    'deb' => [
                        'deborah',
                    ],
                    'debbie' => [
                        'deborah',
                    ],
                    'dee' => [
                        'dolores',
                    ],
                    'della' => [
                        'adelaide',
                    ],
                    'denny' => [
                        'dennis',
                    ],
                    'dick' => [
                        'richard',
                    ],
                    'dicky' => [
                        'richard',
                    ],
                    'dom' => [
                        'dominic',
                        'dominick',
                        'dominik',
                    ],
                    'don' => [
                        'donald',
                        'jordon',
                    ],
                    'donnie' => [
                        'donald',
                    ],
                    'donny' => [
                        'donald',
                        'donovan',
                        'jordon',
                    ],
                    'dora' => [
                        'theodora',
                        'dorothy',
                    ],
                    'dorothy' => [
                        'dorothea',
                    ],
                    'dory' => [
                        'dorothy',
                        'morrison',
                    ],
                    'dot' => [
                        'dorothy',
                    ],
                    'dottie' => [
                        'dorothy',
                    ],
                    'dotty' => [
                        'dorothy',
                    ],
                    'doug' => [
                        'douglas',
                    ],
                    'drew' => [
                        'andrew',
                    ],
                    'dunc' => [
                        'duncan',
                    ],
                    'dunny' => [
                        'duncan',
                    ],
                    'eda' => [
                        'edith',
                    ],
                    'eddie' => [
                        'edward',
                        'edwin',
                        'edmund',
                    ],
                    'eddy' => [
                        'edward',
                        'edmund',
                        'edwin',
                        'theodore',
                    ],
                    'elaine' => [
                        'melanie',
                    ],
                    'elenie' => [
                        'helen',
                    ],
                    'elijah' => [
                        'elliott',
                    ],
                    'eliot' => [
                        'elliott',
                    ],
                    'eliott' => [
                        'elliott',
                    ],
                    'elisa' => [
                        'elisabeth',
                    ],
                    'elise' => [
                        'elizabeth',
                    ],
                    'eliza' => [
                        'elizabeth',
                    ],
                    'ella' => [
                        'eleanor',
                        'elizabeth',
                    ],
                    'elle' => [
                        'eleanor',
                        'elizabeth',
                    ],
                    'ellie' => [
                        'ellen',
                        'helen',
                        'eleanor',
                        'ella',
                        'emily',
                    ],
                    'elliot' => [
                        'elliott',
                    ],
                    'elsa' => [
                        'elizabeth',
                    ],
                    'elsie' => [
                        'elizabeth',
                    ],
                    'emma' => [
                        'emily',
                    ],
                    'emmy' => [
                        'emily',
                    ],
                    'emy' => [
                        'emily',
                    ],
                    'eric' => [
                        'frederick',
                        'roderick',
                    ],
                    'erick' => [
                        'frederick',
                        'roderick',
                    ],
                    'erin' => [
                        'catherine',
                        'katherine',
                    ],
                    'fabes' => [
                        'fabian',
                    ],
                    'fannie' => [
                        'frances',
                    ],
                    'fanny' => [
                        'frances',
                        'francesca',
                    ],
                    'ferd' => [
                        'ferdinand',
                    ],
                    'flo' => [
                        'florence',
                    ],
                    'flora' => [
                        'florence',
                    ],
                    'fran' => [
                        'frances',
                        'francesca',
                        'francesco',
                        'francis',
                    ],
                    'francine' => [
                        'frances',
                        'françoise',
                    ],
                    'frank' => [
                        'francesco',
                        'francis',
                        'franklin',
                    ],
                    'frankie' => [
                        'frank',
                        'francis',
                        'frances',
                        'francesco',
                    ],
                    'franky' => [
                        'francis',
                        'franklin',
                    ],
                    'franny' => [
                        'frances',
                        'francesca',
                    ],
                    'fred' => [
                        'frederick',
                        'alfred',
                        'fredrick',
                    ],
                    'freddie' => [
                        'frederick',
                        'alfred',
                    ],
                    'freddy' => [
                        'alfred',
                        'frederick',
                    ],
                    'gab' => [
                        'gabriel',
                        'gabriella',
                    ],
                    'gabby' => [
                        'gabriel',
                        'gabriella',
                        'gabrielle',
                    ],
                    'gabe' => [
                        'gabriel',
                    ],
                    'gabi' => [
                        'gabrielle',
                    ],
                    'gabrielle' => [
                        'gabriella',
                    ],
                    'gaby' => [
                        'gabriel',
                    ],
                    'gail' => [
                        'abigail',
                    ],
                    'gal' => [
                        'garfield',
                    ],
                    'garry' => [
                        'garfield',
                    ],
                    'gary' => [
                        'garfield',
                    ],
                    'gayle' => [
                        'abigail',
                    ],
                    'gene' => [
                        'eugene',
                        'genevieve',
                    ],
                    'geoff' => [
                        'geoffrey',
                    ],
                    'georg' => [
                        'george',
                    ],
                    'georgy' => [
                        'george',
                    ],
                    'gerry' => [
                        'gerald',
                    ],
                    'gia' => [
                        'gianna',
                    ],
                    'gib' => [
                        'gilbert',
                    ],
                    'gigi' => [
                        'giovanni',
                    ],
                    'gil' => [
                        'gilbert',
                    ],
                    'gill' => [
                        'gillian',
                    ],
                    'gilly' => [
                        'gillian',
                    ],
                    'gina' => [
                        'angelina',
                        'giorgina',
                        'luigina',
                        'virginia',
                        'regina',
                    ],
                    'ginette' => [
                        'georgine',
                        'regine',
                        'virginie',
                        'genevieve',
                    ],
                    'ginger' => [
                        'virginia',
                    ],
                    'ginny' => [
                        'virginia',
                    ],
                    'gio' => [
                        'giovanni',
                    ],
                    'gordy' => [
                        'gordon',
                    ],
                    'greg' => [
                        'gregory',
                    ],
                    'gregg' => [
                        'gregory',
                    ],
                    'greta' => [
                        'margaret',
                    ],
                    'gretchen' => [
                        'margaret',
                    ],
                    'gus' => [
                        'angus',
                        'augustine',
                        'augustus',
                        'gustav',
                        'gustave',
                    ],
                    'gussie' => [
                        'gus',
                        'augusta',
                        'gustava',
                        'augustine',
                    ],
                    'gussy' => [
                        'angus',
                        'augustine',
                        'gustav',
                        'gustave',
                    ],
                    'gwen' => [
                        'guinevere',
                        'gwendolyn',
                    ],
                    'gwendy' => [
                        'gwendolyn',
                    ],
                    'hal' => [
                        'henry',
                        'harold',
                    ],
                    'hank' => [
                        'henry',
                        'harry',
                    ],
                    'hanky' => [
                        'harry',
                        'henry',
                    ],
                    'harriet' => [
                        'henrietta',
                    ],
                    'harris' => [
                        'harrison',
                    ],
                    'harry' => [
                        'henry',
                        'harold',
                        'harris',
                        'harrison',
                    ],
                    'hattie' => [
                        'harriet',
                    ],
                    'hatty' => [
                        'harriet',
                    ],
                    'heath' => [
                        'kerry',
                    ],
                    'heidi' => [
                        'adelheid',
                        'adelaide',
                    ],
                    'henriette' => [
                        'henrietta',
                    ],
                    'herb' => [
                        'herbert',
                    ],
                    'herbie' => [
                        'herbert',
                    ],
                    'hil' => [
                        'hilary',
                    ],
                    'hilly' => [
                        'hilary',
                    ],
                    'howie' => [
                        'howard',
                    ],
                    'hunt' => [
                        'hunter',
                    ],
                    'ian' => [
                        'adrian',
                        'brian',
                        'bryan',
                        'christian',
                        'damian',
                        'fabian',
                        'giovanni',
                        'john',
                        'julian',
                        'maximilian',
                        'sebastian',
                        'tristan',
                    ],
                    'ike' => [
                        'dwight',
                        'isaac',
                        'isaack',
                        'isaak',
                    ],
                    'ina' => [
                        'angelina',
                        'christina',
                        'wilhelmina',
                    ],
                    'ing' => [
                        'ingrid',
                    ],
                    'irv' => [
                        'irving',
                    ],
                    'isa' => [
                        'isabel',
                        'isabella',
                    ],
                    'iva' => [
                        'ivana',
                    ],
                    'ivy' => [
                        'iva',
                        'ivor',
                    ],
                    'jack' => [
                        'john',
                        'jackson',
                        'jaxon',
                        'jaxson',
                    ],
                    'jackie' => [
                        'john',
                        'jacqueline',
                    ],
                    'jacky' => [
                        'jack',
                        'jackson',
                        'jaxon',
                        'jaxson',
                    ],
                    'jacqui' => [
                        'jacqueline',
                        'jacques',
                    ],
                    'jake' => [
                        'jacob',
                        'john',
                        'jason',
                    ],
                    'jamie' => [
                        'james',
                    ],
                    'jan' => [
                        'jane',
                        'janet',
                        'janice',
                    ],
                    'jane' => [
                        'janet',
                        'janice',
                    ],
                    'janet' => [
                        'jane',
                    ],
                    'janey' => [
                        'janet',
                        'janice',
                    ],
                    'janie' => [
                        'jane',
                    ],
                    'jay' => [
                        'jason',
                        'james',
                        'jasper',
                        'jacob',
                        'jack',
                        'jamie',
                        'jayden',
                        'jayson',
                    ],
                    'jeanette' => [
                        'jeanne',
                        'jane',
                    ],
                    'jeannette' => [
                        'jeanne',
                        'jane',
                    ],
                    'jeannie' => [
                        'jean',
                        'jane',
                        'jeanne',
                    ],
                    'jeff' => [
                        'jeffrey',
                        'geoffrey',
                        'jeffery',
                    ],
                    'jeffie' => [
                        'jeffrey',
                    ],
                    'jem' => [
                        'james',
                    ],
                    'jen' => [
                        'jennifer',
                    ],
                    'jenna' => [
                        'jennifer',
                        'jane',
                    ],
                    'jenni' => [
                        'jennifer',
                    ],
                    'jennie' => [
                        'jennifer',
                        'jane',
                    ],
                    'jenny' => [
                        'jennifer',
                        'jane',
                    ],
                    'jeremy' => [
                        'jeremiah',
                    ],
                    'jerry' => [
                        'jerome',
                        'gerald',
                        'gerard',
                        'jeremy',
                    ],
                    'jess' => [
                        'jessica',
                    ],
                    'jessie' => [
                        'jessica',
                        'jane',
                    ],
                    'jessy' => [
                        'jessica',
                    ],
                    'jill' => [
                        'jillian',
                        'julian',
                    ],
                    'jim' => [
                        'james',
                        'jamie',
                    ],
                    'jimbo' => [
                        'james',
                    ],
                    'jimmie' => [
                        'james',
                    ],
                    'jimmy' => [
                        'james',
                        'jamie',
                    ],
                    'jody' => [
                        'judith',
                        'josephine',
                        'joseph',
                    ],
                    'joe' => [
                        'joseph',
                        'joel',
                    ],
                    'joey' => [
                        'joseph',
                        'joel',
                        'josephine',
                    ],
                    'john' => [
                        'giovanni',
                        'johnathan',
                        'jonathan',
                    ],
                    'johnnie' => [
                        'john',
                    ],
                    'johnny' => [
                        'john',
                        'johnathan',
                        'jonathan',
                    ],
                    'jon' => [
                        'jonathan',
                    ],
                    'jonny' => [
                        'jonathan',
                    ],
                    'jordy' => [
                        'jordan',
                        'jordon',
                    ],
                    'josephine' => [
                        'josephina',
                    ],
                    'josh' => [
                        'joshua',
                    ],
                    'joshi' => [
                        'joshua',
                    ],
                    'josie' => [
                        'josephine',
                    ],
                    'judd' => [
                        'jordan',
                    ],
                    'jude' => [
                        'judith',
                        'juliana',
                    ],
                    'judy' => [
                        'judith',
                    ],
                    'jules' => [
                        'julian',
                    ],
                    'julie' => [
                        'juliana',
                    ],
                    'juliet' => [
                        'julie',
                    ],
                    'kara' => [
                        'karina',
                    ],
                    'karen' => [
                        'katherine',
                    ],
                    'kari' => [
                        'katherine',
                    ],
                    'kat' => [
                        'katherine',
                        'katrina',
                    ],
                    'kate' => [
                        'katherine',
                        'kathryn',
                        'catherine',
                    ],
                    'kathy' => [
                        'katherine',
                        'catherine',
                    ],
                    'katie' => [
                        'katherine',
                        'kathryn',
                        'catherine',
                    ],
                    'katy' => [
                        'katherine',
                    ],
                    'kay' => [
                        'katherine',
                    ],
                    'keath' => [
                        'kerry',
                    ],
                    'ken' => [
                        'kenneth',
                        'kevin',
                    ],
                    'kenny' => [
                        'kenneth',
                        'kevin',
                    ],
                    'ker' => [
                        'kerry',
                    ],
                    'kerin' => [
                        'kerry',
                    ],
                    'kim' => [
                        'kimberly',
                        'kimball',
                        'joachim',
                    ],
                    'kimmy' => [
                        'kimberly',
                    ],
                    'kit' => [
                        'christopher',
                        'katherine',
                    ],
                    'kitty' => [
                        'katherine',
                    ],
                    'kori' => [
                        'charles',
                    ],
                    'kris' => [
                        'kristen',
                    ],
                    'krissy' => [
                        'kristen',
                    ],
                    'krista' => [
                        'kristina',
                    ],
                    'kristi' => [
                        'kristina',
                    ],
                    'kristie' => [
                        'kristina',
                    ],
                    'kristy' => [
                        'kristina',
                    ],
                    'lachy' => [
                        'lachlan',
                    ],
                    'lainie' => [
                        'elaine',
                    ],
                    'lana' => [
                        'alana',
                    ],
                    'lance' => [
                        'lancelot',
                    ],
                    'larry' => [
                        'laurence',
                        'lawrence',
                    ],
                    'laurie' => [
                        'laurence',
                        'laura',
                        'lawrence',
                    ],
                    'leanne' => [
                        'leanna',
                    ],
                    'leba' => [
                        'lewis',
                    ],
                    'lee' => [
                        'alexandra',
                        'bailee',
                        'bailey',
                        'leanna',
                        'leanne',
                        'leland',
                        'leonard',
                        'leonardo',
                        'riley',
                    ],
                    'len' => [
                        'leonard',
                        'leonardo',
                    ],
                    'lena' => [
                        'helena',
                        'magdalena',
                    ],
                    'lenny' => [
                        'leonard',
                        'leonardo',
                    ],
                    'leo' => [
                        'leonard',
                        'leonardo',
                    ],
                    'leon' => [
                        'leonard',
                        'leonardo',
                    ],
                    'les' => [
                        'lester',
                    ],
                    'letta' => [
                        'violeta',
                    ],
                    'lettie' => [
                        'violet',
                    ],
                    'lew' => [
                        'lewis',
                    ],
                    'lewie' => [
                        'aloysius',
                        'lewis',
                    ],
                    'lewy' => [
                        'lewis',
                    ],
                    'lex' => [
                        'alexander',
                        'alexandre',
                    ],
                    'lexi' => [
                        'alexandra',
                    ],
                    'liam' => [
                        'william',
                    ],
                    'libb' => [
                        'elizabeth',
                    ],
                    'libby' => [
                        'elizabeth',
                    ],
                    'liddy' => [
                        'elizabeth',
                    ],
                    'lil' => [
                        'lillian',
                        'lily',
                    ],
                    'lilibet' => [
                        'elizabeth',
                    ],
                    'lillian' => [
                        'elizabeth',
                    ],
                    'lillie' => [
                        'lillian',
                        'elizabeth',
                    ],
                    'lilly' => [
                        'lillian',
                    ],
                    'lily' => [
                        'elizabeth',
                    ],
                    'lina' => [
                        'angelina',
                        'carolina',
                        'emmelina',
                        'jacquelina',
                        'paulina',
                    ],
                    'linda' => [
                        'melinda',
                        'belinda',
                    ],
                    'lindy' => [
                        'linda',
                    ],
                    'lio' => [
                        'elliott',
                    ],
                    'lisa' => [
                        'elisabeth',
                        'elizabeth',
                    ],
                    'lisbeth' => [
                        'elizabeth',
                    ],
                    'lissa' => [
                        'melissa',
                    ],
                    'lissie' => [
                        'elizabeth',
                    ],
                    'liv' => [
                        'olivia',
                        'olive',
                    ],
                    'livia' => [
                        'iva',
                    ],
                    'liz' => [
                        'elizabeth',
                    ],
                    'liza' => [
                        'elizabeth',
                    ],
                    'lizbeth' => [
                        'elizabeth',
                    ],
                    'lizzie' => [
                        'elizabeth',
                    ],
                    'lizzy' => [
                        'elizabeth',
                    ],
                    'lola' => [
                        'dolores',
                    ],
                    'lonnie' => [
                        'alonso',
                        'alonzo',
                    ],
                    'loretta' => [
                        'laura',
                    ],
                    'lori' => [
                        'laura',
                    ],
                    'lotte' => [
                        'charlotte',
                    ],
                    'lottie' => [
                        'charlotte',
                    ],
                    'lou' => [
                        'aloysius',
                        'louis',
                    ],
                    'louie' => [
                        'louis',
                    ],
                    'louise' => [
                        'louisa',
                    ],
                    'lucy' => [
                        'lucille',
                    ],
                    'luke' => [
                        'lucas',
                        'lukas',
                    ],
                    'lula' => [
                        'lucy',
                        'louisa',
                    ],
                    'luna' => [
                        'stellaluna',
                    ],
                    'lyn' => [
                        'carolyn',
                    ],
                    'lynette' => [
                        'lynn',
                    ],
                    'lynn' => [
                        'carolyn',
                        'linda',
                        'madeline',
                    ],
                    'lynnette' => [
                        'lynn',
                    ],
                    'mack' => [
                        'mackenzie',
                        'mcdonald',
                    ],
                    'maddie' => [
                        'madeline',
                        'maddison',
                    ],
                    'madeline' => [
                        'madelina',
                    ],
                    'madge' => [
                        'margaret',
                    ],
                    'magda' => [
                        'magdalene',
                    ],
                    'magdaline' => [
                        'madeline',
                    ],
                    'maggie' => [
                        'margaret',
                        'morgan',
                    ],
                    'maidie' => [
                        'margaret',
                    ],
                    'maisie' => [
                        'margaret',
                        'mary',
                    ],
                    'mand' => [
                        'amanda',
                    ],
                    'mandy' => [
                        'amanda',
                    ],
                    'marc' => [
                        'marcus',
                        'markus',
                    ],
                    'marcy' => [
                        'marcus',
                        'markus',
                    ],
                    'marge' => [
                        'margaret',
                    ],
                    'margie' => [
                        'margaret',
                    ],
                    'maria' => [
                        'marianne',
                    ],
                    'marianne' => [
                        'marianna',
                    ],
                    'marie' => [
                        'maria',
                    ],
                    'marietta' => [
                        'maria',
                    ],
                    'mario' => [
                        'marion',
                    ],
                    'mark' => [
                        'marcus',
                        'markus',
                    ],
                    'marky' => [
                        'marcus',
                        'markus',
                    ],
                    'marlon' => [
                        'marcelon',
                        'marcel',
                        'marcus',
                    ],
                    'marsh' => [
                        'marshall',
                    ],
                    'marshy' => [
                        'marshall',
                    ],
                    'marty' => [
                        'martin',
                        'martha',
                    ],
                    'marv' => [
                        'marvin',
                    ],
                    'mary' => [
                        'maria',
                        'marianne',
                        'marie',
                    ],
                    'matt' => [
                        'matthew',
                        'mathew',
                    ],
                    'mattie' => [
                        'matthew',
                        'martha',
                        'matilda',
                    ],
                    'matty' => [
                        'mathew',
                        'matthew',
                    ],
                    'maureen' => [
                        'mary',
                    ],
                    'max' => [
                        'maxwell',
                        'maximilian',
                        'maxim',
                    ],
                    'maxi' => [
                        'maxim',
                    ],
                    'maxie' => [
                        'maxim',
                    ],
                    'meg' => [
                        'margaret',
                        'megan',
                    ],
                    'megan' => [
                        'margaret',
                    ],
                    'mel' => [
                        'melvin',
                        'melanie',
                        'melissa',
                        'melinda',
                    ],
                    'melane' => [
                        'melanie',
                    ],
                    'mellie' => [
                        'melvin',
                    ],
                    'mia' => [
                        'maria',
                    ],
                    'mick' => [
                        'michael',
                    ],
                    'micky' => [
                        'michael',
                    ],
                    'mike' => [
                        'michael',
                    ],
                    'mikey' => [
                        'michael',
                    ],
                    'milan' => [
                        'maxim',
                        'maximilian',
                    ],
                    'milly' => [
                        'emily',
                    ],
                    'mindy' => [
                        'melinda',
                        'amanda',
                        'miranda',
                    ],
                    'minnie' => [
                        'mary',
                        'wilhelmina',
                        'minna',
                        'minerva',
                    ],
                    'missy' => [
                        'melissa',
                    ],
                    'mitch' => [
                        'mitchell',
                    ],
                    'mollie' => [
                        'mary',
                    ],
                    'molly' => [
                        'mary',
                    ],
                    'monty' => [
                        'montgomery',
                    ],
                    'morris' => [
                        'morrison',
                    ],
                    'nabby' => [
                        'abigail',
                    ],
                    'nadetta' => [
                        'bernadette',
                    ],
                    'nancy' => [
                        'anne',
                    ],
                    'nat' => [
                        'natasha',
                        'nathan',
                        'nathaniel',
                    ],
                    'natasha' => [
                        'natalie',
                    ],
                    'nate' => [
                        'nathan',
                        'nathaniel',
                    ],
                    'nath' => [
                        'nathaniel',
                    ],
                    'nathan' => [
                        'johnathan',
                        'jonathan',
                    ],
                    'natty' => [
                        'nathan',
                    ],
                    'ned' => [
                        'edward',
                        'edmund',
                        'edwin',
                        'theodore',
                    ],
                    'neddie' => [
                        'edwin',
                    ],
                    'neddy' => [
                        'edmund',
                        'edward',
                        'theodore',
                    ],
                    'nel' => [
                        'nelson',
                    ],
                    'nell' => [
                        'eleanor',
                        'ellen',
                        'helen',
                    ],
                    'nellie' => [
                        'helen',
                        'ellen',
                        'eleanor',
                    ],
                    'nessie' => [
                        'agnes',
                        'vanessa',
                    ],
                    'nessy' => [
                        'agnes',
                    ],
                    'nettie' => [
                        'janet',
                        'antoinette',
                    ],
                    'nick' => [
                        'nicholas',
                        'dominic',
                        'dominick',
                        'dominik',
                        'nickolas',
                    ],
                    'nicki' => [
                        'nichola',
                        'nichole',
                    ],
                    'nickie' => [
                        'nichola',
                        'nichole',
                    ],
                    'nicky' => [
                        'nicholas',
                        'nicola',
                        'dominic',
                        'dominick',
                        'dominik',
                        'nichola',
                        'nichole',
                        'nickolas',
                    ],
                    'nikki' => [
                        'nichola',
                        'nichole',
                    ],
                    'nina' => [
                        'antonia',
                        'anne',
                    ],
                    'nonie' => [
                        'nora',
                    ],
                    'nora' => [
                        'eleanor',
                        'honora',
                    ],
                    'norm' => [
                        'norman',
                    ],
                    'oleta' => [
                        'violeta',
                    ],
                    'oli' => [
                        'oliver',
                    ],
                    'oliver' => [
                        'olive',
                    ],
                    'olivetta' => [
                        'olive',
                    ],
                    'oliwa' => [
                        'oliver',
                    ],
                    'ollie' => [
                        'oliver',
                        'olive',
                        'olivia',
                    ],
                    'olly' => [
                        'oliver',
                    ],
                    'owen' => [
                        'eugene',
                    ],
                    'paddy' => [
                        'padriag',
                        'patrick',
                    ],
                    'pam' => [
                        'pamela',
                    ],
                    'pammy' => [
                        'pamela',
                    ],
                    'pat' => [
                        'patricia',
                        'patrick',
                    ],
                    'patsy' => [
                        'patricia',
                    ],
                    'patti' => [
                        'patricia',
                    ],
                    'patty' => [
                        'patricia',
                    ],
                    'paulette' => [
                        'paula',
                    ],
                    'pauley' => [
                        'paul',
                    ],
                    'paulie' => [
                        'paul',
                        'paula',
                        'pauline',
                    ],
                    'paulin' => [
                        'pauline',
                    ],
                    'pauline' => [
                        'paulina',
                    ],
                    'pauly' => [
                        'paul',
                        'paula',
                    ],
                    'peggy' => [
                        'margaret',
                        'wendy',
                    ],
                    'penny' => [
                        'penelope',
                    ],
                    'percy' => [
                        'percival',
                    ],
                    'perry' => [
                        'peregrin',
                        'peregrine',
                    ],
                    'pete' => [
                        'peter',
                    ],
                    'petey' => [
                        'peter',
                    ],
                    'phil' => [
                        'philip',
                        'philibert',
                        'phillip',
                    ],
                    'philly' => [
                        'philip',
                        'philippa',
                        'phillip',
                    ],
                    'pippa' => [
                        'philippa',
                    ],
                    'pita' => [
                        'peter',
                    ],
                    'polly' => [
                        'mary',
                        'paul',
                        'paula',
                        'pauline',
                    ],
                    'posey' => [
                        'josephine',
                    ],
                    'quin' => [
                        'quincy',
                        'quinton',
                    ],
                    'quince' => [
                        'quincy',
                    ],
                    'quinn' => [
                        'quincy',
                        'quinton',
                    ],
                    'rach' => [
                        'rachel',
                    ],
                    'rafi' => [
                        'raphael',
                    ],
                    'ralph' => [
                        'raphael',
                    ],
                    'rand' => [
                        'randolph',
                    ],
                    'randy' => [
                        'randall',
                        'randolph',
                        'miranda',
                    ],
                    'ray' => [
                        'raymond',
                        'rachel',
                    ],
                    'rayden' => [
                        'bradley',
                    ],
                    'reg' => [
                        'reginald',
                    ],
                    'reggie' => [
                        'regina',
                        'reginald',
                    ],
                    'rex' => [
                        'reginald',
                    ],
                    'rich' => [
                        'richard',
                    ],
                    'richie' => [
                        'richard',
                    ],
                    'rick' => [
                        'richard',
                        'frederick',
                        'roderick',
                    ],
                    'rickey' => [
                        'richard',
                    ],
                    'ricky' => [
                        'richard',
                        'frederick',
                    ],
                    'rob' => [
                        'robert',
                        'robinson',
                    ],
                    'robbie' => [
                        'robert',
                    ],
                    'robby' => [
                        'robert',
                    ],
                    'robert' => [
                        'robinson',
                    ],
                    'robin' => [
                        'robert',
                        'robinson',
                    ],
                    'robyn' => [
                        'roberta',
                    ],
                    'rod' => [
                        'roderick',
                        'rodney',
                        'rodrick',
                    ],
                    'roddy' => [
                        'roderick',
                        'rodney',
                        'rodrick',
                    ],
                    'ron' => [
                        'ronald',
                        'aaron',
                        'byron',
                        'cameron',
                        'geronimo',
                        'tyron',
                        'tyrone',
                    ],
                    'ronni' => [
                        'veronica',
                    ],
                    'ronnie' => [
                        'ronald',
                        'veronica',
                    ],
                    'ronny' => [
                        'aaron',
                        'byron',
                        'cameron',
                        'ronald',
                        'tyron',
                        'tyrone',
                    ],
                    'rosetta' => [
                        'rosa',
                    ],
                    'rosie' => [
                        'rose',
                    ],
                    'rox' => [
                        'roxanne',
                    ],
                    'roxie' => [
                        'roxanne',
                    ],
                    'roxy' => [
                        'roxanne',
                    ],
                    'rubanetta' => [
                        'gracia',
                    ],
                    'rudy' => [
                        'rudolf',
                    ],
                    'russ' => [
                        'russell',
                    ],
                    'rusty' => [
                        'russell',
                    ],
                    'ryan' => [
                        'adrian',
                        'brian',
                        'bryan',
                    ],
                    'sadie' => [
                        'sarah',
                    ],
                    'sal' => [
                        'salvatore',
                        'sally',
                        'salvador',
                        'zelda',
                    ],
                    'sally' => [
                        'sarah',
                    ],
                    'sam' => [
                        'samuel',
                        'samantha',
                        'simon',
                    ],
                    'sammy' => [
                        'samuel',
                        'samantha',
                        'simon',
                    ],
                    'sandra' => [
                        'alexandra',
                        'cassandra',
                    ],
                    'sandy' => [
                        'alexander',
                        'alexandra',
                        'sandra',
                        'alexandre',
                        'cassandra',
                    ],
                    'shell' => [
                        'michelle',
                    ],
                    'shelly' => [
                        'michelle',
                    ],
                    'sheri' => [
                        'chérie',
                    ],
                    'sherri' => [
                        'chérie',
                    ],
                    'sherry' => [
                        'chérie',
                    ],
                    'sid' => [
                        'sidney',
                        'sydney',
                    ],
                    'sissy' => [
                        'cecilia',
                        'priscilla',
                    ],
                    'sonia' => [
                        'sophia',
                    ],
                    'sonja' => [
                        'sophia',
                    ],
                    'sonya' => [
                        'sophia',
                    ],
                    'spence' => [
                        'spencer',
                    ],
                    'stacey' => [
                        'anastacsa',
                        'eustace',
                    ],
                    'stacy' => [
                        'anastacsa',
                        'eustace',
                        'karina',
                    ],
                    'stan' => [
                        'stanley',
                    ],
                    'ste' => [
                        'stephen',
                    ],
                    'stella' => [
                        'stellaluna',
                    ],
                    'steve' => [
                        'steven',
                        'stephen',
                    ],
                    'stu' => [
                        'stewart',
                        'stuart',
                    ],
                    'stuie' => [
                        'stuart',
                    ],
                    'sue' => [
                        'susan',
                    ],
                    'suey' => [
                        'susan',
                    ],
                    'susie' => [
                        'susan',
                    ],
                    'sylvie' => [
                        'sylvia',
                    ],
                    'tam' => [
                        'tamara',
                        'tamsin',
                    ],
                    'tammy' => [
                        'tamara',
                        'thomasina',
                        'tamsin',
                    ],
                    'tanya' => [
                        'tatiana',
                    ],
                    'tash' => [
                        'natasha',
                    ],
                    'tasha' => [
                        'natalia',
                        'natasha',
                    ],
                    'ted' => [
                        'theodore',
                        'edward',
                        'edmund',
                        'edwin',
                    ],
                    'teddy' => [
                        'edmund',
                        'edward',
                        'edwin',
                        'theodore',
                    ],
                    'tel' => [
                        'terence',
                        'terrance',
                        'terrence',
                    ],
                    'terese' => [
                        'teresa',
                    ],
                    'teri' => [
                        'teresa',
                        'theresa',
                    ],
                    'terri' => [
                        'theresa',
                        'teresa',
                    ],
                    'terrie' => [
                        'teresa',
                        'theresa',
                    ],
                    'terry' => [
                        'terence',
                        'teresa',
                        'terrance',
                        'terrence',
                        'theresa',
                        'torrance',
                    ],
                    'theo' => [
                        'theodore',
                    ],
                    'tia' => [
                        'tiana',
                    ],
                    'tiff' => [
                        'tiffany',
                    ],
                    'tiffy' => [
                        'tiffany',
                    ],
                    'tim' => [
                        'timothy',
                    ],
                    'timmy' => [
                        'timothy',
                    ],
                    'tina' => [
                        'christina',
                    ],
                    'titi' => [
                        'tiffany',
                    ],
                    'toby' => [
                        'tobias',
                    ],
                    'toddy' => [
                        'todd',
                    ],
                    'tom' => [
                        'thomas',
                        'tomas',
                    ],
                    'tommie' => [
                        'thomas',
                    ],
                    'tommy' => [
                        'thomas',
                        'tomas',
                    ],
                    'toni' => [
                        'antonia',
                        'anthea',
                        'antoinette',
                    ],
                    'tony' => [
                        'anthony',
                        'antoine',
                    ],
                    'tonya' => [
                        'antonia',
                    ],
                    'topher' => [
                        'christopher',
                    ],
                    'tori' => [
                        'victoria',
                    ],
                    'tottie' => [
                        'charlotte',
                    ],
                    'tracey' => [
                        'teresa',
                        'theresa',
                    ],
                    'tracy' => [
                        'teresa',
                        'theresa',
                    ],
                    'travis' => [
                        'trevor',
                    ],
                    'trent' => [
                        'trenton',
                    ],
                    'trev' => [
                        'trevor',
                    ],
                    'tricia' => [
                        'patricia',
                    ],
                    'trina' => [
                        'katrina',
                    ],
                    'tris' => [
                        'tristan',
                    ],
                    'trish' => [
                        'patricia',
                    ],
                    'trisha' => [
                        'patricia',
                    ],
                    'trixie' => [
                        'beatrice',
                    ],
                    'tyron' => [
                        'tyrone',
                    ],
                    'val' => [
                        'valentine',
                        'valerie',
                    ],
                    'vester' => [
                        'sylvester',
                    ],
                    'via' => [
                        'olivia',
                    ],
                    'vic' => [
                        'victoria',
                        'victor',
                    ],
                    'vick' => [
                        'victoria',
                    ],
                    'vicki' => [
                        'victoria',
                    ],
                    'vickie' => [
                        'victoria',
                    ],
                    'vicky' => [
                        'victoria',
                    ],
                    'vin' => [
                        'vincent',
                    ],
                    'vince' => [
                        'vincent',
                    ],
                    'vinnie' => [
                        'vincent',
                    ],
                    'viol' => [
                        'violeta',
                    ],
                    'viv' => [
                        'vivian',
                    ],
                    'vivo' => [
                        'vivian',
                    ],
                    'vivy' => [
                        'vivian',
                    ],
                    'von' => [
                        'yvonne',
                    ],
                    'wal' => [
                        'wallace',
                        'walter',
                    ],
                    'wally' => [
                        'wallace',
                        'walter',
                    ],
                    'walt' => [
                        'wallace',
                        'walter',
                    ],
                    'wen' => [
                        'wendy',
                    ],
                    'wend' => [
                        'wendy',
                    ],
                    'wendi' => [
                        'wendy',
                    ],
                    'wes' => [
                        'wesley',
                    ],
                    'will' => [
                        'william',
                        'willard',
                        'wilbert',
                    ],
                    'willy' => [
                        'william',
                    ],
                    'woody' => [
                        'woodrow',
                    ],
                    'zac' => [
                        'zachary',
                    ],
                    'zacchary' => [
                        'zechariah',
                    ],
                    'zach' => [
                        'zachary',
                    ],
                    'zachary' => [
                        'zacarias',
                        'zachariah',
                        'zacharias',
                        'zechariah',
                    ],
                    'zachery' => [
                        'zechariah',
                    ],
                    'zack' => [
                        'zacharias',
                        'zachary',
                    ],
                    'zackary' => [
                        'zechariah',
                    ],
                    'zackery' => [
                        'zechariah',
                    ],
                    'zak' => [
                        'zachary',
                    ],
                    'zakary' => [
                        'zechariah',
                    ],
                    'zana' => [
                        'suzanne',
                    ],
                    'zayn' => [
                        'zane',
                    ],
                ],
                'typoTolerance' => [
                    'disableOnNumbers' => true,
                ],
            ],
        ],
    ],
];
