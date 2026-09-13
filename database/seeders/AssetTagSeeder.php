<?php

namespace Database\Seeders;

use App\Models\AssetTag;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeds sensible default Sub-Categories ("Tags") per Asset Category.
 * Idempotent — safe to re-run; only adds missing entries.
 */
class AssetTagSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'ICT Equipment' => [
                'Laptop', 'Desktop Computer', 'Printer', 'Projector', 'Scanner',
                'Camera', 'Networking Device', 'UPS / Power Backup',
            ],
            'Office Equipment' => [
                'Photocopier', 'Printer', 'Scanner', 'Shredder', 'Fax Machine', 'Audio System',
            ],
            'Communication Equipment' => [
                'Two-Way Radio', 'Telephone', 'Mobile Device', 'Modem / Router',
            ],
            'Disaster Response and Rescue Equipment' => [
                'First Aid Kit', 'Fire Extinguisher', 'Rescue Tool', 'Emergency Light',
            ],
            'Medical Equipment' => [
                'Blood Pressure Monitor', 'Weighing Scale', 'Thermometer', 'First Aid Kit',
            ],
            'Printing Equipment' => [
                'Printer', 'Large-Format Printer', 'Binding Machine', 'Laminating Machine',
            ],
            'Sports Equipment' => [
                'Balls', 'Racket / Net Games', 'Gymnastics', 'Athletics',
            ],
            'Technical and Scientific Equipment' => [
                'Laboratory Apparatus', 'Measuring Instrument', 'Microscope',
            ],
            'Machinery' => ['Generator', 'Water Pump', 'Welding Machine'],
            'Motor Vehicles' => ['Service Vehicle', 'Utility Vehicle'],
            'Furnitures & Fixtures' => [
                'Table', 'Chair', 'Cabinet / Shelf', 'Blackboard / Whiteboard',
            ],
            'Books' => ['Textbook', 'Reference Book', 'Encyclopedia'],
        ];

        foreach ($defaults as $categoryName => $tags) {
            $category = Category::where('name', $categoryName)->first();
            if (!$category) continue;

            foreach ($tags as $tagName) {
                AssetTag::updateOrCreate(
                    ['category_id' => $category->id, 'name' => $tagName],
                    []
                );
            }
        }
    }
}
