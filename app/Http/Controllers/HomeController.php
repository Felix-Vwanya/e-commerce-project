<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $product = Product::all();
        $categories = Category::all();

        return view('home.userpage', compact('product', 'categories'));
    }
    public function redirect()
    {
        $categories = Category::all();
        $usertype=Auth::User()->usertype;
       

        if($usertype == '1')
        {
            $totalProducts = Product::all()->count();
            $totalOrders = Order::all()->count();
            $totalCustomers = User::all()->count();
            $orders = Order::all();
            $deliveredOrders = Order::where('delivery_status', '=', 'delivered')->get()->count();
            $processedOrders = Order::where('delivery_status', '=', 'processing')->get()->count();


            $totalRevenue = 0;

            foreach ($orders as $order) {
                $totalRevenue = $totalRevenue + $order->price;
            }

            return view('admin.home', compact('totalProducts', 'totalOrders', 'totalCustomers', 'totalRevenue', 'deliveredOrders', 'processedOrders'));
        }
        else
            {
                $product = Product::paginate(10);
                return view('home.userpage', compact('product', 'categories'));
            }
    }
    
    public function product_detail($id)
    {
        $product = Product::Find($id);


        return view('home.product_detail', compact('product'));
    }

    public function add_cart(Request $request, $id)
    {
        if(Auth::id())
        {
            $user = Auth::User();
            $product = Product::find($id);

            $cart = new Cart();

            $cart->name = $user->name;
            $cart->email = $user->email;
            $cart->phone = $user->phone;
            $cart->address = $user->address;
            $cart->user_id = $user->id;

            $cart->product_title = $product->title;

            // Check if the product has a discount price, otherwise use the normal price
            $price = $product->discount_price ? $product->discount_price : $product->price;
            $cart->price = $price * $request->quantity;

            $cart->image = $product->image;
            $cart->quantity = $request->quantity;
            $cart->product_id = $product->id;

            $cart->save();

            return redirect()->back()->with('message', 'Product Added Successfully To Cart');
        }
        else
        {
            return redirect('login');
        }
        
       
    }

    public function show_cart()
    {
        if(Auth::id())
        {
            $user = Auth::User();
            $userid = $user->id;

            $cart = Cart::where('user_id', '=', $userid)->get();

            return view('home.showCart', compact('cart'));
        }
        else
        {
            return redirect('login');
        }
         
    }
    public function show_products()
    {
        $products = Product::all();

        return view('home.products', compact('products'));
    }
    public function cash_order()
    {
        $user = Auth::User();
        $userid = $user->id;

        $data = Cart::where('user_id', '=', $userid)->get();

        foreach ($data as $data) {
            $order = new Order();
            
            $order->name = $data->name;
            $order->email = $data->email;
            $order->phone = $data->phone;
            $order->address = $data->address;
            $order->user_id = $data->user_id;

            $order->product_title = $data->product_title;
            $order->price = $data->price;
            $order->image = $data->image;
            $order->quantity = $data->quantity;
            $order->product_id = $data->product_id;

            $order->payment_status= "cash on delivery";
            $order->delivery_status = "Processing";

            $order->save();

            $cartid = $data->id;

            $cart = Cart::find($cartid);

            $cart->delete();
            
        }

        return redirect()->back()->with('message', 'Order succesfully received, Will connect with you As Soon as Possible!!');
         
    }

    public function delete_cart_item($id)
    {
        $item = Cart::Find($id);

        $item->delete();

        return redirect()->back()->with('message', 'Product deleted Successfully');
    }

    public function checkout($totalprice) 
    {

        return view('home.checkout', compact('totalprice'));
    }

    
}
