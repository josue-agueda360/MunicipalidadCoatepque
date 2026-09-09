<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class DeletionCode
{
    public static function validate(Request $request): void
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{8}$/'],
        ], [
            'code.required' => 'Escribe el código de seguridad.',
            'code.string' => 'El código de seguridad no es válido.',
            'code.regex' => 'El código debe contener 8 números.',
        ]);

        $configuredHash = (string) config(
            'security.document_delete_code_hash',
        );

        if (
            $configuredHash === ''
            || ! Hash::check($data['code'], $configuredHash)
        ) {
            throw ValidationException::withMessages([
                'code' => 'El código de seguridad es incorrecto.',
            ]);
        }
    }
}
