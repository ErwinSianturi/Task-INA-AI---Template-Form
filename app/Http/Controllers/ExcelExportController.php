<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Models\TravelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcelExportController extends Controller
{
    public function exportTravelRequests(Request $request)
    {
        $user = Auth::user();
        if (!$user->isRole('finance') && !$user->isRole('admin')) {
            abort(403, 'Unauthorized action for Excel export.');
        }

        $query = TravelRequest::with(['user', 'manager']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $travelRequests = $query->latest()->get();

        $filename = 'TRF_Export_' . date('Y-m-d_H-i-s') . '.xls';

        $output = '
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                th { background-color: #1A2A3A; color: #FFFFFF; font-weight: bold; border: 1px solid #000; text-align: center; }
                td { border: 1px solid #CCC; vertical-align: middle; }
                .title { font-size: 16pt; font-weight: bold; color: #1A2A3A; text-align: center; }
                .meta { font-size: 9pt; color: #555; }
                .text { mso-number-format:"\@"; }
                .date { mso-number-format:"yyyy-mm-dd"; }
                .badge-approved { background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: center; }
                .badge-pending { background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; }
                .badge-rejected { background-color: #FEE2E2; color: #991B1B; font-weight: bold; text-align: center; }
            </style>
        </head>
        <body>
            <table>
                <tr><td colspan="9" class="title">TRAVEL REIMBURSEMENT FORM (TRF) REPORT</td></tr>
                <tr><td colspan="9" class="meta">Company: PT Teknologi Cerdas Berdaulat Indonesia | Exported on: ' . date('d F Y H:i:s') . '</td></tr>
                <tr><td colspan="9"></td></tr>
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 130px;">Request Number</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 120px;">Category</th>
                        <th style="width: 250px;">Company</th>
                        <th style="width: 150px;">Employee</th>
                        <th style="width: 150px;">Manager Approver</th>
                        <th style="width: 110px;">Signed Date</th>
                        <th style="width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($travelRequests as $index => $tr) {
            $statusClass = $tr->status === 'approved' ? 'badge-approved' : ($tr->status === 'rejected' ? 'badge-rejected' : 'badge-pending');
            $statusLabel = strtoupper(str_replace('_', ' ', $tr->status));

            $output .= '
                    <tr>
                        <td style="text-align: center;">' . ($index + 1) . '</td>
                        <td class="text" style="font-weight: bold;">' . htmlspecialchars($tr->request_number) . '</td>
                        <td class="date">' . $tr->date->format('Y-m-d') . '</td>
                        <td>' . htmlspecialchars($tr->category) . '</td>
                        <td>' . htmlspecialchars($tr->company) . '</td>
                        <td>' . htmlspecialchars($tr->user->name) . '</td>
                        <td>' . htmlspecialchars($tr->manager->name ?? '-') . '</td>
                        <td class="date">' . ($tr->signed_date ? $tr->signed_date->format('Y-m-d') : '-') . '</td>
                        <td class="' . $statusClass . '">' . $statusLabel . '</td>
                    </tr>';
        }

        $output .= '
                </tbody>
            </table>
        </body>
        </html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportReimbursements(Request $request)
    {
        $user = Auth::user();
        if (!$user->isRole('finance') && !$user->isRole('admin')) {
            abort(403, 'Unauthorized action for Excel export.');
        }

        $query = Reimbursement::with(['user', 'finance', 'reimbursedByUser', 'items', 'attachments']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reimbursement_status')) {
            $query->where('reimbursement_status', $request->reimbursement_status);
        }

        if ($request->filled('type')) {
            $query->where('reimbursement_type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $reimbursements = $query->latest()->get();

        $filename = 'CRF_Export_' . date('Y-m-d_H-i-s') . '.xls';

        $output = '
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                th { background-color: #1A2A3A; color: #FFFFFF; font-weight: bold; border: 1px solid #000; text-align: center; }
                td { border: 1px solid #CCC; vertical-align: middle; }
                .title { font-size: 16pt; font-weight: bold; color: #1A2A3A; text-align: center; }
                .meta { font-size: 9pt; color: #555; }
                .text { mso-number-format:"\@"; }
                .date { mso-number-format:"yyyy-mm-dd"; }
                .currency { mso-number-format:"\#\,\#\#0"; text-align: right; font-weight: bold; }
                .badge-approved { background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: center; }
                .badge-pending { background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; }
                .badge-rejected { background-color: #FEE2E2; color: #991B1B; font-weight: bold; text-align: center; }
                .badge-reimbursed { background-color: #DBEAFE; color: #1E40AF; font-weight: bold; text-align: center; }
                .badge-not-reimbursed { background-color: #F3F4F6; color: #4B5563; font-weight: bold; text-align: center; }
            </style>
        </head>
        <body>
            <table>
                <tr><td colspan="15" class="title">CASH REIMBURSEMENT FORM (CRF) REPORT</td></tr>
                <tr><td colspan="15" class="meta">Company: PT Teknologi Cerdas Berdaulat Indonesia | Exported on: ' . date('d F Y H:i:s') . '</td></tr>
                <tr><td colspan="15"></td></tr>
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 130px;">Request Number</th>
                        <th style="width: 100px;">Type</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 120px;">Category</th>
                        <th style="width: 250px;">Company</th>
                        <th style="width: 150px;">Employee</th>
                        <th style="width: 140px;">Transfer To</th>
                        <th style="width: 120px;">Total Amount (Rp)</th>
                        <th style="width: 120px;">Approval Status</th>
                        <th style="width: 150px;">Finance Approver</th>
                        <th style="width: 110px;">Signed Date</th>
                        <th style="width: 150px;">Reimbursement Status</th>
                        <th style="width: 110px;">Reimbursed Date</th>
                        <th style="width: 80px;">Receipts</th>
                    </tr>
                </thead>
                <tbody>';

        $totalSum = 0;
        foreach ($reimbursements as $index => $crf) {
            $totalSum += floatval($crf->total);
            $appStatusClass = in_array($crf->status, ['approved', 'verified']) ? 'badge-approved' : ($crf->status === 'rejected' ? 'badge-rejected' : 'badge-pending');
            $appStatusLabel = in_array($crf->status, ['approved', 'verified']) ? 'APPROVED' : strtoupper(str_replace('_', ' ', $crf->status));

            $reimbStatusClass = $crf->reimbursement_status === 'reimbursed' ? 'badge-reimbursed' : 'badge-not-reimbursed';
            $reimbStatusLabel = $crf->reimbursement_status === 'reimbursed' ? 'REIMBURSED' : 'NOT REIMBURSED';

            $output .= '
                    <tr>
                        <td style="text-align: center;">' . ($index + 1) . '</td>
                        <td class="text" style="font-weight: bold;">' . htmlspecialchars($crf->request_number) . '</td>
                        <td style="text-align: center;">' . strtoupper(str_replace('_', ' ', $crf->reimbursement_type)) . '</td>
                        <td class="date">' . $crf->date->format('Y-m-d') . '</td>
                        <td>' . htmlspecialchars($crf->category) . '</td>
                        <td>' . htmlspecialchars($crf->company) . '</td>
                        <td>' . htmlspecialchars($crf->user->name) . '</td>
                        <td>' . htmlspecialchars($crf->transfer_to) . '</td>
                        <td class="currency">' . number_format($crf->total, 0, ',', '.') . '</td>
                        <td class="' . $appStatusClass . '">' . $appStatusLabel . '</td>
                        <td>' . htmlspecialchars($crf->finance->name ?? '-') . '</td>
                        <td class="date">' . ($crf->signed_date ? $crf->signed_date->format('Y-m-d') : '-') . '</td>
                        <td class="' . $reimbStatusClass . '">' . $reimbStatusLabel . '</td>
                        <td class="date">' . ($crf->reimbursed_at ? $crf->reimbursed_at->format('Y-m-d') : '-') . '</td>
                        <td style="text-align: center;">' . $crf->attachments->count() . ' file(s)</td>
                    </tr>';
        }

        $output .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" style="font-weight: bold; text-align: right; background-color: #F8FAFC;">TOTAL SUMMARY:</td>
                        <td class="currency" style="background-color: #FEF8E7; font-size: 11pt;">' . number_format($totalSum, 0, ',', '.') . '</td>
                        <td colspan="6" style="background-color: #F8FAFC;"></td>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
