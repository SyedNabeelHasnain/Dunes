<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            $qrSvg = (string) QrCode::size(150)->margin(1)->generate($verificationUrl);
            $qrCodeUrl = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
        } catch (\Throwable $e) {
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=4&data='.urlencode($verificationUrl);
        }

        $pageRobots = 'noindex, nofollow';

        return view('booking.voucher', compact('booking', 'qrCodeUrl', 'pageRobots'));
    }

    /**
     * Generate and stream the official A4 PDF Ticket Voucher.
     */
    public function downloadPdf(string $reference)
    {
        try {
            $booking = Booking::where('reference', $reference)
                ->with(['addons', 'tier', 'tour'])
                ->firstOrFail();

            $verificationUrl = route('booking.voucher', $booking->reference);
            $qrSvg = null;
            $qrCodeUrl = null;

            try {
                $qrSvg = (string) QrCode::format('svg')->size(140)->margin(1)->generate($verificationUrl);
                $qrCodeUrl = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
            } catch (\Throwable $qrEx) {
                \Log::warning('QR generation failed for public voucher #'.$reference.': '.$qrEx->getMessage());
            }

            $pdf = Pdf::loadView('booking.ticket-pdf', compact('booking', 'qrCodeUrl', 'qrSvg'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 120,
                    'defaultFont' => 'sans-serif',
                ]);

            $fileName = 'Dunes-Discovery-Voucher-'.$booking->reference.'.pdf';

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            \Log::error('Public Voucher PDF generation failed: '.$e->getMessage(), [
                'reference' => $reference,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('booking.voucher', $reference)
                ->with('info', 'Viewing digital boarding pass voucher. You can print directly or save as PDF.');
        }
    }
}
