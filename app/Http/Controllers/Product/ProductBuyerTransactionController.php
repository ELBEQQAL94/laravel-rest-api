<?php

namespace App\Http\Controllers\Product;

use App\Transformers\TransactionTransformer;
use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('transform.input:' . TransactionTransformer::class)
        ->only(['store']);
        $this->middleware('scope:purchase-product')->only('store');
        $this->middleware('can:purchase,buyer')->only('store');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules = [
            'quantity' => 'required|min:1',
        ];

        $this->validate($request, $rules);

        // to be sure that the buyer is not the seller of the product
        if($buyer->id == $product->seller_id) {
            return $this->errorResponse('The buyer must be not the seller', 409);
        }

        // check buyer is verified
        if(!$buyer->isVerified()) {
            return $this->errorResponse(
                'You should verified your account before can buy products',
                409
            );
        }

        // check seller is verified
        if(!$product->seller->isVerified()) {
            return $this->errorResponse(
                'You should verified your account before can buy products',
                409
            );
        }

        // check product if available
        if(!$product->isAvailable()) {
            return $this->errorResponse(
                'Sorry, The product is not available',
                409
            );
        }

        // check request quantity is less or equal than available quantity of product
        if($product->quantity < $request->quantity) {
            return $this->errorResponse(
                'Sorry, The product does have enough units for this transaction',
                409
            );
        }

        return DB::transaction(function() use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $product->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction, 201);
        });
    }
}
