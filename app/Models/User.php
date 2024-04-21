<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Notifications\Notification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    public $verificationToken;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'name_kana',
        'role',
        'graduation_term',
        'nickname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
        'email_verification_token_expires_at',
        'password_reset_token',
        'password_reset_token_expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'graduation_term' => 'integer',
    ];

    /**
     *チケットとのリレーション
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    public function sendEmailVerificationNotification()
    {
        // カスタムメールを送信
        $this->notify(new CustomVerifyEmailNotification($this->email_verification_token));
    }

    /**
     * Slackチャンネルへの通知ルートを提供します。
     */
    public function routeNotificationForSlack(Notification $notification)
    {
        return env('SLACK_WEBHOOK_URL');
    }
}
