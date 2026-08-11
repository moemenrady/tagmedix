<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:types,name',
            'identifier' => 'required|string|max:255|unique:types,identifier',
        ]);

        Type::create($data);

         return redirect()->back()  
            ->with('success', 'Type added successfully!');
    }

    public function update(Request $request, Type $type)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:types,name,' . $type->id,
            'identifier' => 'required|string|max:255|unique:types,identifier,' . $type->id,
        ]);

        $type->update($data);

         return redirect()->back()  
            ->with('success', 'Type updated successfully!');
    }

    public function destroy(Type $type)
    {
        $type->delete();

         return redirect()->back()  
            ->with('success', 'Type deleted successfully!');
    }
}