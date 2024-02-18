<?php

namespace App\Contracts;

trait GetRequestData
{
    /**
     * Get dates range from request
     *
     * @return mixed|string
     */
    public function getDateRangeRequest()
    {
        $dates =  request()->get('dates');

        if (empty($dates)) {
            $dates = now()->startOfMonth()->format('Y-m-d') . '*' . now()->endOfMonth()->format('Y-m-d');
        }

        return $dates;
    }

    /**
     * Get Branch Id from request
     * @return mixed
     */
    public function getBranchId()
    {
        $branch_id = request()->get('branch_id');

        if (request()->user()->isAgent())
        {
            $branch_id = request()->user()->branch_id;
        }

        return $branch_id;

    }

    /**
     * Get Date range interval eg. daily
     * @return mixed|string
     */
    public function getRequestInterval()
    {
        $interval = request()->get('interval');


        if (empty($interval)) {
            $interval = 'weekly';
        }

        return $interval;
    }
}
