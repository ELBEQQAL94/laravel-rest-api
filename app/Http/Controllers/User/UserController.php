<?php

namespace App\Http\Controllers\User;

use App\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['store', 'resend']);
        $this->middleware('auth:api')->except(['store', 'verify', 'resend']);
        $this->middleware('transform.input:' . UserTransformer::class)
        ->only(['store', 'update']);
        $this->middleware('scope:manage-account')->only(['show', 'update']);

        // only authenticated user can show account
        $this->middleware('can:view,user')->only('show');

        // only authenticated user can update account
        $this->middleware('can:update,user')->only('update');

        // only authenticated user can delete account
        $this->middleware('can:delete,user')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->allowedAdminAction();

        $users = User::all();

        return $this->showAll($users);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        // check if user already exists
        $checkIfUserExists = DB::table('users')->where('email',$request->email)->first();

        if($checkIfUserExists) {
            return $this->errorResponse('Email already exists', 409);
        }

        $this->validate($request, $rules);

        $data = $request->all();


        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        // create user
        $user =  User::create($data);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {

        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'required|min:6|confirmed',
            'admin' => 'in:' . User::ADMIN_USER . ',' . USer::REGULAR_USER,
        ];

        // UPDATE NAME
        if($request->has('name')) {
            $user->name = $request->name;
        }

        // UPDATE EMAIL
        if($request->has('email') && $user->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        // UPDATE PASSWORD
        if($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        // UPDATE ADMIN
        if($request->has('admin')) {
            // only admin user can change admin status
            $this->allowedAdminAction();

            if(!$user->isVerified()) {
                return $this->errorResponse('Only verified users can modified the admin field', 409);
            }

            $user->admin = $request->admin;

        }

        // NOTHING TO UPDATE PREVENT REQUEST
        if(!$user->isDirty()) {
            return $this->errorResponse('You need to specify a diffrent value to update', 422);
        }

        // SAVE CHANGES VALUES
        $user->save();

        // UPDATE SUCCESS
        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        // DELETED SUCCESS
        return $this->showOne($user);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;

        $user->save();

        return $this->showMessage('The account has been verified successfully');
    }

    public function resend(User $user)
    {
        // check if user is verified
        if($user->isVerified()) {
            return $this->errorResponse('This user is already verified', 409);
        }

        retry(5, function() use ($user) {
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('The verification email has been send, Please check your inbox.');
    }

}
