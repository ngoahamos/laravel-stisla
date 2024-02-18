<?php

namespace App\Contracts;

use Carbon\Carbon;
use stdClass;

trait DateHelperContract
{
    /**
     * Prepares a date range in an interval
     *
     * @param mixed $range
     * @param string $interval
     * @return array
     */
    public function makeRangeLabels($range, $interval)
    {
        $start_date = $range->start->copy();

        $result = [];

        if ($interval == 'daily') {
            while ($start_date->lte($range->end)) {
                $end = $start_date->copy()->addDay();

                $result[] = [
                    'label' => $start_date->copy()->format('D d M'),
                    'alt_label' => $start_date->copy()->format('D M d'),
                    'start' => $this->makeDateEndpoint($start_date),
                    'end' => $this->makeDateEndpoint($start_date, false)
                ];

                $start_date->addDay();
            }
        } elseif ($interval == 'weekly') {
            $week = 1;

            do {
                $end = $start_date->copy()->endOfWeek();

                if ($end->lte($range->end)) {
                    $result[] = [
                        'label' => $start_date->copy()->format('D d M'). ' - ' .$end->format('D d M'),
                        'alt_label' => "Week $week",
                        'start' => $this->makeDateEndpoint($start_date),
                        'end' => $this->makeDateEndpoint($end, false)
                    ];
                    $week++;

                    $start_date = $end->copy()->addDay();
                } else {
                    $result[] = [
                        'label' => $start_date->copy()->format('D d M'). ' - ' .$range->end->format('D d M'),
                        'alt_label' => "Week $week",
                        'start' => $this->makeDateEndpoint($start_date),
                        'end' => $this->makeDateEndpoint($range->end, false)
                    ];

                    $week++;

                    break;
                }
            } while ($end->lte($range->end));
        } elseif ($interval == 'monthly') {
            do {
                $end = $start_date->copy()->endOfMonth();

                if ($end->lte($range->end)) {
                    $result[] = [
                        'label' => $start_date->copy()->format('D d M'). ' - ' .$end->format('D d M'),
                        'alt_label' => $start_date->copy()->format('F Y'),
                        'start' => $this->makeDateEndpoint($start_date),
                        'end' => $this->makeDateEndpoint($end, false)
                    ];

                    $start_date = $end->addDay();
                } else {
                    $result[] = [
                        'label' => $start_date->copy()->format('D d M'). ' - ' .$range->end->format('D d M'),
                        'alt_label' => $start_date->copy()->format('F Y'),
                        'start' => $this->makeDateEndpoint($start_date),
                        'end' => $this->makeDateEndpoint($range->end, false)
                    ];

                    break;
                }
            } while ($start_date->lte($range->end));
        } elseif ($interval == 'annually') {
            do {
                $end = $start_date->copy()->endOfYear();

                if ($end->lte($range->end)) {
                    $result[] = [
                        'label' => $start_date->copy()->format('D d M'). ' - ' .$end->format('D d M'),
                        'start' => $this->makeDateEndpoint($start_date),
                        'alt_label' => $start_date->copy()->format('Y'),
                        'end' => $this->makeDateEndpoint($end, false)
                    ];

                    $start_date = $end->addDay();
                } else {
                    $result[] = [
                        'label' => $start_date->copy()->format('D d M'). ' - ' .$range->end->format('D d M'),
                        'start' => $this->makeDateEndpoint($start_date),
                        'alt_label' => $start_date->copy()->format('Y'),
                        'end' => $this->makeDateEndpoint($range->end, false)
                    ];

                    break;
                }
            } while ($start_date->lte($range->end));
        }

        return $result;
    }

    /**
     * Splits the date range into start and end dates
     *
     * @param String $range
     * @return stdClass|null
     */
    private function getDateRanges($range)
    {
        if ($range) {
            $range = explode('*', $range);

            if (count($range) == 2) {
                $dates_range = new stdClass();
                $dates_range->start = Carbon::parse($range[0]);
                $dates_range->end = Carbon::parse($range[1]);
                return $dates_range;
            }
        }

        return null;
    }


    /**
     * Prepares date end points
     *
     * @param mixed $date
     * @param boolean $min
     * @return string
     */
    private function makeDateEndpoint($date, $min = true)
    {
        if ($min) {
            return Carbon::parse($date)->toDateString() . ' 00:00:00';
        } else {
            return Carbon::parse($date)->toDateString() . ' 23:59:00';
        }
    }

    /**
     * prepare date and label
     * @param string $dates
     * @param string $interval
     * @return array
     */
    public function makeIntervals(string $dates, string $interval)
    {
        return $this->makeRangeLabels($this->getDateRanges($dates), $interval);
    }

    /**
     * get human-readable date
     *
     * @param string $dates
     * @return string
     */
    public function prettyStartInterval(string $dates)
    {
        $range = $this->getDateRanges($dates);

        if ($range == null) {
            return '';
        }
        return $range->start->format('D d M') . ' - ' . $range->end->format('D d M');
    }
}
