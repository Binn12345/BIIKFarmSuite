<?php

namespace App\Support;

class FarmRecordCatalog
{
    /**
     * @return array<string, array{title: string, description: string, fields: array<string, array{label: string, type: string, required?: bool, options?: array<string, string>, relation?: string}>, historical?: bool}>
     */
    public static function modules(): array
    {
        return [
            'piglets' => [
                'title' => 'Piglet Records',
                'description' => 'Track piglet identification, litter, weight, and weaning history.',
                'fields' => [
                    'reference' => ['label' => 'Piglet ID', 'type' => 'text', 'required' => true],
                    'record_date' => ['label' => 'Birth date', 'type' => 'date'],
                    'weight' => ['label' => 'Birth weight (kg)', 'type' => 'decimal'],
                    'related_animal_id' => ['label' => 'Sow', 'type' => 'animal'],
                    'related_record_id' => ['label' => 'Farrowing record ID', 'type' => 'record', 'relation' => 'farrowing'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['alive' => 'Alive', 'weaned' => 'Weaned', 'sold' => 'Sold', 'deceased' => 'Deceased']],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'breeding' => [
                'title' => 'Breeding Management',
                'description' => 'Record matings, expected farrowing dates, and breeding outcomes.',
                'fields' => [
                    'reference' => ['label' => 'Breeding reference', 'type' => 'text', 'required' => true],
                    'record_date' => ['label' => 'Mating date', 'type' => 'date', 'required' => true],
                    'related_animal_id' => ['label' => 'Sow', 'type' => 'animal', 'required' => true],
                    'boar_animal_id' => ['label' => 'Boar', 'type' => 'animal-reference'],
                    'expected_date' => ['label' => 'Expected farrowing date', 'type' => 'date'],
                    'status' => ['label' => 'Outcome', 'type' => 'select', 'options' => ['pending' => 'Pregnancy not confirmed', 'confirmed' => 'Pregnancy confirmed', 'failed' => 'Unsuccessful', 'completed' => 'Completed']],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'farrowing' => [
                'title' => 'Farrowing Management',
                'description' => 'Record sow farrowing dates and litter outcomes.',
                'fields' => [
                    'reference' => ['label' => 'Farrowing reference', 'type' => 'text', 'required' => true],
                    'record_date' => ['label' => 'Farrowing date', 'type' => 'date', 'required' => true],
                    'related_animal_id' => ['label' => 'Sow', 'type' => 'animal', 'required' => true],
                    'related_record_id' => ['label' => 'Breeding record ID', 'type' => 'record', 'relation' => 'breeding'],
                    'quantity' => ['label' => 'Total piglets born', 'type' => 'integer', 'required' => true],
                    'born_alive' => ['label' => 'Born alive', 'type' => 'integer', 'required' => true],
                    'stillborn' => ['label' => 'Stillborn', 'type' => 'integer', 'required' => true],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'health' => [
                'title' => 'Health and Veterinary Records',
                'description' => 'Record observations, vaccinations, treatments, and follow-up visits.',
                'fields' => [
                    'reference' => ['label' => 'Record reference', 'type' => 'text'],
                    'record_date' => ['label' => 'Visit or treatment date', 'type' => 'date', 'required' => true],
                    'related_animal_id' => ['label' => 'Animal', 'type' => 'animal', 'required' => true],
                    'category' => ['label' => 'Record type', 'type' => 'select', 'required' => true, 'options' => ['observation' => 'Observation', 'vaccination' => 'Vaccination', 'treatment' => 'Treatment', 'veterinary_visit' => 'Veterinary visit']],
                    'title' => ['label' => 'Observation or treatment', 'type' => 'text', 'required' => true],
                    'follow_up_date' => ['label' => 'Follow-up date', 'type' => 'date'],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'feeding' => [
                'title' => 'Feeding Management',
                'description' => 'Track feed inventory, feed types, and recorded consumption.',
                'fields' => [
                    'reference' => ['label' => 'Feed or movement reference', 'type' => 'text'],
                    'title' => ['label' => 'Feed type', 'type' => 'text', 'required' => true],
                    'record_date' => ['label' => 'Date', 'type' => 'date', 'required' => true],
                    'quantity' => ['label' => 'Quantity (stock or consumption)', 'type' => 'decimal', 'required' => true],
                    'unit' => ['label' => 'Unit', 'type' => 'text', 'required' => true],
                    'category' => ['label' => 'Entry type', 'type' => 'select', 'options' => ['stock' => 'Stock balance', 'consumption' => 'Consumption']],
                    'related_animal_id' => ['label' => 'Animal (optional)', 'type' => 'animal'],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'mortality' => [
                'title' => 'Mortality Records',
                'description' => 'Maintain mortality events and animal history.',
                'fields' => [
                    'reference' => ['label' => 'Mortality reference', 'type' => 'text'],
                    'record_date' => ['label' => 'Date', 'type' => 'date', 'required' => true],
                    'related_animal_id' => ['label' => 'Animal', 'type' => 'animal', 'required' => true],
                    'category' => ['label' => 'Cause (if known)', 'type' => 'text'],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'animal-categories' => [
                'title' => 'Animal Categories',
                'description' => 'Maintain classifications used to organize farm animals.',
                'fields' => [
                    'reference' => ['label' => 'Category code', 'type' => 'text', 'required' => true],
                    'title' => ['label' => 'Category name', 'type' => 'text', 'required' => true],
                    'notes' => ['label' => 'Description', 'type' => 'textarea'],
                ],
            ],
            'breeds' => [
                'title' => 'Breed Management',
                'description' => 'Maintain breed names for consistent animal records.',
                'fields' => [
                    'reference' => ['label' => 'Breed code', 'type' => 'text'],
                    'title' => ['label' => 'Breed name', 'type' => 'text', 'required' => true],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'inventory' => [
                'title' => 'Inventory Management',
                'description' => 'Track stock items, quantities, units, and reorder levels.',
                'fields' => [
                    'reference' => ['label' => 'Item code', 'type' => 'text', 'required' => true],
                    'title' => ['label' => 'Item name', 'type' => 'text', 'required' => true],
                    'category' => ['label' => 'Category', 'type' => 'text'],
                    'quantity' => ['label' => 'Quantity on hand', 'type' => 'decimal', 'required' => true],
                    'min_quantity' => ['label' => 'Low-stock threshold', 'type' => 'decimal'],
                    'unit' => ['label' => 'Unit', 'type' => 'text', 'required' => true],
                    'amount' => ['label' => 'Unit cost', 'type' => 'money'],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'customers' => [
                'title' => 'Customer Management',
                'description' => 'Maintain customer records and sales history.',
                'fields' => [
                    'reference' => ['label' => 'Customer reference', 'type' => 'text'],
                    'title' => ['label' => 'Customer name', 'type' => 'text', 'required' => true],
                    'email' => ['label' => 'Email', 'type' => 'email'],
                    'phone' => ['label' => 'Phone', 'type' => 'text'],
                    'address' => ['label' => 'Address', 'type' => 'textarea'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'suppliers' => [
                'title' => 'Supplier Management',
                'description' => 'Maintain supplier records and purchase history.',
                'fields' => [
                    'reference' => ['label' => 'Supplier reference', 'type' => 'text'],
                    'title' => ['label' => 'Supplier name', 'type' => 'text', 'required' => true],
                    'email' => ['label' => 'Email', 'type' => 'email'],
                    'phone' => ['label' => 'Phone', 'type' => 'text'],
                    'address' => ['label' => 'Address', 'type' => 'textarea'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'purchases' => [
                'title' => 'Purchase Management',
                'description' => 'Record supplier purchases and receive stock into inventory.',
                'fields' => [
                    'reference' => ['label' => 'Purchase reference', 'type' => 'text', 'required' => true],
                    'record_date' => ['label' => 'Purchase date', 'type' => 'date', 'required' => true],
                    'related_record_id' => ['label' => 'Supplier record ID', 'type' => 'record', 'relation' => 'suppliers', 'required' => true],
                    'inventory_record_id' => ['label' => 'Inventory item', 'type' => 'record', 'relation' => 'inventory', 'required' => true],
                    'title' => ['label' => 'Item or goods description', 'type' => 'text', 'required' => true],
                    'quantity' => ['label' => 'Quantity', 'type' => 'decimal', 'required' => true],
                    'unit' => ['label' => 'Unit', 'type' => 'text', 'required' => true],
                    'unit_price' => ['label' => 'Unit cost', 'type' => 'money', 'required' => true],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'sales' => [
                'title' => 'Farm Sales',
                'description' => 'Record customer sales and complete or cancel transactions.',
                'fields' => [
                    'reference' => ['label' => 'Sale reference', 'type' => 'text', 'required' => true],
                    'record_date' => ['label' => 'Sale date', 'type' => 'date', 'required' => true],
                    'related_record_id' => ['label' => 'Customer record ID', 'type' => 'record', 'relation' => 'customers'],
                    'related_animal_id' => ['label' => 'Animal (optional)', 'type' => 'animal'],
                    'inventory_item_id' => ['label' => 'Inventory item (optional)', 'type' => 'record', 'relation' => 'inventory'],
                    'title' => ['label' => 'Item description', 'type' => 'text', 'required' => true],
                    'quantity' => ['label' => 'Quantity', 'type' => 'decimal', 'required' => true],
                    'unit' => ['label' => 'Unit', 'type' => 'text'],
                    'unit_price' => ['label' => 'Unit price', 'type' => 'money', 'required' => true],
                    'notes' => ['label' => 'Notes', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
            'expenses' => [
                'title' => 'Farm Expenses',
                'description' => 'Record farm expenses by category, date, and payment reference.',
                'fields' => [
                    'reference' => ['label' => 'Expense reference', 'type' => 'text'],
                    'record_date' => ['label' => 'Expense date', 'type' => 'date', 'required' => true],
                    'category' => ['label' => 'Category', 'type' => 'text', 'required' => true],
                    'title' => ['label' => 'Description', 'type' => 'text', 'required' => true],
                    'amount' => ['label' => 'Amount', 'type' => 'money', 'required' => true],
                    'unit' => ['label' => 'Payment method', 'type' => 'text'],
                    'notes' => ['label' => 'Notes / reference', 'type' => 'textarea'],
                ],
                'historical' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function for(string $module): array
    {
        abort_unless(isset(self::modules()[$module]), 404);

        $catalog = self::modules()[$module];
        $titleKey = 'biik.modules.'.$module.'.title';
        $descriptionKey = 'biik.modules.'.$module.'.description';
        if (__($titleKey) !== $titleKey) {
            $catalog['title'] = __($titleKey);
        }
        if (__($descriptionKey) !== $descriptionKey) {
            $catalog['description'] = __($descriptionKey);
        }
        foreach ($catalog['fields'] as $key => &$field) {
            $labelKey = 'biik.fields.'.$key;
            if (__($labelKey) !== $labelKey) {
                $field['label'] = __($labelKey);
            }
            foreach ($field['options'] ?? [] as $value => $label) {
                $optionKey = 'biik.values.'.$value;
                if (__($optionKey) !== $optionKey) {
                    $field['options'][$value] = __($optionKey);
                }
            }
        }
        unset($field);

        return $catalog;
    }
}
