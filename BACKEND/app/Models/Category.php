<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_kategori', 'deskripsi'])]
class Category extends Model
{
    use HasFactory;

    /**
     * Get all submissions in this category.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
