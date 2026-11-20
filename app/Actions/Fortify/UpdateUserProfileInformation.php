<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'celular' => ['nullable', 'digits:8'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
        ])->validateWithBag('updateProfileInformation');

        $celular = $input['celular'] ?? null;

        $datosContacto = [
            'celular' => $celular,
            'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null,
        ];

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input, $datosContacto);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                ...$datosContacto,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input, array $datosContacto): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
            ...$datosContacto,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
