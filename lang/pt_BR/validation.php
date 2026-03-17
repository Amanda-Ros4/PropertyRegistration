<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'email' => 'O campo :attribute deve ser um e-mail válido.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'unique' => 'Este :attribute já está cadastrado.',
    'exists' => 'O :attribute selecionado é inválido.',
    'enum' => 'O valor selecionado para :attribute é inválido.',
    'max' => [
        'string' => 'O campo :attribute não deve ter mais de :max caracteres.',
    ],
    'invalid_cpf' => 'O CPF informado não é válido.',
    'attributes' => [
        'name' => 'Nome Completo',
        'birth_date' => 'Data de Nascimento',
        'cpf' => 'CPF',
        'gender' => 'Gênero',
        'phone' => 'Telefone',
        'email' => 'E-mail',
        'person_id' => 'Proprietário',
        'street' => 'Logradouro',
        'number' => 'Número',
        'neighborhood' => 'Bairro',
        'complement' => 'Complemento',
    ],
];
