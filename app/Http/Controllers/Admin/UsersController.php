<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Filter\Query\UserFilter;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(UserFilter $filter): View|Factory|Application
    {
        $users = User::filter($filter)->orderBy('id', 'asc')->paginate(8);
        return view('admin.users')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit(int $id)
    {
        $user = User::where('id', $id)->get()->first();
        return view('admin.user')->with('user', $user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     */
    public function update(Request $request, int $id)
    {
        $user = User::where('id', $id);

        if($user->get()->pluck('phone')->first() !== $request->phone){
            $request->validate([
                'phone'     => 'required|phone|unique:users,phone|size:11',
            ]);
        }
        if($user->get()->pluck('email')->first() !== $request->email){
            $request->validate([
                'email'     => 'required|regex:/(.+)@(.+)\.(.+)/i|unique:users,email',
            ]);
        }

        $request->validate([
            'name'      => 'required',
            'lastname'  => 'required',
        ]);



        try {
            $user->update([
                'name'     => $request->name,
                'phone'    => $request->phone,
                'lastname' => $request->lastname,
                'email'    => $request->email,
            ]);
            return response()->json(['message' => 'User successfully modified'], 200);
        }catch (Exception $e){
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     */
    public function destroy(Request $request)
    {
        try {
            User::where('id', (int)$request->id)->delete();
            return response()->json(['message' => 'User successfully deleted!'], 200);
        }catch (Exception $e){
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'new_password'  => 'required',
            'new_confirm_password'  => 'required|same:new_password',
        ]);

        try {
            $password = Hash::make($request->new_password);

            User::where('id',$request->id)->update([
                'password'     => $password,
            ]);

            return response()->json(['message' => 'Password changed successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
