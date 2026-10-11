<?php

namespace App\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    protected $fillable = ['email', 'is_subscribed', 'subscribed_at', 'unsubscribed_at', 'unsubscribe_token'];

    protected function casts(): array
    {
        return ['is_subscribed' => 'boolean', 'subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }
}
