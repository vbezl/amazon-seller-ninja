<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;

use DB;
use App\Models\Product;
use App\Models\Price;
use App\Models\Rank;


class ProductChartController extends Controller
{

    /**
     * Show the connect form
     *
     */
    public function showChart($id)
    {

        $product = Product::find($id);
        $category = $product->categories()->wherePivot('track', 1)->first();
        $ranks = Rank::selectRaw('MIN(created_at) as created_at, AVG(rank) as rank')
            ->where('product_id',$product->id)
            ->where('category_id', $category->id)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
            ->get();
        $prices = Price::selectRaw('MIN(created_at) as created_at, AVG(regular_price) as regular_price, AVG(buying_price) as buying_price')
            ->where('product_id',$product->id)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
            ->get();

        $data = [];
        foreach ($ranks as $rank) {
            $data[$rank->created_at->toDateString()]['rank'] = $rank->rank;
        }
        foreach ($prices as $price) {
            $data[$price->created_at->toDateString()]['buying_price'] = $price->buying_price;
        }

        return view('panel.chart', ['data' => $data]);
    }


}