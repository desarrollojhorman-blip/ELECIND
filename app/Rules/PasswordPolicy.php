<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Política de contraseña para cuentas de usuario.
 *
 * Reglas:
 *   1. Longitud ≥ 8 (un DNI/NIF español tiene 9, los cubre).
 *   2. Al menos una letra y un dígito.
 *   3. No puede contener el username, ni el nombre, ni los apellidos (tokens
 *      de ≥ 3 caracteres). Comparación case-insensitive.
 *   4. No puede ser una de las contraseñas más comunes (`12345678`, `password`…).
 *
 * Pensada para acompañar a usuarios "operarios" cuya contraseña inicial suele
 * ser su DNI. El DNI pasa (longitud + mezcla letra/número), pero rechaza
 * basura ("1234", "pepe", "qwerty").
 */
class PasswordPolicy implements ValidationRule
{
    /**
     * Contraseñas demasiado comunes — recorte de listas públicas top-1000.
     * Comparación case-insensitive contra la contraseña en bruto.
     *
     * @var array<int, string>
     */
    private const COMUNES = [
        '12345678', '123456789', '1234567890', '00000000', '11111111',
        'password', 'password1', 'passw0rd', 'qwerty', 'qwerty123',
        'qwertyuiop', 'abcdefgh', 'iloveyou', 'admin123', 'administrador',
        'usuario12', 'welcome1', 'letmein1', 'football', 'baseball',
        'sunshine', 'princess', 'starwars', 'master12', 'login123',
    ];

    /**
     * @param  array<int, string|null>  $datosPersonales  Tokens que la contraseña NO puede contener
     *                                                   (típicamente: username, nombre, apellidos).
     */
    public function __construct(private readonly array $datosPersonales = [])
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return; // Otra regla ('required'/'nullable') decide qué hacer con vacío.
        }

        // 1) Longitud
        if (mb_strlen($value) < 8) {
            $fail('La contraseña debe tener al menos 8 caracteres.');
            return;
        }

        // 2) Letra + dígito
        if (! preg_match('/\pL/u', $value)) {
            $fail('La contraseña debe contener al menos una letra.');
            return;
        }
        if (! preg_match('/\d/', $value)) {
            $fail('La contraseña debe contener al menos un número.');
            return;
        }

        // 4) Lista negra (antes que la 3 para mensaje más específico).
        $valorLower = mb_strtolower($value);
        if (in_array($valorLower, self::COMUNES, true)) {
            $fail('Esa contraseña es demasiado común. Elige otra.');
            return;
        }

        // 3) No contener datos personales (tokens ≥ 3 caracteres).
        foreach ($this->datosPersonales as $dato) {
            if ($dato === null) {
                continue;
            }
            // Partir por espacios y guiones para que apellidos compuestos
            // ("García López") generen 2 tokens, no uno solo difícil de chocar.
            $tokens = preg_split('/[\s\-_.]+/u', trim((string) $dato)) ?: [];
            foreach ($tokens as $token) {
                if (mb_strlen($token) < 3) {
                    continue;
                }
                if (str_contains($valorLower, mb_strtolower($token))) {
                    $fail('La contraseña no puede contener tu usuario, nombre o apellidos.');
                    return;
                }
            }
        }
    }
}
