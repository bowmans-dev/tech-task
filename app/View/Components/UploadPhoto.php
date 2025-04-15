<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UploadPhoto extends Component
{
    /**
     * Properties for the component.
     */
    public $label;

    public $id;

    public $name;

    public $dragText;

    public $fileNameId;

    public $filePreviewId;

    public $currentImageUrl; // Add this property

    /**
     * Create a new component instance.
     */
    public function __construct(
        $label = null,
        $id = null,
        $name = null,
        $dragText = null,
        $fileNameId = null,
        $filePreviewId = null,
        $currentImageUrl = null // Accept the current image URL
    ) {
        $this->label = $label ?? 'Profile Picture';
        $this->id = $id ?? 'file-upload';
        $this->name = $name ?? 'file-upload';
        $this->dragText = $dragText ?? 'or drag and drop';
        $this->fileNameId = $fileNameId ?? 'file-name';
        $this->filePreviewId = $filePreviewId ?? 'file-preview';
        $this->currentImageUrl = $currentImageUrl; // Assign the current image URL
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.upload-photo');
    }
}
