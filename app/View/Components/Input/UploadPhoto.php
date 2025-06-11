<?php

namespace App\View\Components\Input;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UploadPhoto extends Component
{
    public $label;

    public $id;

    public $name;

    public $dragText;

    public $fileNameId;

    public $filePreviewId;

    public $currentImageUrl;


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


    public function render(): View|Closure|string
    {
        return view('components.input.upload-photo');
    }
}
