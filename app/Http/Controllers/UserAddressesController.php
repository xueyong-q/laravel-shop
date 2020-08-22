<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    /**
     * 收货地址列表
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        return view('user_addresses.index', [
            'addresses' => $request->user()->addresses,
        ]);
    }

    /**
     * 新增修改地址页面
     *
     * @return void
     */
    public function create()
    {
        return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }

    /**
     * 保存地址信息
     *
     * @param UserAddressRequest $request
     * @return void
     */
    public function store(UserAddressRequest $request)
    {
        $request->user()->addresses()->create($request->only([
            'province', 'city', 'district', 'address', 'zip', 'contact_name', 'contact_phone'
        ]));

        return redirect()->route('user_addresses.index');
    }

    /**
     * 更新地址界面
     *
     * @param UserAddress $user_address
     * @return void
     */
    public function edit(UserAddress $user_address)
    {
        $this->authorize('own', $user_address);

        return view('user_addresses.create_and_edit', ['address' => $user_address]);
    }

    /**
     * 更新地址
     *
     * @param UserAddress $user_address
     * @param UserAddressRequest $request
     * @return void
     */
    public function update(UserAddress $user_address, UserAddressRequest $request)
    {
        $this->authorize('own', $user_address);

        $user_address->update($request->only([
            'province', 'city', 'district', 'address', 'zip', 'contact_name', 'contact_phone'
        ]));

        return redirect()->route('user_addresses.index');
    }

    /**
     * 删除地址
     *
     * @param UserAddress $user_address
     * @return void
     */
    public function destroy(UserAddress $user_address)
    {
        $this->authorize('own', $user_address);
        
        $user_address->delete();

        return [];
    }
}
