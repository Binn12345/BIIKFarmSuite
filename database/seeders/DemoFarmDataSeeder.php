<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\Pen;
use App\Models\SalePayment;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoFarmDataSeeder extends Seeder
{
    private const COUNT = 30;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) && ! config('farmsuite.allow_demo_seeding')) {
            throw new RuntimeException('Demo seeding is limited to local/testing. Set FARMSUITE_ALLOW_DEMO_SEEDING=true only for a dedicated demo environment.');
        }

        DB::transaction(function (): void {
            $pens = collect(range(1, self::COUNT))->map(fn (int $i) => Pen::firstOrCreate(
                ['name' => sprintf('DEMO-PEN-%03d', $i)],
                ['capacity' => 10, 'status' => 'active', 'location' => 'Demo barn '.(intdiv($i - 1, 10) + 1), 'notes' => 'Seeded demonstration pen'],
            ));

            $sows = collect();
            $boars = collect();
            $mortalityAnimals = collect();
            foreach (range(1, self::COUNT) as $i) {
                $pen = $pens[($i - 1) % self::COUNT];
                $sows->push(Animal::firstOrCreate(
                    ['tag_number' => sprintf('DEMO-SOW-%03d', $i)],
                    $this->animalData('female', $pen, $i, 'Sow'),
                ));
                $boars->push(Animal::firstOrCreate(
                    ['tag_number' => sprintf('DEMO-BOAR-%03d', $i)],
                    $this->animalData('male', $pen, $i, 'Boar'),
                ));
                $mortalityAnimals->push(Animal::firstOrCreate(
                    ['tag_number' => sprintf('DEMO-MORT-%03d', $i)],
                    $this->animalData('female', $pen, $i, 'Mortality demo animal'),
                ));
            }

            $breeds = ['Large White', 'Landrace', 'Duroc', 'Pietrain', 'Berkshire', 'Local cross'];
            $categories = ['Breeding sow', 'Breeding boar', 'Grower', 'Finisher', 'Replacement gilt', 'Piglet'];
            $this->seedLookupRecords('breeds', $breeds, 'DEMO-BREED-');
            $this->seedLookupRecords('animal-categories', $categories, 'DEMO-CAT-');

            $inventory = collect();
            foreach (range(1, self::COUNT) as $i) {
                $item = FarmRecord::firstOrCreate(
                    ['module' => 'inventory', 'reference' => sprintf('DEMO-ITEM-%03d', $i)],
                    [
                        'title' => ['Pellet feed', 'Mineral supplement', 'Cleaning supply', 'Farm tool', 'Bedding material'][$i % 5].' '.sprintf('%02d', $i),
                        'record_date' => now()->subDays(90 - $i)->toDateString(),
                        'status' => 'active',
                        'quantity' => 100,
                        'unit' => $i % 2 ? 'kg' : 'piece',
                        'category' => ['Feed', 'Veterinary', 'Cleaning', 'Equipment', 'Bedding'][$i % 5],
                        'data' => ['demo_batch' => 'farmsuite-2026-01', 'reorder_level' => 15],
                        'notes' => 'Seeded demonstration stock item',
                    ],
                );
                if ($item->wasRecentlyCreated) {
                    $this->movement($item, 'opening_balance', 100, 100, 'DEMO-OPEN-'.sprintf('%03d', $i));
                }
                $inventory->push($item);
            }

            $customers = $this->seedParties('customers', 'DEMO-CUST-', 'Customer');
            $suppliers = $this->seedParties('suppliers', 'DEMO-SUP-', 'Supplier');

            foreach (range(1, self::COUNT) as $i) {
                $index = $i - 1;
                $sow = $sows[$index];
                $breedingDate = now()->subDays(180 - $i);
                $farrowingDate = $breedingDate->copy()->addDays(114);
                $breeding = FarmRecord::firstOrCreate(
                    ['module' => 'breeding', 'reference' => sprintf('DEMO-BREEDING-%03d', $i)],
                    [
                        'title' => 'Sow mating record '.sprintf('%03d', $i),
                        'record_date' => $breedingDate->toDateString(),
                        'status' => 'completed',
                        'related_animal_id' => $sow->id,
                        'data' => [
                            'boar_animal_id' => $boars[$index]->id,
                            'expected_date' => $farrowingDate->toDateString(),
                            'pregnancy_status' => 'confirmed',
                            'demo_batch' => 'farmsuite-2026-01',
                        ],
                        'notes' => 'Demonstration mating and pregnancy outcome',
                    ],
                );
                $farrowing = FarmRecord::firstOrCreate(
                    ['module' => 'farrowing', 'reference' => sprintf('DEMO-FARROW-%03d', $i)],
                    [
                        'title' => 'Litter outcome '.sprintf('%03d', $i),
                        'record_date' => $farrowingDate->toDateString(),
                        'status' => 'completed',
                        'quantity' => 8,
                        'related_animal_id' => $sow->id,
                        'related_record_id' => $breeding->id,
                        'data' => ['born_alive' => 7, 'stillborn' => 1, 'demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Demo litter: 7 born alive and 1 stillborn',
                    ],
                );
                foreach (range(1, 7) as $pigletNumber) {
                    $birthWeight = 1.05 + (($i + $pigletNumber) % 8) / 10;
                    FarmRecord::firstOrCreate(
                        ['module' => 'piglets', 'reference' => sprintf('DEMO-PIGLET-%03d-%02d', $i, $pigletNumber)],
                        [
                            'title' => 'Piglet '.sprintf('%03d-%02d', $i, $pigletNumber),
                            'record_date' => $farrowingDate->toDateString(),
                            'status' => 'weaned',
                            'weight' => $birthWeight,
                            'related_animal_id' => $sow->id,
                            'related_record_id' => $farrowing->id,
                            'data' => [
                                'litter_reference' => $farrowing->reference,
                                'birth_weight_kg' => $birthWeight,
                                'weight_history' => [
                                    ['date' => $farrowingDate->toDateString(), 'weight_kg' => $birthWeight],
                                    ['date' => $farrowingDate->copy()->addDays(28)->toDateString(), 'weight_kg' => 7.0 + (($i + $pigletNumber) % 7)],
                                ],
                                'weaning_date' => $farrowingDate->copy()->addDays(28)->toDateString(),
                                'demo_batch' => 'farmsuite-2026-01',
                            ],
                            'notes' => 'Demo piglet with birth and weaning weight observations',
                        ],
                    );
                }
                FarmRecord::firstOrCreate(
                    ['module' => 'health', 'reference' => sprintf('DEMO-HEALTH-%03d', $i)],
                    [
                        'title' => ['Vaccination visit', 'Routine observation', 'Veterinary follow-up'][$i % 3],
                        'record_date' => now()->subDays(60 - $i)->toDateString(),
                        'status' => 'completed',
                        'category' => ['vaccination', 'observation', 'veterinary_visit'][$i % 3],
                        'related_animal_id' => $sow->id,
                        'data' => ['follow_up_date' => now()->addDays($i % 15 + 1)->toDateString(), 'demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Demonstration veterinary record; no diagnosis or treatment prescribed',
                    ],
                );
                $feed = FarmRecord::firstOrCreate(
                    ['module' => 'feeding', 'reference' => sprintf('DEMO-FEED-%03d', $i)],
                    [
                        'title' => ['Grower ration', 'Sow ration', 'Finisher ration'][$i % 3],
                        'record_date' => now()->subDays(45 - $i)->toDateString(),
                        'status' => 'active',
                        'quantity' => 200,
                        'unit' => 'kg',
                        'category' => 'stock',
                        'data' => ['feed_type' => ['Grower ration', 'Sow ration', 'Finisher ration'][$i % 3], 'demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Seeded demo feed stock',
                    ],
                );
                if ($feed->wasRecentlyCreated) {
                    $this->movement($feed, 'opening_balance', 200, 200, 'DEMO-FEED-OPEN-'.sprintf('%03d', $i));
                }
                $consumption = FarmRecord::firstOrCreate(
                    ['module' => 'feeding', 'reference' => sprintf('DEMO-CONSUME-%03d', $i)],
                    [
                        'title' => $feed->title.' consumption',
                        'record_date' => now()->subDays(15 - ($i % 15))->toDateString(),
                        'status' => 'completed',
                        'quantity' => 5,
                        'unit' => 'kg',
                        'category' => 'consumption',
                        'related_record_id' => $feed->id,
                        'data' => ['demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Recorded feed use in demonstration data',
                    ],
                );
                if ($consumption->wasRecentlyCreated) {
                    $feed->decrement('quantity', 5);
                    $this->movement($feed, 'feed_consumption', -5, $feed->fresh()->quantity, $consumption->reference, $consumption->id);
                }

                $mortality = FarmRecord::firstOrCreate(
                    ['module' => 'mortality', 'reference' => sprintf('DEMO-MORTALITY-%03d', $i)],
                    [
                        'title' => 'Mortality record '.sprintf('%03d', $i),
                        'record_date' => now()->subDays(100 - $i)->toDateString(),
                        'status' => 'completed',
                        'category' => ['Unknown', 'Natural causes', 'Accidental injury'][$i % 3],
                        'related_animal_id' => $mortalityAnimals[$index]->id,
                        'data' => ['demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Demonstration historical mortality event',
                    ],
                );
                if ($mortality->wasRecentlyCreated) {
                    $mortalityAnimals[$index]->update(['status' => 'deceased']);
                }

                $supplier = $suppliers[$index];
                $customer = $customers[$index];
                $item = $inventory[$index];
                $purchase = FarmRecord::firstOrCreate(
                    ['module' => 'purchases', 'reference' => sprintf('DEMO-PURCHASE-%03d', $i)],
                    [
                        'title' => 'Purchase of '.$item->title,
                        'record_date' => now()->subDays(30 - ($i % 20))->toDateString(),
                        'status' => 'received',
                        'quantity' => 10,
                        'unit_price' => 18 + ($i % 11),
                        'amount' => (10 * (18 + ($i % 11))),
                        'related_record_id' => $supplier->id,
                        'data' => ['inventory_record_id' => $item->id, 'demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Demonstration received purchase',
                    ],
                );
                if ($purchase->wasRecentlyCreated) {
                    $item->increment('quantity', 10);
                    $this->movement($item, 'purchase_received', 10, $item->fresh()->quantity, $purchase->reference, $purchase->id);
                }
                $sale = FarmRecord::firstOrCreate(
                    ['module' => 'sales', 'reference' => sprintf('DEMO-SALE-%03d', $i)],
                    [
                        'title' => 'Sale of '.$item->title,
                        'record_date' => now()->subDays(10 - ($i % 9))->toDateString(),
                        'status' => 'completed',
                        'quantity' => 2,
                        'unit_price' => 32 + ($i % 13),
                        'amount' => (2 * (32 + ($i % 13))),
                        'related_record_id' => $customer->id,
                        'data' => ['inventory_item_id' => $item->id, 'demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Demonstration completed product sale',
                    ],
                );
                if ($sale->wasRecentlyCreated) {
                    $item->decrement('quantity', 2);
                    $this->movement($item, 'sale', -2, $item->fresh()->quantity, $sale->reference, $sale->id);
                    SalePayment::firstOrCreate(
                        ['sale_id' => $sale->id, 'reference' => sprintf('DEMO-PAY-%03d', $i)],
                        ['amount' => $sale->amount, 'method' => ['cash', 'bank transfer', 'mobile payment'][$i % 3]],
                    );
                }
                FarmRecord::firstOrCreate(
                    ['module' => 'expenses', 'reference' => sprintf('DEMO-EXPENSE-%03d', $i)],
                    [
                        'title' => ['Utilities', 'Transport', 'Repairs', 'Feed supplies', 'Veterinary supplies'][$i % 5],
                        'record_date' => now()->subDays(20 - ($i % 19))->toDateString(),
                        'status' => 'completed',
                        'category' => ['Utilities', 'Transport', 'Repairs', 'Feed', 'Veterinary'][$i % 5],
                        'amount' => 45 + ($i * 3.25),
                        'unit' => ['cash', 'bank transfer'][$i % 2],
                        'data' => ['demo_batch' => 'farmsuite-2026-01'],
                        'notes' => 'Demonstration expense record',
                    ],
                );
            }
        });

        $this->reportCounts();
    }

    private function animalData(string $sex, Pen $pen, int $index, string $kind): array
    {
        return [
            'name' => $kind.' '.sprintf('%03d', $index),
            'breed' => ['Large White', 'Landrace', 'Duroc', 'Pietrain', 'Berkshire', 'Local cross'][$index % 6],
            'category' => match ($kind) {
                'Sow' => 'Breeding sow',
                'Boar' => 'Breeding boar',
                default => 'Grower',
            },
            'sex' => $sex,
            'birth_date' => now()->subDays(250 + $index)->toDateString(),
            'status' => 'active',
            'pen_id' => $pen->id,
            'acquisition_source' => 'Farm-bred demo stock',
            'acquired_at' => now()->subDays(200 + $index)->toDateString(),
            'purchase_cost' => 150 + ($index * 3.5),
            'notes' => 'Seeded demonstration animal',
        ];
    }

    private function seedLookupRecords(string $module, array $values, string $prefix): void
    {
        foreach ($values as $i => $value) {
            FarmRecord::firstOrCreate(
                ['module' => $module, 'reference' => $prefix.sprintf('%02d', $i + 1)],
                ['title' => $value, 'status' => 'active', 'category' => 'demo', 'notes' => 'Demo lookup value'],
            );
        }
    }

    private function seedParties(string $module, string $prefix, string $kind)
    {
        return collect(range(1, self::COUNT))->map(fn (int $i) => FarmRecord::firstOrCreate(
            ['module' => $module, 'reference' => $prefix.sprintf('%03d', $i)],
            [
                'title' => 'Demonstration '.$kind.' '.sprintf('%03d', $i),
                'status' => 'active',
                'data' => ['demo_batch' => 'farmsuite-2026-01'],
                'notes' => 'Synthetic demonstration business account',
            ],
        ));
    }

    private function movement(FarmRecord $item, string $type, float $quantity, float $balance, string $reference, ?int $sourceId = null): void
    {
        StockMovement::firstOrCreate(
            ['item_record_id' => $item->id, 'movement_type' => $type, 'reference' => $reference],
            [
                'quantity' => $quantity,
                'balance_after' => $balance,
                'source_record_id' => $sourceId,
            ],
        );
    }

    private function reportCounts(): void
    {
        $demoRecords = fn (string $module, string $reference = 'DEMO-%') => FarmRecord::query()
            ->where('module', $module)
            ->where('reference', 'like', $reference)
            ->get();
        $breedingRecords = $demoRecords('breeding', 'DEMO-BREEDING-%');
        $farrowingRecords = $demoRecords('farrowing', 'DEMO-FARROW-%');
        $pigletRecords = $demoRecords('piglets', 'DEMO-PIGLET-%');
        $healthRecords = $demoRecords('health', 'DEMO-HEALTH-%');
        $feedStockRecords = $demoRecords('feeding', 'DEMO-FEED-%');
        $feedConsumptionRecords = $demoRecords('feeding', 'DEMO-CONSUME-%');
        $mortalityRecords = $demoRecords('mortality', 'DEMO-MORTALITY-%');
        $purchaseRecords = $demoRecords('purchases', 'DEMO-PURCHASE-%');
        $saleRecords = $demoRecords('sales', 'DEMO-SALE-%');
        $inventoryRecords = $demoRecords('inventory');
        $allPenAssignmentsFit = Pen::query()
            ->where('name', 'like', 'DEMO-PEN-%')
            ->withCount(['animals as active_animals_count' => fn ($query) => $query->where('status', 'active')])
            ->get()
            ->every(fn (Pen $pen) => $pen->active_animals_count <= $pen->capacity);
        $breedingRelationshipsValid = $breedingRecords->every(fn (FarmRecord $record) => $record->animal?->sex === 'female'
            && isset($record->data['boar_animal_id'])
            && Animal::query()->whereKey($record->data['boar_animal_id'])->where('sex', 'male')->exists());
        $farrowingRelationshipsValid = $farrowingRecords->every(fn (FarmRecord $record) => $record->animal?->sex === 'female'
            && $record->relatedRecord?->module === 'breeding'
            && $record->relatedRecord?->related_animal_id === $record->related_animal_id);
        $pigletRelationshipsValid = $pigletRecords->every(fn (FarmRecord $record) => $record->animal?->sex === 'female'
            && $record->relatedRecord?->module === 'farrowing'
            && $record->relatedRecord?->related_animal_id === $record->related_animal_id);
        $healthRelationshipsValid = $healthRecords->every(fn (FarmRecord $record) => $record->animal !== null);
        $feedRelationshipsValid = $feedConsumptionRecords->every(fn (FarmRecord $record) => $record->relatedRecord?->module === 'feeding'
            && str_starts_with((string) $record->relatedRecord?->reference, 'DEMO-FEED-'));
        $mortalityRelationshipsValid = $mortalityRecords->every(fn (FarmRecord $record) => $record->animal?->status === 'deceased');
        $purchasesLinked = $purchaseRecords->every(fn (FarmRecord $record) => $record->relatedRecord?->module === 'suppliers'
            && isset($record->data['inventory_record_id'])
            && FarmRecord::query()->whereKey($record->data['inventory_record_id'])->where('module', 'inventory')->exists());
        $salesLinked = $saleRecords->every(fn (FarmRecord $record) => $record->relatedRecord?->module === 'customers'
            && isset($record->data['inventory_item_id'])
            && FarmRecord::query()->whereKey($record->data['inventory_item_id'])->where('module', 'inventory')->exists()
            && $record->payments()->exists());
        $balancesNonnegative = $inventoryRecords->every(fn (FarmRecord $record) => (float) $record->quantity >= 0)
            && $feedStockRecords->every(fn (FarmRecord $record) => (float) $record->quantity >= 0)
            && StockMovement::query()->where('reference', 'like', 'DEMO-%')->where('balance_after', '<', 0)->doesntExist();

        $counts = [
            ['Pens', 30, Pen::query()->where('name', 'like', 'DEMO-PEN-%')->count(), $allPenAssignmentsFit],
            ['Animals', 30, Animal::query()->where('tag_number', 'like', 'DEMO-%')->count(), Animal::query()->where('tag_number', 'like', 'DEMO-%')->whereNotNull('pen_id')->count() === Animal::query()->where('tag_number', 'like', 'DEMO-%')->count()],
        ];
        foreach (['piglets', 'breeding', 'farrowing', 'health', 'feeding', 'mortality', 'inventory', 'customers', 'suppliers', 'purchases', 'sales', 'expenses'] as $module) {
            $counts[] = [
                ucfirst($module).' records',
                30,
                $module === 'feeding'
                    ? $feedConsumptionRecords->count()
                    : FarmRecord::query()->where('module', $module)->where('reference', 'like', 'DEMO-%')->count(),
                match ($module) {
                    'piglets' => $pigletRelationshipsValid,
                    'breeding' => $breedingRelationshipsValid,
                    'farrowing' => $farrowingRelationshipsValid,
                    'health' => $healthRelationshipsValid,
                    'feeding' => $feedRelationshipsValid && $feedConsumptionRecords->count() === self::COUNT,
                    'mortality' => $mortalityRelationshipsValid,
                    'inventory' => $balancesNonnegative,
                    'purchases' => $purchasesLinked,
                    'sales' => $salesLinked,
                    default => true,
                },
            ];
        }
        $counts[] = ['Animal categories', 6, FarmRecord::query()->where('module', 'animal-categories')->where('reference', 'like', 'DEMO-CAT-%')->count(), true];
        $counts[] = ['Breeds', 6, FarmRecord::query()->where('module', 'breeds')->where('reference', 'like', 'DEMO-BREED-%')->count(), true];
        $counts[] = ['Feed stock records', 30, $feedStockRecords->count(), $balancesNonnegative];
        $counts[] = ['Feed consumption records', 30, $feedConsumptionRecords->count(), $feedRelationshipsValid];
        $counts[] = ['Stock movements', 30, StockMovement::query()->where('reference', 'like', 'DEMO-%')->count(), $balancesNonnegative];
        $counts[] = ['Sale payments', 30, SalePayment::query()->where('reference', 'like', 'DEMO-PAY-%')->count(), $salesLinked];
        $counts[] = [
            'Breeding records linked to eligible animals', 30,
            $breedingRecords->count(fn (FarmRecord $record) => $record->animal?->sex === 'female'
                && isset($record->data['boar_animal_id'])
                && Animal::query()->whereKey($record->data['boar_animal_id'])->where('sex', 'male')->exists()),
            $breedingRelationshipsValid,
        ];
        $counts[] = [
            'Farrowing records linked to matching breeding records', 30,
            $farrowingRecords->count(fn (FarmRecord $record) => $record->animal?->sex === 'female'
                && $record->relatedRecord?->module === 'breeding'
                && $record->relatedRecord?->related_animal_id === $record->related_animal_id),
            $farrowingRelationshipsValid,
        ];
        $counts[] = [
            'Piglets linked to farrowing records', 30,
            $pigletRecords->count(fn (FarmRecord $record) => $record->animal?->sex === 'female'
                && $record->relatedRecord?->module === 'farrowing'
                && $record->relatedRecord?->related_animal_id === $record->related_animal_id),
            $pigletRelationshipsValid,
        ];
        $counts[] = [
            'Mortality events with deceased animals', 30,
            $mortalityRecords->count(fn (FarmRecord $record) => $record->animal?->status === 'deceased'),
            $mortalityRelationshipsValid,
        ];
        $counts[] = [
            'Sales linked to customers and payments', 30,
            $saleRecords->count(fn (FarmRecord $record) => $record->relatedRecord?->module === 'customers'
                && isset($record->data['inventory_item_id'])
                && FarmRecord::query()->whereKey($record->data['inventory_item_id'])->where('module', 'inventory')->exists()
                && $record->payments()->exists()),
            $salesLinked,
        ];
        $counts[] = [
            'Purchases linked to suppliers and inventory', 30,
            $purchaseRecords->count(fn (FarmRecord $record) => $record->relatedRecord?->module === 'suppliers'
                && isset($record->data['inventory_record_id'])
                && FarmRecord::query()->whereKey($record->data['inventory_record_id'])->where('module', 'inventory')->exists()),
            $purchasesLinked,
        ];

        $this->command?->table(
            ['Entity / sub-module', 'Minimum target', 'Actual demo count', 'Integrity / relationship check'],
            collect($counts)->map(fn (array $row) => [$row[0], $row[1], $row[2], $row[3] && $row[2] >= $row[1] ? 'PASS' : 'CHECK'])->all(),
        );
    }
}
