<?php

namespace App\Http\Controllers;

use App\Models\PantType;
use App\Models\Type;
use Illuminate\Http\Request;

class PantTypeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:pant_types,name',
            'identifier' => 'required|string|max:255|unique:pant_types,identifier',
        ]);

        PantType::create($data);

         return redirect()->back()  
            ->with('success', 'Pant Type added successfully!');
    }

    public function update(Request $request, PantType $pantType)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:pant_types,name,' . $pantType->id,
            'identifier' => 'required|string|max:255|unique:pant_types,identifier,' . $pantType->id,
        ]);

        $pantType->update($data);

         return redirect()->back()  
            ->with('success', 'Pant Type updated successfully!');
    }

    public function destroy(PantType $pantType)
    {
        $pantType->delete();

         return redirect()->back()  
            ->with('success', 'Pant Type deleted successfully!');
    }
}