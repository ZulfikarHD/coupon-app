<?php

namespace App\Exports;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CouponExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected Carbon $dateFrom;
    protected Carbon $dateTo;

    public function __construct(Carbon $dateFrom, Carbon $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * Get the collection of coupons to export.
     */
    public function collection(): Collection
    {
        return Coupon::with(['user', 'validations.validator'])
            ->whereBetween('created_at', [$this->dateFrom->startOfDay(), $this->dateTo->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            'Kode Kupon',
            'Nama Pelanggan',
            'Nomor Telepon',
            'Email Pelanggan',
            'Media Sosial',
            'Tipe Kupon',
            'Deskripsi',
            'Status',
            'Tanggal Dibuat',
            'Tanggal Kedaluwarsa',
            'Tanggal Validasi',
            'Divalidasi Oleh',
        ];
    }

    /**
     * Map each coupon to a row in the export.
     */
    public function map($coupon): array
    {
        // Get the latest "used" validation if exists
        $latestValidation = $coupon->validations
            ->where('action', 'used')
            ->sortByDesc('validated_at')
            ->first();

        return [
            $coupon->code,
            $coupon->customer_name,
            $coupon->formatted_phone ?? $coupon->customer_phone,
            $coupon->customer_email ?? '-',
            $coupon->customer_social_media ?? '-',
            $coupon->type,
            $coupon->description,
            $this->getStatusLabel($coupon->status),
            $coupon->created_at->format('Y-m-d H:i:s'),
            $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '-',
            $latestValidation ? $latestValidation->validated_at->format('Y-m-d H:i:s') : '-',
            $latestValidation && $latestValidation->validator ? $latestValidation->validator->name : '-',
        ];
    }

    /**
     * Set column widths for better readability.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Kode Kupon
            'B' => 20, // Nama Pelanggan
            'C' => 18, // Nomor Telepon
            'D' => 25, // Email Pelanggan
            'E' => 20, // Media Sosial
            'F' => 20, // Tipe Kupon
            'G' => 40, // Deskripsi
            'H' => 12, // Status
            'I' => 20, // Tanggal Dibuat
            'J' => 20, // Tanggal Kedaluwarsa
            'K' => 20, // Tanggal Validasi
            'L' => 20, // Divalidasi Oleh
        ];
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Get Indonesian status label.
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            Coupon::STATUS_ACTIVE => 'Aktif',
            Coupon::STATUS_USED => 'Terpakai',
            Coupon::STATUS_EXPIRED => 'Kedaluwarsa',
            default => $status,
        };
    }
}
