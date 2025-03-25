<?php

return [
    'required' => ':attribute is required.',
    'integer' => ':attribute must be an integer.',
    'exists' => ':attribute does not exist in the database.',
    'min' => ':attribute must be at least :min.',
    'max' => ':attribute must be less than or equal to :max.',
    'string' => ':attribute must be a string.',
    'email' => ':attribute must be a valid email address.',
    'unique' => ':attribute has already been taken.',
    'father_id' => ':attribute is required.',
    'servant_id' => ':attribute is required.',
    'user_id' => ':attribute is required.',
    'attendant' => ':attribute is required.',
    'attendant_phone' => ':attribute phone is required.',
    'address_type' => ':attribute is required.',
    'address' => ':attribute is required.',
    'address_url' => ':attribute is required.',
    'area_id' => ':attribute is required.',
    'patient_nums' => ':attribute is required.',
    'from' => ':attribute is required.',
    'to' => ':attribute is required.',
    'date' => ':attribute is required.',

    // Custom messages for the fields in LoginRequest
    'custom' => [
        'E1C1F' => [
            'required' => 'Family ID (E1C1F) is required.',
            'integer' => 'Family ID (E1C1F) must be an integer.',
            'exists' => 'Family ID (E1C1F) does not exist in the records.',
        ],
        'NR' => [
            'required' => 'NR number is required.',
            'integer' => 'NR number must be an integer.',
            'min' => 'NR number must be at least 1.',
            'max' => 'NR number must be less than or equal to 6.',
        ],
        'password' => [
            'required' => 'Password is required.',
            'string' => 'Password must be a string.',
        ],
        'language' => [
            'unique' => 'Language has already been taken.',
        ],
        'attendant_phone' => [
            'required' => 'Attendant phone is required.',
            'string' => 'Attendant phone must be a string.',
            'digits' => 'Attendant phone must be a valid phone number form 11 digits.',
        ],
        'date' => [
            'required' => 'Date is required.',
            'after_or_equal' => 'Date must be a date after or equal to :date.',
        ],
    ],

    // This section will allow you to customize the attribute names as well
    'attributes' => [
        'E1C1F' => 'Family ID',
        'NR' => 'NR number',
        'password' => 'Password',
        'name_en' => 'Name (English)',
        'name_ar' => 'Name (Arabic)',
        'phone' => 'Phone',
        'email' => 'Email',
        'father_id' => 'Father',
        'servant_id' => 'Servant',
        'user_id' => 'User',
        'attendant' => 'Attendant',
        'attendant_phone' => 'Attendant Phone',
        'address_type' => 'Address Type',
        'address' => 'Address',
        'address_url' => 'Address URL',
        'area_id' => 'Area',
        'patient_nums' => 'Patient Numbers',
        'from' => 'From',
        'to' => 'To',
        'date' => 'Date',
    ],
];
