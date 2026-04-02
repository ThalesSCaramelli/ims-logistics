<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class OcrService
{
    /**
     * Process uploaded worksheet image(s) using Google Cloud Vision.
     * Returns structured data ready for worksheet form.
     * Multiple pages are merged into a single result.
     */
    public function processWorksheet(array $files): array
    {
        $allText = [];

        foreach ($files as $file) {
            $text = $this->extractText($file);
            $allText[] = $text;
        }

        $merged = implode("\n", $allText);
        return $this->mapToWorksheetFields($merged);
    }

    /**
     * Extract text from image using Google Cloud Vision API.
     */
    private function extractText(UploadedFile $file): string
    {
        $client = new \Google\Cloud\Vision\V1\ImageAnnotatorClient([
            'credentials' => config('services.google.credentials_path'),
        ]);

        $image = file_get_contents($file->getRealPath());
        $response = $client->documentTextDetection($image);
        $client->close();

        return $response->getFullTextAnnotation()?->getText() ?? '';
    }

    /**
     * Map extracted text to IMS worksheet fields.
     * Uses pattern matching based on the fixed IMS form layout.
     * Fields with low confidence are flagged for manual review.
     */
    private function mapToWorksheetFields(string $text): array
    {
        $lines = array_map('trim', explode("\n", $text));
        $result = [
            'customer'   => ['value' => null, 'confidence' => 'low'],
            'site'       => ['value' => null, 'confidence' => 'low'],
            'date'       => ['value' => null, 'confidence' => 'low'],
            'containers' => [],
            'crew'       => ['value' => [], 'confidence' => 'low'],
            'raw_text'   => $text,
        ];

        // Customer — usually first large text in form
        foreach ($lines as $i => $line) {
            if (preg_match('/CUSTOMER|CLIENT/i', $line) && isset($lines[$i + 1])) {
                $result['customer'] = ['value' => trim($lines[$i + 1]), 'confidence' => 'high'];
            }
            if (preg_match('/SITE/i', $line) && isset($lines[$i + 1])) {
                $result['site'] = ['value' => trim($lines[$i + 1]), 'confidence' => 'high'];
            }
            if (preg_match('/DATE/i', $line) && isset($lines[$i + 1])) {
                $date = $this->parseDate($lines[$i + 1]);
                $result['date'] = ['value' => $date, 'confidence' => $date ? 'high' : 'low'];
            }
            // Container numbers (format: 4 letters + 7 digits)
            if (preg_match('/([A-Z]{4}\s?\d{7})/i', $line, $matches)) {
                $result['containers'][] = [
                    'number'     => strtoupper(str_replace(' ', '', $matches[1])),
                    'confidence' => 'high',
                ];
            }
            // Crew section
            if (preg_match('/CREW/i', $line)) {
                $crewLines = array_slice($lines, $i + 1, 5);
                $crew = array_filter($crewLines, fn($l) => strlen($l) > 2 && !preg_match('/CREW|SIGN/i', $l));
                $result['crew'] = ['value' => array_values($crew), 'confidence' => 'medium'];
            }
        }

        return $result;
    }

    private function parseDate(string $raw): ?string
    {
        // Try common Australian date formats: DD.MM.YY, DD/MM/YYYY
        $raw = trim($raw);
        if (preg_match('/(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})/', $raw, $m)) {
            $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            return "{$year}-{$m[2]}-{$m[1]}";
        }
        return null;
    }
}
