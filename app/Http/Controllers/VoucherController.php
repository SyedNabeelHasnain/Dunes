<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VoucherController extends Controller
{
    /**
     * Display the web-based luxury voucher / boarding pass.
     */
    public function show(string $reference)
    {
        $booking = Booking::where('reference', $reference)
            ->with(['addons', 'tier'])
            ->firstOrFail();

        $verificationUrl = route('booking.voucher', $booking->reference);
        try {
            $qrSvg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->margin(1)->generate($verificationUrl);
            $qrCodeUrl = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        } catch (\Throwable $e) {
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=4&data=' . urlencode($verificationUrl);
        }

        return view('booking.voucher', compact('booking', 'qrCodeUrl'));
    }

    /**
     * Generate and stream the official A4 PDF Ticket Voucher.
     */
    public function downloadPdf(string $reference)
    {
        $booking = Booking::where('reference', $reference)
            ->with(['addons', 'tier'])
            ->firstOrFail();

        $verificationUrl = route('booking.voucher', $booking->reference);
        try {
            $qrSvg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->margin(1)->generate($verificationUrl);
            $qrCodeUrl = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        } catch (\Throwable $e) {
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=4&data=' . urlencode($verificationUrl);
        }

        $pdf = Pdf::loadView('booking.ticket-pdf', compact('booking', 'qrCodeUrl'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'dpi' => 120,
                'defaultFont' => 'sans-serif'
            ]);

        $fileName = 'Dunes-Discovery-Voucher-' . $booking->reference . '.pdf';

        return $pdf->download($fileName);
    }
}
