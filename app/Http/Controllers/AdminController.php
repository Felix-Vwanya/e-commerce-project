<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;


class AdminController extends Controller
{
    public function view_category()
    {
        $data = Category::all();
    

        return view('admin.category', compact('data'));
    }

    public function order()
    {
        $orders = Order::all();
    

        return view('admin.order', compact('orders'));
    }

    public function searchdata(Request $request)
    {

        $queryText= $request->search;

        $orders = Order::where('name', 'LIKE', "%$queryText%")->get();
    

        return view('admin.order', compact('orders'));
    }
    
    public function searchProduct(Request $request)
    {

        $queryText= $request->search;

        $products = Product::where('title', 'LIKE', "%$queryText%")->get();
    

        return view('admin.products', compact('products'));
    }

    public function add_category(Request $request)
    {
        $data = new Category();

        $data->category_name = $request->category;
        $data->save();

        return redirect()->back()->with('message', 'Category created Successfully');
    }

    public function delete_category($id)
    {
        $data = Category::Find($id);

        $data->delete();

        return redirect()->back()->with('message', 'Category deleted Successfully');
    }
    public function view_product()
    {
        $category = Category::all();
        

        return view('admin.product', compact('category'));
    }
    public function show_product()
    {
        $products = Product::all();
        return view('admin.products', compact('products'));
    }

    public function add_product(Request $request)
    {
        $data = new Product();

        $data->title = $request->title;
        $data->description = $request->description;
        $data->price = $request->price;
        $data->quantity = $request->quantity;
        $data->category_id = $request->category_id;
        $data->discount_price = $request->discount_price;

        $image = $request->image;
        $imagename = time().'.'.$image->getClientOriginalExtension();
        $request->image->move('product', $imagename);
        $data->image = $imagename;

        $data->save();

        return redirect()->back()->with('message', 'Product added Successfully');
    }

    public function delete_product($id)
    {
        $product = Product::Find($id);

        $product->delete();

        return redirect()->back()->with('message', 'Product deleted Successfully');
    }

    public function update_product($id)
    {
        $product = Product::Find($id);
        $category = Category::all();
        
        return view('admin.updateproduct', compact('product', 'category'));
    }

    public function update_product_confirm(Request $request, $id)
    {
        $product = Product::Find($id);

        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->category = $request->category;
        $product->discount_price = $request->discount_price;

        $image = $request->image;

        if($image)
        {
            $imagename = time().'.'.$image->getClientOriginalExtension();
            $request->image->move('product', $imagename);
            $product->image = $imagename;
        }
       

        $product->save();

        return redirect()->back()->with('message', 'Product updated Successfully');
    }

}
