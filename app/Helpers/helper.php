<?php
/*
To get the preload data before every page load:
    - Register this file in "autoload" of composer.json
    - composer dump-autoload
*/

use App\Models\Category;

function getCategories(){
    return Category::orderBy('name', 'ASC')
        ->with('sub_category')
        ->orderBy('id', 'DESC')
        ->where('status', 1)
        ->where('showHome', 'Yes')
        ->get();
}
?>
