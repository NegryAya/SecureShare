<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Affiche la liste des fichiers de l'utilisateur connecte ("Mes fichiers").
     */
    public function index(Request $request): View
    {
        $files = $request->user()
            ->files()
            ->latest()
            ->paginate(10);

        return view('files.index', compact('files'));
    }

    /**
     * Affiche le formulaire d'upload.
     */
    public function create(): View
    {
        return view('files.upload');
    }

    /**
     * Traite l'upload d'un fichier :
     * - validation (extension/MIME/taille) via UploadFileRequest
     * - renommage en UUID (le nom stocke n'est jamais devinable)
     * - stockage sur le disque prive "local"
     * - enregistrement des metadonnees en base
     * - journalisation de l'action "upload"
     */
    public function store(UploadFileRequest $request): RedirectResponse
    {
        $uploaded = $request->file('file');

        $extension = strtolower($uploaded->getClientOriginalExtension());
        // Str::uuid() genere un identifiant aleatoire (UUID v4) impossible
        // a deviner : le nom physique du fichier sur le disque n'a donc
        // aucun rapport avec son nom d'origine.
        $storedName = Str::uuid()->toString().'.'.$extension;

        // Rangement dans un sous-dossier par utilisateur : files/{user_id}/{uuid}.ext
        $directory = 'files/'.$request->user()->id;
        $path = $uploaded->storeAs($directory, $storedName, File::DISK);

        $file = $request->user()->files()->create([
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_name' => $storedName,
            'extension' => $extension,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
            'storage_path' => $path,
        ]);

        Log::record(Log::ACTION_UPLOAD, $request->user()->id);

        return redirect()->route('files.index')
            ->with('status', "Le fichier « {$file->original_name} » a ete uploade avec succes.");
    }

    /**
     * Telecharge un fichier. Seul le proprietaire du fichier peut le faire
     * (FilePolicy::view), sinon une reponse 403 est renvoyee automatiquement.
     */
    public function download(Request $request, File $file): StreamedResponse
    {
        $this->authorize('view', $file);

        Log::record(Log::ACTION_DOWNLOAD, $request->user()->id);

        return Storage::disk(File::DISK)->download($file->storage_path, $file->original_name);
    }

    /**
     * Supprime un fichier : retire la ligne en base ET le fichier physique
     * du disque de stockage. Les liens de partage associes sont supprimes
     * automatiquement (contrainte "cascadeOnDelete" de la migration).
     */
    public function destroy(Request $request, File $file): RedirectResponse
    {
        $this->authorize('delete', $file);

        Storage::disk(File::DISK)->delete($file->storage_path);

        $name = $file->original_name;
        $file->delete();

        Log::record(Log::ACTION_DELETE, $request->user()->id);

        return redirect()->route('files.index')
            ->with('status', "Le fichier « {$name} » a ete supprime.");
    }
}
