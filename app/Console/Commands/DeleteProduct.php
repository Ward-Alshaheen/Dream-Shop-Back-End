<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Traits\GeneralTrait;
use Illuminate\Console\Command;

class DeleteProduct extends Command
{
    use GeneralTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete_product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the product after the expiration date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $products = Product::all();
        foreach ($products as $product) {
           echo $product['remaining_days']-=1;
            if ($product['remaining_days'] <= 0) {
                $product->delete();
            } else {
                $product['price'] = $this->price(json_decode($product['discounts'], true), $product['remaining_days']);
                $product->save();
            }
        }
    }
}
