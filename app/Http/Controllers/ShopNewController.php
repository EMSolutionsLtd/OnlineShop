<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ShopNewController extends Controller
{
    public function index(Request $request) {
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        $categories = Category::orderBy('name', 'ASC')->with('sub_category')->where('status', 1)->get();
        $brands = Brand::orderBy('name', 'ASC')->where('status', 1)->get();

        $products = Product::where('status', 1);
        // Apply Filters here
        if(!empty($request->get('categorySlug'))) {
            $category = Category::where('slug', $request->get('categorySlug'))->first();
            $products = $products->where('category_id', $category->id);
            $categorySelected = $category->id;
        }

        if(!empty($request->get('subCategorySlug'))) {
            $subCategory = SubCategory::where('slug', $request->get('subCategorySlug'))->first();
            $products = $products->where('sub_category_id', $subCategory->id);
            $subCategorySelected = $subCategory->id;
        }

        if(!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        if($request->get('price_max') != '' && $request->get('price_min') != '') {
            if($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }

        if($request->get('sort')) {
            if($request->get('sort') == 'latest') {
                $products = $products->orderBy('id', 'DESC');
            } else if($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'ASC');
            } else if($request->get('sort') == 'price_desc') {
                $products = $products->orderBy('price', 'DESC');
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }
        $products = $products->paginate(6);

        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandsArray'] = $brandsArray;
        $data['priceMax'] = (intval($request->get('price_max')) == 0) ? 1000 : $request->get('price_max');
        $data['priceMin'] = intval($request->get('price_min'));
        $data['sort'] = $request->get('sort');

        return view('front.shop_new', $data);
    }

    public function getProducts(Request $request){
        // print_r($request->all());

        $brandsArray = [];

        $products = Product::where('status', 1)->with('product_images');

        // Apply Filters here
        if(!empty($request->get('categorySlug'))) {
            $category = Category::where('slug', $request->get('categorySlug'))->first();
            $products = $products->where('category_id', $category->id);
        }

        if(!empty($request->get('subCategorySlug'))) {
            $subCategory = SubCategory::where('slug', $request->get('subCategorySlug'))->first();
            $products = $products->where('sub_category_id', $subCategory->id);
        }

        if(!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        if($request->get('price_max') != '' && $request->get('price_min') != '') {
            if($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }

        if($request->get('sort')) {
            if($request->get('sort') == 'latest') {
                $products = $products->orderBy('id', 'DESC');
            } else if($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'ASC');
            } else if($request->get('sort') == 'price_desc') {
                $products = $products->orderBy('price', 'DESC');
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }

        $products = $products->paginate(6)->withQueryString();
        $paginationLinks = (string) $products->links();

        return response()->json([
            'status' => 200,
            'products' => $products,
            'pagination' => $paginationLinks
        ]);
    }
}
