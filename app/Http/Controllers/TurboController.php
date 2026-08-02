<?php

namespace App\Http\Controllers;

use App\Services\Turbo\TurboApiService;
use Illuminate\Http\Request;

class TurboController extends Controller
{
    public function __construct(
        protected TurboApiService $turboApi
    ) {}

    /**
     * شاشة التذاكر
     */
    public function index()
    {
        $turbo = $this->turboApi->getTurbo();

        $categories = $this->turboApi->getCategories();

        $statuses = $this->turboApi->getStatuses();

        return view(

            'turbo.index',

            compact(

                'turbo',

                'categories',

                'statuses'

            )

        );
    }
    public function store(Request $request)
    {
        $validated = $request->validate([

            'category_id' => [

                'required',

                'integer',

            ],

            'content' => [

                'required',

                'string',

                'min:10',

            ],

            'code' => [

                'nullable',

                'string',

            ],

            'media.*' => [

                'nullable',

                'file',

                'max:10240',

            ],

        ]);

        $turbo = $this->turboApi->createTurbo(

            categoryId: $validated['category_id'],

            content: $validated['content'],

            code: $validated['code'] ?? null,

            media: $request->file('media', [])

        );

        return response()->json([

            'success' => true,

            'message' => 'Turbo created successfully',

            'data' => $turbo,

        ]);
    }
    public function addMessage(
        Request $request,

        int $turboId
    ) {

        $validated = $request->validate([

            'content' => [

                'required',

                'string',

            ],

            'media.*' => [

                'nullable',

                'file',

                'max:10240',

            ],

        ]);

        $message = $this->turboApi->addMessage(

            turboId: $turboId,

            content: $validated['content'],

            media: $request->file('media', [])

        );

        return response()->json([

            'success' => true,

            'message' => 'Message added successfully',

            'data' => $message,

        ]);
    }
    public function rate(
        Request $request,

        int $turboId
    ) {

        $validated = $request->validate([

            'rate' => [

                'required',

                'integer',

                'min:1',

                'max:5',

            ],

        ]);

        $result = $this->turboApi->rateTurbo(

            turboId: $turboId,

            rate: $validated['rate']

        );

        return response()->json([

            'success' => true,

            'data' => $result,

        ]);
    }
}
