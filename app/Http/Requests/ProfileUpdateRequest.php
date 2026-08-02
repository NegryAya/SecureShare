<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valide les informations du profil (prenom, nom, email).
 * Le changement de mot de passe est gere separement (voir
 * ProfileController::updatePassword et sa validation inline), car il
 * s'agit d'une action distincte avec ses propres regles de securite.
 */
class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'utilisateur ne peut modifier que son propre profil : il n'y a
        // pas de parametre d'ID dans la route, $this->user() EST le
        // profil modifie.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // unique, sauf pour l'utilisateur courant lui-meme.
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prenom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => 'Cette adresse email est deja utilisee par un autre compte.',
        ];
    }
}
