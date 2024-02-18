<?php

namespace App\Services;

use App\Models\AccountTagger;
use App\Models\Balance;
use App\Models\Customer;

class AccountTaggerService
{

    public function __construct()
    {
    }

    public function runTag()
    {
        logger('starting tagging ...');
        $tags = AccountTagger::all();

        foreach ($tags as $tag) {
            logger('Tagging For ' . $tag->company_id);
            $accountsToTag = Balance::where([
                ['company_id', $tag->company_id],
                ['amount', '<=', $tag->min_balance]
            ])
                ->whereDate('updated_at', '<', now()->subDays($tag->inactive_days))
                ->get('customer_id');

            if (count($accountsToTag) > 0) {
                Customer::whereNull('tag')
                    ->where('company_id', $tag->company_id)
                    ->whereIn('id', $accountsToTag)
                    ->update(['tag' => '2', 'tagged_at' => now()]);
            }


        }

        logger('end tagging ...');
    }

}
