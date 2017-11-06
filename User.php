<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Helpers\General;
use App\UserFollower;
use Carbon\Carbon;
use DB;

class User extends Authenticatable
{
    use Notifiable, CustomModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected static $rolesModel = 'Cartalyst\Sentinel\Roles\EloquentRole';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function attributes(){
        return $this->belongsToMany('App\AttributeField', 'user_attributes', 'user_id', 'attribute_id')
            ->withPivot('value');
    }

    public function getValOfAttrSlug($attr) {
        $attrlist = $this->attributes()->get()->groupBy('slug');
        if(isset($attrlist[$attr])) {
            $rawlist = $attrlist[$attr];
            if(count($rawlist) == 0) return null;
            elseif(count($rawlist) == 1) return General::navigate($rawlist[0], 'pivot.value');
            else {
                return $rawlist->map(function($item) {
                    return General::navigate($item, 'pivot.value');
                });
            }
        } 
        else return null;
    }


    public function friendList()
    {
        return $this->recived()->where('status', '=', '1')->union($this->sent()->where('status', '=', '1'))->with('friend');
    }

    public function friends() {
        // return $this->hasMany()
        return $this->recived()->union($this->sent())->with('friend');
    }

    public function followers() {
        return $this->hasMany('App\UserFollower', 'user_id')->with('follower');
    }

    public function followings() {
        return $this->hasMany('App\UserFollower', 'follower_id')->with('following');
    }

    public function recived() {
        return $this->hasMany('App\Friendship', 'request_to', 'id')->select([
            "request_by AS friend_id", "status", DB::raw("'RECIVED' AS request_type")]);
    }

    public function sent() {
        return $this->hasMany('App\Friendship', 'request_by', 'id')->select([
            "request_to AS friend_id", "status", DB::raw("'SENT' AS request_type")]);
    }

    public function gallery() {
        return $this->hasMany('App\Gallery');
    }

    public function isFollowing(User $friend) {
        return $this->followings()->where('user_id', $friend->id)->count() != 0;
    }


    public function unfollow(User $friend) {
        UserFollower::where('user_id', $friend->id)
            ->where('follower_id', $this->id)
            ->delete();
    }

    public function roles()
    {
        return $this->belongsToMany(static::$rolesModel, 'role_users', 'user_id', 'role_id')->withTimestamps();
    }

    public function getUserAttrib($attribute)
    {
        return $this->attributes()->where('slug', $attribute)->get()->map(function($item) use ($attribute) {
            return General::userAttr($attribute)[$item->pivot->value];
        });    
        
    }

    public function posts() {
        return $this->hasMany('App\UserPost');
    }

    // public function getAttribute($attribute, $default = '')
    // {
    //     $possiblities = General::userAttr($attribute);

    //     if($attribute == "orientation") {
    //         return "Some thing"; 
    //     }

    //     return $attribute;
    // }

    public function awards() {
        return $this->belongsToMany('App\Award', 'user_awards')->withPivot('award_type', 'award_date');
    }

    public function gifts() {
        return $this->belongsToMany('App\Gift', 'user_gifts', 'receiver_id', 'gift_id')->where('user_gifts.type', 'O');
    }

    public function wishlist() {
        return $this->belongsToMany('App\Gift', 'user_gifts', 'receiver_id', 'gift_id')->where('user_gifts.type', 'W');
    }

    public function reviews() {
        return $this->morphMany('App\Review', 'reference'); 
        // old code
        // return $this->hasMany('App\PerformerReview', 'performer_id');

    }

    public function passbook() {
        return $this->hasMany('App\Passbook');
    }

    public function tags() {
        return $this->belongsToMany('App\CategoriesTag', 'user_tags', 'user_id', 'tag_id');
    }

    public function profile_images() {
        return $this->belongsToMany('App\Media', 'user_images', 'user_id', 'media_id');
    }
    public function questions() {
        return $this->hasMany('App\QuestionAnswer', 'performer_id'); 
    }
    public function comments() {
        return $this->morphMany('App\Comment', 'reference'); 
    }
    // public function profileReviews() {
    //     return $this->morphMany('App\Review', 'reference'); 
    // }

    public function replays() {
        return $this->hasMany('App\Video', 'user_id','id');
    }

    public function recentReplays() {
        $date = new Carbon; //  DateTime string will be 2014-04-03 13:57:34
        $date->subWeek(); // or $date->subDays(7),  2014-03-27 13:58:25
        return $this->replays()->where('created_at', '>', $date);
    }


    public function performances() {
        return $this->hasMany('App\PerformanceTricks', 'user_id','id');
    }
    
    public function question_answers(){
        return $this->hasMany('App\Answers','user_id','id')->with('question');
    }

    public function contacts() {
        return $this->hasMany('App\Contact');
    }


    public function tippers() {
        return $this->hasMany('App\Tip', 'performer_id', 'id');
    }

    public function bestTippers() {
        return $this->hasMany('App\Tip', 'performer_id', 'id')->orderBy('token', "DESC");
    }


    public function sellingItems() {
        return $this->hasMany('App\Product', 'seller_id', 'id');
    }
}
    

