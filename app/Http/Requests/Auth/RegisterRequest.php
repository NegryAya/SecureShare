<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

/**
 * Valide les donnees soumises via le formulaire d'inscription (Register).
 *
 * Utiliser une Form Request dediee permet de garder le controleur leger
 * et de centraliser toutes les regles de validation a un seul endroit.
 */
class RegisterRequest extends FormRequest
{
    /**
     * N'importe qui peut soumettre une demande d'inscription.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regles de validation appliquees aux champs du formulaire.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Rules::password() impose par defaut une longueur minimale de 8
            // caracteres ; on renforce avec lettres/chiffres/symboles.
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Messages d'erreur personnalises (en francais).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prenom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => 'Cette adresse email est deja utilisee.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
