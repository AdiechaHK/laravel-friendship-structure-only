<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    protected $table = 'friendships';


    public function requested_by(){
    	return $this->belongsTo('App\User','request_by', 'id');
    }

    public function requested_to(){
    	return $this->belongsTo('App\User','request_to', 'id');
    }

    public function friend() {
    	return $this->belongsTo('App\User', 'friend_id', 'id');
    }

    public static function getFriendship($user_id, $friend_id) {
    	return static::where(function($query) use ($user_id, $friend_id) {
    		$query->where('request_to', $user_id)->where('request_by', $friend_id);
    	})->orWhere(function($query) use ($user_id, $friend_id) {
    		$query->where('request_to', $friend_id)->where('request_by', $user_id);
    	})->first();
    }

}
