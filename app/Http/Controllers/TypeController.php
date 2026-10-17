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
            'price'      => 'nullable|numeric|min:0',
        ]);
        $data['price'] = $data['price'] ?? 0;

        Type::create($data);

        return redirect()->back()  
            ->with('success', 'تم إضافة النوع بنجاح!');
    }

    public function update(Request $request, Type $type)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:types,name,' . $type->id,
            'identifier' => 'required|string|max:255|unique:types,identifier,' . $type->id,
            'price'      => 'nullable|numeric|min:0',
        ]);
        $data['price'] = $data['price'] ?? 0;

        $type->update($data);

        return redirect()->back()  
            ->with('success', 'تم تحديث النوع بنجاح!');
    }

    public function destroy(Type $type)
    {
        $type->delete();

        return redirect()->back()  
            ->with('success', 'تم حذف النوع بنجاح!');
    }
}