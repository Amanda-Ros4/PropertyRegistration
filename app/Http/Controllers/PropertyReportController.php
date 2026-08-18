<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Services\PropertyService;
use App\Support\AddressInput;
use App\Support\Digits;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PropertyReportController extends Controller
{
    private const FOOTER_FONT_SIZE = 11;

    public function __construct(private readonly PropertyService $propertyService) {}

    public function synthetic(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $idDigits = Digits::only($request->input('id'));
        $numberDigits = Digits::only($request->input('number'));
        $type = $request->input('type');
        $status = $request->input('status');

        $filters = [
            'id' => $idDigits !== '' ? $idDigits : null,
            'type' => is_string($type) && in_array($type, PropertyType::values(), true) ? $type : null,
            'street' => AddressInput::sanitize($request->input('street')),
            'number' => $numberDigits !== '' ? $numberDigits : null,
            'neighborhood' => AddressInput::sanitize($request->input('neighborhood')),
            'person_id' => $request->filled('person_id') ? (int) $request->input('person_id') : null,
            'status' => is_string($status) && in_array($status, PropertyStatus::values(), true) ? $status : null,
        ];

        $properties = $this->propertyService->allForReport($request->user(), $filters);

        $pdf = Pdf::loadView('reports.properties-synthetic', [
            'properties' => $properties,
        ])->setPaper('a4', 'landscape');

        $this->addPageNumbers($pdf);

        return $pdf->download('relatorio-sintetico-imoveis.pdf');
    }

    public function individual(Property $property): Response
    {
        $this->authorize('view', $property);

        $property->load([
            'person',
            'endorsements' => fn ($query) => $query->reorder()->orderBy('occurred_on')->orderBy('id'),
        ]);

        $pdf = Pdf::loadView('reports.properties-individual', [
            'property' => $property,
        ])->setPaper('a4', 'portrait');

        $this->addPageNumbers($pdf);

        return $pdf->download('relatorio-imovel-'.$property->id.'.pdf');
    }

    private function addPageNumbers(PdfDocument $pdf): void
    {
        $pdf->render();

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('Helvetica');

        $text = '-- {PAGE_NUM} '.__('reports.page_of').' {PAGE_COUNT} --';

        // page_text only substitutes the placeholders while writing each page, so
        // the width is measured against the widest value they can expand to.
        $widest = str_replace(
            ['{PAGE_NUM}', '{PAGE_COUNT}'],
            (string) $canvas->get_page_count(),
            $text
        );

        $canvas->page_text(
            ($canvas->get_width() - $fontMetrics->getTextWidth($widest, $font, self::FOOTER_FONT_SIZE)) / 2,
            $canvas->get_height() - 34,
            $text,
            $font,
            self::FOOTER_FONT_SIZE,
            [0.25, 0.25, 0.25],
        );
    }
}
