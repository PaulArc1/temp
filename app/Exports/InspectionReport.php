<?php

namespace App\Exports;

use App\Models\Document;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InspectionReport implements FromView, WithDefaultStyles, WithStyles, WithDrawings, WithEvents, ShouldAutoSize
{
    use RegistersEventListeners;

    protected $document;

    protected $equipment;

    protected $equipmentType;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->equipmentType = EquipmentType::all()->pluck('name', 'id');
        $this->equipment = Equipment::all()->pluck('name', 'id');
    }

    public function columnWidths(): array
    {
        /*return [
            'A' => 10,
            'B' => 10,
            'C' => 15,
            'D' => 10,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 10,
        ];*/
    }

    public function drawings()
    {
        if (nova_get_setting('logo')) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('This is my logo');
            //$drawing->setPath(public_path('/img/logo.jpg'));
            $drawing->setPath(public_path('storage/'.nova_get_setting('logo')));
            $drawing->setHeight(50);
            $drawing->setCoordinates('A1');

            return [$drawing];
        } else {
            return [];
        }
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return [
            'font' => [
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'indent' => 1,
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => ['font' => ['size' => 14]],

            // Styling a specific cell by coordinates.
            '1' => ['font' => ['bold' => true, 'size' => 26, 'underline' => true]],
        ];
    }

    public function view(): View
    {
        return view('exports.inspection-report', [
            'document' => $this->document,
            'equipmentType' => $this->equipmentType,
            'equipment' => $this->equipment,
        ]);
    }

    public static function beforeExport(BeforeExport $event)
    {
    }

    public static function beforeWriting(BeforeWriting $event)
    {
        $event->getWriter()
            ->getDelegate()
            ->getActiveSheet()
            ->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(true);
    }

    public static function beforeSheet(BeforeSheet $event)
    {
    }

    public static function afterSheet(AfterSheet $event)
    {
    }
}
