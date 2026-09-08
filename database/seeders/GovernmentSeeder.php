<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Government;
use App\Models\Area;
use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Support\Facades\Log;

class GovernmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // استدعاء خدمة TurboService
        $turboService = app(TurboService::class);

        $this->command->info('جاري جلب بيانات المحافظات والمناطق من السيرفر الخارجي...');

        try {
            $response = $turboService->getGovernments();
            // استخراج البيانات مع مراعاة اختلاف الـ response
            $governments = $response['feed'] ?? $response['data'] ?? [];

            if (empty($governments)) {
                $this->command->warn('لم يتم العثور على بيانات للمحافظات.');
                return;
            }

            // إنشاء شريط تقدم ليوضح لك التحميل في الكونسول
            $bar = $this->command->getOutput()->createProgressBar(count($governments));
            $bar->start();

            foreach ($governments as $gov) {
                // 1. حفظ المحافظة
                Government::updateOrCreate(
                    ['id' => $gov['id']],
                    ['name' => $gov['name']]
                );
                
                // 2. جلب مناطق هذه المحافظة
                $areasResponse = $turboService->getAreas($gov['id']);
                $areas = $areasResponse['feed'] ?? $areasResponse['data'] ?? [];

                foreach ($areas as $area) {
                    // 3. حفظ المنطقة
                    Area::updateOrCreate(
                        ['id' => $area['id']],
                        [
                            'government_id' => $gov['id'], 
                            'name' => $area['name']
                        ]
                    );
                }

                $bar->advance();
            }

            $bar->finish();
            $this->command->info("\nتم سحب وحفظ جميع المحافظات والمناطق بنجاح!");

        } catch (\Throwable $e) {
            Log::error('Government Seeder Error: ' . $e->getMessage());
            $this->command->error("\nحدث خطأ أثناء جلب البيانات: " . $e->getMessage());
        }
    }
}