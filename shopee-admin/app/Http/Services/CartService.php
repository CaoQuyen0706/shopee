<?php

namespace App\Http\Services;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function create($request)
    {
        $qty = (int)$request->input('quantity');
        $product_id = (int)$request->input('product_id');

        if($qty<=0||$product_id<=0){
            Session::flash('error', 'Số lượng hoặc sản phẩm không chính xác');
            return false;
        }

        $carts = Session::get('carts');

        if (is_null($carts)){
            Session::put('carts', [
                $product_id => $qty
            ]);
            return true;
        }
        $exists = Arr::exists($carts, $product_id);
        if ($exists)
        {
            $carts[$product_id] = $carts[$product_id] + $qty;
            Session::put('carts', $carts);

            return true;
        }

        $carts[$product_id] = $qty;
        Session::put('carts', $carts);
        return true;
    }

    public function getProduct()
    {
        $carts = Session::get('carts');
        if (is_null($carts))
        {
            return [];
        }

        $productId = array_keys($carts);

        return Product::select('id', 'name', 'price', 'price_sale', 'thumb')
            ->where('active', 1)
            ->whereIn('id', $productId)
            ->get();
    }

    public function update($request){
        Session::put('carts', $request->input('quantity'));
        return true;
    }

    public function remove($id)
    {
        $carts = Session::get('carts');

        unset($carts[$id]);

        Session::put('carts', $carts);

        return true;
    }

    public function addCart($request)
    {
        try {
            DB::beginTransaction();
            $carts = Session::get('carts');
            if (is_null($carts))
            {
                return [];
            }

            $customer = Customer::create([
                'name'=>$request->input('name'),
                'phone'=>$request->input('phone'),
                'address'=>$request->input('address')
            ]);

            $this->infoProductCart($carts, $customer->id);
            DB::commit();
            Session::flash('success', 'Đặt hàng thành công');
            Session::flush();
//            $productId = array_keys($carts);
//
//            $products = Product::select('id', 'name', 'price', 'price_sale', 'thumb')
//                ->where('active', 1)
//                ->whereIn('id', $productId)
//                ->get();
        }
        catch (\Exception $err) {
            DB::rollBack();
            Session::flash('success', 'Đặt hàng lỗi');
            return false;
        }
        return true;
    }

    protected function infoProductCart($carts, $customer_id)
    {
        $productId = array_keys($carts);

        $products = Product::select('id', 'name', 'price', 'price_sale', 'thumb')
            ->where('active', 1)
            ->whereIn('id', $productId)
            ->get();

        $data = [];
        foreach ($products as $product)
        {
            $data[] = [
                'customer_id' => $customer_id,
                'product_id' => $product->id,
                'pty' => $carts[$product->id],
                'price' => $product->price_sale != 0 ? $product->price_sale : $product->price
            ];

        }
        return Cart::insert($data);
    }

    public function getCustomer()
    {
        return Customer::orderByDesc('id')->paginate(15);
    }
}
