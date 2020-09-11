<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * 下单接口
     *
     * @param OrderRequest $orderRequest
     * @return void
     */
    public function store(OrderRequest $orderRequest, OrderService $orderService)
    {
        $user = $orderRequest->user();
        $address = UserAddress::find($orderRequest->input('address_id'));

        return $orderService->store($user, $address, $orderRequest->input('remark'), $orderRequest->input('items'));
    }

    /**
     * 订单列表
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    /**
     * 订单详情
     *
     * @param Order $order
     * @param Request $request
     * @return void
     */
    public function show(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.product', 'items.productSku'])]);
    }
}
