<?php

return [
    'required' => ':attribute حقل مطلوب.',
    'integer' => ':attribute يجب أن يكون رقم صحيح.',
    'exists' => ':attribute غير موجود في قاعدة البيانات.',
    'min' => ':attribute يجب أن يكون على الأقل :min.',
    'max' => ':attribute يجب أن يكون أقل من أو يساوي :max.',
    'string' => ':attribute يجب أن يكون نصاً.',
    'email' => ':attribute يجب أن يكون بريد إلكتروني صحيحا',
    'unique' => ':attribute موجود مسبقا.',
    'father_id' => ':attribute حقل مطلوب.',
    'servant_id' => ':attribute حقل مطلوب.',
    'user_id' => ':attribute حقل مطلوب.',
    'attendant' => ':attribute حقل مطلوب.',
    'attendant_phone' => ':attribute حقل مطلوب.',
    'address_type' => ':attribute حقل مطلوب.',
    'address' => ':attribute حقل مطلوب.',
    'address_url' => ':attribute حقل مطلوب.',
    'area_id' => ':attribute حقل مطلوب.',
    'patient_nums' => ':attribute حقل مطلوب.',
    'from' => ':attribute حقل مطلوب.',
    'to' => ':attribute حقل مطلوب.',
    'date' => ':attribute حقل مطلوب.',

    'custom' => [
        'E1C1F' => [
            'required' => 'رقم العائلة (E1C1F) مطلوب.',
            'integer' => 'رقم العائلة (E1C1F) يجب أن يكون رقماً صحيحاً.',
            'exists' => 'رقم العائلة (E1C1F) غير موجود في السجلات.',
        ],
        'NR' => [
            'required' => 'رقم NR مطلوب.',
            'integer' => 'رقم الشخص في العائله يجب أن يكون رقماً صحيحاً.',
            'min' => 'رقم الشخص في العائله يجب أن يكون على الأقل 1.',
            'max' => 'رقم الشخص في العائله يجب أن يكون أقل من أو يساوي 6.',
        ],
        'password' => [
            'required' => 'كلمة المرور مطلوبة.',
            'string' => 'كلمة المرور يجب أن تكون نصاً.',
        ],
        'language' => [
            'unique' => 'اللغة :attribute موجودة بالفعل.',
        ],
        'attendant_phone' => [
            'required' => 'رقم الهاتف مطلوب.',
            'string' => 'رقم الهاتف يجب أن يكون نصاً.',
            'digits' => 'رقم الهاتف الصحيح من 11 رقم.',
        ],
        'date' => [
            'required' => 'التاريخ مطلوب.',
            'after_or_equal' => 'التاريخ يجب أن يكون بعد أو يساوي اليوم.',
        ],

    ],

    // This section will allow you to customize the attribute names as well
    'attributes' => [
        'E1C1F' => 'رقم العائلة',
        'NR' => 'رقم الشخص في العائله',
        'password' => 'كلمة المرور',
        'name_en' => 'الاسم باللغه الانجليزية',
        'name_ar' => 'الاسم باللغه العربية',
        'phone' => 'رقم الهاتف',
        'email' => 'البريد الالكتروني',
        'father_id' => 'اسم الكاهن',
        'servant_id' => 'اسم الخادم',
        'user_id' => 'اسم المستخدم',
        'attendant' => 'اسم المرافق',
        'attendant_phone' => 'رقم هاتف المرافق',
        'address_type' => 'نوع العنوان',
        'address' => 'العنوان',
        'address_url' => 'رابط العنوان',
        'area_id' => 'اسم المنطقة',
        'patient_nums' => 'عدد المرضى',
        'from' => 'من',
        'to' => 'إلى',
        'date' => 'التاريخ',
    ],
];
