<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide le fichier envoye par le formulaire d'upload.
 *
 * Securite appliquee ici :
 * - "mimes:" verifie a la fois l'extension ET le type MIME reel du
 *   fichier (deduit de son contenu binaire, pas seulement de son nom),
 *   ce qui empeche un fichier malveillant de se faire passer pour un
 *   PDF/image en renommant simplement son extension.
 * - "max:20480" limite la taille a 20480 Ko = 20 Mo.
 */
class UploadFileRequest extends FormRequest
{
    /**
     * Extensions autorisees par le cahier des charges du Sprint 2.
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];

    /**
     * Taille maximale autorisee, en kilo-octets (20 Mo).
     */
    public const MAX_SIZE_KB = 20480;

    public function authorize(): bool
    {
        // Seul un utilisateur authentifie peut uploader (voir middleware
        // "auth" applique sur la route). Toujours vrai ici car la route
        // est deja protegee en amont.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:'.implode(',', self::ALLOWED_EXTENSIONS),
                'max:'.self::MAX_SIZE_KB,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Veuillez selectionner un fichier a uploader.',
            'file.file' => 'Le fichier envoye est invalide.',
            'file.mimes' => 'Type de fichier non autorise. Formats acceptes : PDF, DOCX, XLSX, JPG, PNG, ZIP.',
            'file.max' => 'Le fichier depasse la taille maximale autorisee (20 Mo).',
        ];
    }
}
