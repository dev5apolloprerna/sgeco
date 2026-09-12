<?php

/**
 * Combine rendered form pages into a Word-compatible HTML document.
 *
 * Word can open HTML delivered with a .doc extension. Keeping the report's
 * existing HTML and CSS also makes the Word layout match its PDF preview more
 * closely than converting the form into spreadsheet cells.
 */
function buildOtherReportWordDocument(array $pages, $title, $pageSelector)
{
    if (!$pages) {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' .
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8') .
            '</title></head><body><p>No Data Found !</p></body></html>';
    }

    $firstPage = (string) $pages[0];
    preg_match('/<style\b[^>]*>(.*?)<\/style>/is', $firstPage, $styleMatch);
    $style = isset($styleMatch[1]) ? $styleMatch[1] : '';
    $bodies = array();

    foreach ($pages as $page) {
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', (string) $page, $bodyMatch)) {
            $bodies[] = $bodyMatch[1];
        }
    }

    $wordStyle = '@page{size:A4;margin:0}' .
        'body{margin:0;padding:0;background:#fff}' .
        // Word displays dotted table gridlines for borderless HTML tables. A
        // white structural border keeps those gridlines hidden while the
        // form's intentional underline and photo-box borders remain visible.
        'table,table td{border:1pt solid #fff;mso-border-alt:solid #fff 1pt}' .
        $pageSelector . '{margin:0 auto;page-break-inside:avoid}';

    // Word's HTML importer does not consistently honour page-break-after on a
    // fixed-height div. Its proprietary line-break marker is reliable and
    // guarantees exactly one employee form on every page.
    $pageBreak = '<br clear="all" style="mso-special-character:line-break;' .
        'page-break-before:always">';

    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' .
        htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><style>' .
        $style . $wordStyle . '</style></head><body>' .
        implode("\n" . $pageBreak . "\n", $bodies) .
        '</body></html>';
}

function outputOtherReportWordDocument($html, $filename)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/msword; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    echo "\xEF\xBB\xBF" . $html;
    exit;
}
