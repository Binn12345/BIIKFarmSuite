<?php

namespace Tests\Feature;

use App\Livewire\Farm\AnimalIndex;
use App\Livewire\Farm\FarmRecordIndex;
use App\Livewire\Farm\FarmReportIndex;
use App\Livewire\Farm\PenIndex;
use App\Models\Animal;
use App\Models\Pen;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FarmManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_animal_and_pen_routes_are_protected_and_render_crud_pages(): void
    {
        $this->get(route('modules.animals'))->assertRedirect(route('login'));
        $this->get(route('modules.pens'))->assertRedirect(route('login'));

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('modules.animals'))
            ->assertOk()
            ->assertSee('Animal Management')
            ->assertSee('Register animal');

        $this->get(route('modules.pens'))
            ->assertOk()
            ->assertSee('Pens Management')
            ->assertSee('Add pen');

        Livewire::actingAs($admin)->test(AnimalIndex::class)
            ->call('createAnimal')
            ->set('tag_number', 'ADMIN-PIG')
            ->set('sex', 'female')
            ->call('saveAnimal')
            ->assertHasNoErrors();

        Livewire::actingAs($admin)->test(PenIndex::class)
            ->call('createPen')
            ->set('name', 'Admin pen')
            ->set('capacity', '2')
            ->call('savePen')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('animals', ['tag_number' => 'ADMIN-PIG']);
        $this->assertDatabaseHas('pens', ['name' => 'Admin pen']);
    }

    public function test_basic_users_need_view_permission_for_animal_and_pen_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');

        $this->actingAs($user)->get(route('modules.animals'))->assertForbidden();
        $this->get(route('modules.pens'))->assertForbidden();

        Role::findByName('basic-user')->givePermissionTo(['animals.view', 'pens.view']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('modules.animals'))
            ->assertSee(route('modules.pens'));

        $this->get(route('modules.animals'))->assertOk();
        $this->get(route('modules.pens'))->assertOk();
    }

    public function test_animal_crud_validates_unique_tags_tracks_actor_and_archives_history(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo([
            'animals.view',
            'animals.create',
            'animals.update',
            'animals.delete',
        ]);

        $pen = Pen::create(['name' => 'North pen', 'capacity' => 2, 'status' => 'active']);

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('createAnimal')
            ->set('tag_number', 'PIG-001')
            ->set('name', 'Daisy')
            ->set('breed', 'Large White')
            ->set('sex', 'female')
            ->set('birth_date', now()->subMonths(3)->toDateString())
            ->set('pen_id', $pen->id)
            ->call('saveAnimal')
            ->assertHasNoErrors()
            ->assertSee('Animal record saved.');

        $animal = Animal::firstOrFail();
        $this->assertSame('PIG-001', $animal->tag_number);
        $this->assertSame($user->id, $animal->created_by);
        $this->assertSame($user->id, $animal->updated_by);
        $this->assertSame($pen->id, $animal->pen_id);

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('createAnimal')
            ->set('tag_number', 'PIG-001')
            ->set('sex', 'male')
            ->call('saveAnimal')
            ->assertHasErrors(['tag_number' => 'unique']);

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('showAnimal', $animal->id)
            ->assertSee('Daisy')
            ->call('editAnimal', $animal->id)
            ->set('name', 'Daisy Updated')
            ->call('saveAnimal')
            ->assertHasNoErrors()
            ->assertSee('Animal record saved.');

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('archiveAnimal', $animal->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'archived']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'animal.archived', 'subject_id' => (string) $animal->id]);
    }

    public function test_animals_cannot_be_assigned_beyond_pen_capacity_or_to_maintenance_pens(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo(['animals.view', 'animals.create']);
        $pen = Pen::create(['name' => 'Full pen', 'capacity' => 1, 'status' => 'active']);
        Animal::create(['tag_number' => 'EXISTING', 'sex' => 'male', 'status' => 'active', 'pen_id' => $pen->id]);

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('createAnimal')
            ->set('tag_number', 'NEW-ANIMAL')
            ->set('sex', 'female')
            ->set('pen_id', $pen->id)
            ->call('saveAnimal')
            ->assertHasErrors('pen_id');

        $pen->update(['status' => 'maintenance']);
        $pen->update(['capacity' => 2]);

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('createAnimal')
            ->set('tag_number', 'NEW-ANIMAL')
            ->set('sex', 'female')
            ->set('pen_id', $pen->id)
            ->call('saveAnimal')
            ->assertHasErrors('pen_id');
    }

    public function test_pen_crud_prevents_capacity_reduction_and_deletion_with_animal_history(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo([
            'pens.view',
            'pens.create',
            'pens.update',
            'pens.delete',
        ]);

        Livewire::actingAs($user)->test(PenIndex::class)
            ->call('createPen')
            ->set('name', 'South pen')
            ->set('capacity', '3')
            ->set('location', 'South barn')
            ->call('savePen')
            ->assertHasNoErrors()
            ->assertSee('Pen record saved.');

        $pen = Pen::where('name', 'South pen')->firstOrFail();
        Animal::create(['tag_number' => 'PIG-SOUTH', 'sex' => 'male', 'status' => 'active', 'pen_id' => $pen->id]);
        Animal::create(['tag_number' => 'PIG-SOUTH-2', 'sex' => 'female', 'status' => 'active', 'pen_id' => $pen->id]);

        Livewire::actingAs($user)->test(PenIndex::class)
            ->call('showPen', $pen->id)
            ->assertSee('PIG-SOUTH')
            ->call('editPen', $pen->id)
            ->set('capacity', '1')
            ->call('savePen')
            ->assertHasErrors('capacity');

        Livewire::actingAs($user)->test(PenIndex::class)
            ->call('deletePen', $pen->id)
            ->assertHasErrors('pen');

        $this->assertDatabaseHas('pens', ['id' => $pen->id]);

        $emptyPen = Pen::create(['name' => 'Empty pen', 'capacity' => 1, 'status' => 'active']);
        Livewire::actingAs($user)->test(PenIndex::class)
            ->call('deletePen', $emptyPen->id)
            ->assertHasNoErrors()
            ->assertSee('Pen deleted.');
        $this->assertDatabaseMissing('pens', ['id' => $emptyPen->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'pen.deleted', 'subject_id' => (string) $emptyPen->id]);
    }

    public function test_livewire_mutations_enforce_each_module_action_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo(['animals.view', 'pens.view']);

        Livewire::actingAs($user)->test(AnimalIndex::class)
            ->call('createAnimal')
            ->assertForbidden();

        Livewire::actingAs($user)->test(PenIndex::class)
            ->call('createPen')
            ->assertForbidden();
    }

    public function test_generic_farm_modules_have_real_permission_protected_routes_and_persist_records(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        foreach (['piglets', 'breeding', 'farrowing', 'health', 'feeding', 'mortality', 'inventory', 'customers', 'suppliers', 'purchases', 'sales', 'expenses', 'animal-categories', 'breeds'] as $module) {
            $this->actingAs($admin)->get(route('modules.'.$module))->assertOk();
        }

        $user = User::factory()->create();
        $user->assignRole('basic-user');
        $this->actingAs($user)->get(route('modules.customers'))->assertForbidden();
        Role::findByName('basic-user')->givePermissionTo(['customers.view', 'customers.create']);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'customers'])
            ->call('createRecord')
            ->set('form.title', 'Market customer')
            ->set('form.email', 'market@example.test')
            ->call('saveRecord')
            ->assertHasNoErrors()
            ->assertSee('Customer Management record saved.');

        $this->assertDatabaseHas('farm_records', [
            'module' => 'customers',
            'title' => 'Market customer',
            'created_by' => $user->id,
        ]);
    }

    public function test_completed_animal_sales_prevent_duplicate_sale_and_cancellation_restores_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo(['sales.view', 'sales.create', 'sales.update', 'sales.cancel']);
        $animal = Animal::create(['tag_number' => 'SALE-PIG', 'sex' => 'female', 'status' => 'active']);

        $createSale = fn (string $reference) => Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('createRecord')
            ->set('form.reference', $reference)
            ->set('form.record_date', now()->toDateString())
            ->set('form.related_animal_id', (string) $animal->id)
            ->set('form.title', 'Animal sale')
            ->set('form.quantity', '1')
            ->set('form.unit_price', '450.50')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $createSale('SALE-001');
        $sale = \App\Models\FarmRecord::where('module', 'sales')->firstOrFail();
        $this->assertSame('450.50', $sale->fresh()->amount);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('completeSale', $sale->id)
            ->assertHasNoErrors();
        $this->assertSame('sold', $animal->fresh()->status);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('createRecord')
            ->set('form.reference', 'SALE-002')
            ->set('form.record_date', now()->toDateString())
            ->set('form.related_animal_id', (string) $animal->id)
            ->set('form.title', 'Animal sale')
            ->set('form.quantity', '1')
            ->set('form.unit_price', '450.50')
            ->call('saveRecord')
            ->assertHasErrors('form.related_animal_id');

        $secondSale = \App\Models\FarmRecord::create([
            'module' => 'sales',
            'reference' => 'SALE-002',
            'record_date' => now()->toDateString(),
            'related_animal_id' => $animal->id,
            'title' => 'Animal sale',
            'quantity' => 1,
            'unit_price' => 450.50,
            'amount' => 450.50,
            'status' => 'draft',
        ]);
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('completeSale', $secondSale->id)
            ->assertStatus(409);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('cancelSale', $sale->id)
            ->assertHasNoErrors();
        $this->assertSame('active', $animal->fresh()->status);
        $this->assertSame('cancelled', $sale->fresh()->status);
    }

    public function test_purchase_receiving_updates_inventory_once_and_stock_cannot_go_negative(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo([
            'inventory.view', 'inventory.create', 'inventory.update',
            'purchases.view', 'purchases.create', 'purchases.receive',
        ]);
        $supplier = \App\Models\FarmRecord::create([
            'module' => 'suppliers',
            'title' => 'Feed supplier',
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $inventory = \App\Models\FarmRecord::create([
            'module' => 'inventory',
            'reference' => 'FEED-01',
            'title' => 'Feed',
            'quantity' => 10,
            'status' => 'active',
        ]);
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'purchases'])
            ->call('createRecord')
            ->set('form.reference', 'PUR-01')
            ->set('form.record_date', now()->toDateString())
            ->set('form.related_record_id', (string) $supplier->id)
            ->set('form.inventory_record_id', (string) $inventory->id)
            ->set('form.title', 'Feed bag')
            ->set('form.quantity', '5')
            ->set('form.unit', 'bag')
            ->set('form.unit_price', '20')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $purchase = \App\Models\FarmRecord::where('module', 'purchases')->firstOrFail();
        $this->assertSame('100.00', $purchase->amount);
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'purchases'])
            ->call('receivePurchase', $purchase->id)
            ->assertHasNoErrors();
        $this->assertSame('15.000', $inventory->fresh()->quantity);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'purchases'])
            ->call('receivePurchase', $purchase->id)
            ->assertStatus(409);
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'inventory'])
            ->set('stockAdjustment', '-20')
            ->call('adjustInventory', $inventory->id)
            ->assertStatus(422);
        $this->assertSame('15.000', $inventory->fresh()->quantity);
    }

    public function test_mortality_updates_animal_status_and_preserves_the_event(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo(['mortality.view', 'mortality.create']);
        $animal = Animal::create(['tag_number' => 'MORTALITY-PIG', 'sex' => 'male', 'status' => 'active']);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'mortality'])
            ->call('createRecord')
            ->set('form.record_date', now()->toDateString())
            ->set('form.related_animal_id', (string) $animal->id)
            ->set('form.category', 'unknown')
            ->set('form.notes', 'Cause not determined')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $this->assertSame('deceased', $animal->fresh()->status);
        $this->assertDatabaseHas('farm_records', ['module' => 'mortality', 'related_animal_id' => $animal->id]);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'mortality'])
            ->call('createRecord')
            ->set('form.record_date', now()->toDateString())
            ->set('form.related_animal_id', (string) $animal->id)
            ->call('saveRecord')
            ->assertHasErrors('form.related_animal_id');
    }

    public function test_sales_collect_payments_without_exceeding_total_and_write_an_immutable_movement_ledger(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo([
            'sales.view',
            'sales.create',
            'sales.update',
            'inventory.view',
            'inventory.create',
            'inventory.update',
        ]);
        $inventory = \App\Models\FarmRecord::create([
            'module' => 'inventory',
            'reference' => 'MEAT-1',
            'title' => 'Farm product',
            'quantity' => 5,
            'status' => 'active',
        ]);

        $inventoryForm = Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'inventory']);
        $inventoryForm->call('createRecord');
        $inventoryForm->set('form.reference', 'MEAT-1-OPEN');
        $inventoryForm->set('form.title', 'Opening stock');
        $inventoryForm->set('form.quantity', '5');
        $inventoryForm->set('form.unit', 'kg');
        $inventoryForm->call('saveRecord')->assertHasNoErrors();

        $sale = Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('createRecord')
            ->set('form.reference', 'PRODUCT-SALE')
            ->set('form.record_date', now()->toDateString())
            ->set('form.inventory_item_id', (string) $inventory->id)
            ->set('form.title', 'Farm product')
            ->set('form.quantity', '2')
            ->set('form.unit_price', '10')
            ->call('saveRecord')
            ->assertHasNoErrors();
        $saleRecord = \App\Models\FarmRecord::where('reference', 'PRODUCT-SALE')->firstOrFail();
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->call('completeSale', $saleRecord->id)
            ->assertHasNoErrors();

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->set('paymentAmount', '5')
            ->set('paymentMethod', 'Cash')
            ->set('paymentReference', 'RCPT-1')
            ->call('recordPayment', $saleRecord->id)
            ->assertHasNoErrors();

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'sales'])
            ->set('paymentAmount', '16')
            ->set('paymentMethod', 'Cash')
            ->call('recordPayment', $saleRecord->id)
            ->assertHasErrors('paymentAmount');

        $this->assertSame('3.000', $inventory->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', ['item_record_id' => $inventory->id, 'movement_type' => 'sale']);
        $this->assertDatabaseHas('sale_payments', ['sale_id' => $saleRecord->id, 'amount' => '5.00']);
    }

    public function test_reports_are_database_backed_and_respect_date_range(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-10-16 12:00:00');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        \App\Models\FarmRecord::create([
            'module' => 'expenses',
            'reference' => 'REPORT-EXP',
            'title' => 'Feed expense',
            'record_date' => '2026-10-10',
            'amount' => 125.50,
            'status' => 'active',
        ]);
        \App\Models\FarmRecord::create([
            'module' => 'sales',
            'reference' => 'REPORT-SALE',
            'title' => 'Animal sale',
            'record_date' => '2026-09-10',
            'amount' => 900,
            'status' => 'completed',
        ]);

        $this->actingAs($admin)->get(route('modules.reports'))
            ->assertOk()
            ->assertSee('Farm Reports')
            ->assertSee('125.50')
            ->assertDontSee('900.00');

        Livewire::actingAs($admin)->test(FarmReportIndex::class)
            ->set('from', '2026-10-01')
            ->set('to', '2026-10-31')
            ->assertSee('125.50')
            ->assertDontSee('900.00')
            ->call('exportCsv')
            ->assertFileDownloaded('farmsuite-report.csv');
        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_customer_records_can_be_updated_and_archived_without_deleting_history(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo([
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
        ]);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'customers'])
            ->call('createRecord')
            ->set('form.title', 'Initial Customer')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $customer = \App\Models\FarmRecord::where('module', 'customers')->firstOrFail();
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'customers'])
            ->call('editRecord', $customer->id)
            ->set('form.title', 'Updated Customer')
            ->call('saveRecord')
            ->assertHasNoErrors();

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'customers'])
            ->call('archiveRecord', $customer->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('farm_records', [
            'id' => $customer->id,
            'title' => 'Updated Customer',
            'status' => 'archived',
            'deleted_at' => null,
        ]);
    }

    public function test_feed_consumption_changes_stock_and_creates_a_persisted_history_record(): void
    {
        $user = User::factory()->create();
        $user->assignRole('basic-user');
        Role::findByName('basic-user')->givePermissionTo(['feeding.view', 'feeding.create', 'feeding.update']);

        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'feeding'])
            ->call('createRecord')
            ->set('form.title', 'Grower feed')
            ->set('form.record_date', now()->toDateString())
            ->set('form.quantity', '20')
            ->set('form.unit', 'kg')
            ->set('form.category', 'stock')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $feed = \App\Models\FarmRecord::where('module', 'feeding')->where('category', 'stock')->firstOrFail();
        Livewire::actingAs($user)->test(FarmRecordIndex::class, ['module' => 'feeding'])
            ->set('stockAdjustment', '3.5')
            ->call('consumeFeed', $feed->id)
            ->assertHasNoErrors();

        $this->assertSame('16.500', $feed->fresh()->quantity);
        $this->assertDatabaseHas('farm_records', [
            'module' => 'feeding',
            'category' => 'consumption',
            'quantity' => '3.500',
            'related_record_id' => $feed->id,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'item_record_id' => $feed->id,
            'movement_type' => 'feed_consumption',
            'quantity' => '-3.500',
        ]);
    }
}
