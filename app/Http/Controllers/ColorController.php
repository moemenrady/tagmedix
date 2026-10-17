<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:colors,name',
            'identifier' => 'required|string|max:255|unique:colors,identifier',
        ]);

        $color = Color::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'color' => $color
            ]);
        }

         return redirect()->back()  
            ->with('success', 'Color added successfully!');
    }

    public function update(Request $request, Color $color)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:colors,name,' . $color->id,
            'identifier' => 'required|string|max:255|unique:colors,identifier,' . $color->id,
        ]);

        $color->update($data);

         return redirect()->back()  
            ->with('success', 'Color updated successfully!');
    }

    public function destroy(Color $color)
    {
        $color->delete();

        return redirect()->back()
            ->with('success', 'Color deleted successfully!');
    }
}
