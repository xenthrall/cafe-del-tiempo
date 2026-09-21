---
paths:
  - 'app-modules/finance/src/Filament/Resources/Movements/Actions/**'
---

# Movements Actions

## A Livewire action must return StreamedResponse/BinaryFileResponse to trigger a download
Livewire only recognizes a file download from an action's return value via `SupportFileDownloads::valueIsntAFileResponse()`, which checks `instanceof StreamedResponse || instanceof BinaryFileResponse` — nothing else. `Barryvdh\DomPDF`'s `Pdf::download()` returns a plain `Illuminate\Http\Response` (content already built into the body), which fails that check. Livewire then tries to serialize the return value as a normal AJAX payload, and `json_encode()` throws "Malformed UTF-8 characters" on the raw PDF binary bytes — a confusing error with no obvious link to the real cause.

Fix: never return a PDF/binary response type directly from an action. Wrap it — `response()->streamDownload(fn () => print($pdf->output()), $filename, ['Content-Type' => 'application/pdf'])` — or write to a file and use `response()->download($path)` (real `BinaryFileResponse`, already correctly recognized — this is why the Excel/xlsx path via `response()->download()` never hit this). See `GenerateMovementsReportAction::generatePdf()`.
