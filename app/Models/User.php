<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'post',
    ];

    protected $appends = ['post_title'];

    public function getPostTitleAttribute()
    {
        foreach (self::$posts as $post) {
            if ($post['id'] === $this->post) {
                return $post['name'];
            }
        }
    }

    public static $posts = array(
        [
            'id' => 'owner',
            'name' => 'Владелец',
        ],
        [
            'id' => 'admin',
            'name' => 'Администратор',
        ],
        [
            'id' => 'worker',
            'name' => 'Механизатор',
        ]
    );

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function workerUnits()
    {
        return $this->hasMany(WorkerUnit::class, 'worker_id', 'id');
    }

    public function moderatedOperationNotes()
    {
        return $this->hasMany(OperationNote::class, 'id', 'created_by');
    }
}
