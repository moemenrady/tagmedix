<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    /**
     * البحث عن العملاء بواسطة رقم الهاتف (phone_1 أو phone_2)
     */
    public function searchByPhone(string $phone): Collection
    {
        if (strlen($phone) < 3) {
            return new Collection();
        }

        return Customer::query()
            ->where('phone_1', 'LIKE', "%{$phone}%")
            ->orWhere('phone_2', 'LIKE', "%{$phone}%")
            ->with(['orderAddresses' => function ($q) {
                $q->latest()->limit(1); // أحدث عنوان مسجل للعميل
            }])
            ->limit(10)
            ->get();
    }

    /**
     * تحديث بيانات العميل الرئيسي أو إنشاؤه بدون التأثير على أرقام/بيانات الطلبات السابقة
     */
    public function upsertCustomer(array $data): Customer
    {
        $customer = Customer::where('phone_1', $data['phone_1'])->first();

        if ($customer) {
            $customer->update([
                'phone_2'           => $data['phone_2'] ?? $customer->phone_2,
                'instagram_profile' => $data['instagram_profile'] ?? $customer->instagram_profile,
            ]);
            return $customer;
        }

        return Customer::create([
            'phone_1'           => $data['phone_1'],
            'name'              => $data['customer_name'],
            'phone_2'           => $data['phone_2'] ?? null,
            'instagram_profile' => $data['instagram_profile'] ?? null,
        ]);
    }
}