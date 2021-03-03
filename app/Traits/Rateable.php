<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Rateable
{
    public function ratings()
    {
        return $this->morphMany('App\Rating', 'rateable')->orderBy('id', 'DESC');
    }

    public function limitedReview()
    {
        return $this->morphMany('App\Rating', 'rateable')->orderBy('id', 'DESC')->inRandomOrder()->limit(5);
    }

    public function averageRating()
    {
        if (!empty($this->ratings()->avg('rating'))) {
            return $this->ratings()->avg('rating');
        } else {
            return 0;
        }
    }

    public function sumRating()
    {
        return $this->ratings()->sum('rating');
    }

    public function userAverageRating()
    {
        return $this->ratings()->where('user_id', Auth::id())->avg('rating');
    }

    public function userSumRating()
    {
        return $this->ratings()->where('user_id', Auth::id())->sum('rating');
    }

    public function ratingPercent($max = 5)
    {
        $quantity = $this->ratings()->count();
        $total = $this->sumRating();

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    public function getAverageRatingAttribute()
    {
        return $this->averageRating();
    }

    public function getSumRatingAttribute()
    {
        return $this->sumRating();
    }

    public function getUserAverageRatingAttribute()
    {
        return $this->userAverageRating();
    }

    public function getUserSumRatingAttribute()
    {
        return $this->userSumRating();
    }

    public function getRatingPercentAttribute()
    {
        return $this->ratingPercent();
    }

    public function getTotalRatingAttribute()
    {
        return $this->ratings()->count();
    }
}
