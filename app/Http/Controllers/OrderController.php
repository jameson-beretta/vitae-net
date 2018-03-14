<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $orders = Order::all();
        return view('admin.orders', ['orders' => Order::all()]);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $this->authorize('create', Order::class);
        return view('admin.orders.create');
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Requests\CreateOrder $request) {
        $name = request('name');
        $file = $request->doc;

        // create a new patient using the form data
        $order = new \App\Order;
        $path = 'orders/';
        $fileName = str_replace(' ', '-', $name) . rand(1111, 9999) . '.pdf';
        $pathInStorage = $path . $fileName;
        $order->name = $name;
        $order->description = request('description');
        $order->file_path = $pathInStorage;
        $pat_id = request('patient_id');
        if ($pat_id !== null) {
            $order->patient_id = request('patient_id');
        }
        $order->completed = request('completed') || 0;

        // save it to the database
        $order->save();

        // store the pdf
        Storage::disk('public')->putFileAs($path, $file, $fileName);

        // redirect to home page
        return redirect()->route('orders.index')->with('message','Order has been added successfully');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $order = Order::findOrFail($id);
        $pdf = asset('storage/' . $order->file_path);
        return view('admin.order', ['order' => $order, 'pdf' => $pdf]);
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        return view('admin.order.edit', ['order' => $order]);
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Requests\UpdateOrder $request, $id)
    {
        $order = Order::find($id);
        $orderUpdate = $request->all();
        $order->update($orderUpdate);
        return redirect()->route('orders.index')->with('message','Order has been updated successfully');
    }

    public function complete(Request $request)
    {
        $id = $request->order_id;
        Order::findOrFail($id)->update(['completed' => 1]);
        return back();
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        // TODO: To be changed with correct storage location
        // $orderFile = $order->file_path;
        // File::delete('storage/' . $orderFile);
        Storage::disk('public')->delete($order->file_path);
        $order->delete();
        return redirect()->route('orders.index')->with('message','Order has been deleted successfully');
    }
}
