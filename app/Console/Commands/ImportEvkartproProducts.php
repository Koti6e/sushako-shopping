<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportEvkartproProducts extends Command
{
    protected $signature = 'evkartpro:import
                            {--dry-run : Preview the import without changing the database}';

    protected $description = 'Import EVKart Pro products into the Evkartpro seller catalog';

    private const CSV_PATH = 'storage/app/products-export-2026-08-27.csv';
    private const VENDOR_ID = 70;
    private const PARENT_CATEGORY_ID = 9;
    private const TAX_SLAB_ID = 4;

    private const CATEGORY_MAP = [
        'Accessories' => 10,
        'EV Switches & Flashers Mosfets' => 11,
        'Brake Pad for Discs' => 12,
        'EV Motors & Motor Accessories' => 13,
        'Throttle' => 14,
        'OLA Spares' => 15,
        'EV Brake & Cable Parts' => 16,
        'EV Controllers & Kits' => 17,
        'DC-DC Convertors' => 18,
    ];

    public function handle(): int
    {
        $path = base_path(self::CSV_PATH);

        if (! File::exists($path)) {
            $this->error("CSV not found: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readCsv($path);

        if (count($rows) !== 93) {
            $this->error('Expected 93 CSV rows, found ' . count($rows) . '.');
            return self::FAILURE;
        }

        $this->info('EVKart Pro import');
        $this->line('Vendor ID: ' . self::VENDOR_ID);
        $this->line('CSV rows: ' . count($rows));
        $this->line('Mode: ' . ($this->option('dry-run') ? 'DRY RUN' : 'LIVE IMPORT'));
        $this->newLine();

        $this->validateCategories();
        $this->validateRows($rows);

        $existing = Product::query()
            ->where('vendor_id', self::VENDOR_ID)
            ->pluck('id', 'name');

        $existingCsvProduct = Product::query()
            ->where('vendor_id', self::VENDOR_ID)
            ->where('name', 'Premium 12-Inch Electric Scooter Hub Motor for Hero Optima Series')
            ->first();

        $newCount = 0;
        $updateCount = 0;

        foreach ($rows as $row) {
            if ($existingCsvProduct
                && $row['ID'] === '165'
                && $row['Product Name'] === $existingCsvProduct->name
            ) {
                $updateCount++;
            } else {
                $newCount++;
            }
        }

        $this->table(
            ['Action', 'Count'],
            [
                ['Create products', $newCount],
                ['Update existing product #30', $updateCount],
                ['CSV total', count($rows)],
            ]
        );

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('DRY RUN COMPLETE — no database changes were made.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Proceed with the LIVE import?', false)) {
            $this->warn('Import cancelled.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $this->importRow($row);
            }
        });

        $this->newLine();
        $this->info('Import completed successfully.');

        $productCount = Product::query()
            ->where('vendor_id', self::VENDOR_ID)
            ->count();

        $variantCount = ProductVariant::query()
            ->whereHas('product', fn ($query) => $query->where('vendor_id', self::VENDOR_ID))
            ->count();

        $this->line("Evkartpro products now: {$productCount}");
        $this->line("Evkartpro variants now: {$variantCount}");

        return self::SUCCESS;
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV: {$path}");
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);
            throw new \RuntimeException('CSV is empty.');
        }

       $headers = array_map(
    fn ($header) => trim((string) $header, " \t\n\r\0\x0B\xEF\xBB\xBF\""),
    $headers
);
        $required = [
            'ID',
            'Product Name',
            'Category',
            'Price',
            'Stock',
            'Status',
            'Created Date',
        ];

        foreach ($required as $header) {
            if (! in_array($header, $headers, true)) {
                fclose($handle);
                throw new \RuntimeException("Missing CSV column: {$header}");
            }
        }

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($data[$index] ?? ''));
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function validateCategories(): void
    {
        $parent = Category::query()->find(self::PARENT_CATEGORY_ID);

        if (! $parent) {
            throw new \RuntimeException('EV & Automotive category ID 9 was not found.');
        }

        foreach (self::CATEGORY_MAP as $name => $id) {
            $category = Category::query()->find($id);

            if (! $category) {
                throw new \RuntimeException(
                    "Required category ID {$id} ({$name}) was not found."
                );
            }

            if ((int) $category->parent_id !== self::PARENT_CATEGORY_ID) {
                throw new \RuntimeException(
                    "Category {$id} ({$name}) does not belong to EV & Automotive."
                );
            }
        }
    }

    private function validateRows(array $rows): void
    {
        $ids = [];

        foreach ($rows as $row) {
            if ($row['ID'] === '') {
                throw new \RuntimeException('CSV contains a row without an ID.');
            }

            if ($row['Product Name'] === '') {
                throw new \RuntimeException(
                    "CSV product {$row['ID']} has no product name."
                );
            }

            if (! isset(self::CATEGORY_MAP[$row['Category']])) {
                throw new \RuntimeException(
                    "Unknown CSV category '{$row['Category']}' for product {$row['ID']}."
                );
            }

            if (! is_numeric($row['Price'])) {
                throw new \RuntimeException(
                    "Invalid price for product {$row['ID']}."
                );
            }

            if (! ctype_digit($row['Stock'])) {
                throw new \RuntimeException(
                    "Invalid stock for product {$row['ID']}."
                );
            }

            if (isset($ids[$row['ID']])) {
                throw new \RuntimeException(
                    "Duplicate CSV ID detected: {$row['ID']}."
                );
            }

            $ids[$row['ID']] = true;
        }
    }

    private function importRow(array $row): void
    {
        $sourceId = $row['ID'];
        $name = $row['Product Name'];
        $categoryId = self::CATEGORY_MAP[$row['Category']];
        $price = (float) $row['Price'];
        $stock = (int) $row['Stock'];

        /*
         * CSV source ID 165 is already represented by Sushako
         * product ID 30. Update it instead of creating a duplicate.
         */
        $product = Product::query()
            ->where('vendor_id', self::VENDOR_ID)
            ->where('name', $name)
            ->first();

        if ($product) {
            $this->updateExistingProduct($product, $row, $categoryId, $price);

            return;
        }

        $slug = $this->uniqueProductSlug($name);

        $product = Product::query()->create([
            'vendor_id' => self::VENDOR_ID,
            'category_id' => $categoryId,
            'seller_storefront_category_id' => null,
            'tax_slab_id' => self::TAX_SLAB_ID,

            'name' => $name,
            'slug' => $slug,

            'collection' => 'Seller Marketplace',
            'subcategory' => $row['Category'],
            'brand' => 'Evkartpro',
            'badge' => 'Seller Pick',

            'short_description' => $name . ' for EV and automotive applications.',
            'full_description' => $name . ' is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',

            'fabric' => null,
            'fit' => null,
            'sleeve' => null,
            'pattern' => null,
            'occasion' => null,

            'country_of_origin' => 'India',
            'return_policy' => 'Sushako support with invoice after order',

            'mrp' => $price,
            'selling_price' => $price,
            'buying_price' => 0,

            'rating' => 0,
            'reviews' => 0,

            'is_published' => strtolower($row['Status']) === 'active',
            'is_new' => true,
            'is_best_seller' => false,

            'local_delivery' => true,
            'fulfillment_scope' => 'Seller Self-Shipping',

            'sort_order' => 0,

            'seller_status' => 'approved',
            'published_mode' => 'publish_now',

            'product_condition' => 'new',

            'refurbishment_type' => null,
            'refurbishment_grade' => null,
            'condition_description' => null,
            'cosmetic_condition' => null,
            'testing_details' => null,

            'warranty_period' => null,
            'package_contents' => null,

            'weight_grams' => null,
            'length_cm' => null,
            'width_cm' => null,
            'height_cm' => null,

            'low_stock_threshold' => 2,
            'seller_rejection_reason' => null,

            'continue_selling_when_out_of_stock' => true,
            'seller_product_views' => 0,
        ]);

        $this->createVariant(
            $product,
            $sourceId,
            $price,
            $stock
        );

        $this->line("Created #{$product->id}: {$name}");
    }

    private function updateExistingProduct(
        Product $product,
        array $row,
        int $categoryId,
        float $price
    ): void {
        $product->update([
            'category_id' => $categoryId,
            'tax_slab_id' => self::TAX_SLAB_ID,
            'collection' => 'Seller Marketplace',
            'subcategory' => $row['Category'],
            'brand' => 'Evkartpro',
            'badge' => 'Seller Pick',
            'mrp' => $price,
            'selling_price' => $price,
            'is_published' => strtolower($row['Status']) === 'active',
            'seller_status' => 'approved',
            'published_mode' => 'publish_now',
            'product_condition' => 'new',
        ]);

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->first();

        $stock = (int) $row['Stock'];

        if ($variant) {
            $variant->update([
                'price' => $price,
                'stock' => $stock,
            ]);
        } else {
            $this->createVariant(
                $product,
                $row['ID'],
                $price,
                $stock
            );
        }

        $this->line(
            "Updated existing #{$product->id}: {$product->name} | stock={$stock}"
        );
    }

    private function createVariant(
        Product $product,
        string $sourceId,
        float $price,
        int $stock
    ): ProductVariant {
        return ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'EVKARTPRO-' . $sourceId,
            'colour' => 'Standard',
            'colour_hex' => '#d8ccb9',
            'size' => 'Standard',
            'option_name' => null,
            'option_value' => null,
            'price' => $price,
            'stock' => $stock,
        ]);
    }

    private function uniqueProductSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'evkartpro-product';
        $slug = $base;
        $counter = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}