<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'unique' => 'Este :attribute ya está registrado.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'enum' => 'El valor seleccionado para :attribute no es válido.',
    'max' => [
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'invalid_cpf' => 'El CPF proporcionado no es válido.',
    'attributes' => [
        'name' => 'Nombre Completo',
        'birth_date' => 'Fecha de Nacimiento',
        'cpf' => 'CPF',
        'gender' => 'Género',
        'phone' => 'Teléfono',
        'email' => 'Correo',
        'person_id' => 'Propietario',
        'street' => 'Calle',
        'number' => 'Número',
        'neighborhood' => 'Barrio',
        'complement' => 'Complemento',
    ],
];
