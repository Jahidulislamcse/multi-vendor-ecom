<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainOrder;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orderList = MainOrder::with(['customerInfo', 'vendor'])->orderBy('id', 'desc')->get();
        return view('admin.order.index', compact('orderList'));
    }

    public function orderDetails(Request $request, $id = null)
    {

        $orderInfo = MainOrder::with('orderDetails', 'vendor', 'customerInfo', 'orderDetails.productInfo', 'orderDetails.productInfo.imagesProduct', 'orderDetails.stockInfo')->where('id', $request->id)->first();
        return view('admin.order.details', compact('orderInfo'));
    }

    public function PendingOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'pending')->orderBy('id', 'DESC')->get();

        $countOrder = MainOrder::where('status', 'pending')->count('id');
        return view('admin.order.pending', compact('orderList', 'countOrder'));
    }

    public function ConfirmedOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'confirm')->orderBy('id', 'DESC')->get();


        return view('admin.order.confirm', compact('orderList'));
    }

    public function ProcessingOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'processing')->orderBy('id', 'DESC')->get();


        return view('admin.order.processing', compact('orderList'));
    }

    public function DeliveredOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'deliverd')->orderBy('id', 'DESC')->get();


        return view('admin.order.confirm', compact('orderList'));
    }

    public function CancledOrder()
    {

        $orderList = MainOrder::with(['customerInfo', 'vendor'])->where('status', 'cancel')->orderBy('id', 'DESC')->get();


        return view('admin.order.cancle', compact('orderList'));
    }

    public function PendingToConfirm($order_id)
    {
        MainOrder::findOrFail($order_id)->update(['status' => 'confirm', 'confirmed_date' => Carbon::now()->format('d F Y')]);
        OrderItem::where('main_order_id', $order_id)
            ->update([
                'status' => 'confirm',
            ]);
        $notification = array(
            'message' => 'Order Confirm Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.order.confirmed')->with($notification);
    }

    public function PendingToCancel($order_id)
    {
        MainOrder::findOrFail($order_id)->update([
            'status' => 'cancel',
            'cancel_date' => Carbon::now()->format('d F Y'),
        ]);
        OrderItem::where('main_order_id', $order_id)
            ->update([
                'status' => 'cancel',
            ]);
        $order = MainOrder::where('id', $order_id)->first();
        $orderItem = OrderItem::where('main_order_id', $order_id)->get();
        foreach ($orderItem as $item) {
            $product = Stock::where('id', $item->stock_info_id)->first();
            $product->order_qty = $product->order_qty - $item->qty;
            $product->quantity = $product->quantity + $item->qty;
            $product->save();
        }

        $notification = array(
            'message' => 'Order Cancel Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.order.cancled')->with($notification);
    }


    public function ConfirmToProcess($order_id)
    {
        MainOrder::findOrFail($order_id)->update(['status' => 'processing', 'processing_date' => Carbon::now()->format('d F Y')]);
        OrderItem::where('main_order_id', $order_id)
            ->update([
                'status' => 'processing',
            ]);
        $notification = array(
            'message' => 'Order Processing Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.order.processing')->with($notification);
    }


    public function ProcessToDelivered($order_id)
    {


        MainOrder::findOrFail($order_id)->update(['status' => 'deliverd', 'delivered_date' => Carbon::now()]);
        OrderItem::where('main_order_id', $order_id)
            ->update([
                'status' => 'deliverd',
            ]);
        $notification = array(
            'message' => 'Order Deliverd Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.order.delivered')->with($notification);
    }


    public function AdminInvoiceDownload($order_id)
    {

        $order = MainOrder::with('customerInfo')->where('id', $order_id)->first();
        $orderItem = OrderItem::with('productInfo', 'productInfo.imagesProduct', 'stockInfo')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        $currency = env('currency', '৳');
        $pdf = Pdf::loadView('admin.order.invoice', compact('order', 'orderItem', 'currency'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);
        return $pdf->download($order->invoice_no . '.pdf');
    }
}
