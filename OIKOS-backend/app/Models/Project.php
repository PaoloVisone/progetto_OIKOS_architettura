<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'long_description',
        'client',
        'location',
        'project_date',
        'area',
        'status',
        'is_featured',
        'sort_order',
        'category_id',
        'tags',
        'featured_image',
    ];

    protected $casts = [
        'project_date' => 'date',
        'is_featured' => 'boolean',
        'area' => 'decimal:2',
        'tags' => 'array',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('sort_order');
    }

    public function images()
    {
        return $this->hasMany(ProjectMedia::class)->where('type', 'image')->orderBy('sort_order');
    }

    public function videos()
    {
        return $this->hasMany(ProjectMedia::class)->where('type', 'video')->orderBy('sort_order');
    }

    public function featuredMedia()
    {
        return $this->hasOne(ProjectMedia::class)->where('is_featured', true);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('project_date', 'desc');
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Accessors
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }

        $featuredMedia = $this->featuredMedia;
        if ($featuredMedia) {
            return $featuredMedia->url;
        }

        $firstImage = $this->images()->first();
        return $firstImage ? $firstImage->url : asset('images/project-placeholder.jpg');
    }

    public function getExcerptAttribute()
    {
        return Str::limit($this->description, 150);
    }

    public function getFormattedDateAttribute()
    {
        return $this->project_date ? $this->project_date->format('F Y') : null;
    }
}
