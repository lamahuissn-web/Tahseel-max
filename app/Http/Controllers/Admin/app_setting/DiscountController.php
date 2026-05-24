<?php
namespace App\Http\Controllers\Admin\app_setting;
use App\Http\Controllers\Controller;
use App\Models\app_setting\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::all();
        return view('dashbord.admin.app_setting.discounts.index', compact('discounts'));
    }

    public function create()
    {
        return view('dashbord.admin.app_setting.discounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'percentage' => 'required|numeric',
            'amount' => 'required|numeric',
            'max_limit' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            //'name' => 'required',
        ]);

        $insert_data = $request->all();
        $insert_data['name'] = ['en' => $request->name_en, 'ar' => $request->name_ar];
        $inserted_data = Discount::create($insert_data);
        $insert_id = $inserted_data->id;
        return redirect()->route('admin.app_setting.Discount.index')->with('success', 'Discount created successfully.');
    }

    public function edit($id)
    {
        $discount = Discount::findOrFail($id);
        return view('discounts.edit', compact('discount'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'percentage' => 'required|numeric',
            'amount' => 'required|numeric',
            'max_limit' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
          //  'name' => 'required',
        ]);

        $data = Discount::findOrFail($id);
        $update_data = $request->all();
        $update_data['name'] = ['en' => $request->name_en, 'ar' => $request->name_ar];
        $data->update($update_data);


        return redirect()->route('admin.app_setting.Discount.index')->with('success', 'Discount updated successfully.');
    }

    public function destroy($id)
    {
        Discount::destroy($id);
        return redirect()->route('admin.app_setting.Discount.index')->with('success', 'Discount deleted successfully.');
    }
}
