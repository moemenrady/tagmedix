<?php

namespace App\Http\Controllers;

use App\Models\SleeveType;
use Illuminate\Http\Request;

class SleeveTypeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:sleeve_types,name',
            'identifier' => 'required|string|max:255|unique:sleeve_types,identifier',
        ]);

        SleeveType::create($data);

        return redirect()->back()
            ->with('success', 'Sleeve Type added successfully!');
    }

    public function update(Request $request, SleeveType $sleeveType)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:sleeve_types,name,' . $sleeveType->id,
            'identifier' => 'required|string|max:255|unique:sleeve_types,identifier,' . $sleeveType->id,
        ]);

        $sleeveType->update($data);

        return redirect()->back()
            ->with('success', 'Sleeve Type updated successfully!');
    }

    public function destroy(SleeveType $sleeveType)
    {
        $sleeveType->delete();

        return redirect()->back()
            ->with('success', 'Sleeve Type deleted successfully!');
    }
}