<?php

return [
    'between' => [
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file' => 'El campo :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'confirmed' => 'La confirmación del campo :attribute no coincide.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
        'file' => 'El campo :attribute no debe pesar más de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El valor del campo :attribute ya está en uso.',

    // Regla propia App\Rules\StrongPassword; :criteria lista los criterios incumplidos.
    'strong_password' => 'La contraseña no cumple: :criteria.',

    'custom' => [
        'email' => [
            'unique' => 'Ese email ya está registrado.',
        ],
    ],

    'attributes' => [
        'name' => 'nombre',
        'age' => 'edad',
        'email' => 'email',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
    ],
];
