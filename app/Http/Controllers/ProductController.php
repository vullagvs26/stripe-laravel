<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('product.index', compact('products'));
    }

    public function checkout()
    {
        $stripeKey = trim(config('services.stripe.secret', env('STRIPE_SECRET_KEY')));

        if (empty($stripeKey)) {
            abort(500, 'Stripe API key not configured. Set STRIPE_SECRET_KEY in .env.');
        }

        \Stripe\Stripe::setApiKey($stripeKey);

        $products = Product::all();
        $LineItems = [];
        $totalPrice = 0;
        foreach ($products as $product) {
            $totalPrice += $product->price;
            $LineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                        'images' => [$product->image],
                    ],
                    'unit_amount' => $product->price * 100,
                ],
                'quantity' => 1,
            ];
        }

        $session = \Stripe\Checkout\Session::create([
            'line_items' => $LineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => route('checkout.cancel', [], true),
        ]);

        $orders = new Order();
        $orders->status = 'unpaid';
        $orders->total_price = $totalPrice;
        $orders->session_id = $session->id;
        $orders->save();

        return redirect()->away($session->url);
    }

    public function success(Request $request)
    {
        try {
            $session_id = $request->input('session_id');
            if (empty($session_id)) {
                abort(404, 'Missing Checkout session ID');
            }

            if (empty($stripeKey = trim(config('services.stripe.secret', env('STRIPE_SECRET_KEY'))))) {
                abort(500, 'Stripe API key not configured. Set STRIPE_SECRET_KEY in .env.');
            }

            \Stripe\Stripe::setApiKey($stripeKey);
            $session = \Stripe\Checkout\Session::retrieve($session_id, ['expand' => ['customer']]);

            $customerName = null;
            if (!empty($session->customer_details->name)) {
                $customerName = $session->customer_details->name;
            } elseif (is_string($session->customer)) {
                $customer = \Stripe\Customer::retrieve($session->customer);
                $customerName = $customer->name ?? null;
            } else {
                $customerName = $session->customer->name ?? null;
            }

            $order = Order::where('session_id', $session_id)->where('status', 'unpaid ')->first();
            if (!$order) {
                throw new NotFoundHttpException('Order not found for session ID: ' . $session_id);
            }
            $order->status = 'paid';
            $order->save();


            return view('product.checkout-success', compact('customerName'));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            abort(500, 'Stripe API error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    public function cancel(Request $request)
    {

    }
}
