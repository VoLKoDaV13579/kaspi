<?php

namespace App\Jobs;

use App\Models\PriceChangeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class fetchPriceChange implements ShouldQueue
{
    use Queueable;

    protected $productId;
    protected $url;

    public function __construct($productId, $url)
    {
        $this->productId = $productId;
        $this->url = $url;
    }

    
    public function handle(): void
    {
        
        $productId = $this->productId;
        $url = $this->url;

        $product = Product::firstOrCreate(['name' => $productId]);
        
        
        $productPrices = $this->fetchPrices($url, $productId);
        
        if ($productPrices !== null) {
            foreach ($productPrices as $merchantName => $price) {
                $seller = Seller::firstOrCreate(['name' => $merchantName]);
                // Log::info('Продавец', ['seller'=> $seller['id'], 'product'=> $product['id']]);
                $priceHistory = PriceHistory::where('product_id', $product['id'])
                            ->where('seller_id', $seller['id'])
                            ->first();

                    if ($priceHistory !== null && $priceHistory['old_price'] > $price) {
                        PriceChangeNotification::create([
                            'product_id' => $product['id'],
                            'seller_id' => $seller['id'],
                            'old_price' => $priceHistory['old_price'],
                            'new_price' => $price,
                        ]);
                        
                        Log::info('Цена снизилась', [
                            'product' => [
                                'id' => $product['id'],
                                'name' => $product['name'],
                            ],
                            'seller' => [
                                'id' => $seller['id'],
                                'name' => $seller['name'],
                            ],
                            'price_change' => [
                                'old_price' => $priceHistory['old_price'],
                                'new_price' => $price,
                            ]
                        ]);
                        
                    } else {
                        Log::info(sprintf(
                            'Цена на товар с номером "%s" (%s) не изменилась у продавца "%s". Текущая цена: %s. Ссылка на товар: %s',
                            $product->name,
                            $product->id,
                            $seller->name,
                            $price,
                            $product->url
                        ));
                        
                    }
                            
                PriceHistory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'seller_id' => $seller->id,
                    ],
                    [
                        'old_price' => PriceHistory::where('product_id', $product->id)
                                                    ->where('seller_id', $seller->id)
                                                    ->value('new_price') ?? 0,
                        'new_price' => $price,
                        'updated_at' => now(),
                    ]
                );
            }
        } else {
            Log::error('Не удалось получить цены для продукта: ' . $productId);
        }
    }

    public function fetchPrices($url, $productId)
    {
        $headers = [
            'Accept' => 'application/json, text/*',
            'Accept-Encoding' => 'gzip, deflate, br, zstd',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cookie' => '_ga_0R30CM934D=GS1.1.1732521373.1.1.1732521411.22.0.0; ks.tg=64; k_stat=881b41d1-7ece-4017-9a91-94a671e37cbc; _ga=GA1.2.1253918746.1732521373; _gid=GA1.2.1854501878.1732522895; _fbp=fb.1.1732522895331.458414337367405772; _ga_NT9Q2XGFJ8=GS1.1.1732522894.1.0.1732522900.54.0.0; kaspi.storefront.cookie.city=750000000',
            'Host' => 'kaspi.kz',
            'Origin' => 'https://kaspi.kz',
            'Referer' => $url,
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-origin',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'X-KS-City' => $this->extractCityId($url) ?? '750000000',
            'sec-ch-ua' => '"Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
        ];


        $data = [
            'cityId' => $this->extractCityId($url),
            'id' => $this->extractProductIdFromUrl($url) ?? '750000000',
            'merchantUID' => '',
            'product' => [
                'brand' => 'Leadbros',
                'categoryCodes' => [
                    'Refrigerators',
                    'Big home appliances',
                    'Home equipment',
                    'Categories',
                ],
                'baseProductCodes' => [],
                'groups' => null,
            ],
            'sortOption' => 'PRICE',
            'highRating' => null,
            'searchText' => null,
            'zoneId' => [
                'Magnum_ZONE1',
            ],
            'installationId' => '-1',
        ];


        $urlOffers = 'https://kaspi.kz/yml/offer-view/offers/' . $productId;
        try {

            $response = Http::withHeaders($headers)
                ->withBody(json_encode($data), 'application/json')
                ->post($urlOffers);
                
            if ($response->successful()) {
                $responseData = $response->json();
                $offers = $responseData['offers'] ?? [];
                $prices = [];
                foreach ($offers as $offer) {
                    $prices[$offer['merchantName']] = $offer['price'];
                    $seller = Seller::firstOrCreate(['name' => $offer['merchantName']]);
                }

                return $prices;
            } else {
                Log::error('Error fetching data from Kaspi', ['response' => $response->body()]);
                return null;
            }
        } catch (\Exception $e) {

            Log::error('Error fetching data from Kaspi', ['exception' => $e->getMessage()]);
            return null;
        }
    }
    
    private function extractProductIdFromUrl(string $url)
    {
        $pattern = '/-([0-9]+)(?:\/\?c=|$)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
    function extractCityId(string $url) {
        if (preg_match('/\?c=(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null; 
    }
}
