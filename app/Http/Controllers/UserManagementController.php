<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->orderBy('email')
            ->get()
            ->map(fn (User $user): array => $this->payload($user))
            ->values();

        return view('user-management', ['users' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'not_regex:/\s/',
            ],
            'password_confirmation' => ['required', 'string'],
        ], [
            'name.required' => 'Escribe el nombre del usuario.',
            'name.max' => 'El nombre puede tener hasta 120 caracteres.',
            'email.required' => 'Escribe el correo electrónico.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'Escribe una contraseña.',
            'password.min' => 'La contraseña debe contener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede superar 128 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'Incluye mayúscula, minúscula, número y símbolo.',
            'password.not_regex' => 'La contraseña no debe contener espacios.',
            'password_confirmation.required' => 'Repite la contraseña.',
        ]);

        $user = DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            DB::table('password_histories')->insert([
                'user_id' => $user->id,
                'password_hash' => $user->password,
                'created_at' => now(),
            ]);

            return $user;
        });

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'user' => $this->payload($user),
        ], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $adminEmail = mb_strtolower((string) config('security.user_management_admin_email'));

        if (
            $user->is($request->user())
            || mb_strtolower($user->email) === $adminEmail
        ) {
            return response()->json([
                'message' => 'La cuenta del administrador principal no se puede eliminar.',
            ], 422);
        }

        DB::transaction(function () use ($request, $user): void {
            $administratorId = $request->user()->id;

            DB::table('project_folders')
                ->where('user_id', $user->id)
                ->update(['user_id' => $administratorId]);
            DB::table('projects')
                ->where('user_id', $user->id)
                ->update(['user_id' => $administratorId]);
            DB::table('project_monitorings')
                ->where('user_id', $user->id)
                ->update(['user_id' => $administratorId]);
            DB::table('project_financial_movements')
                ->where('recorded_by_user_id', $user->id)
                ->update(['recorded_by_user_id' => $administratorId]);
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            $user->delete();
        });

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }

    /** @return array{id: int, name: string, email: string, created_at: string, created_at_label: string, is_locked: bool, recovery_available: bool, is_admin: bool, delete_url: string|null} */
    private function payload(User $user): array
    {
        $isAdmin = mb_strtolower($user->email)
            === mb_strtolower((string) config('security.user_management_admin_email'));

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toIso8601String(),
            'created_at_label' => $user->created_at->format('d/m/Y'),
            'is_locked' => $user->locked_until?->isFuture() ?? false,
            'recovery_available' => true,
            'is_admin' => $isAdmin,
            'delete_url' => $isAdmin ? null : route('user-management.destroy', $user),
        ];
    }
}
