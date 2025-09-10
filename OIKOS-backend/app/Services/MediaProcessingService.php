<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaProcessingService
{
    /**
     * Cancella i file associati a un singolo media (se esistono).
     */
    public function deleteMediaFiles(ProjectMedia $media, string $disk = 'public'): void
    {
        try {
            $paths = array_filter([
                $media->path,
                $media->thumbnail_path,
                $media->compressed_path,
            ]);

            foreach ($paths as $p) {
                if ($p && Storage::disk($disk)->exists($p)) {
                    Storage::disk($disk)->delete($p);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Errore cancellando file media', [
                'media_id' => $media->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cancella tutti i file di una collezione di media.
     *
     * @param iterable<ProjectMedia> $mediaCollection
     */
    public function deleteMediaCollection(iterable $mediaCollection, string $disk = 'public'): void
    {
        foreach ($mediaCollection as $m) {
            $this->deleteMediaFiles($m, $disk);
        }
    }

    /**
     * Salva più file in un colpo solo in modo atomico.
     *
     * @param Project $project
     * @param array<array{
     *     file: UploadedFile,
     *     type?: 'image'|'video',
     *     alt?: string|null,
     *     description?: string|null,
     *     is_featured?: bool,
     *     sort_order?: int
     * }> $items
     * @param string $disk
     * @return array<ProjectMedia>
     *
     * Esempio $items:
     * [
     *   ['file'=>$request->file('file1'),'type'=>'image','alt'=>'Frontale','sort_order'=>1],
     *   ['file'=>$request->file('file2'),'type'=>'video','description'=>'Walkthrough']
     * ]
     */
    public function processMultipleFiles(Project $project, array $items, string $disk = 'public'): array
    {
        $created = [];
        $storedPaths = []; // per rollback file in caso di eccezione

        DB::beginTransaction();

        try {
            foreach ($items as $payload) {
                /** @var UploadedFile|null $file */
                $file = Arr::get($payload, 'file');
                if (! $file instanceof UploadedFile) {
                    throw new \InvalidArgumentException('Elemento senza file valido in processMultipleFiles.');
                }

                $type        = Arr::get($payload, 'type', 'image'); // default image
                $alt         = Arr::get($payload, 'alt');
                $description = Arr::get($payload, 'description');
                $isFeatured  = (bool) Arr::get($payload, 'is_featured', false);
                $sortOrder   = (int)  Arr::get($payload, 'sort_order', 0);

                // Salva il singolo file + record DB
                [$media, $paths] = $this->storeSingleMedia(
                    project: $project,
                    file: $file,
                    type: $type,
                    attributes: [
                        'alt_text'    => $alt,
                        'description' => $description,
                        'is_featured' => $isFeatured,
                        'sort_order'  => $sortOrder,
                    ],
                    disk: $disk
                );

                $created[] = $media;
                $storedPaths = array_merge($storedPaths, $paths);
            }

            DB::commit();
            return $created;
        } catch (\Throwable $e) {
            DB::rollBack();

            // rollback file già salvati su disco
            foreach ($storedPaths as $p) {
                if ($p && Storage::disk($disk)->exists($p)) {
                    Storage::disk($disk)->delete($p);
                }
            }

            Log::error('processMultipleFiles: rollback per errore', [
                'project_id' => $project->id,
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Salva un singolo file + crea il record ProjectMedia.
     *
     * @param Project $project
     * @param UploadedFile $file
     * @param 'image'|'video' $type
     * @param array $attributes  attributi extra: alt_text, description, is_featured, sort_order
     * @param string $disk
     * @return array{0: ProjectMedia, 1: array<int, string>}  [media_creato, paths_salvati_per_eventuale_rollback]
     */
    protected function storeSingleMedia(
        Project $project,
        UploadedFile $file,
        string $type,
        array $attributes = [],
        string $disk = 'public'
    ): array {
        // cartelle coerenti con la tua struttura
        $folder = $type === 'video'
            ? 'projects/videos/original'
            : 'projects/images';

        // Dimensioni immagine (se disponibile senza dipendenze extra)
        $width = null;
        $height = null;
        if ($type === 'image') {
            try {
                [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
            } catch (\Throwable) {
                // ignora, non blocchiamo il flow
            }
        }

        // Salvataggio file con nome hash
        $storedPath = $file->store($folder, $disk);
        $filename   = basename($storedPath);

        $media = ProjectMedia::create([
            'project_id'     => $project->id,
            'type'           => $type,
            'filename'       => $filename,
            'original_name'  => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
            'path'           => $storedPath,
            'thumbnail_path' => null,   // generala in un job se vuoi
            'compressed_path' => null,   // per video, generala via FFmpeg in coda
            'width'          => $width,
            'height'         => $height,
            'duration'       => null,   // calcolala in un job se video
            'alt_text'       => Arr::get($attributes, 'alt_text'),
            'description'    => Arr::get($attributes, 'description'),
            'is_featured'    => (bool) Arr::get($attributes, 'is_featured', false),
            'sort_order'     => (int)  Arr::get($attributes, 'sort_order', 0),
        ]);

        return [$media, array_filter([$storedPath])];
    }
}
