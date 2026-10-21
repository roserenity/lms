<?php

namespace App\Helpers;
class FilterHelper
{


    public static function queryCourseTitleAndCodeSearch($search, $query)
    {
        $query->where(function ($query) use ($search) {
            $query->where('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('course_code', $search);
        });
    
        return $query;
    }

    public static function queryCreatedAt($query, $date_from, $date_to)
    {
        return $query->when($date_from && $date_to, function ($query) use ($date_from, $date_to){
            $query->whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to);
        })
        ->when($date_from && !$date_to, function ($query) use ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        })
        ->when($date_to && !$date_from, function ($query) use ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        });
    }

    public static function queryAvailabilityDates($query, $start_date, $end_date)
    {
        if ($start_date || $end_date) {
            $query->whereNotNull('available_until')
                  ->where(function ($query) use ($start_date, $end_date) {
                      if ($start_date && $end_date) {
                          $query->whereBetween('available_until->start_date', [$start_date, $end_date])
                                ->orWhereBetween('available_until->end_date', [$start_date, $end_date])
                                ->orWhere(function ($query) use ($start_date, $end_date) {
                                    $query->where('available_until->start_date', '<=', $start_date)
                                          ->where('available_until->end_date', '>=', $end_date);
                                });
                      } elseif ($start_date) {
                            $query->whereDate('available_until->start_date', '>=', $start_date);
                      } elseif ($end_date) {
                            $query->whereDate('available_until->start_date', '<=', $end_date);
                      }
                  });
        }
        return $query;
    }

    public static function filterEnrolledUsers($query, $course_id)
    {
        return $query->whereHas('enrollments', function($innerQuery) use ($course_id) {
            $innerQuery->where('course_id', $course_id);
        });
    }

}
