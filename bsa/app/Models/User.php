<?php

namespace App\Models;

// Uncomment the line below once you want email verification enforced:
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $first_name
 * @property string      $last_name
 * @property string|null $middle_initial
 * @property string|null $sex
 * @property string      $email
 * @property string|null $contact_number
 * @property string|null $birthday
 * @property string      $account_type       buyer|seller
 * @property string|null $province
 * @property string|null $municipality_city
 * @property string|null $barangay
 * @property string|null $street_address
 * @property string|null $id_document        stored file path
 * @property string|null $business_name
 * @property string|null $line_of_business
 * @property string|null $business_permit    stored file path
 * @property string      $status             pending|approved|disapproved
 * @property Carbon|null $approved_at
 * @property Carbon|null $email_verified_at
 * @property string      $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'first_name',
    'last_name',
    'middle_initial',
    'sex',
    'email',
    'contact_number',
    'birthday',
    'account_type',
    'province',
    'municipality_city',
    'barangay',
    'street_address',
    'id_document',
    'business_name',
    'line_of_business',
    'business_permit',
    'status',
    'approved_at',
    'password',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at'       => 'datetime',
            'birthday'          => 'date',
            'password'          => 'hashed',
        ];
    }
}
