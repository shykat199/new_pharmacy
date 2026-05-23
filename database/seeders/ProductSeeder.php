<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $companyIds = Company::pluck('id')->toArray();

        if (empty($companyIds)) {
            $this->command->info('No companies found. Please seed the companies table first.');
            return;
        }

        $medicineNames = [
            'Paracetamol', 'Ibuprofen', 'Aspirin', 'Amoxicillin', 'Metformin', 'Omeprazole',
            'Atorvastatin', 'Losartan', 'Gabapentin', 'Cetirizine', 'Doxycycline', 'Levothyroxine',
            'Ciprofloxacin', 'Hydrochlorothiazide', 'Prednisone', 'Metoprolol', 'Azithromycin',
            'Clopidogrel', 'Alprazolam', 'Furosemide', 'Simvastatin', 'Lisinopril', 'Fluoxetine',
            'Sertraline', 'Montelukast', 'Amlodipine', 'Citalopram', 'Warfarin', 'Tramadol',
            'Hydrocodone', 'Oxycodone', 'Morphine', 'Codeine', 'Diazepam', 'Lorazepam',
            'Ranitidine', 'Famotidine', 'Esomeprazole', 'Pantoprazole', 'Budesonide',
            'Mometasone', 'Fluticasone', 'Salbutamol', 'Formoterol', 'Tiotropium', 'Albuterol',
            'Levalbuterol', 'Ipratropium', 'Theophylline', 'Clarithromycin', 'Erythromycin',
            'Duloxetine', 'Venlafaxine', 'Mirtazapine', 'Trazodone', 'Bupropion', 'Topiramate',
            'Lamotrigine', 'Carbamazepine', 'Valproate', 'Phenobarbital', 'Levetiracetam',
            'Methotrexate', 'Cyclophosphamide', 'Tacrolimus', 'Cyclosporine', 'Mycophenolate',
            'Sirolimus', 'Rituximab', 'Infliximab', 'Adalimumab', 'Etanercept', 'Tofacitinib',
            'Anakinra', 'Hydroxychloroquine', 'Sulfasalazine', 'Mesalazine', 'Balsalazide',
            'Prednisolone', 'Betamethasone', 'Dexamethasone', 'Triamcinolone', 'Clobetasol',
            'Methylprednisolone', 'Halobetasol', 'Hydrocortisone', 'Fluocinolone', 'Tacrolimus (topical)',
            'Pimecrolimus', 'Isotretinoin', 'Tretinoin', 'Adapalene', 'Benzoyl Peroxide',
            'Salicylic Acid', 'Clindamycin (topical)', 'Erythromycin (topical)', 'Mupirocin',
            'Neomycin', 'Polymyxin B', 'Gentamicin', 'Linezolid', 'Vancomycin', 'Daptomycin',
            'Meropenem', 'Imipenem', 'Ceftriaxone', 'Cefotaxime', 'Cefepime', 'Ceftazidime',
            'Penicillin G', 'Penicillin V', 'Ampicillin', 'Dicloxacillin', 'Nafcillin',
            'Piperacillin', 'Tazobactam', 'Ceftaroline', 'Cephalexin', 'Cefuroxime', 'Cefpodoxime',
            'Cefdinir', 'Cefixime', 'Levofloxacin', 'Moxifloxacin', 'Ofloxacin', 'Norfloxacin',
            'Tetracycline', 'Minocycline', 'Chloramphenicol', 'Rifampin', 'Isoniazid', 'Ethambutol',
            'Pyrazinamide', 'Streptomycin', 'Metronidazole', 'Tinidazole', 'Nitrofurantoin',
            'Fosfomycin', 'Fluconazole', 'Itraconazole', 'Ketoconazole', 'Voriconazole', 'Posaconazole',
            'Amphotericin B', 'Caspofungin', 'Anidulafungin', 'Micafungin', 'Griseofulvin',
            'Terbinafine', 'Acyclovir', 'Valacyclovir', 'Famciclovir', 'Ganciclovir', 'Foscarnet',
            'Cidofovir', 'Oseltamivir', 'Zanamivir', 'Peramivir', 'Baloxavir', 'Remdesivir',
            'Favipiravir', 'Ribavirin', 'Sofosbuvir', 'Ledipasvir', 'Daclatasvir', 'Velpatasvir',
            'Elbasvir', 'Glecaprevir', 'Pibrentasvir', 'Boceprevir', 'Telaprevir', 'Maraviroc',
            'Raltegravir', 'Dolutegravir', 'Bictegravir', 'Efavirenz', 'Nevirapine', 'Etravirine',
            'Rilpivirine', 'Lopinavir', 'Ritonavir', 'Darunavir', 'Atazanavir', 'Saquinavir',
            'Indinavir', 'Nelfinavir', 'Enfuvirtide', 'Ibalizumab', 'Fostemsavir', 'Tenofovir',
            'Lamivudine', 'Emtricitabine', 'Abacavir', 'Zidovudine', 'Stavudine', 'Didanosine',
            'Hydroxyzine', 'Diphenhydramine', 'Loratadine', 'Fexofenadine', 'Meclizine',
            'Promethazine', 'Ondansetron', 'Granisetron', 'Palonosetron', 'Aprepitant',
            'Fosaprepitant', 'Netupitant', 'Rolapitant', 'Scopolamine', 'Dimenhydrinate',
            'Prochlorperazine', 'Thiethylperazine'
        ];


        foreach (range(1, 100) as $index) {
            $name = $faker->randomElement($medicineNames);
            Product::create([
                'name'        => $name,
                'slug'        => Str::slug($name) . '-' . $index, // Generate unique slug
                'company_id'  => $faker->randomElement($companyIds),
                'unit_price'  => $faker->randomFloat(2, 10, 500), // Random price between 10 and 500
                'box_per_pic' => $faker->numberBetween(1, 20), // Random boxes per pic
                'stock'       => $faker->numberBetween(0, 1000), // Random stock count
                'status'      => $faker->randomElement([ACTIVE_STATUS, INACTIVE_STATUS]), // Random status
            ]);
        }
    }
}
