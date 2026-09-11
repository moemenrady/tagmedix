<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    /**
     * توليد صيغ مختلفة لرقم الهاتف للبحث والمطابقة (مثل +2012... و 012... و 2012...)
     */
    public function getPhoneVariations(string $phone): array
    {
        $variations = [trim($phone)];
        $digits = preg_replace('/[^\d]/', '', $phone);
        if (empty($digits)) {
            return array_values(array_unique($variations));
        }

        $variations[] = $digits;
        $variations[] = '+' . $digits;

        // الأرقام المصرية (12 رقم تبدأ بـ 201)
        if (str_starts_with($digits, '201') && strlen($digits) === 12) {
            $local = '0' . substr($digits, 2); // 01223237666
            $variations[] = $local;
            $variations[] = '00' . $digits;
        }
        // الأرقام المصرية المحلية (11 رقم تبدأ بـ 01)
        elseif (str_starts_with($digits, '01') && strlen($digits) === 11) {
            $intl = '20' . substr($digits, 1); // 201223237666
            $variations[] = $intl;
            $variations[] = '+' . $intl;
            $variations[] = '00' . $intl;
        }
        // الأرقام التي تبدأ بـ 00 ككود دولي
        elseif (str_starts_with($digits, '00')) {
            $withoutZeros = substr($digits, 2);
            $variations[] = $withoutZeros;
            $variations[] = '+' . $withoutZeros;
        }

        return array_values(array_unique(array_filter($variations)));
    }

    /**
     * البحث عن العملاء بواسطة رقم الهاتف (phone_1 أو phone_2)
     */
    public function searchByPhone(string $phone): Collection
    {
        $clean = trim($phone);
        if (strlen($clean) < 3) {
            return new Collection();
        }

        $variations = $this->getPhoneVariations($clean);
        $digits = preg_replace('/[^\d]/', '', $clean);

        return Customer::query()
            ->where(function ($q) use ($variations, $digits) {
                foreach ($variations as $var) {
                    $q->orWhere('phone_1', 'LIKE', "%{$var}%")
                      ->orWhere('phone_2', 'LIKE', "%{$var}%");
                }
                // إذا تم إدخال 6 أرقام أو أكثر، ابحث بآخر 8 أرقام للمطابقة مع أي صيغة محلية/دولية
                if (strlen($digits) >= 6) {
                    $suffix = substr($digits, -8);
                    $q->orWhere('phone_1', 'LIKE', "%{$suffix}%")
                      ->orWhere('phone_2', 'LIKE', "%{$suffix}%");
                }
            })
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
        $phone1 = $data['phone_1'];
        $variations = $this->getPhoneVariations($phone1);

        $customer = Customer::where('phone_1', $phone1)->first();
        if (!$customer) {
            $customer = Customer::whereIn('phone_1', $variations)
                ->orWhereIn('phone_2', $variations)
                ->first();
        }

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