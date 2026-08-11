<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:sizes,name',
            'identifier' => 'required|string|max:255|unique:sizes,identifier',
        ]);

        Size::create($data);

         return redirect()->back()  
            ->with('success', 'Size added successfully!');
    }

    public function update(Request $request, Size $size)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:sizes,name,' . $size->id,
            'identifier' => 'required|string|max:255|unique:sizes,identifier,' . $size->id,
        ]);

        $size->update($data);

         return redirect()->back()  
            ->with('success', 'Size updated successfully!');
    }

    public function destroy(Size $size)
    {
        $size->delete();

         return redirect()->back()  
            ->with('success', 'Size deleted successfully!');
    }
}