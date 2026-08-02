<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenameFileRequest;
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
     *
     * Sprint 3 : ajoute la recherche par nom, le filtrage par type
     * (extension) et le tri par date, sans changer le comportement de
     * base (pagination, colonnes affichees) developpe au Sprint 2.
     */
    public function index(Request $request): View
    {
        $query = $request->user()->files();

        // Recherche par nom de fichier (insensible a la casse).
        if ($search = trim((string) $request->query('search'))) {
            $query->where('original_name', 'like', '%'.$search.'%');
        }

        // Filtrage par type de fichier (extension).
        if ($type = $request->query('type')) {
            $query->where('extension', $type);
        }

        // Tri : date d'ajout (par defaut, plus recent d'abord), ou nom.
        $sort = $request->query('sort', 'date_desc');
        match ($sort) {
            'date_asc' => $query->oldest(),
            'name_asc' => $query->orderBy('original_name'),
            'name_desc' => $query->orderBy('original_name', 'desc'),
            'size_desc' => $query->orderBy('size', 'desc'),
            default => $query->latest(),
        };

        $files = $query->paginate(10)->withQueryString();

        // Types distincts possedes par l'utilisateur, pour peupler le
        // filtre "Type" sans lister des extensions qu'il n'a jamais uploadees.
        $availableTypes = $request->user()->files()
            ->select('extension')
            ->distinct()
            ->orderBy('extension')
            ->pluck('extension');

        return view('files.index', [
            'files' => $files,
            'availableTypes' => $availableTypes,
            'filters' => [
                'search' => $search ?? '',
                'type' => $type ?? '',
                'sort' => $sort,
            ],
        ]);
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

    /**
     * Renomme un fichier (modifie uniquement le nom affiche, jamais le
     * fichier physique ni son extension reelle).
     */
    public function rename(RenameFileRequest $request, File $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $oldName = $file->original_name;

        // On conserve l'extension d'origine (celle du contenu reel du
        // fichier) meme si l'utilisateur ne la saisit pas explicitement.
        $newName = trim($request->validated('name'));
        if (! str_ends_with(strtolower($newName), '.'.strtolower($file->extension))) {
            $newName .= '.'.$file->extension;
        }

        $file->update(['original_name' => $newName]);

        Log::record(Log::ACTION_RENAME, $request->user()->id);

        return redirect()->route('files.index')
            ->with('status', "Le fichier « {$oldName} » a ete renomme en « {$newName} ».");
    }

    /**
     * Remplace le contenu d'un fichier existant tout en conservant le
     * meme enregistrement (meme ID) : les liens de partage deja crees
     * pour ce fichier restent donc valides et pointent vers le nouveau
     * contenu, sans que l'utilisateur ait besoin de les regenerer.
     */
    public function replace(UploadFileRequest $request, File $file): RedirectResponse
    {
        $this->authorize('update', $file);

        $uploaded = $request->file('file');
        $extension = strtolower($uploaded->getClientOriginalExtension());
        $storedName = Str::uuid()->toString().'.'.$extension;
        $directory = 'files/'.$request->user()->id;

        $newPath = $uploaded->storeAs($directory, $storedName, File::DISK);

        // On supprime l'ancien fichier physique seulement une fois le
        // nouveau bien enregistre, pour ne jamais se retrouver sans
        // fichier valide en cas d'erreur pendant l'upload.
        Storage::disk(File::DISK)->delete($file->storage_path);

        $file->update([
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_name' => $storedName,
            'extension' => $extension,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
            'storage_path' => $newPath,
        ]);

        Log::record(Log::ACTION_REPLACE, $request->user()->id);

        return redirect()->route('files.index')
            ->with('status', "Le fichier « {$file->original_name} » a ete remplace avec succes.");
    }
}
