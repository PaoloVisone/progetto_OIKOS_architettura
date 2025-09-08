<?php

namespace App\Services;

use App\Models\ProjectMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Gestisce la rimozione sicura dei file media (immagini/video)
 * collegati a un record ProjectMedia.
 */
class MediaProcessingService
{
    /**
     * Cancella i file associati a un singolo media (se esistono).
     * Non lancia eccezioni se i file non ci sono: semplicemente logga e prosegue.
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
            // Non blocchiamo il flusso di cancellazione del progetto:
            // log e si continua.
            Log::warning('Errore cancellando file media', [
                'media_id' => $media->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * (Opzionale) Cancella tutti i file di una collezione di media.
     * Utile se vuoi richiamarla direttamente con $project->media.
     *
     * @param iterable<ProjectMedia> $mediaCollection
     */
    public function deleteMediaCollection(iterable $mediaCollection, string $disk = 'public'): void
    {
        foreach ($mediaCollection as $m) {
            $this->deleteMediaFiles($m, $disk);
        }
    }
}
