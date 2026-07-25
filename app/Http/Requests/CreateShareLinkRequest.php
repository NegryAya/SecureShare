<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide les options choisies par l'utilisateur lors de la creation
 * d'un lien de partage : mot de passe optionnel et duree d'expiration.
 */
class CreateShareLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation reelle (le fichier appartient-il a l'utilisateur ?)
        // est verifiee dans le controleur via $this->authorize('share', $file),
        // qui s'appuie sur FilePolicy.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Mot de passe optionnel : si fourni, au moins 4 caracteres.
            'password' => ['nullable', 'string', 'min:4', 'max:255'],

            // Duree d'expiration : aucune, 24 heures ou 7 jours.
            'expires_in' => ['required', 'in:none,24h,7d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.min' => 'Le mot de passe doit contenir au moins 4 caracteres.',
            'expires_in.required' => "Veuillez choisir une duree d'expiration.",
            'expires_in.in' => "Duree d'expiration invalide.",
        ];
    }
}
