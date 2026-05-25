<?php
// app/Http/Requests/StoreInvoiceRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.color_id' => ['nullable'], // نحتاجه لجلب السعر بدقة
            'items.*.size_id' => ['nullable'],
            'items.*.color_name' => ['nullable', 'string'],
            'items.*.size_name' => ['nullable', 'string'],
            'items.*.is_printed' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string']
        ];
    }
}
