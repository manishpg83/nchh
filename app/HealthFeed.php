<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Cohensive\Embed\Facades\Embed;

class HealthFeed extends Model
{
    protected $table = "health_feeds";

    protected $fillable = ['user_id', 'title', 'cover_photo', 'content', 'category_ids', 'likes', 'views', 'status', 'feedback_message', 'video_url', 'other_category'];

    public function category()
    {
        return $this->belongsTo('App\HealthFeedCategory', 'category_ids');
    }

    //Get the user record associated with the HealthFeed.
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function getUrlAttribute(): string
    {
        return action('Front\HealthFeedController@show', [$this->id, str_slug($this->name)]);
    }

    //get cover_photo file path
    public function getCoverPhotoAttribute($value)
    {
        if ($this->attributes['cover_photo']) {
            if (File::exists(storage_path('app/healthfeed/' . $this->attributes['cover_photo']))) {
                return url('storage/app/healthfeed') . '/' . $this->attributes['cover_photo'];
            } else {
                return url('public/images/') . '/feed_default.png';
            }
        }
    }

    public function getCoverPhotoNameAttribute($value)
    {
        return  $this->attributes['cover_photo'];
    }

    public function getHealthFeedDateAttribute($value)
    {
        return $this->created_at->format('j F, Y');
    }

    public function getHealthFeedFullDateAttribute($value)
    {
        return $this->created_at->format('j F, Y h:i:s A');
    }

    //get HealthFeed short title with capital
    public function getShortTitleAttribute($value)
    {
        $title = Str::limit($this->attributes['title'], 40);
        return ucfirst($title);
    }

    //get HealthFeed short description 
    public function getShortContentAttribute($value)
    {
        $content = strip_tags($this->attributes['content']);
        // $text = str_ireplace('<p>', '', $this->attributes['content']);
        // $text = str_ireplace('</p>', '', $text);
        return Str::limit($content, 250);

        strip_tags($this->attributes['content']);
    }

    public function getHtmlVideoUrlAttribute()
    {
        $embed = Embed::make($this->attributes['video_url'])->parseUrl();

        if (!$embed)
            return '';

        $embed->setAttribute(['width' => 727, 'height' => 409]);
        return $embed->getHtml();
    }
}
