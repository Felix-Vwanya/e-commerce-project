<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function showProducts($id)
    {
        // Fetch the category along with its products
        $category = Category::with('products')->findOrFail($id);
        
        // Pass data to the view
        return view('home.categories', compact('category'));
    }
}


