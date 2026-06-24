<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use Notifiable, MustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'UsType', 'UsAvatar', 'UsStatus', 'UsSlug', 'UsRol', 'UsRolDesc', 'UsRol2', 'UsRolDesc2', 'updated_by', 'FK_UserPers'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function getRouteKeyName()
    {
        return 'UsSlug';
    }

    //se especifica la raclacion con la tabla personals
    public function persona(){
        return $this->belongsTo('App\Personal', 'FK_UserPers', 'ID_Pers');
    }

    //se especifica la raclacion con la tabla declaraciones
    public function ArticuloProv()
    {
        return $this->hasMany('App\ArticuloPorProveedor', 'ID_ArtiProve', 'id');//como user tiene muchas declaraciones
    }
    public function OrdenCompras()
    {
        return $this->hasMany('App\ordenCompra', 'ID_Orden', 'id');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new Notifications\VerifyEmail);
    }
}
