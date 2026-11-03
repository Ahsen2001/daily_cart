$ErrorActionPreference = 'Stop'
$taskDir = 'D:\FullStack\DailyCart1\literature_review_work'
$pythonExe = 'C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe'
$renderer = 'C:\Users\USER\.codex\plugins\cache\openai-primary-runtime\documents\26.1026.10807\skills\documents\render_docx.py'
$documentPath = Join-Path $taskDir 'DailyCart_Final_Literature_Review.docx'
$renderDir = Join-Path $taskDir 'rendered'
New-Item -ItemType Directory -Path $renderDir -Force | Out-Null
& $pythonExe $renderer $documentPath --output_dir $renderDir --emit_pdf
if ($LASTEXITCODE -ne 0) {
    Write-Output 'Packaged renderer unavailable; using Microsoft Word export for document layout verification.'
    $wordApp = New-Object -ComObject Word.Application
    $wordApp.Visible = $false
    $wordApp.DisplayAlerts = 0
    try {
        $wordDoc = $wordApp.Documents.Open($documentPath, $false, $true)
        $wordDoc.Repaginate()
        $pdfPath = Join-Path $renderDir 'DailyCart_Final_Literature_Review.pdf'
        $wordDoc.ExportAsFixedFormat($pdfPath, 17)
        Write-Output ('Word pages: ' + $wordDoc.ComputeStatistics(2))
        $wordDoc.Close(0)
    } finally {
        $wordApp.Quit()
    }
    $poppler = 'C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\native\poppler\Library\bin\pdftoppm.exe'
    & $poppler -r 100 -png $pdfPath (Join-Path $renderDir 'page')
    if ($LASTEXITCODE -ne 0) { throw 'PDF page rendering failed.' }
}
