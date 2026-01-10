<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendee2 extends Model
{
    protected $table = 'attendees2';

    protected $fillable = [
        'no',
        'register_date',

        // personal
        'first_name_th',
        'last_name_th',
        'first_name_en',
        'last_name_en',

        'email',
        'phone',

        // organization
        'organization',
        'academic_position',
        'admin_position',

        // location
        'province_type_1',
        'province_type_2',
        'province',

        // travel
        'travel_from_province',

        // food
        'food_type',
        'food_allergy',
        'food_other_constraints',

        // activity
        'activity_workshop',
        'activity_conference',
        'activity_excursion',

        // presentation
        'presentation_conference',
        'presentation_oral',
        'presentation_poster',

        // status
        'register_status',
        'attendance_status',

        // note
        'note',
        'admin_note',

        // misc
        'care',
        'qr_code',

        // extra dates
        'register_date1',
        'register_date2',
        'status',
    ];

    protected $casts = [
        'register_date'  => 'date',
    'register_date1' => 'datetime', // ✅ วัน + เวลา
    'register_date2' => 'datetime', // ✅ วัน + เวลา

        // booleans
        'province_type_1' => 'boolean',
        'province_type_2' => 'boolean',

        'activity_workshop'   => 'boolean',
        'activity_conference' => 'boolean',
        'activity_excursion'  => 'boolean',

        'presentation_conference' => 'boolean',
        'presentation_oral'        => 'boolean',
        'presentation_poster'      => 'boolean',
    ];
}
