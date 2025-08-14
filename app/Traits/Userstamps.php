<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait Userstamps
{
    /**
     * Boot the userstamps trait for a model.
     *
     * @return void
     */
    protected static function bootUserstamps()
    {
        // Hook into the 'creating' event
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        // Hook into the 'updating' event
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get the user that created the model.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the model.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
