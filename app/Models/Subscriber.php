<?php

namespace App\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property bool $is_verified
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['email', 'name', 'is_verified'])]
class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
        ];
    }
}
