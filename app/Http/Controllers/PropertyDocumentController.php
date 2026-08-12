<?php

namespace App\Http\Controllers;

use App\Http\Requests\Properties\StorePropertyDocumentsRequest;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Services\PropertyDocumentService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyDocumentController extends Controller
{
    public function __construct(
        private readonly PropertyDocumentService $documentService,
    ) {}

    public function store(StorePropertyDocumentsRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $this->documentService->storeMany(
            $property,
            $this->uploadedFiles($request),
        );

        return redirect()->back()
            ->with('flash', Flash::success(__('properties.documents.uploaded')));
    }

    public function show(Property $property, PropertyDocument $document): StreamedResponse
    {
        $this->authorize('view', $property);
        abort_unless((int) $document->property_id === (int) $property->id, 404);

        $disk = Storage::disk($document->disk);

        abort_unless($disk->exists($document->path), 404);

        return $disk->response(
            $document->path,
            $document->original_name,
            ['Content-Type' => $document->mime_type],
        );
    }

    public function destroy(Property $property, PropertyDocument $document): RedirectResponse
    {
        $this->authorize('update', $property);
        abort_unless((int) $document->property_id === (int) $property->id, 404);

        $this->documentService->delete($document);

        return redirect()->back()
            ->with('flash', Flash::success(__('properties.documents.deleted')));
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function uploadedFiles(StorePropertyDocumentsRequest $request): array
    {
        $files = $request->file('documents', []);

        if ($files instanceof UploadedFile) {
            return [$files];
        }

        return is_array($files) ? $files : [];
    }
}
