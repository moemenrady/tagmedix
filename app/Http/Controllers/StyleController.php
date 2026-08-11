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
        ]);

        Style::create($data);

         return redirect()->back()  
            ->with('success', 'Style added successfully!');
    }

    public function update(Request $request, Style $style)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:styles,name,' . $style->id,
            'identifier' => 'required|string|max:255|unique:styles,identifier,' . $style->id,
        ]);

        $style->update($data);

         return redirect()->back()  
            ->with('success', 'Style updated successfully!');
    }

    public function destroy(Style $style)
    {
        $style->delete();

         return redirect()->back()  
            ->with('success', 'Style deleted successfully!');
    }
}