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
 * @property string|null $google_id
 * @property string|null $contact_number
 * @property string|null $tin
 * @property string|null $birthday
 * @property string      $account_type       buyer|seller|logistics
 * @property string|null $region
 * @property string|null $province
 * @property string|null $district           NCR only: 1st-4th District
 * @property string|null $municipality_city
 * @property string|null $barangay
 * @property string|null $postal_code
 * @property string|null $street_address
 * @property string|null $id_document        stored file path
 * @property string|null $business_name
 * @property string|null $business_type      sole_proprietor|partnership|corporation
 * @property string|null $line_of_business
 * @property string|null $business_permit    stored file path
 * @property string|null $dti_registration   stored file path
 * @property string|null $bir_2303           stored file path
 * @property string|null $sec_certificate    stored file path
 * @property bool        $privacy_policy_accepted
 * @property string      $status             pending|approved|disapproved
 * @property string      $verification_status unverified|verified
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
    'google_id',
    'contact_number',
    'tin',
    'birthday',
    'account_type',
    'region',
    'province',
    'district',
    'municipality_city',
    'barangay',
    'postal_code',
    'street_address',
    'id_document',
    'business_name',
    'business_type',
    'line_of_business',
    'business_permit',
    'dti_registration',
    'bir_2303',
    'sec_certificate',
    'privacy_policy_accepted',
    'status',
    'verification_status',
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
