<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Government;
use App\Models\Area;
use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GovernmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turboService = app(TurboService::class);

        $this->command->info(
            'جاري جلب أحدث بيانات المحافظات والمناطق من السيرفر الخارجي...'
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Fetch Governments First
            |--------------------------------------------------------------------------
            |
            | مهم:
            | لا نمسح البيانات القديمة قبل التأكد أن السيرفر رجّع بيانات صحيحة.
            |
            */

            $response = $turboService->getGovernments();

            $governments = $response['feed']
                ?? $response['data']
                ?? [];

            if (empty($governments)) {
                $this->command->warn(
                    'لم يتم العثور على بيانات للمحافظات من السيرفر، لذلك لم يتم حذف البيانات الحالية.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Delete Old Data
            |--------------------------------------------------------------------------
            |
            | areas مرتبطة بـ governments عن طريق Foreign Key.
            | لذلك نمسح areas أولاً ثم governments.
            |
            */

            $this->command->info(
                'جاري حذف المحافظات والمناطق القديمة...'
            );

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            Area::truncate();
            Government::truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->command->info(
                'تم حذف البيانات القديمة بنجاح.'
            );

            /*
            |--------------------------------------------------------------------------
            | 3. Progress Bar
            |--------------------------------------------------------------------------
            */

            $bar = $this->command
                ->getOutput()
                ->createProgressBar(count($governments));

            $bar->start();

            /*
            |--------------------------------------------------------------------------
            | 4. Insert Fresh Data
            |--------------------------------------------------------------------------
            */

            foreach ($governments as $gov) {

                /*
                |--------------------------------------------------------------------------
                | Save Government
                |--------------------------------------------------------------------------
                |
                | id:
                |     Internal Laravel ID
                |
                | real_id:
                |     ID القادم من Turbo
                |
                */

                $government = Government::create([
                    'real_id' => $gov['id'],
                    'name' => $gov['name'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Get Areas
                |--------------------------------------------------------------------------
                |
                | نستخدم real_id الخاص بالمحافظة عند طلب المناطق
                | من Turbo.
                |
                */

                $areasResponse = $turboService->getAreas(
                    $gov['id']
                );

                $areas = $areasResponse['feed']
                    ?? $areasResponse['data']
                    ?? [];

                /*
                |--------------------------------------------------------------------------
                | Save Areas
                |--------------------------------------------------------------------------
                */

                foreach ($areas as $area) {

                    Area::create([
                        /*
                        |--------------------------------------------------------------------------
                        | Internal Government ID
                        |--------------------------------------------------------------------------
                        |
                        | العلاقة المحلية تستخدم ID الداخلي.
                        |
                        */
                        'government_id' => $government->id,

                        /*
                        |--------------------------------------------------------------------------
                        | Turbo Area ID
                        |--------------------------------------------------------------------------
                        */
                        'real_id' => $area['id'],

                        'name' => $area['name'],
                    ]);
                }

                $bar->advance();
            }

            $bar->finish();

            $this->command->newLine();

            $this->command->info(
                'تم حذف البيانات القديمة وإضافة أحدث المحافظات والمناطق من Turbo بنجاح!'
            );

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            $this->command->info(
                'عدد المحافظات: ' . Government::count()
            );

            $this->command->info(
                'عدد المناطق: ' . Area::count()
            );
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Make sure Foreign Keys are enabled again
            |--------------------------------------------------------------------------
            */

            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $ignored) {
                // Ignore
            }

            Log::error(
                'Government Seeder Error: ' . $e->getMessage(),
                [
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $this->command->error(
                "\nحدث خطأ أثناء جلب وحفظ البيانات: "
                    . $e->getMessage()
            );
        }
    }
}
