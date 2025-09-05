<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'type',
        'filename',
        'original_name',
        'mime_type',
        'file_size',
        'path',
        'thumbnail_path',
        'compressed_path',
        'width',
        'height',
        'duration',
        'alt_text',
        'description',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        return $this->type === 'image' ? $this->url : asset('images/video-placeholder.jpg');
    }

    public function getCompressedUrlAttribute()
    {
        return $this->compressed_path ? asset('storage/' . $this->compressed_path) : null;
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) return null;

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    // Methods
    public function isImage()
    {
        return $this->type === 'image';
    }

    public function isVideo()
    {
        return $this->type === 'video';
    }
}
