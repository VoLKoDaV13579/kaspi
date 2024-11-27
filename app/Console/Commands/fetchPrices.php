<?php

namespace App\Console\Commands;

use App\Jobs\fetchPriceChange;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class fetchPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check prices change and send notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    // Запускает проверку цен на все отслеживаемые продукты
        $products = Product::all();
        foreach ($products as $product) {
            fetchPriceChange::dispatch($product->name, $product->url);
        }

    }
}
