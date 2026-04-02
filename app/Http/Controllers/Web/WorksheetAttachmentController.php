<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorksheetAttachmentController extends Controller
{
    private array $allowedMimes = [
        'image/jpeg', 'image/png', 'image/webp', 'application/pdf'
    ];

    /**
     * Upload one or more files to a worksheet.
     */
    public function upload(Request $request, Worksheet $worksheet)
    {
        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:20480', // 20MB
        ]);

        $attachments = $worksheet->attachments ?? [];

        foreach ($request->file('files') as $file) {
            $dir      = "worksheets/{$worksheet->id}";
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs($dir, $filename, 'public');

            $attachments[] = [
                'uuid'      => Str::uuid(),
                'filename'  => $file->getClientOriginalName(),
                'path'      => $path,
                'mime'      => $file->getMimeType(),
                'size'      => $file->getSize(),
                'url'       => Storage::url($path),
                'is_primary'=> empty($attachments) && $file->getMimeType() === 'application/pdf',
                'uploaded_at' => now()->toISOString(),
            ];
        }

        $worksheet->update(['attachments' => $attachments]);

        return response()->json([
            'success'     => true,
            'attachments' => $attachments,
        ]);
    }

    /**
     * Delete an attachment by its path.
     */
    public function delete(Request $request, Worksheet $worksheet)
    {
        $request->validate(['path' => 'required|string']);

        $attachments = collect($worksheet->attachments ?? [])
            ->filter(fn($a) => $a['path'] !== $request->path)
            ->values()
            ->toArray();

        // Delete physical file
        Storage::disk('public')->delete($request->path);

        $worksheet->update(['attachments' => $attachments]);

        return response()->json(['success' => true, 'attachments' => $attachments]);
    }

    /**
     * Set an attachment as the primary PDF for OCR.
     */
    public function setPrimary(Request $request, Worksheet $worksheet)
    {
        $request->validate(['path' => 'required|string']);

        $attachments = collect($worksheet->attachments ?? [])->map(function ($a) use ($request) {
            $a['is_primary'] = ($a['path'] === $request->path);
            return $a;
        })->toArray();

        $worksheet->update(['attachments' => $attachments]);

        return response()->json(['success' => true]);
    }

    /**
     * Run OCR on the primary PDF and return extracted data.
     */
    public function ocr(Worksheet $worksheet)
    {
        $primary = collect($worksheet->attachments ?? [])->firstWhere('is_primary', true);

        if (!$primary) {
            return response()->json(['error' => 'No primary PDF set.'], 422);
        }

        $fullPath = Storage::disk('public')->path($primary['path']);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        try {
            $text = $this->extractText($fullPath, $primary['mime']);
            $data = $this->parseWorksheetText($text);

            return response()->json([
                'success'   => true,
                'raw_text'  => $text,
                'extracted' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Text extraction ───────────────────────────────────────────────

    private function extractText(string $filePath, string $mime): string
    {
        if ($mime === 'application/pdf') {
            return $this->extractFromPdf($filePath);
        }
        return $this->extractFromImage($filePath);
    }

    private function extractFromPdf(string $pdfPath): string
    {
        // Convert first pages to images then OCR each
        $tmpDir  = sys_get_temp_dir() . '/ws_ocr_' . uniqid();
        mkdir($tmpDir, 0777, true);

        try {
            // Try pdftotext first (fast, works for digital PDFs)
            $text = shell_exec("pdftotext -layout " . escapeshellarg($pdfPath) . " -");
            if ($text && strlen(trim($text)) > 20) {
                return $text;
            }

            // Fallback: convert to images with Ghostscript and OCR
            $imgBase = $tmpDir . '/page';
            shell_exec("gs -dNOPAUSE -dBATCH -sDEVICE=jpeg -r200 -sOutputFile={$imgBase}_%d.jpg " . escapeshellarg($pdfPath) . " 2>/dev/null");

            $allText = '';
            foreach (glob($tmpDir . '/page_*.jpg') as $imgFile) {
                $allText .= $this->extractFromImage($imgFile) . "\n";
            }

            return $allText ?: 'No text extracted.';
        } finally {
            // Cleanup temp files
            foreach (glob($tmpDir . '/*') as $f) @unlink($f);
            @rmdir($tmpDir);
        }
    }

    private function extractFromImage(string $imagePath): string
    {
        // Try thiagoalessio/tesseract_ocr if available
        if (class_exists('\TesseractOCR')) {
            return (new \TesseractOCR($imagePath))
                ->lang('eng')
                ->run();
        }

        // Fallback: direct tesseract CLI
        $tmpOut = sys_get_temp_dir() . '/tesseract_' . uniqid();
        shell_exec("tesseract " . escapeshellarg($imagePath) . " " . escapeshellarg($tmpOut) . " -l eng 2>/dev/null");
        $text = @file_get_contents($tmpOut . '.txt') ?: '';
        @unlink($tmpOut . '.txt');
        return $text;
    }

    // ── Data parsing ──────────────────────────────────────────────────

    private function parseWorksheetText(string $text): array
    {
        $lines      = array_map('trim', explode("\n", $text));
        $containers = [];
        $date       = null;
        $client     = null;
        $site       = null;

        // Date patterns: dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd
        foreach ($lines as $line) {
            if (!$date) {
                if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $line, $m)) {
                    $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                    $year  = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
                    $date  = "{$year}-{$month}-{$day}";
                }
            }
        }

        // Container numbers: standard format XXXX 0000000 or XXXX0000000
        foreach ($lines as $line) {
            // Match container number pattern: 4 letters + 7 digits
            if (preg_match_all('/\b([A-Z]{4})\s*(\d{7})\b/', strtoupper($line), $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $num = $match[1] . ' ' . $match[2];
                    if (!in_array($num, array_column($containers, 'container_number'))) {
                        // Try to extract boxes count from same line
                        $boxes  = null;
                        $skills = null;
                        if (preg_match('/(\d{3,5})\s*(?:boxes?|caixas?|ctns?)/i', $line, $bm)) {
                            $boxes = (int) $bm[1];
                        }
                        if (preg_match('/(\d{1,3})\s*(?:skills?|hab)/i', $line, $sm)) {
                            $skills = (int) $sm[1];
                        }

                        $containers[] = [
                            'container_number' => $num,
                            'feet'             => $this->detectFeet($line),
                            'boxes_count'      => $boxes,
                            'skills_count'     => $skills,
                        ];
                    }
                }
            }
        }

        // Client and site: look for common keywords
        foreach ($lines as $line) {
            if (!$client && preg_match('/(?:client|cliente)[:\s]+(.+)/i', $line, $m)) {
                $client = trim($m[1]);
            }
            if (!$site && preg_match('/(?:site|local|warehouse|depot)[:\s]+(.+)/i', $line, $m)) {
                $site = trim($m[1]);
            }
        }

        return [
            'date'            => $date,
            'client'          => $client,
            'site'            => $site,
            'containers'      => $containers,
            'container_count' => count($containers),
            'raw_lines'       => count($lines),
        ];
    }

    private function detectFeet(string $line): string
    {
        if (preg_match('/40\s*ft|40\s*feet|40hc|40\'/i', $line)) return '40';
        if (preg_match('/20\s*ft|20\s*feet|20\'/i', $line)) return '20';
        return '40'; // default
    }
}
