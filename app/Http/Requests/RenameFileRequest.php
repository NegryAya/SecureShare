<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide le nouveau nom saisi lors du renommage d'un fichier.
 *
 * Seul le nom "visible" (original_name, sans l'extension) est modifiable :
 * l'extension physique du fichier reste liee a son contenu reel et n'est
 * jamais modifiable par ce formulaire (voir FileController::rename).
 */
class RenameFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation reelle (le fichier appartient-il a l'utilisateur ?)
        // est verifiee dans le controleur via $this->authorize('update', $file).
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:245',
                // Interdit les caracteres de chemin (securite : evite toute
                // tentative de path traversal via le nom affiche).
                'regex:/^[^\/\\\\]+$/',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nouveau nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas depasser 245 caracteres.',
            'name.regex' => 'Le nom ne peut pas contenir de "/" ou "\\".',
        ];
    }
}
