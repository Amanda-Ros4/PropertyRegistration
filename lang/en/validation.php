<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'email' => 'The :attribute field must be a valid email address.',
    'date' => 'The :attribute field must be a valid date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'integer' => 'The :attribute field must be an integer.',
    'unique' => 'The :attribute has already been taken.',
    'exists' => 'The selected :attribute is invalid.',
    'enum' => 'The selected :attribute is invalid.',
    'max' => [
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'invalid_cpf' => 'The CPF provided is not valid.',
    'attributes' => [
        'name' => 'Full Name',
        'birth_date' => 'Birth Date',
        'cpf' => 'CPF',
        'gender' => 'Gender',
        'phone' => 'Phone',
        'email' => 'E-mail',
        'person_id' => 'Owner',
        'street' => 'Street',
        'number' => 'Number',
        'neighborhood' => 'Neighborhood',
        'complement' => 'Complement',
    ],
];
