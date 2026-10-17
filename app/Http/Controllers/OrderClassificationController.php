<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\PantType;
use App\Models\Size;
use App\Models\SleeveType;
use App\Models\Style;
use App\Models\Type;
use Illuminate\Http\Request;

class OrderClassificationController extends Controller
{
  protected function getModel()
{
    $resource = request()->segment(2);

    return match ($resource) {
        'colors'      => Color::class,
        'sizes'       => Size::class,
        'styles'      => Style::class,
        'types'       => Type::class,
        'pant_types'  => PantType::class,
        'sleeve_types'=> SleeveType::class,
        default       => abort(404),
    };
}

public function index(Request $request)
{
    $activeTab = $request->get('tab', 'colors');

    $colors = Color::latest()->get();
    $sizes = Size::latest()->get();
    $styles = Style::latest()->get();
    $types = Type::latest()->get();
    $pantTypes = PantType::latest()->get();
    $sleeveTypes = SleeveType::latest()->get();

    return view(
        'pages.order_classifications.index',
        compact(
            'colors',
            'sizes',
            'styles',
            'types',
            'pantTypes',
            'sleeveTypes',
            'activeTab'
        )
    );
}

    public function store(Request $request)
    {
        $model = $this->getModel();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:255',
        ]);

        $item = $model::create($data);

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    public function show(string $id)
    {
        $model = $this->getModel();

        return response()->json(
            $model::findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $model = $this->getModel();

        $item = $model::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:255',
        ]);

        $item->update($data);

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroy(string $id)
    {
        $model = $this->getModel();

        $item = $model::findOrFail($id);

        $item->delete();

        return response()->json([
            'success' => true,
        ]);
    }
    public function colors()
    {
        return Color::latest()->get();
    }

    public function sizes()
    {
        return Size::latest()->get();
    }

    public function styles()
    {
        return Style::latest()->get();
    }
}
