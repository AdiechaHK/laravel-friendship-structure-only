<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
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

    public function friendList()
    {
        return $this->recived()->where('status', '=', '1')->union($this->sent()->where('status', '=', '1'))->with('friend');
    }

    public function friends() {
        return $this->recived()->union($this->sent())->with('friend');
    }

    public function recived() {
        return $this->hasMany('App\Friendship', 'request_to', 'id')->select([
            "request_by AS friend_id", "status", DB::raw("'RECIVED' AS request_type")]);
    }

    public function sent() {
        return $this->hasMany('App\Friendship', 'request_by', 'id')->select([
            "request_to AS friend_id", "status", DB::raw("'SENT' AS request_type")]);
    }

}
    

