<?php

namespace App\Support\BulkImport;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class DocumentTextExtractor
{
    /**
     * @return array{text: string, extension: string}
     */
    public function extract(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        $text = match ($extension) {
            'txt' => (string) file_get_contents($file->getRealPath()),
            'docx', 'doc' => $this->fromWord($file->getRealPath(), $extension),
            'pdf' => $this->fromPdf($file->getRealPath()),
            default => throw new RuntimeException('Unsupported file type. Upload a .docx, .doc, .pdf, or .txt template.'),
        };

        $normalized = $this->normalize($text);

        if (trim($normalized) === '') {
            throw new RuntimeException('No readable text was found in the uploaded file.');
        }

        return [
            'text' => $normalized,
            'extension' => $extension,
        ];
    }

    private function fromWord(string $path, string $extension): string
    {
        try {
            $reader = $extension === 'doc' ? 'MsDoc' : 'Word2007';
            $phpWord = WordIOFactory::load($path, $reader);
        } catch (Throwable $e) {
            if ($extension === 'doc') {
                throw new RuntimeException(
                    'Could not read this .doc file. Save it as .docx in Microsoft Word and upload again.',
                    0,
                    $e
                );
            }

            throw new RuntimeException('Could not read this Word document. '.$e->getMessage(), 0, $e);
        }

        $parts = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $parts[] = $this->elementText($element);
            }
        }

        return implode("\n", array_filter($parts, fn ($line) => $line !== ''));
    }

    private function fromPdf(string $path): string
    {
        try {
            $pdf = (new PdfParser)->parseFile($path);
        } catch (Throwable $e) {
            throw new RuntimeException('Could not read this PDF. Export as text-based PDF (not a scanned image) and try again.', 0, $e);
        }

        return $pdf->getText() ?? '';
    }

    private function elementText(mixed $element): string
    {
        if (method_exists($element, 'getText')) {
            $text = $element->getText();

            return is_string($text) ? $text : '';
        }

        if (method_exists($element, 'getElements')) {
            $chunks = [];
            foreach ($element->getElements() as $child) {
                $chunks[] = $this->elementText($child);
            }

            return implode(' ', array_filter($chunks));
        }

        return '';
    }

    private function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
