<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\Pen;
use App\Models\SalePayment;
use App\Models\StockMovement;
use Database\Seeders\DemoFarmDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoFarmDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_is_interconnected_repeatable_and_preserves_unrelated_records(): void
    {
        Animal::create(['tag_number' => 'KEEP-ANIMAL', 'sex' => 'female', 'status' => 'active']);
        FarmRecord::create(['module' => 'customers', 'reference' => 'KEEP-CUSTOMER', 'title' => 'Existing customer']);

        $seeder = app(DemoFarmDataSeeder::class);
        $seeder->run();

        $this->assertSame(90, Animal::query()->where('tag_number', 'like', 'DEMO-%')->count());
        $this->assertSame(30, Pen::query()->where('name', 'like', 'DEMO-PEN-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'breeding')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'farrowing')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(210, FarmRecord::query()->where('module', 'piglets')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'health')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'mortality')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'inventory')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'customers')->where('reference', 'like', 'DEMO-CUST-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'suppliers')->where('reference', 'like', 'DEMO-SUP-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'purchases')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'sales')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'expenses')->where('reference', 'like', 'DEMO-%')->count());
        $this->assertSame(30, SalePayment::query()->where('reference', 'like', 'DEMO-PAY-%')->count());
        $this->assertGreaterThanOrEqual(30, StockMovement::query()->where('reference', 'like', 'DEMO-%')->count());

        $this->assertSame(30, FarmRecord::query()->where('module', 'breeding')->where('reference', 'like', 'DEMO-%')->whereHas('animal', fn ($query) => $query->where('sex', 'female'))->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'farrowing')->where('reference', 'like', 'DEMO-%')->whereHas('animal', fn ($query) => $query->where('sex', 'female'))->whereHas('relatedRecord', fn ($query) => $query->where('module', 'breeding'))->count());
        $this->assertSame(210, FarmRecord::query()->where('module', 'piglets')->where('reference', 'like', 'DEMO-%')->whereHas('relatedRecord', fn ($query) => $query->where('module', 'farrowing'))->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'sales')->where('reference', 'like', 'DEMO-%')->whereHas('relatedRecord', fn ($query) => $query->where('module', 'customers'))->whereHas('payments')->count());
        $this->assertSame(30, FarmRecord::query()->where('module', 'mortality')->where('reference', 'like', 'DEMO-%')->whereHas('animal', fn ($query) => $query->where('status', 'deceased'))->count());

        $this->assertDatabaseHas('animals', ['tag_number' => 'KEEP-ANIMAL']);
        $this->assertDatabaseHas('farm_records', ['reference' => 'KEEP-CUSTOMER']);

        $countsBefore = [
            Animal::query()->count(),
            Pen::query()->count(),
            FarmRecord::query()->count(),
            SalePayment::query()->count(),
            StockMovement::query()->count(),
        ];
        $seeder->run();
        $this->assertSame($countsBefore, [
            Animal::query()->count(),
            Pen::query()->count(),
            FarmRecord::query()->count(),
            SalePayment::query()->count(),
            StockMovement::query()->count(),
        ]);
        $this->assertGreaterThanOrEqual(0, FarmRecord::query()->where('module', 'inventory')->where('reference', 'like', 'DEMO-%')->min('quantity'));
    }
}
