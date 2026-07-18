param(
    [Parameter(Mandatory = $true)][string]$InputDocx,
    [Parameter(Mandatory = $true)][string]$OutputDir
)

$resolvedInput = (Resolve-Path -LiteralPath $InputDocx).Path
$resolvedOutput = [System.IO.Path]::GetFullPath($OutputDir)

if (-not $resolvedOutput.StartsWith('D:\FullStack\DailyCart1\artifacts\document_qa', [System.StringComparison]::OrdinalIgnoreCase)) {
    throw "Output directory must remain inside the DailyCart document QA folder."
}

[System.IO.Directory]::CreateDirectory($resolvedOutput) | Out-Null
$pdfPath = Join-Path $resolvedOutput (([System.IO.Path]::GetFileNameWithoutExtension($resolvedInput)) + '.pdf')

$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($resolvedInput, $false, $true, $false)
    $document.ExportAsFixedFormat($pdfPath, 17)
}
finally {
    if ($null -ne $document) {
        $document.Close($false)
        [System.Runtime.InteropServices.Marshal]::FinalReleaseComObject($document) | Out-Null
    }
    if ($null -ne $word) {
        $word.Quit()
        [System.Runtime.InteropServices.Marshal]::FinalReleaseComObject($word) | Out-Null
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}

Write-Output $pdfPath
