<?php

namespace App;

use App\Traits\Rateable;
use App\Traits\UserTraits;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, Rateable, UserTraits, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['referral_link'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];
    
    protected $dates = ['deleted_at'];

    public function getDoctorProfileUrlAttribute(): string
    {
        return action('Front\PageController@doctorProfile', [$this->id, $this->name_slug]);
    }

    public function getProfileUrl($value)
    {
        return action('Front\PageController@viewProfile', [$value, $this->id, $this->name_slug]);
    }

    public function getNameSlugAttribute($value)
    {
        return str_slug($this->attributes['name'], '-');
    }

    public function getProfilePictureAttribute($value)
    {
        if ($this->attributes['profile_picture']) {
            if (File::exists(storage_path('app/user/' . $this->attributes['profile_picture']))) {
                return url('storage/app/user') . '/' . $this->attributes['profile_picture'];
            } else {
                return url('public/images/') . '/default_user.png';
            }
        }
    }

    public function getImageNameAttribute($value)
    {
        return  $this->attributes['profile_picture'];
    }

    public function getNotificationAttribute($value)
    {
        $notification = Notification::where('receiver_id', '=', Auth::id())
            ->where('is_read', '=', 0)
            ->orderBy('id', 'DESC')->limit(15)->get();
        return $notification;
    }

    public function getNotificationCountAttribute($value)
    {
        $notification_count = Notification::where('receiver_id', '=', Auth::id())
            ->where('is_read', '=', 0)
            ->count();
        return $notification_count;
    }

    public function getNameAttribute($value)
    {
        return ucwords($this->attributes['name']);
    }

    public function getPhoneWithDialcodeAttribute($value)
    {
        return '+' . $this->attributes['dialcode'] . '' . $this->attributes['phone'];
    }

    public function getFullAddressAttribute($value)
    {
        $address =  $this->attributes['address'];
        $address .=  isset($this->attributes['city']) ? '<br>' . $this->attributes['city'] : '';
        $address .=  isset($this->attributes['city']) ? (isset($this->attributes['state']) ? ', ' . $this->attributes['state'] : '') : (isset($this->attributes['state']) ? '<br>' . $this->attributes['state'] : '');
        $address .= '<br>' . $this->attributes['country'] . ', ' . $this->attributes['pincode'];
        return $address;
    }

    public function getShortAddressAttribute($value)
    {
        $address = isset($this->address) ? ($this->address . ',') : '';
        $city = isset($this->city) ? ($this->city . ',') : '';
        $pincode = isset($this->pincode) ? $this->pincode : '';
        return  $address . $city . $pincode;
    }

    /**
     * Get the user's referral link.
     *
     * @return string
     */
    public function getReferralLinkAttribute()
    {
        return $this->referral_link = route('register', ['ref' => $this->referral_code]);
    }

    public function role()
    {
        return $this->belongsTo('App\UserRole', 'role_id');
    }

    public function detail()
    {
        return $this->hasOne('App\UserDetail', 'user_id');
    }

    public function gallery()
    {
        return $this->hasMany('App\UserGallery', 'user_id');
    }

    public function notification()
    {
        return $this->hasMany('App\Notification', 'receiver_id')->orderBy('id', 'DESC');
    }

    public function messages()
    {
        return $this->hasMany('App\Message', 'to');
    }

    public function country()
    {
        return $this->belongsTo('App\Country', 'country_id');
    }

    public function state()
    {
        return $this->belongsTo('App\State', 'state_id');
    }

    public function city()
    {
        return $this->belongsTo('App\City', 'city_id');
    }

    public function healthfeed()
    {
        return $this->hasMany('App\HealthFeed', 'user_id');
    }

    public function practice()
    {
        /* status = 1 | active practice and accept invitation */
        return $this->hasMany('App\PracticeManager', 'doctor_id')->where('status', 1);
    }

    public function practiceAsStaff()
    {
        return $this->hasMany('App\PracticeManager', 'added_by')->where('status', 1);
    }

    public function wishlist()
    {
        return $this->hasMany('App\Wishlist', 'user_id');
    }

    public function setting()
    {
        return $this->hasOne('App\Setting', 'user_id');
    }

    public function staff()
    {
        return $this->hasMany('App\StaffManager', 'added_by');
    }

    public function drug()
    {
        return $this->hasMany('App\Drug', 'added_by');
    }

    public function feedback()
    {
        return $this->hasOne('App\Feedback', 'user_id');
    }

    //patient appointment list
    public function appointment()
    {
        return $this->hasMany('App\Appointment', 'patient_id');
    }

    //get added by details
    public function addedBy()
    {
        return $this->belongsTo('App\User', 'added_by');
    }

    /**
     * A user has a referrer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id', 'id');
    }

    /**
     * A user has many referrals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id', 'id');
    }

    /* Functions */
    public function isWishlist($id)
    {
        $user = Wishlist::where('user_id', '=', Auth::id())->where('doctor_id', '=', $id)->count();
        return $user;
    }

    public function services()
    {
        return $this->hasMany('App\DiagnosticsService', 'diagnostics_id');
    }
    
    public function bankDetail()
    {
        return $this->hasOne('App\UserBankAccount', 'user_id');
    }

    public function withdrawHistory()
    {
        return $this->hasMany('App\UserWithdrawHistory', 'user_id');
    }

    public function commission()
    {
        return $this->hasOne('App\Commission', 'user_id');
    }

    public function agentDocuments()    {
        
        return $this->hasMany(Upload::class)->where('type', 'agent');
    }

    public function diagnosticsDocuments()    {
        
        return $this->hasMany(Upload::class)->where('type', 'diagnostics');
    }

    public function pharmacyDocuments()    {
        
        return $this->hasMany(Upload::class)->where('type', 'pharmacy');
    }
}
