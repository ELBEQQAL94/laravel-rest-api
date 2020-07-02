<?php

namespace App\Http\Controllers\Seller;

use App\Transformers\ProductTransformer;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Seller;
use App\User;
use App\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        // tranform inputs to original attruibutes
        $this->middleware('transform.input:' . ProductTransformer::class)
        ->only(['store', 'update']);

        // seller can't do anything except show all products
        $this->middleware('scope:manage-products')
        ->except('index');

        // seller can only view all products
        $this->middleware('can:view,seller')->only('index');

        // seller can only sale product
        $this->middleware('can:sale,seller')->only('store');

        // seller can only update product
        $this->middleware('can:update,seller')->only('update');

        // seller can only destroy product
        $this->middleware('can:delete,seller')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        // manage-produtcs scope
        $manageProducts = request()->user()->tokenCan('manage-products');

        // read-general scope
        $readGeneral = request()->user()->tokenCan('read-general');

        // check if user have scopes for manage-products or read-general
        if($manageProducts || $readGeneral) {
            $products = $seller->products;
            return $this->showAll($products);
        }

        throw new AuthorizationException;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in:' . Product::AVAILABLE_PRODUCT . ',' . Product::UNAVAILABLE_PRODUCT,
            'image' => 'image',
        ];

        $this->validate($request, $rules);

        $this->checkSeller($seller, $product);

        $product->fill($request->only([
            'name',
            'quantity',
            'description',
        ]));

        // UPDATE STATUS
        if($request->has('status')) {
            $product->status = $request->status;

            if($product->isAvailable() && $product->categories()->count() == 0) {
                return $this->errorResponse('An active product must have at least one category.', 409);
            }
        }

        // UPDATE IMAGE
        if($request->hasFile('image')) {

            // delete current image
            Storage::delete($product->image);

            // store new image
            $product->image = $request->image->store('');
        }

        // NOTHING TO UPDATE PREVENT REQUEST
        if($product->isClean()) {
            return $this->errorResponse(
                'You need to specify a diffrent value to update',
                422
            );
        }

        // SAVE CHANGES VALUES
        $product->save();

        // UPDATE SUCCESS
        return $this->showOne($product);
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        // Check if seller owner of the product
        $this->checkSeller($seller, $product);

        Storage::delete($product->image);

        $product->delete();

        // DELETED SUCCESS
        return $this->showOne($product);
    }

    protected function checkSeller($seller, $product)
    {
        if($seller->id != $product->seller_id) {
            throw new HttpException(
                422,
                'The specified seller is not the actual seller of the product.'
            );
        }
    }

}
