<?php

namespace App\Helpers;

class Permissions {
    private $permissions = [
        [
            'name' => 'admins',
            'permissions' => [
                'view admins' => 'view',
                'add admin' => 'add',
                'edit admin' => 'edit',
                'delete admin' => 'delete',
            ]
        ],
        [
            'name' => 'roles',
            'permissions' => [
                'view roles' => 'view',
                'add role' => 'add',
                'edit role' => 'edit',
                'delete role' => 'delete',
            ]
        ],
        [
            'name' => 'users',
            'permissions' => [
                'view users' => 'view',
                'add user' => 'add',
                'edit user' => 'edit',
                'delete user' => 'delete',
            ]
        ],
//        [
//            'name' => 'vendors',
//            'permissions' => [
//                'view vendors' => 'view',
//                'add vendor' => 'add',
//                'edit vendor' => 'edit',
//                'delete vendor' => 'delete',
//            ]
//        ],
        [
            'name' => 'partners',
            'permissions' => [
                'view partners' => 'view',
                'add partner' => 'add',
                'edit partner' => 'edit',
                'delete partner' => 'delete',
            ]
        ],
        [
            'name' => 'videos',
            'permissions' => [
                'view videos' => 'view',
            ]
        ],
//        [
//            'name' => 'gifts',
//            'permissions' => [
//                'view gifts' => 'view',
//                'add gift' => 'add',
//                'edit gift' => 'edit',
//                'delete gift' => 'delete',
//            ]
//        ],
//        [
//            'name' => 'cities',
//            'permissions' => [
//                'view cities' => 'view',
//                'add city' => 'add',
//                'edit city' => 'edit',
//                'delete city' => 'delete',
//            ]
//        ],
//        [
//            'name' => 'categories',
//            'permissions' => [
//                'view categories' => 'view',
//                'add category' => 'add',
//                'edit category' => 'edit',
//                'delete category' => 'delete',
//            ]
//        ],
//        [
//            'name' => 'colors',
//            'permissions' => [
//                'view colors' => 'view',
//                'add color' => 'add',
//                'edit color' => 'edit',
//                'delete color' => 'delete',
//            ]
//        ],
//        [
//            'name' => 'ages',
//            'permissions' => [
//                'view ages' => 'view',
//                'add age' => 'add',
//                'edit age' => 'edit',
//                'delete age' => 'delete',
//            ]
//        ],
//        [
//            'name' => 'animal pens',
//            'permissions' => [
//                'view animal_pens' => 'view',
//                'add animal_pen' => 'add',
//                'edit animal_pen' => 'edit',
//                'delete animal_pen' => 'delete',
//            ]
//        ],
//
        [
            'name' => 'notifications',
            'permissions' => [
                'view notifications' => 'view',
                'add notification' => 'add',
                'edit notification' => 'edit',
                'delete notification' => 'delete',
            ]
        ],

        [
            'name' => 'pages',
            'permissions' => [
                'edit page' => 'edit',
            ]
        ],

//        [
//            'name' => 'sounds',
//            'permissions' => [
//                'view sounds' => 'view',
//                'add sound' => 'add',
//                'edit sound' => 'edit',
//                'delete sound' => 'delete',
//            ]
//        ],
        [
            'name' => 'packages',
            'permissions' => [
                'view packages' => 'view',
                'add package' => 'add',
                'edit package' => 'edit',
                'delete package' => 'delete',
            ]
        ],
    ];

    public function get_permissions() {
        return $this->permissions;
    }
}
