<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Illuminate\Http\Request;

class StyleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:styles,name',
            'identifier' => 'required|string|max:255|unique:styles,identifier',
            'price'      => 'nullable|numeric|min:0',
        ]);
        $data['price'] = $data['price'] ?? 0;

        Style::create($data);

        return redirect()->back()  
            ->with('success', 'تم إضافة الستايل بنجاح!');
    }

    public function update(Request $request, Style $style)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:styles,name,' . $style->id,
            'identifier' => 'required|string|max:255|unique:styles,identifier,' . $style->id,
            'price'      => 'nullable|numeric|min:0',
        ]);
        $data['price'] = $data['price'] ?? 0;

        $style->update($data);

        return redirect()->back()  
            ->with('success', 'تم تحديث الستايل بنجاح!');
    }

    public function destroy(Style $style)
    {
        $style->delete();

        return redirect()->back()  
            ->with('success', 'تم حذف الستايل بنجاح!');
    }
}